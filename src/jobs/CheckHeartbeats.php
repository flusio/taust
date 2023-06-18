<?php

namespace taust\jobs;

use Minz\Job;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class CheckHeartbeats extends Job
{
    public static function install(): void
    {
        $job = new self();
        if (!self::existsBy(['name' => $job->name])) {
            $perform_at = \Minz\Time::now();
            $job->performLater($perform_at);
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->frequency = '+1 minute';
    }

    /**
     * Check the heartbeats of the domains.
     */
    public function perform(): void
    {
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl_session, CURLOPT_USERAGENT, 'taust/dev (https://github.com/flusio/taust)');

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

            if (!$heartbeat->is_success) {
                \Minz\Log::warning("{$heartbeat->domain_id}: {$heartbeat->details}");
            }
        }

        curl_close($curl_session);
    }
}
