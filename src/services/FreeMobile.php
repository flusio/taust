<?php

namespace taust\services;

use Minz\Template\SimpleTemplateHelpers;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class FreeMobile
{
    public const SMS_API = 'https://smsapi.free-mobile.fr/sendmsg';

    public function sendAlarm(string $login, string $key, models\Alarm $alarm): int
    {
        if ($alarm->domain_id) {
            $object = "{$alarm->domain_id} domain (no heartbeats)";
        } elseif ($alarm->server_id) {
            $server = models\Server::find($alarm->server_id);

            if (!$server) {
                throw new \Exception("Alarm #{$alarm->id} has invalid server.");
            }

            $object = "{$server->hostname} server ({$alarm->type})";
        } else {
            throw new \Exception("Alarm #{$alarm->id} has no domain nor server associated.");
        }

        $message = SimpleTemplateHelpers::formatGettext(
            'Hey, this is taust robot at %s.',
            \Minz\Url::absoluteFor('home')
        );
        $message .= ' ';
        $message .= SimpleTemplateHelpers::formatGettext(
            'It looks like you have a problem with the %s.',
            $object
        );

        $query_fields = [
            'user' => $login,
            'pass' => $key,
            'msg' => $message,
        ];
        $query = http_build_query($query_fields);

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, self::SMS_API . '?' . $query);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_USERAGENT, 'taust/dev (https://github.com/flusio/taust)');
        curl_exec($curl_session);

        $http_code = curl_getinfo($curl_session, CURLINFO_RESPONSE_CODE);

        curl_close($curl_session);

        return $http_code;
    }
}
