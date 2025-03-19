<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110170000DropIndexPagesHostname
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            DROP INDEX idx_pages_hostname;
        SQL;

        $database->exec($sql);

        return true;
    }
}
