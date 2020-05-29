<?php

namespace taust\migrations;

class Migration202005290000CreateUsers
{
    public function migrate()
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

