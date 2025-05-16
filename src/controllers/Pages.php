<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\forms;
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
            'form' => new forms\NewPage(),
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

        $page = new models\Page();

        $form = new forms\NewPage(model: $page);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('pages/new.phtml', [
                'form' => $form,
            ]);
        }

        $page = $form->model();
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

        $form = new forms\Page([
            'domain_ids' => array_column($page->domains(), 'id'),
            'server_ids' => array_column($page->servers(), 'id'),
        ], model: $page);

        return Response::ok('pages/edit.phtml', [
            'page' => $page,
            'form' => $form,
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
        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        $form = new forms\Page(model: $page);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('pages/edit.phtml', [
                'page' => $page,
                'form' => $form,
            ]);
        }

        $page = $form->model();
        $page->save();

        models\PageToDomain::set($page->id, $form->domain_ids);
        models\PageToServer::set($page->id, $form->server_ids);

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
