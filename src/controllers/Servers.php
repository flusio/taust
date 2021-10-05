<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Servers
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $servers = models\Server::daoToList('listAllOrderById');
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
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
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

        $server->save();

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

        $id = $request->param('id');
        $server = models\Server::find($id);
        if (!$server) {
            return Response::notFound('not_found.phtml');
        }

        $alarms = models\Alarm::daoToList('listByServerIdOrderByDescCreatedAt', $server->id);
        $metric = models\Metric::daoToModel('findLastByServerId', $server->id);

        return Response::ok('servers/show.phtml', [
            'server' => $server,
            'metric' => $metric,
            'alarms' => $alarms,
        ]);
    }

    public function delete($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::redirect('show server', ['id' => $id]);
        }

        $server = models\Server::find($id);
        if (!$server) {
            return Response::notFound('not_found.phtml');
        }

        models\Server::delete($server->id);

        return Response::redirect('home');
    }
}
