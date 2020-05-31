<?php

namespace taust\migrations;

class Migration202005310001CreateAlarms
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE alarms (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                notified_at TIMESTAMPTZ,
                finished_at TIMESTAMPTZ,
                domain_id TEXT NOT NULL REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}

