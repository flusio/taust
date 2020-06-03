<?php

namespace taust\mailers;

use taust\models;

class Alarms extends \Minz\Mailer
{
    public function sendAlarm($email, $alarm)
    {
        $subject = _('[taust] A new problem has been detected');
        $created_at = date_create_from_format(
            \Minz\Model::DATETIME_FORMAT,
            $alarm['created_at']
        );

        if ($alarm['domain_id']) {
            $object = $alarm['domain_id'] . ' domain';
        } else {
            $server_dao = new models\dao\Server();
            $db_server = $server_dao->find($alarm['server_id']);
            $object = $db_server['hostname'] . ' server';
        }

        $this->setBody(
            'mailers/alarms/alarm.phtml',
            'mailers/alarms/alarm.txt',
            [
                'created_at' => $created_at,
                'details' => $alarm['details'],
                'object' => $object,
            ]
        );
        return $this->send($email, $subject);
    }
}
