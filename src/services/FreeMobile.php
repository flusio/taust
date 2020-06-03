<?php

namespace taust\services;

use taust\models;

class FreeMobile
{
    public const SMS_API = 'https://smsapi.free-mobile.fr/sendmsg';

    public function sendAlarm($login, $key, $alarm)
    {
        if ($alarm['domain_id']) {
            $object = $alarm['domain_id'] . ' domain';
        } else {
            $server_dao = new models\dao\Server();
            $db_server = $server_dao->find($alarm['server_id']);
            $object = $db_server['hostname'] . ' server';
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
