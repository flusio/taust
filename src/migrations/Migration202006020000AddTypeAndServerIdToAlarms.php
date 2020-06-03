<?php

namespace taust\migrations;

class Migration202006020000AddTypeAndServerIdToAlarms
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE alarms
                ADD COLUMN type TEXT NOT NULL DEFAULT 'heartbeat',
                ADD COLUMN server_id TEXT REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE,
                ALTER COLUMN domain_id DROP NOT NULL;
        SQL;

        return $database->exec($sql) !== false;
    }
}

