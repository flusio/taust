<?php

namespace taust\jobs;

use Minz\Job;
use taust\models;
use taust\services;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class CheckAlarms extends Job
{
    public static function install(): void
    {
        $job = new self();
        if (!self::existsBy(['name' => $job->name])) {
            $perform_at = \Minz\Time::now();
            $job->performLater($perform_at);
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->frequency = '+5 seconds';
    }

    /**
     * Verify the alarms that need to be raised or to be stopep.
     */
    public function perform(): void
    {
        $domains = models\Domain::listAll();
        $servers = models\Server::listAll();

        $results = [];

        foreach ($domains as $domain) {
            $heartbeat = models\Heartbeat::findLastHeartbeatByDomainId($domain->id);
            if (!$heartbeat) {
                continue;
            }

            $alarm = models\Alarm::findOngoingByDomainId($domain->id);
            if ($alarm && $heartbeat->is_success) {
                $alarm->finish();
                $alarm->save();

                \Minz\Log::notice("Domain {$domain->id}: alarm finished");
            } elseif (!$alarm && !$heartbeat->is_success) {
                $alarm = models\Alarm::initFromHeartbeat($heartbeat);
                $alarm->save();

                \Minz\Log::warning("Domain {$domain->id}: new alarm");
            }
        }

        foreach ($servers as $server) {
            $metric = models\Metric::findLastByServerId($server->id);
            if (!$metric) {
                continue;
            }

            $alarm = models\Alarm::findOngoingByServerIdAndType($server->id, 'status');
            $is_down = $metric->created_at <= \Minz\Time::ago(1, 'minutes');
            if ($alarm && !$is_down) {
                $alarm->finish();
                $alarm->save();

                \Minz\Log::notice("{$server->hostname} status: alarm finished");
            } elseif (!$alarm && $is_down) {
                $alarm = models\Alarm::initForServer(
                    $server->id,
                    'status',
                    'The server sent no metrics for more than a minute.'
                );
                $alarm->save();

                \Minz\Log::warning("{$server->hostname} status: new alarm");
            }

            if ($is_down) {
                // Do not test an old metric, the rest is probably not accurate
                continue;
            }

            $alarm = models\Alarm::findOngoingByServerIdAndType($server->id, 'cpu_usage');
            $cpu_percents = $metric->cpuPercents();
            $cpu_average = array_sum($cpu_percents) / count($cpu_percents);
            if ($alarm && $cpu_average < 90) {
                $alarm->finish();
                $alarm->save();

                \Minz\Log::warning("{$server->hostname} CPU: alarm finished");
            } elseif (!$alarm && $cpu_average >= 90) {
                $alarm = models\Alarm::initForServer(
                    $server->id,
                    'cpu_usage',
                    "CPU average usage is more than 90% of its capacity ({$cpu_average} %)."
                );
                $alarm->save();

                \Minz\Log::warning("{$server->hostname} CPU: new alarm");
            }

            $alarm = models\Alarm::findOngoingByServerIdAndType($server->id, 'memory_usage');
            $memory_used_percent = $metric->memoryUsedPercent();
            if ($alarm && $memory_used_percent < 90) {
                $alarm->finish();
                $alarm->save();

                \Minz\Log::notice("{$server->hostname} memory: alarm finished");
            } elseif (!$alarm && $memory_used_percent >= 90) {
                $alarm = models\Alarm::initForServer(
                    $server->id,
                    'memory_usage',
                    "Memory usage is more than 90% of its capacity ({$memory_used_percent} %)."
                );
                $alarm->save();

                \Minz\Log::warning("{$server->hostname} memory: new alarm");
            }

            foreach ($metric->disks() as $disk) {
                $type = 'disk_usage:' . $disk['name'];
                $alarm = models\Alarm::findOngoingByServerIdAndType($server->id, $type);
                $disk_used_percent = $metric->diskUsedPercent($disk);
                if ($alarm && $disk_used_percent < 80) {
                    $alarm->finish();
                    $alarm->save();

                    \Minz\Log::notice("{$server->hostname} {$disk['name']} disk: alarm finished");
                } elseif (!$alarm && $disk_used_percent >= 80) {
                    $alarm = models\Alarm::initForServer(
                        $server->id,
                        $type,
                        "Disk {$disk['name']} usage is more than 80% of its capacity ({$disk_used_percent} %)."
                    );
                    $alarm->save();

                    \Minz\Log::warning("{$server->hostname} {$disk['name']} disk: new alarm");
                }
            }
        }
    }
}
