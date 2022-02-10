<?php

namespace taust\cli;

use Minz\Response;
use taust\mailers;
use taust\models;
use taust\services;

class Alarms
{
    public function monitor($request)
    {
        $domains = models\Domain::listAll();
        $servers = models\Server::listAll();

        $results = [];

        foreach ($domains as $domain) {
            $heartbeat = models\Heartbeat::daoToModel('findLastHeartbeatByDomainId', $domain->id);
            if (!$heartbeat) {
                continue;
            }

            $alarm = models\Alarm::daoToModel('findOngoingByDomainId', $domain->id);
            if ($alarm) {
                if ($heartbeat->is_success) {
                    $alarm->finish();
                    $alarm->save();
                    $details = 'Alarm finished';
                } else {
                    $details = 'Alarm not finished';
                }
            } else {
                if (!$heartbeat->is_success) {
                    $alarm = models\Alarm::initFromHeartbeat($heartbeat);
                    $alarm->save();
                    $details = 'New alarm!';
                } else {
                    $details = 'All good';
                }
            }

            $results[] = "Domain {$domain->id}: {$details}";
        }

        foreach ($servers as $server) {
            $metric = models\Metric::daoToModel('findLastByServerId', $server->id);
            if (!$metric) {
                continue;
            }

            $alarm = models\Alarm::daoToModel('findOngoingByServerIdAndType', $server->id, 'status');
            $is_down = $metric->created_at <= \Minz\Time::ago(1, 'minutes');
            if ($alarm) {
                if (!$is_down) {
                    $alarm->finish();
                    $alarm->save();
                    $details_status = 'Alarm finished';
                } else {
                    $details_status = 'Alarm not finished';
                }
            } else {
                if ($is_down) {
                    $alarm = models\Alarm::initForServer(
                        $server->id,
                        'status',
                        'The server sent no metrics for more than a minute.'
                    );
                    $alarm->save();
                    $details_status = 'New alarm!';
                } else {
                    $details_status = 'All good';
                }
            }

            $results[] = "{$server->hostname} status: {$details_status}";

            if ($is_down) {
                // Do not test an old metric, the rest is probably not accurate
                continue;
            }

            $alarm = models\Alarm::daoToModel('findOngoingByServerIdAndType', $server->id, 'cpu_usage');
            $cpu_percents = $metric->cpuPercents();
            $cpu_average = array_sum($cpu_percents) / count($cpu_percents);
            if ($alarm) {
                if ($cpu_average < 90) {
                    $alarm->finish();
                    $alarm->save();
                    $details_cpu = 'Alarm finished';
                } else {
                    $details_cpu = 'Alarm not finished';
                }
            } else {
                if ($cpu_average >= 90) {
                    $alarm = models\Alarm::initForServer(
                        $server->id,
                        'cpu_usage',
                        "CPU average usage is more than 90% of its capacity ({$cpu_average} %)."
                    );
                    $alarm->save();
                    $details_cpu = 'New alarm!';
                } else {
                    $details_cpu = 'All good';
                }
            }

            $results[] = "{$server->hostname} CPU: {$details_cpu}";

            $alarm = models\Alarm::daoToModel('findOngoingByServerIdAndType', $server->id, 'memory_usage');
            $memory_used_percent = $metric->memoryUsedPercent();
            if ($alarm) {
                if ($memory_used_percent < 90) {
                    $alarm->finish();
                    $alarm->save();
                    $details_memory = 'Alarm finished';
                } else {
                    $details_memory = 'Alarm not finished';
                }
            } else {
                if ($memory_used_percent >= 90) {
                    $alarm = models\Alarm::initForServer(
                        $server->id,
                        'memory_usage',
                        "Memory usage is more than 90% of its capacity ({$memory_used_percent} %)."
                    );
                    $alarm->save();
                    $details_memory = 'New alarm!';
                } else {
                    $details_memory = 'All good';
                }
            }

            $results[] = "{$server->hostname} memory: {$details_memory}";

            foreach ($metric->disks() as $disk) {
                $type = 'disk_usage:' . $disk->name;
                $alarm = models\Alarm::daoToModel('findOngoingByServerIdAndType', $server->id, $type);
                $disk_used_percent = $metric->diskUsedPercent($disk);
                if ($alarm) {
                    if ($disk_used_percent < 80) {
                        $alarm->finish();
                        $alarm->save();
                        $details_disk = 'Alarm finished';
                    } else {
                        $details_disk = 'Alarm not finished';
                    }
                } else {
                    if ($disk_used_percent >= 80) {
                        $alarm = models\Alarm::initForServer(
                            $server->id,
                            $type,
                            "Disk {$disk->name} usage is more than 80% of its capacity ({$disk_used_percent} %)."
                        );
                        $alarm->save();
                        $details_disk = 'New alarm!';
                    } else {
                        $details_disk = 'All good';
                    }
                }

                $results[] = "{$server->hostname} {$disk->name} disk: {$details_disk}";
            }
        }

        return Response::text(200, implode("\n", $results));
    }

    public function notify($request)
    {
        $alarms_mailer = new mailers\Alarms();
        $free_mobile_service = new services\FreeMobile();

        $users = models\User::listAll();
        $alarms = models\Alarm::daoToList('listToNotify');
        foreach ($alarms as $alarm) {
            foreach ($users as $user) {
                if ($user->email) {
                    $alarms_mailer->sendAlarm($user->email, $alarm);
                }

                if ($user->free_mobile_login && $user->free_mobile_key) {
                    $free_mobile_service->sendAlarm(
                        $user->free_mobile_login,
                        $user->free_mobile_key,
                        $alarm
                    );
                }
            }

            $alarm->notify();
            $alarm->save();
        }

        $alarms_count = count($alarms);
        return Response::text(200, "{$alarms_count} alarms have been notified.");
    }
}
