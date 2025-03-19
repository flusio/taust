<?php

namespace taust\mailers;

use taust\models;
use Minz\Mailer;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Alarms extends Mailer
{
    public function sendAlarm(string $to, models\Alarm $alarm): Mailer\Email
    {
        if ($alarm->domain_id) {
            $object = $alarm->domain_id . ' domain';
        } elseif ($alarm->server_id) {
            $server = models\Server::find($alarm->server_id);

            if (!$server) {
                throw new \Exception("Alarm #{$alarm->id} has invalid server.");
            }

            $object = $server->hostname . ' server';
        } else {
            throw new \Exception("Alarm #{$alarm->id} has no domain nor server associated.");
        }

        $email = new Mailer\Email();
        $email->setSubject(_('[taust] A new problem has been detected'));
        $email->setBody(
            'mailers/alarms/alarm.phtml',
            'mailers/alarms/alarm.txt',
            [
                'created_at' => $alarm->created_at,
                'details' => $alarm->details,
                'object' => $object,
            ]
        );

        $this->send($email, to: $to);

        return $email;
    }
}
