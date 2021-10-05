<?php

namespace taust\mailers;

use taust\models;

class Alarms extends \Minz\Mailer
{
    public function sendAlarm($email, $alarm)
    {
        $subject = _('[taust] A new problem has been detected');

        if ($alarm->domain_id) {
            $object = $alarm->domain_id . ' domain';
        } else {
            $server = models\Server::find($alarm->server_id);
            $object = $server->hostname . ' server';
        }

        $this->setBody(
            'mailers/alarms/alarm.phtml',
            'mailers/alarms/alarm.txt',
            [
                'created_at' => $alarm->created_at,
                'details' => $alarm->details,
                'object' => $object,
            ]
        );
        return $this->send($email, $subject);
    }
}
