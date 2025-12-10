<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\auth;
use taust\forms;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Alarms extends BaseController
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function index(Request $request): Response
    {
        auth\CurrentUser::require();

        return Response::ok('alarms/index.phtml', [
            'ongoing_alarms' => models\Alarm::listOngoingOrderByDescCreatedAt(),
            'finished_alarms' => models\Alarm::listLastFinished(),
        ]);
    }

    /**
     * @request_param string id
     * @request_param string csrf_token
     * @request_param string from
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 404
     *     If the alarm doesn't exist.
     * @response 302 /:from
     *     On success or if the CSRF is invalid.
     */
    public function finish(Request $request): Response
    {
        auth\CurrentUser::require();

        $alarm = models\Alarm::requireFromRequest($request);
        $from = $request->parameters->getString('from', '');

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::found($from);
        }

        $alarm->finish();
        $alarm->save();

        return Response::found($from);
    }
}
