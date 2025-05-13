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
class Domains
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

        $domains = models\Domain::listAllOrderById();
        return Response::ok('domains/index.phtml', [
            'domains' => $domains,
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

        return Response::ok('domains/new.phtml', [
            'id' => '',
        ]);
    }

    /**
     * @request_param string id
     *     Must be a valid domain name.
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id', '');
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $domain = new models\Domain($id);
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id', '');

        $domain = models\Domain::find($id);

        if (!$domain) {
            return Response::notFound('not_found.phtml');
        }

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
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id', '');
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
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
