<?php

namespace taust\migrations;

class Migration202006010000CreateServers
{
    public function migrate()
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

        return $database->exec($sql) !== false;
    }
}

