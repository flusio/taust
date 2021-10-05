<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Domains
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $domains = models\Domain::daoToList('listAllOrderById');
        return Response::ok('domains/index.phtml', [
            'domains' => $domains,
        ]);
    }

    public function new()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('domains/new.phtml', [
            'id' => '',
        ]);
    }

    public function create($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $domain = models\Domain::init($id);
        $errors = $domain->validate();
        if ($errors) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'errors' => $errors,
            ]);
        }

        $exist = models\Domain::exists($domain->id);
        if ($exist) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'errors' => [
                    'id' => _('This domain already exists.'),
                ],
            ]);
        }

        $domain->save();

        return Response::redirect('show domain', [
            'id' => $domain->id,
        ]);
    }

    public function show($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $domain = models\Domain::find($id);
        if (!$domain) {
            return Response::notFound('not_found.phtml');
        }

        $alarms = models\Alarm::daoToList('listByDomainIdOrderByDescCreatedAt', $domain->id);
        $last_heartbeat = models\Heartbeat::daoToModel('findLastHeartbeatByDomainId', $domain->id);
        $last_heartbeat_at = null;
        if ($last_heartbeat) {
            $last_heartbeat_at = $last_heartbeat->created_at;
        }

        return Response::ok('domains/show.phtml', [
            'domain' => $domain,
            'last_heartbeat_at' => $last_heartbeat_at,
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
            return Response::redirect('show domain', ['id' => $id]);
        }

        $domain = models\Domain::find($id);
        if (!$domain) {
            return Response::notFound('not_found.phtml');
        }

        models\Domain::delete($domain->id);

        return Response::redirect('home');
    }
}
