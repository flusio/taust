<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202006010001CreateMetrics
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE metrics (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                payload JSON NOT NULL,
                server_id TEXT NOT NULL REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}
