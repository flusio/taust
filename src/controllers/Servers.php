<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\auth;
use taust\forms;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Servers extends BaseController
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function index(): Response
    {
        auth\CurrentUser::require();

        return Response::ok('servers/index.phtml', [
            'servers' => models\Server::listAllOrderById(),
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
        auth\CurrentUser::require();

        return Response::ok('servers/new.phtml', [
            'form' => new forms\Server(),
        ]);
    }

    /**
     * @request_param string hostname
     * @request_param string csrf_token
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
        auth\CurrentUser::require();

        $server = new models\Server();
        $form = new forms\Server(model: $server);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('servers/new.phtml', [
                'form' => $form,
            ]);
        }

        $server = $form->model();
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
        auth\CurrentUser::require();

        $server = models\Server::requireFromRequest($request);

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
     * @request_param string csrf_token
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
        auth\CurrentUser::require();

        $server = models\Server::requireFromRequest($request);

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::redirect('show server', ['id' => $server->id]);
        }

        $server->remove();

        return Response::redirect('home');
    }
}
