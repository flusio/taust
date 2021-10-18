<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Announcements
{
    public function edit($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $announcement = models\Announcement::find($id);
        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page = models\Page::find($announcement->page_id);

        return Response::ok('announcements/edit.phtml', [
            'page' => $page,
            'announcement' => $announcement,
            'type' => $announcement->type,
            'planned_at' => $announcement->planned_at,
            'title' => $announcement->title,
            'content' => $announcement->content,
        ]);
    }

    public function update($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $announcement = models\Announcement::find($id);
        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page = models\Page::find($announcement->page_id);

        $csrf = $request->param('csrf');
        $type = $request->param('type');
        $planned_at = $request->param('planned_at');
        $planned_at = date_create_from_format('Y-m-d\TH:i', $planned_at);
        $title = $request->param('title');
        $content = $request->param('content');

        if (!\Minz\CSRF::validate($csrf)) {
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

        return Response::redirect('announcements', ['id' => $page->id]);
    }

    public function updateStatus($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $announcement = models\Announcement::find($id);
        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page = models\Page::find($announcement->page_id);

        $csrf = $request->param('csrf');
        $status = $request->param('status');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::redirect('announcements', ['id' => $page_id]);
        }

        if ($status === 'ongoing') {
            $announcement->status = 'ongoing';
        } else {
            $announcement->status = 'finished';
        }

        $announcement->save();

        return Response::redirect('announcements', ['id' => $page->id]);
    }

    public function delete($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = $request->param('csrf');

        $announcement = models\Announcement::find($id);
        if (!$announcement) {
            return Response::notFound('not_found.phtml');
        }

        $page_id = $announcement->page_id;

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::redirect('announcements', ['id' => $page_id]);
        }

        models\Announcement::delete($announcement->id);

        return Response::redirect('announcements', ['id' => $page_id]);
    }
}
