<?php

namespace taust\models;

use Minz\Database;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'heartbeats')]
class Heartbeat
{
    use Database\Recordable;

    #[Database\Column]
    public int $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    public bool $is_success;

    #[Database\Column]
    public string $details;

    #[Database\Column]
    public string $domain_id;

    public static function initSuccess(string $domain_id): self
    {
        $heartbeat = new self();

        $heartbeat->domain_id = $domain_id;
        $heartbeat->is_success = true;
        $heartbeat->details = 'OK';

        return $heartbeat;
    }

    public static function initError(string $domain_id, string $details): self
    {
        $heartbeat = new self();

        $heartbeat->domain_id = $domain_id;
        $heartbeat->is_success = false;
        $heartbeat->details = $details;

        return $heartbeat;
    }

    public static function findLastHeartbeatByDomainId(string $domain_id): ?self
    {
        $sql = <<<'SQL'
            SELECT * FROM heartbeats
            WHERE domain_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$domain_id]);

        $result = $statement->fetch();
        if (is_array($result)) {
            return self::fromDatabaseRow($result);
        } else {
            return null;
        }
    }

    public static function findLastSuccessfulHeartbeatByDomainId(string $domain_id): ?self
    {
        $sql = <<<'SQL'
            SELECT * FROM heartbeats
            WHERE domain_id = ?
            AND is_success = true
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$domain_id]);

        $result = $statement->fetch();
        if (is_array($result)) {
            return self::fromDatabaseRow($result);
        } else {
            return null;
        }
    }

    public static function deleteOlderThan(\DateTimeImmutable $datetime): bool
    {
        $sql = <<<SQL
            DELETE FROM heartbeats
            WHERE created_at < ?;
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        return $statement->execute([
            $datetime->format(Database\Column::DATETIME_FORMAT)
        ]);
    }
}
