<?php

namespace taust\controllers\pages;

use Minz\Request;
use Minz\Response;
use taust\auth;
use taust\controllers\BaseController;
use taust\forms;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Announcements extends BaseController
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
        $page = models\Page::requireFromRequest($request);

        return Response::ok('pages/announcements/index.phtml', [
            'page' => $page,
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
     *     If the page does not exist.
     * @response 400
     *     If one of the parameter is invalid.
     * @response 302 /page/:id
     *     On success.
     */
    public function create(Request $request): Response
    {
        auth\CurrentUser::require();

        $page = models\Page::requireFromRequest($request);

        $announcement = new models\Announcement($page);
        $form = new forms\Announcement(model: $announcement);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('pages/show.phtml', [
                'page' => $page,
                'announcement_form' => $form,
            ]);
        }

        $announcement = $form->model();
        $announcement->save();

        return Response::redirect('show page', ['id' => $page->id]);
    }
}
