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
}
