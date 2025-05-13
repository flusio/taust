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
class Pages
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

        $pages = models\Page::listAllOrderByTitle();

        return Response::ok('pages/index.phtml', [
            'pages' => $pages,
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

        return Response::ok('pages/new.phtml', [
            'title' => '',
        ]);
    }

    /**
     * @request_param string title
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 400
     *     If one of the parameters is invalid.
     * @response 302 /pages/:id/edit
     *     On success.
     */
    public function create(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $title = $request->param('title', '');
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('pages/new.phtml', [
                'title' => $title,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $page = new models\Page($title);
        $errors = $page->validate();
        if ($errors) {
            return Response::badRequest('pages/new.phtml', [
                'title' => $title,
                'errors' => $errors,
            ]);
        }

        $page->save();

        return Response::redirect('edit page', ['id' => $page->id]);
    }

    /**
     * @request_param string id
     *
     * @response 404
     *     If the page doesn't exist.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
    {
        $id = $request->param('id', '');

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        return Response::ok('pages/show.phtml', [
            'page' => $page,
            'domains' => $page->domains(),
            'servers' => $page->servers(),
            'announcements_by_days' => $page->weekAnnouncements(),
            'type' => 'maintenance',
            'planned_at' => \Minz\Time::now(),
            'title' => '',
            'content' => '',
        ]);
    }

    /**
     * @request_param string id
     *
     * @response 404
     *     If the server doesn't exist.
     * @response 200
     *     On success.
     */
    public function feed(Request $request): Response
    {
        $id = $request->param('id', '');

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        return Response::ok('pages/feed.atom.xml.phtml', [
            'page' => $page,
            'announcements' => $page->announcements(),
        ]);
    }

    /**
     * @request_param string id
     *
     * @response 404
     *     If the server doesn't exist.
     * @response 200
     *     On success.
     */
    public function style(Request $request): Response
    {
        $id = $request->param('id', '');

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        $response = Response::text(200, $page->style);
        $response->setHeader('Content-Type', 'text/css');
        return $response;
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
    public function edit(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id', '');

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        $servers = models\Server::listAllOrderById();
        $domains = models\Domain::listAllOrderById();

        return Response::ok('pages/edit.phtml', [
            'page' => $page,
            'domain_ids' => array_column($page->domains(), 'id'),
            'server_ids' => array_column($page->servers(), 'id'),
            'servers' => $servers,
            'domains' => $domains,
            'hostname' => $page->hostname,
            'style' => $page->style,
            'locale' => $page->locale,
        ]);
    }

    /**
     * @request_param string id
     * @request_param string[] domain_ids
     * @request_param string[] server_ids
     * @request_param string hostname
     * @request_param string style
     * @request_param string locale
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the server doesn't exist.
     * @response 400
     *     If one of the parameters is invalid.
     * @response 302 /pages/:id/edit
     *     On success.
     */
    public function update(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id', '');
        $csrf = $request->param('csrf', '');
        /** @var string[] */
        $domain_ids = $request->paramArray('domain_ids', []);
        /** @var string[] */
        $server_ids = $request->paramArray('server_ids', []);
        $hostname = $request->param('hostname', '');
        $style = $request->param('style', '');
        $locale = $request->param('locale', '');

        $page = models\Page::find($id);
        $servers = models\Server::listAllOrderById();
        $domains = models\Domain::listAllOrderById();

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('pages/edit.phtml', [
                'page' => $page,
                'domain_ids' => $domain_ids,
                'server_ids' => $server_ids,
                'servers' => $servers,
                'domains' => $domains,
                'hostname' => $hostname,
                'style' => $style,
                'locale' => $locale,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $existing_page = models\Page::findBy([
            'hostname' => $hostname,
        ]);
        if ($existing_page && $existing_page->id !== $page->id && $hostname !== '') {
            return Response::badRequest('pages/edit.phtml', [
                'page' => $page,
                'domain_ids' => $domain_ids,
                'server_ids' => $server_ids,
                'servers' => $servers,
                'domains' => $domains,
                'hostname' => $hostname,
                'style' => $style,
                'locale' => $locale,
                'errors' => [
                    'hostname' => _('A page already has the same hostname.'),
                ],
            ]);
        }

        $page->hostname = $hostname;
        $page->style = $style;
        $page->locale = $locale;
        $page->save();

        models\PageToDomain::set($page->id, $domain_ids);
        models\PageToServer::set($page->id, $server_ids);

        return Response::redirect('edit page', ['id' => $page->id]);
    }

    /**
     * @request_param string id
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 302 /pages/:id/edit
     *     If the CSRF is invalid.
     * @response 404
     *     If the server doesn't exist.
     * @response 302 /pages
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
            return Response::redirect('edit server', ['id' => $id]);
        }

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        models\Page::delete($page->id);

        return Response::redirect('pages');
    }
}
