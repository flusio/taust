<?php

namespace taust\migrations;

class Migration202110160000CreatePages
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE pages (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                title TEXT NOT NULL
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}

