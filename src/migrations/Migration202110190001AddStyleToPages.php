<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110190001AddStyleToPages
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE pages ADD COLUMN style TEXT NOT NULL DEFAULT '';
        SQL;

        $database->exec($sql);

        return true;
    }
}
