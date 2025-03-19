<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005310001CreateAlarms
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE alarms (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                notified_at TIMESTAMPTZ,
                finished_at TIMESTAMPTZ,
                domain_id TEXT NOT NULL REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
            );
        SQL;

        $database->exec($sql);

        return true;
    }
}
