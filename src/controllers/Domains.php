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
class Domains extends BaseController
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

        return Response::ok('domains/index.phtml', [
            'domains' => models\Domain::listAllOrderById(),
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

        return Response::ok('domains/new.phtml', [
            'form' => new forms\Domain(),
        ]);
    }

    /**
     * @request_param string id
     *     Must be a valid domain name.
     * @request_param string csrf_token
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 400
     *     If the id or the CSRF are invalid, or if the id already exists.
     * @response 302 /domains/:id
     *     On success.
     */
    public function create(Request $request): Response
    {
        auth\CurrentUser::require();

        $domain = new models\Domain();
        $form = new forms\Domain(model: $domain);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('domains/new.phtml', [
                'form' => $form,
            ]);
        }

        $domain = $form->model();
        $domain->save();

        return Response::redirect('show domain', [
            'id' => $domain->id,
        ]);
    }

    /**
     * @request_param string id
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the domain doesn't exist.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
    {
        auth\CurrentUser::require();

        $domain = models\Domain::requireFromRequest($request);

        $alarms = models\Alarm::listByDomainIdOrderByDescCreatedAt($domain->id);
        $last_heartbeat = models\Heartbeat::findLastHeartbeatByDomainId($domain->id);
        $last_heartbeat_at = $last_heartbeat->created_at ?? null;

        return Response::ok('domains/show.phtml', [
            'domain' => $domain,
            'last_heartbeat_at' => $last_heartbeat_at,
            'alarms' => $alarms,
        ]);
    }

    /**
     * @request_param string id
     * @request_param string csrf_token
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 302 /domains/:id
     *     If the CSRF is invalid.
     * @response 404
     *     If the domain doesn't exist.
     * @response 302 /
     *     On success.
     */
    public function delete(Request $request): Response
    {
        auth\CurrentUser::require();

        $domain = models\Domain::requireFromRequest($request);

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::redirect('show domain', ['id' => $domain->id]);
        }

        $domain->remove();

        return Response::redirect('home');
    }
}
