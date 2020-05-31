<?php

namespace taust;

use Minz\Response;

class Alarms
{
    public function monitor($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $domain_dao = new models\dao\Domain();
        $heartbeat_dao = new models\dao\Heartbeat();
        $alarm_dao = new models\dao\Alarm();
        $db_domains = $domain_dao->listAll();

        $results = [];
        foreach ($db_domains as $db_domain) {
            $domain_id = $db_domain['id'];

            $heartbeat = $heartbeat_dao->findLastHeartbeat($domain_id);
            if (!$heartbeat) {
                // TODO what to do when there're no heartbeats?
                continue;
            }

            $alarm = $alarm_dao->findOngoing($domain_id);
            if ($alarm) {
                if ($heartbeat['is_success']) {
                    $details = 'Alarm finished';
                    $alarm_dao->update($alarm['id'], [
                        'finished_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                    ]);
                } else {
                    $details = 'Alarm not finished';
                }
            } else {
                if (!$heartbeat['is_success']) {
                    $details = 'New alarm!';
                    $alarm_dao->create([
                        'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                        'domain_id' => $domain_id,
                    ]);
                } else {
                    $details = 'All good';
                }
            }

            $results[] = "{$domain_id}: {$details}";
        }

        return Response::text(200, implode("\n", $results));
    }
}
