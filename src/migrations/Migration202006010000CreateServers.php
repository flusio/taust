<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202006010000CreateServers
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE servers (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                hostname TEXT NOT NULL,
                ipv4 TEXT NOT NULL,
                ipv6 TEXT,
                auth_token TEXT NOT NULL
            );
        SQL;

        $database->exec($sql);

        return true;
    }
}
