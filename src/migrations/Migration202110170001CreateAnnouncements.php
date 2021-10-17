<?php

namespace taust\migrations;

class Migration202110170001CreateAnnouncements
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE announcements (
                id TEXT PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                planned_at TIMESTAMPTZ NOT NULL,
                type TEXT NOT NULL,
                status TEXT NOT NULL,
                page_id TEXT NOT NULL REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,

                title TEXT NOT NULL,
                content TEXT NOT NULL
            );
        SQL;

        return $database->exec($sql) !== false;
    }
}

