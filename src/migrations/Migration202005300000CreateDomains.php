<?php

namespace taust\migrations;

class Migration202005300000CreateDomains
{
    public function migrate()
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

