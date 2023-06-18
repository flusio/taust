<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Dashboard
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function index(): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $domains = models\Domain::listAll();
        $number_domains = count($domains);
        $domains_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($domains as $domain) {
            $domains_by_status[$domain->status()][] = $domain;
        }

        $servers = models\Server::listAll();
        $number_servers = count($servers);
        $servers_by_status = [
            'unknown' => [],
            'up' => [],
            'down' => [],
        ];
        foreach ($servers as $server) {
            $servers_by_status[$server->status()][] = $server;
        }

        $ongoing_alarms = models\Alarm::listOngoingOrderByDescCreatedAt();

        $number_errors = count($domains_by_status['unknown']) + count($domains_by_status['down'])
                       + count($servers_by_status['unknown']) + count($servers_by_status['down'])
                       + count($ongoing_alarms);

        $no_setup = ($number_domains + $number_servers) === 0;

        return Response::ok('dashboard/index.phtml', [
            'domains_by_status' => $domains_by_status,
            'servers_by_status' => $servers_by_status,
            'ongoing_alarms' => $ongoing_alarms,
            'all_good' => $number_errors === 0,
            'no_setup' => $no_setup,
        ]);
    }
}
