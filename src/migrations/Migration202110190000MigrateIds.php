<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110190000MigrateIds
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $tables_to_migrate = ['announcements', 'pages', 'servers', 'users'];
        foreach ($tables_to_migrate as $table) {
            $statement = $database->query(<<<SQL
                SELECT id FROM {$table}
            SQL);

            $database->beginTransaction();

            foreach ($statement->fetchAll() as $row) {
                $new_id = \Minz\Random::timebased();

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
