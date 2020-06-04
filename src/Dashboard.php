<?php

namespace taust;

use Minz\Response;

class Dashboard
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $domain_dao = new models\dao\Domain();
        $server_dao = new models\dao\Server();
        $alarm_dao = new models\dao\Alarm();

        $db_domains = $domain_dao->listAll();
        $domains_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($db_domains as $db_domain) {
            $domain = new models\Domain($db_domain);
            $domains_by_status[$domain->status()][] = $domain;
        }

        $db_servers = $server_dao->listAll();
        $servers_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($db_servers as $db_server) {
            $server = new models\Server($db_server);
            $servers_by_status[$server->status()][] = $server;
        }

        $ongoing_alarms = $alarm_dao->listOngoingOrderByDescCreatedAt();

        $number_errors = count($domains_by_status['unknown']) + count($domains_by_status['down'])
                       + count($servers_by_status['unknown']) + count($servers_by_status['down'])
                       + count($ongoing_alarms);

        return Response::ok('dashboard/index.phtml', [
            'domains_by_status' => $domains_by_status,
            'servers_by_status' => $servers_by_status,
            'ongoing_alarms' => $ongoing_alarms,
            'all_good' => $number_errors === 0,
        ]);
    }
}
