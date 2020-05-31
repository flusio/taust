<?php

namespace taust\migrations;

class Migration202005310000ChangeHeartbeatsDomainIdToNotNull
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE heartbeats
            ALTER COLUMN domain_id SET NOT NULL
        SQL;

        return $database->exec($sql) !== false;
    }
}

