<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202306200001CreateJobs
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $database->exec(<<<'SQL'
            CREATE TABLE jobs (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL,
                updated_at TIMESTAMPTZ NOT NULL,
                perform_at TIMESTAMPTZ NOT NULL,
                name TEXT NOT NULL DEFAULT '',
                args JSON NOT NULL DEFAULT '{}',
                frequency TEXT NOT NULL DEFAULT '',
                queue TEXT NOT NULL DEFAULT 'default',
                locked_at TIMESTAMPTZ,
                number_attempts BIGINT NOT NULL DEFAULT 0,
                last_error TEXT NOT NULL DEFAULT '',
                failed_at TIMESTAMPTZ
            );
        SQL);

        return true;
    }

    public function rollback(): bool
    {
        $database = \Minz\Database::get();

        $database->exec(<<<'SQL'
            DROP TABLE jobs;
        SQL);

        return true;
    }
}
