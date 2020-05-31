<?php

namespace taust\migrations;

class Migration202005310003AddDetailsToAlarms
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE alarms ADD COLUMN details TEXT NOT NULL DEFAULT '';
        SQL;

        return $database->exec($sql) !== false;
    }
}

