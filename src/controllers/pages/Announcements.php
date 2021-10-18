<?php

namespace taust\controllers\pages;

use Minz\Response;
use taust\models;
use taust\utils;

class Announcements
{
    public function create($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $page = models\Page::find($id);
        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        $type = $request->param('type');
        $planned_at = $request->param('planned_at');
        $planned_at = date_create_from_format('Y-m-d\TH:i', $planned_at);
        $title = $request->param('title');
        $content = $request->param('content');
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::badRequest('pages/show.phtml', [
                'page' => $page,
                'domains' => $page->domains(),
                'servers' => $page->servers(),
                'announcements' => $page->announcements(),
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        if ($type === 'maintenance') {
            $announcement = models\Announcement::initMaintenance(
                $page->id,
                $planned_at,
                $title,
                $content
            );
        } else {
            $announcement = models\Announcement::initIncident(
                $page->id,
                $planned_at,
                $title,
                $content
            );
        }

        $errors = $announcement->validate();
        if ($errors) {
            return Response::badRequest('pages/show.phtml', [
                'page' => $page,
                'domains' => $page->domains(),
                'servers' => $page->servers(),
                'announcements' => $page->announcements(),
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'errors' => $errors,
            ]);
        }

        $announcement->save();

        return Response::redirect('show page', ['id' => $page->id]);;
    }
}
