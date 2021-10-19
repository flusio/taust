<?php

namespace taust\migrations;

use taust\utils;

class Migration202110190000MigrateIds
{
    public function migrate()
    {
        $database = \Minz\Database::get();

        $tables_to_migrate = ['announcements', 'pages', 'servers', 'users'];
        foreach ($tables_to_migrate as $table) {
            $statement = $database->query(<<<SQL
                SELECT id FROM {$table}
            SQL);

            $database->beginTransaction();

            foreach ($statement->fetchAll() as $row) {
                $new_id = utils\Random::timebased();

                $statement = $database->prepare(<<<SQL
                    UPDATE {$table} SET id = ? WHERE id = ?;
                SQL);
                $statement->execute([$new_id, $row['id']]);
            }

            $database->commit();
        }

        return true;
    }
}

