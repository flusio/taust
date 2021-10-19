<?php

namespace taust\migrations;

class Migration202110190002AddLocaleToPages
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE pages ADD COLUMN locale TEXT NOT NULL DEFAULT 'auto';
        SQL;

        return $database->exec($sql) !== false;
    }
}

