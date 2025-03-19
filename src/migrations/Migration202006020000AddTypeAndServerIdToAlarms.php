<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202006020000AddTypeAndServerIdToAlarms
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE alarms
                ADD COLUMN type TEXT NOT NULL DEFAULT 'heartbeat',
                ADD COLUMN server_id TEXT REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE,
                ALTER COLUMN domain_id DROP NOT NULL;
        SQL;

        $database->exec($sql);

        return true;
    }
}
