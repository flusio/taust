<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\forms;
use taust\models;
use taust\utils;

class Announcements extends BaseController
{
    /**
     * @request_param string id
     *
     * @response 404
     *     If the announcement doesn't exist.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
    {
        $id = $request->parameters->getString('id', '');
        $announcement = models\Announcement::require($id);

        return Response::ok('announcements/show.phtml', [
            'announcement' => $announcement,
        ]);
    }

    /**
     * @request_param string id
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the announcement doesn't exist.
     * @response 200
     *     On success.
     */
    public function edit(Request $request): Response
    {
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $announcement = models\Announcement::require($id);

        return Response::ok('announcements/edit.phtml', [
            'announcement' => $announcement,
            'form' => new forms\Announcement(model: $announcement),
        ]);
    }

    /**
     * @request_param string id
     * @request_param string type
     * @request_param string planned_at
     *     Format: Y-m-d\TH:i
     * @request_param string title
     * @request_param string content
     * @request_param string csrf_token
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the announcement doesn't exist.
     * @response 400
     *     If one of the parameter is invalid.
     * @response 302 /a/:id
     *     On success.
     */
    public function update(Request $request): Response
    {
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $announcement = models\Announcement::require($id);

        $form = new forms\Announcement(model: $announcement);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('announcements/edit.phtml', [
                'announcement' => $announcement,
                'form' => $form,
            ]);
        }

        $announcement = $form->model();
        $announcement->save();

        return Response::redirect('show announcement', ['id' => $announcement->id]);
    }

    /**
     * @request_param string id
     * @request_param string status
     * @request_param string csrf_token
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the announcement doesn't exist.
     * @response 302 /pages/:page_id
     *     If the CSRF is invalid.
     * @response 302 /a/:id
     *     On success.
     */
    public function updateStatus(Request $request): Response
    {
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $announcement = models\Announcement::require($id);

        $form = new forms\AnnouncementStatus(model: $announcement);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::redirect('show page', ['id' => $announcement->page_id]);
        }

        $announcement = $form->model();
        $announcement->save();

        return Response::redirect('show announcement', ['id' => $announcement->id]);
    }

    /**
     * @request_param string id
     * @request_param string csrf_token
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the announcement doesn't exist.
     * @response 302 /pages/:page_id
     *     On success or if the CSRF is invalid.
     */
    public function delete(Request $request): Response
    {
        $this->requireCurrentUser();

        $id = $request->parameters->getString('id', '');
        $announcement = models\Announcement::require($id);

        $page_id = $announcement->page_id;

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::redirect('show page', ['id' => $page_id]);
        }

        $announcement->remove();

        return Response::redirect('show page', ['id' => $page_id]);
    }
}
