<?php

namespace taust\migrations;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Migration202005310002AddEmailAndFreeMobileCredentialsToUsers
{
    public function migrate(): bool
    {
        $database = \Minz\Database::get();

        $sql = <<<'SQL'
            ALTER TABLE users
                ADD COLUMN email TEXT,
                ADD COLUMN free_mobile_login TEXT,
                ADD COLUMN free_mobile_key TEXT;
        SQL;

        $database->exec($sql);

        return true;
    }
}
