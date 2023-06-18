<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005300001CreateHeartbeats
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE heartbeats (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                is_success BOOLEAN NOT NULL,
                details TEXT NOT NULL DEFAULT '',
                domain_id TEXT REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}
