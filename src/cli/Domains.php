<?php

namespace taust\cli;

use Minz\Response;
use taust\models;

class Domains
{
    public function heartbeats($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 5);

        $results = [];
        $domains = models\Domain::listAll();
        foreach ($domains as $domain) {
            curl_setopt($curl_session, CURLOPT_URL, 'https://' . $domain->id);

            $result = curl_exec($curl_session);
            $http_code = curl_getinfo($curl_session, CURLINFO_RESPONSE_CODE);

            if ($http_code >= 200 && $http_code < 400) {
                $heartbeat = models\Heartbeat::initSuccess($domain->id);
            } else {
                $error = curl_error($curl_session);
                $details = "{$error} (code {$http_code})";
                $heartbeat = models\Heartbeat::initError($domain->id, $details);
            }

            $heartbeat->save();

            $results[] = "{$heartbeat->domain_id}: {$heartbeat->details}";
        }

        curl_close($curl_session);

        return Response::text(200, implode("\n", $results));
    }
}
