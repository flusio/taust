<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005310003AddDetailsToAlarms
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE alarms ADD COLUMN details TEXT NOT NULL DEFAULT '';
        SQL;

        return $database->exec($sql) !== false;
    }
}
