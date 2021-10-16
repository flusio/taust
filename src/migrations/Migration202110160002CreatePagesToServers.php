<?php

namespace taust\migrations;

class Migration202110160002CreatePagesToServers
{
    public function migrate()
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

        return $database->exec($sql) !== false;
    }
}

