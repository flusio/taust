<?php

namespace taust\services;

use taust\models;

class FreeMobile
{
    public const SMS_API = 'https://smsapi.free-mobile.fr/sendmsg';

    public function sendAlarm($login, $key, $alarm)
    {
        if ($alarm->domain_id) {
            $object = "{$alarm->domain_id} domain (no heartbeats)";
        } else {
            $server = models\Server::find($alarm->server_id);
            $object = "{$server->hostname} server ({$alarm->type})";
        }

        $message = vsprintf(_('Hey, this is taust robot at %s.'), \Minz\Url::absoluteFor('home'));
        $message .= vsprintf(_('It looks like you have a problem with the %s.'), $object);

        $query_fields = [
            'user' => $login,
            'pass' => $key,
            'msg' => $message,
        ];
        $query = http_build_query($query_fields);

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, self::SMS_API . '?' . $query);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl_session);

        $http_code = curl_getinfo($curl_session, CURLINFO_RESPONSE_CODE);

        curl_close($curl_session);

        return $http_code;
    }
}
