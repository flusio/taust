<?php

namespace taust\migrations;

class Migration202110170000DropIndexPagesHostname
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            DROP INDEX idx_pages_hostname;
        SQL;

        return $database->exec($sql) !== false;
    }
}

