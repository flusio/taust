<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\models;
use taust\utils;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Servers
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

        $servers = models\Server::listAllOrderById();
        return Response::ok('servers/index.phtml', [
            'servers' => $servers,
        ]);
    }

    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function new(): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('servers/new.phtml', [
            'hostname' => '',
        ]);
    }

    /**
     * @request_param string hostname
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 400
     *     If one of the parameters is invalid.
     * @response 302 /servers/:id
     *     On success.
     */
    public function create(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $hostname = $request->param('hostname', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('servers/new.phtml', [
                'hostname' => $hostname,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $server = new models\Server($hostname);
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

    /**
     * @request_param string id
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the server doesn't exist.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        $server = models\Server::find($id);

        if (!$server) {
            return Response::notFound('not_found.phtml');
        }

        $alarms = models\Alarm::listByServerIdOrderByDescCreatedAt($server->id);
        $metric = models\Metric::findLastByServerId($server->id);

        return Response::ok('servers/show.phtml', [
            'server' => $server,
            'metric' => $metric,
            'alarms' => $alarms,
        ]);
    }

    /**
     * @request_param string id
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the server doesn't exist.
     * @response 302 /servers/:id
     *     If the CSRF is invalid.
     * @response 302 /
     *     On success.
     */
    public function delete(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
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
