<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005300000CreateDomains
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE domains (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}
