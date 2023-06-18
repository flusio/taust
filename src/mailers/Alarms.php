<?php

namespace taust\mailers;

use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Alarms extends \Minz\Mailer
{
    public function sendAlarm(string $email, models\Alarm $alarm): bool
    {
        $subject = _('[taust] A new problem has been detected');

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
