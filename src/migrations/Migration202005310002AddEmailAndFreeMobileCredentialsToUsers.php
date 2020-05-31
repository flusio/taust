<?php

namespace taust\migrations;

class Migration202005310002AddEmailAndFreeMobileCredentialsToUsers
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE users
                ADD COLUMN email TEXT,
                ADD COLUMN free_mobile_login TEXT,
                ADD COLUMN free_mobile_key TEXT;
        SQL;

        return $database->exec($sql) !== false;
    }
}

