<?php

namespace taust;

use Minz\Response;

class Servers
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $server_dao = new models\dao\Server();

        $db_servers = $server_dao->listAllOrderById();
        $servers = [];
        foreach ($db_servers as $db_server) {
            $servers[] = new models\Server($db_server);
        }

        return Response::ok('servers/index.phtml', [
            'servers' => $servers,
        ]);
    }

    public function new()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('servers/new.phtml', [
            'hostname' => '',
        ]);
    }

    public function create($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $hostname = $request->param('hostname');
        $csrf = new \Minz\CSRF();

        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::badRequest('servers/new.phtml', [
                'hostname' => $hostname,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $server = models\Server::init($hostname);
        $errors = $server->validate();
        if ($errors) {
            return Response::badRequest('servers/new.phtml', [
                'hostname' => $hostname,
                'errors' => $errors,
            ]);
        }

        $server_dao = new models\dao\Server();
        $server_dao->save($server);

        return Response::redirect('show server', [
            'id' => $server->id,
        ]);
    }

    public function show($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $server_dao = new models\dao\Server();
        $metric_dao = new models\dao\Metric();
        $alarm_dao = new models\dao\Alarm();

        $id = $request->param('id');
        $db_server = $server_dao->find($id);
        if (!$db_server) {
            return Response::notFound('not_found.phtml');
        }

        $server = new models\Server($db_server);
        $alarms = $alarm_dao->listBy([
            'server_id' => $server->id
        ]);
        $db_metric = $metric_dao->findLastByServerId($server->id);
        $metric = null;
        if ($db_metric) {
            $metric = new models\Metric($db_metric);
        }

        $response = Response::ok('servers/show.phtml', [
            'server' => $server,
            'metric' => $metric,
            'alarms' => $alarms,
        ]);
        $response->setContentSecurityPolicy('style-src', "'self' 'unsafe-inline'");
        return $response;
    }

    public function delete($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = new \Minz\CSRF();

        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::redirect('show server', ['id' => $id]);
        }

        $server_dao = new models\dao\Server();
        $db_server = $server_dao->find($id);
        if (!$db_server) {
            return Response::notFound('not_found.phtml');
        }

        $server_dao->delete($db_server['id']);

        return Response::redirect('home');
    }
}
