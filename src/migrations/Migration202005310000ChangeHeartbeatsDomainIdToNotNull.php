<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005310000ChangeHeartbeatsDomainIdToNotNull
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE heartbeats
            ALTER COLUMN domain_id SET NOT NULL
        SQL;

        return $database->exec($sql) !== false;
    }
}
