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
class Alarms
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function index(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $ongoing_alarms = models\Alarm::listOngoingOrderByDescCreatedAt();
        $finished_alarms = models\Alarm::listLastFinished();

        return Response::ok('alarms/index.phtml', [
            'ongoing_alarms' => $ongoing_alarms,
            'finished_alarms' => $finished_alarms,
        ]);
    }

    /**
     * @request_param string id
     * @request_param string csrf
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
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $id = $request->param('id', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        /** @var string */
        $from = $request->param('from', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::found($from);
        }

        $alarm = models\Alarm::find($id);
        if (!$alarm) {
            return Response::notFound('not_found.phtml');
        }

        $alarm->finish();
        $alarm->save();

        return Response::found($from);
    }
}
