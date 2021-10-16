<?php

namespace taust\migrations;

class Migration202110160003AddHostnameToPages
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE pages ADD COLUMN hostname TEXT NOT NULL DEFAULT '';
            CREATE UNIQUE INDEX idx_pages_hostname ON pages(hostname);
        SQL;

        return $database->exec($sql) !== false;
    }
}

