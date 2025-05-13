<?php

namespace taust\controllers\pages;

use Minz\Request;
use Minz\Response;
use taust\models;
use taust\utils;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Announcements
{
    /**
     * @request_param string id
     *
     * @response 404
     *     If the page does not exist.
     * @response 200
     *     On success.
     */
    public function index(Request $request): Response
    {
        $id = $request->param('id', '');

        $page = models\Page::find($id);

        if (!$page) {
            return Response::notFound('not_found.phtml');
        }

        return Response::ok('pages/announcements/index.phtml', [
            'page' => $page,
            'announcements_by_years' => $page->announcementsByYears(),
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
     *     If the page does not exist.
     * @response 400
     *     If one of the parameter is invalid.
     * @response 302 /page/:id
     *     On success.
     */
    public function create(Request $request): Response
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

        $type = $request->param('type', '');
        $planned_at = $request->paramDatetime('planned_at', \Minz\Time::now());
        $title = $request->param('title', '');
        $content = $request->param('content', '');
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('pages/show.phtml', [
                'page' => $page,
                'domains' => $page->domains(),
                'servers' => $page->servers(),
                'announcements_by_days' => $page->weekAnnouncements(),
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
                'announcements_by_days' => $page->weekAnnouncements(),
                'type' => $type,
                'planned_at' => $planned_at,
                'title' => $title,
                'content' => $content,
                'errors' => $errors,
            ]);
        }

        $announcement->save();

        return Response::redirect('show page', ['id' => $page->id]);
    }
}
