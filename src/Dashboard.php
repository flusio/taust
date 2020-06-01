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

        $db_domains = $domain_dao->listAll();
        $domains = [];
        foreach ($db_domains as $db_domain) {
            $domains[] = new models\Domain($db_domain);
        }

        $db_servers = $server_dao->listAll();
        $servers = [];
        foreach ($db_servers as $db_server) {
            $servers[] = new models\Server($db_server);
        }

        return Response::ok('dashboard/index.phtml', [
            'domains' => $domains,
            'servers' => $servers,
        ]);
    }
}
