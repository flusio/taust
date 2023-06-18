<?php

namespace taust\models;

use Minz\Database;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'alarms')]
class Alarm
{
    use Database\Recordable;

    #[Database\Column]
    public int $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    public ?\DateTimeImmutable $notified_at = null;

    #[Database\Column]
    public ?\DateTimeImmutable $finished_at = null;

    #[Database\Column]
    public string $type;

    #[Database\Column]
    public string $details;

    #[Database\Column]
    public ?string $domain_id = null;

    #[Database\Column]
    public ?string $server_id = null;

    public static function initFromHeartbeat(Heartbeat $heartbeat): self
    {
        $alarm = new self();

        $alarm->domain_id = $heartbeat->domain_id;
        $alarm->type = 'heartbeat';
        $alarm->details = $heartbeat->details;

        return $alarm;
    }

    public static function initForServer(string $server_id, string $type, string $details): self
    {
        $alarm = new self();

        $alarm->server_id = $server_id;
        $alarm->type = $type;
        $alarm->details = $details;

        return $alarm;
    }

    public function notify(): void
    {
        $this->notified_at = \Minz\Time::now();
    }

    public function finish(): void
    {
        $this->finished_at = \Minz\Time::now();
    }

    public static function findOngoingByDomainId(string $domain_id): ?self
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE domain_id = ? AND finished_at IS NULL
            ORDER BY created_at DESC, id
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

    public static function findOngoingByServerIdAndType(string $server_id, string $type): ?self
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE server_id = ? AND type = ? AND finished_at IS NULL
            ORDER BY created_at DESC, id
            LIMIT 1
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$server_id, $type]);

        $result = $statement->fetch();
        if (is_array($result)) {
            return self::fromDatabaseRow($result);
        } else {
            return null;
        }
    }

    /**
     * @return self[]
     */
    public static function listToNotify(): array
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL
            AND notified_at IS NULL
            AND created_at < ?;
        SQL;

        // we don't want to notify immediatly because the alarm could be
        // temporary (e.g. a sudden spike in memory consumption)
        $three_minutes_ago = \Minz\Time::ago(3, 'minutes');

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([
            $three_minutes_ago->format(Database\Column::DATETIME_FORMAT),
        ]);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listOngoingOrderByDescCreatedAt(): array
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL
            ORDER BY created_at DESC, id;
        SQL;

        $database = Database::get();
        $statement = $database->query($sql);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listLastFinished(): array
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NOT NULL
            ORDER BY created_at DESC, id
            LIMIT 50;
        SQL;

        $database = Database::get();
        $statement = $database->query($sql);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listByDomainIdOrderByDescCreatedAt(string $domain_id): array
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE domain_id = ?
            ORDER BY created_at DESC, id
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$domain_id]);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listByServerIdOrderByDescCreatedAt(string $server_id): array
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE server_id = ?
            ORDER BY created_at DESC, id
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$server_id]);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    public static function deleteFinishedOlderThan(\DateTimeImmutable $datetime): bool
    {
        $sql = <<<SQL
            DELETE FROM alarms
            WHERE created_at < ?
            AND finished_at IS NOT NULL;
        SQL;

        $when = $datetime->format(Database\Column::DATETIME_FORMAT);

        $database = Database::get();
        $statement = $database->prepare($sql);
        return $statement->execute([$when]);
    }
}
