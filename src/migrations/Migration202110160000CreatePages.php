<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110160000CreatePages
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE pages (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                title TEXT NOT NULL
            );
        SQL;

        $database->exec($sql);

        return true;
    }
}
