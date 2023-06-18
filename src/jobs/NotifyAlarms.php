<?php

namespace taust\jobs;

use Minz\Job;
use taust\mailers;
use taust\models;
use taust\services;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class NotifyAlarms extends Job
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
     * Send alarms to the users.
     */
    public function perform(): void
    {
        $alarms_mailer = new mailers\Alarms();
        $free_mobile_service = new services\FreeMobile();

        $users = models\User::listAll();
        $alarms = models\Alarm::listToNotify();
        foreach ($alarms as $alarm) {
            foreach ($users as $user) {
                if ($user->email) {
                    $alarms_mailer->sendAlarm($user->email, $alarm);
                }

                if ($user->free_mobile_login && $user->free_mobile_key) {
                    $free_mobile_service->sendAlarm(
                        $user->free_mobile_login,
                        $user->free_mobile_key,
                        $alarm
                    );
                }
            }

            $alarm->notify();
            $alarm->save();
        }

        $alarms_count = count($alarms);
        if ($alarms_count > 0) {
            \Minz\Log::notice("{$alarms_count} alarms have been notified.");
        }
    }
}
