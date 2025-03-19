<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110160001CreatePagesToDomains
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE pages_to_domains (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                page_id TEXT REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,
                domain_id TEXT REFERENCES domains ON DELETE CASCADE ON UPDATE CASCADE
            );

            CREATE UNIQUE INDEX idx_pages_to_domains ON pages_to_domains(page_id, domain_id);
        SQL;

        $database->exec($sql);

        return true;
    }
}
