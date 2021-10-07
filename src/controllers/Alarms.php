<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Alarms
{
    public function index($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $ongoing_alarms = models\Alarm::daoToList('listOngoingOrderByDescCreatedAt');
        $finished_alarms = models\Alarm::daoToList('listLastFinished');

        return Response::ok('alarms/index.phtml', [
            'ongoing_alarms' => $ongoing_alarms,
            'finished_alarms' => $finished_alarms,
        ]);
    }

    public function finish($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = $request->param('csrf');
        $from = $request->param('from');

        if (!\Minz\CSRF::validate($csrf)) {
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
