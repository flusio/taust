<?php

namespace taust\mailers;

class Alarms extends \Minz\Mailer
{
    public function sendAlarm($email, $alarm)
    {
        $subject = _('[taust] A new problem has been detected');
        $created_at = date_create_from_format(
            \Minz\Model::DATETIME_FORMAT,
            $alarm['created_at']
        );
        $this->setBody(
            'mailers/alarms/alarm.phtml',
            'mailers/alarms/alarm.txt',
            [
                'created_at' => $created_at,
                'details' => $alarm['details'],
                'domain' => $alarm['domain_id'],
            ]
        );
        return $this->send($email, $subject);
    }
}
