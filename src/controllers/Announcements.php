<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\models;
use taust\utils;

class Announcements
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
        /** @var string */
        $id = $request->param('id', '');

        $announcement = models\Announcement::find($id);
        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page = models\Page::find($announcement->page_id);

        return Response::ok('announcements/show.phtml', [
            'page' => $page,
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        $announcement = models\Announcement::find($id);

        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        return Response::ok('announcements/edit.phtml', [
            'page' => $announcement->page(),
            'announcement' => $announcement,
            'type' => $announcement->type,
            'planned_at' => $announcement->planned_at,
            'title' => $announcement->title,
            'content' => $announcement->content,
        ]);
    }

    /**
     * @request_param string id
     * @request_param string type
     * @request_param string planned_at
     *     Format: Y-m-d\TH:i
     * @request_param string title
     * @request_param string content
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        $announcement = models\Announcement::find($id);

        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page = $announcement->page();

        /** @var string */
        $csrf = $request->param('csrf', '');

        /** @var string */
        $type = $request->param('type', '');

        /** @var string */
        $planned_at = $request->param('planned_at', '');
        $planned_at = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $planned_at);

        /** @var string */
        $title = $request->param('title', '');

        /** @var string */
        $content = $request->param('content', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('announcements/edit.phtml', [
                'page' => $page,
                'announcement' => $announcement,
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        if ($planned_at === false) {
            return Response::badRequest('announcements/edit.phtml', [
                'page' => $page,
                'announcement' => $announcement,
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'errors' => [
                    'planned_at' => _('Enter a valid date.'),
                ],
            ]);
        }

        $announcement->type = $type;
        $announcement->planned_at = $planned_at;
        $announcement->title = $title;
        $announcement->content = $content;

        $errors = $announcement->validate();
        if ($errors) {
            return Response::badRequest('announcements/edit.phtml', [
                'page' => $page,
                'announcement' => $announcement,
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'errors' => $errors,
            ]);
        }

        $announcement->save();

        return Response::redirect('show announcement', ['id' => $announcement->id]);
    }

    /**
     * @request_param string id
     * @request_param string status
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        $announcement = models\Announcement::find($id);

        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        /** @var string */
        $csrf = $request->param('csrf', '');

        /** @var string */
        $status = $request->param('status', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::redirect('show page', ['id' => $announcement->page_id]);
        }

        if ($status === 'ongoing') {
            $announcement->status = 'ongoing';
        } else {
            $announcement->status = 'finished';
        }

        $announcement->save();

        return Response::redirect('show announcement', ['id' => $announcement->id]);
    }

    /**
     * @request_param string id
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        $announcement = models\Announcement::find($id);

        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page_id = $announcement->page_id;

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::redirect('show page', ['id' => $page_id]);
        }

        models\Announcement::delete($announcement->id);

        return Response::redirect('show page', ['id' => $page_id]);
    }
}
