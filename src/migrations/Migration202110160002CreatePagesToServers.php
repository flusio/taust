<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202110160002CreatePagesToServers
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            CREATE TABLE pages_to_servers (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                page_id TEXT REFERENCES pages ON DELETE CASCADE ON UPDATE CASCADE,
                server_id TEXT REFERENCES servers ON DELETE CASCADE ON UPDATE CASCADE
            );

            CREATE UNIQUE INDEX idx_pages_to_servers ON pages_to_servers(page_id, server_id);
        SQL;

        $database->exec($sql);

        return true;
    }
}
