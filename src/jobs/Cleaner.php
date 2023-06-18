<?php

namespace taust\jobs;

use Minz\Job;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Cleaner extends Job
{
    public static function install(): void
    {
        $job = new self();
        if (!self::existsBy(['name' => $job->name])) {
            $perform_at = \Minz\Time::relative('tomorrow 2:00');
            $job->performLater($perform_at);
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->frequency = '+1 day';
    }

    /**
     * Clear old data (metrics, heartbeats and alarms).
     */
    public function perform(): void
    {
        models\Metric::deleteOlderThan(\Minz\Time::ago(2, 'weeks'));
        models\Heartbeat::deleteOlderThan(\Minz\Time::ago(2, 'weeks'));
        models\Alarm::deleteFinishedOlderThan(\Minz\Time::ago(2, 'weeks'));
    }
}
