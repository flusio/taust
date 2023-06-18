<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005290000CreateUsers
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE users (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                username TEXT NOT NULL,
                password_hash TEXT NOT NULL
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}
