<?php

namespace taust\migrations;

class Migration202110190001AddStyleToPages
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE pages ADD COLUMN style TEXT NOT NULL DEFAULT '';
        SQL;

        return $database->exec($sql) !== false;
    }
}

