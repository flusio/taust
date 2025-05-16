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
class Pages extends BaseController
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function index(): Response
    {
        $this->requireCurrentUser();

        return Response::ok('pages/index.phtml', [
            'pages' => models\Page::listAllOrderByTitle(),
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
        $this->requireCurrentUser();

        return Response::ok('pages/new.phtml', [
            'form' => new forms\NewPage(),
        ]);
    }

    /**
     * @request_param string title
     * @request_param string csrf_token
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
        $this->requireCurrentUser();

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
        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);
        $announcement = new models\Announcement($page);

        return Response::ok('pages/show.phtml', [
            'page' => $page,
            'announcement_form' => new forms\Announcement(model: $announcement),
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
        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);

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
        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);

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
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);

        return Response::ok('pages/edit.phtml', [
            'page' => $page,
            'form' => new forms\Page(model: $page),
        ]);
    }

    /**
     * @request_param string id
     * @request_param string[] domain_ids
     * @request_param string[] server_ids
     * @request_param string hostname
     * @request_param string style
     * @request_param string locale
     * @request_param string csrf_token
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
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);

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
     * @request_param string csrf_token
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
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $page = models\Page::require($id);

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::redirect('edit page', ['id' => $page->id]);
        }

        $page->remove();

        return Response::redirect('pages');
    }
}
