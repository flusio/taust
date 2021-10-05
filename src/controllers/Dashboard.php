<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Dashboard
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $number_domains = models\Domain::count();
        $number_servers = models\Server::count();
        $no_setup = ($number_domains + $number_servers) === 0;

        $domains = models\Domain::listAll();
        $domains_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($domains as $domain) {
            $domains_by_status[$domain->status()][] = $domain;
        }

        $servers = models\Server::listAll();
        $servers_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($servers as $server) {
            $servers_by_status[$server->status()][] = $server;
        }

        $ongoing_alarms = models\Alarm::daoToList('listOngoingOrderByDescCreatedAt');

        $number_errors = count($domains_by_status['unknown']) + count($domains_by_status['down'])
                       + count($servers_by_status['unknown']) + count($servers_by_status['down'])
                       + count($ongoing_alarms);

        return Response::ok('dashboard/index.phtml', [
            'domains_by_status' => $domains_by_status,
            'servers_by_status' => $servers_by_status,
            'ongoing_alarms' => $ongoing_alarms,
            'all_good' => $number_errors === 0,
            'no_setup' => $no_setup,
        ]);
    }
}
