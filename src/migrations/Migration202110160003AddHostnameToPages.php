<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110160003AddHostnameToPages
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE pages ADD COLUMN hostname TEXT NOT NULL DEFAULT '';
            CREATE UNIQUE INDEX idx_pages_hostname ON pages(hostname);
        SQL;

        return $database->exec($sql) !== false;
    }
}
