<?php

namespace taust;

use Minz\Response;

class Alarms
{
    public function monitor($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $domain_dao = new models\dao\Domain();
        $server_dao = new models\dao\Server();
        $heartbeat_dao = new models\dao\Heartbeat();
        $metric_dao = new models\dao\Metric();
        $alarm_dao = new models\dao\Alarm();

        $db_domains = $domain_dao->listAll();
        $db_servers = $server_dao->listAll();

        $results = [];

        foreach ($db_domains as $db_domain) {
            $domain_id = $db_domain['id'];

            $heartbeat = $heartbeat_dao->findLastHeartbeatByDomainId($domain_id);
            if (!$heartbeat) {
                continue;
            }

            $alarm = $alarm_dao->findOngoingByDomainId($domain_id);
            if ($alarm) {
                if ($heartbeat['is_success']) {
                    $details = 'Alarm finished';
                    $alarm_dao->update($alarm['id'], [
                        'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                    ]);
                } else {
                    $details = 'Alarm not finished';
                }
            } else {
                if (!$heartbeat['is_success']) {
                    $details = 'New alarm!';
                    $alarm_dao->create([
                        'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        'type' => 'heartbeat',
                        'domain_id' => $domain_id,
                        'details' => $heartbeat['details'],
                    ]);
                } else {
                    $details = 'All good';
                }
            }

            $results[] = "Domain {$domain_id}: {$details}";
        }

        foreach ($db_servers as $db_server) {
            $server_id = $db_server['id'];

            $db_metric = $metric_dao->findLastByServerId($server_id);
            if (!$db_metric) {
                continue;
            }

            $metric = new models\Metric($db_metric);

            $alarm = $alarm_dao->findOngoingByServerIdAndType($server_id, 'status');
            $is_down = $metric->created_at <= \Minz\Time::ago(1, 'minutes');
            if ($alarm) {
                if (!$is_down) {
                    $details_status = 'Alarm finished';
                    $alarm_dao->update($alarm['id'], [
                        'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                    ]);
                } else {
                    $details_status = 'Alarm not finished';
                }
            } else {
                if ($is_down) {
                    $details_status = 'New alarm!';
                    $alarm_dao->create([
                        'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        'type' => 'status',
                        'server_id' => $server_id,
                        'details' => 'The server didn’t sent any metrics for more than a minute.',
                    ]);
                } else {
                    $details_status = 'All good';
                }
            }

            $results[] = "{$db_server['hostname']} status: {$details_status}";

            $alarm = $alarm_dao->findOngoingByServerIdAndType($server_id, 'cpu_usage');
            $cpu_percents = $metric->cpuPercents();
            $cpu_average = array_sum($cpu_percents) / count($cpu_percents);
            if ($alarm) {
                if ($cpu_average < 80) {
                    $details_cpu = 'Alarm finished';
                    $alarm_dao->update($alarm['id'], [
                        'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                    ]);
                } else {
                    $details_cpu = 'Alarm not finished';
                }
            } else {
                if ($cpu_average >= 80) {
                    $details_cpu = 'New alarm!';
                    $alarm_dao->create([
                        'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        'type' => 'cpu_usage',
                        'server_id' => $server_id,
                        'details' => "CPU average usage is more than 80% of its capacity ({$cpu_average} %).",
                    ]);
                } else {
                    $details_cpu = 'All good';
                }
            }

            $results[] = "{$db_server['hostname']} CPU: {$details_cpu}";

            $alarm = $alarm_dao->findOngoingByServerIdAndType($server_id, 'memory_usage');
            $memory_used_percent = $metric->memoryUsedPercent();
            if ($alarm) {
                if ($memory_used_percent < 80) {
                    $details_memory = 'Alarm finished';
                    $alarm_dao->update($alarm['id'], [
                        'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                    ]);
                } else {
                    $details_memory = 'Alarm not finished';
                }
            } else {
                if ($memory_used_percent >= 80) {
                    $details_memory = 'New alarm!';
                    $alarm_dao->create([
                        'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        'type' => 'memory_usage',
                        'server_id' => $server_id,
                        'details' => "Memory usage is more than 80% of its capacity ({$memory_used_percent} %).",
                    ]);
                } else {
                    $details_memory = 'All good';
                }
            }

            $results[] = "{$db_server['hostname']} memory: {$details_memory}";

            foreach ($metric->disks() as $disk) {
                $type = 'disk_usage:' . $disk->name;
                $alarm = $alarm_dao->findOngoingByServerIdAndType($server_id, $type);
                $disk_used_percent = $metric->diskUsedPercent($disk);
                if ($alarm) {
                    if ($disk_used_percent < 80) {
                        $details_disk = 'Alarm finished';
                        $alarm_dao->update($alarm['id'], [
                            'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        ]);
                    } else {
                        $details_disk = 'Alarm not finished';
                    }
                } else {
                    if ($disk_used_percent >= 80) {
                        $details_disk = 'New alarm!';
                        $alarm_dao->create([
                            'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                            'type' => $type,
                            'server_id' => $server_id,
                            'details' => "Disk {$disk->name} usage is more than 80% of its capacity ({$disk_used_percent} %).",
                        ]);
                    } else {
                        $details_disk = 'All good';
                    }
                }

                $results[] = "{$db_server['hostname']} {$disk->name} disk: {$details_disk}";
            }
        }

        return Response::text(200, implode("\n", $results));
    }

    public function notify($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $user_dao = new models\dao\User();
        $alarm_dao = new models\dao\Alarm();

        $alarms_mailer = new mailers\Alarms();
        $free_mobile_service = new services\FreeMobile();

        $db_users = $user_dao->listAll();
        $alarms = $alarm_dao->listOngoingAndNotNotified();
        foreach ($alarms as $alarm) {
            foreach ($db_users as $db_user) {
                if ($db_user['email']) {
                    $alarms_mailer->sendAlarm($db_user['email'], $alarm);
                }

                if ($db_user['free_mobile_login'] && $db_user['free_mobile_key']) {
                    $free_mobile_service->sendAlarm(
                        $db_user['free_mobile_login'],
                        $db_user['free_mobile_key'],
                        $alarm
                    );
                }
            }

            $alarm_dao->update($alarm['id'], [
                'notified_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
            ]);
        }

        $alarms_count = count($alarms);
        return Response::text(200, "{$alarms_count} alarms have been notified.");
    }
}
