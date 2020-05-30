<?php

namespace taust\migrations;

class Migration202005300001CreateHeartbeats
{
    public function migrate()
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

