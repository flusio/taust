<?php

namespace taust\models;

use Minz\Database;
use Minz\Translatable;
use Minz\Validable;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'domains')]
class Domain
{
    use Database\Recordable;
    use Database\Resource;
    use Validable;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Select a domain name.'),
    )]
    #[Check\Domain(
        message: new Translatable('Select a valid domain name.'),
    )]
    #[Validable\Unique(
        message: new Translatable('This domain already exists.'),
    )]
    public string $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    public function setId(string $url): void
    {
        $url_components = parse_url($url);
        $this->id = $url_components['host'] ?? $url;
    }

    public function status(): string
    {
        $last_heartbeat = $this->lastHeartbeat();
        if ($last_heartbeat) {
            if ($last_heartbeat->created_at <= \Minz\Time::ago(5, 'minutes')) {
                return 'unknown';
            } elseif ($last_heartbeat->is_success) {
                return 'up';
            } else {
                return 'down';
            }
        } else {
            return 'unknown';
        }
    }

    /**
     * @return Alarm[]
     */
    public function alarms(): array
    {
        return Alarm::listByDomainIdOrderByDescCreatedAt($this->id);
    }

    public function lastHeartbeat(): ?Heartbeat
    {
        return Heartbeat::findLastHeartbeatByDomainId($this->id);
    }

    public function lastSuccessfulHeartbeat(): ?Heartbeat
    {
        return Heartbeat::findLastSuccessfulHeartbeatByDomainId($this->id);
    }

    /**
     * @return self[]
     */
    public static function listAllOrderById(): array
    {
        $sql = 'SELECT * FROM domains ORDER BY id';

        $database = Database::get();
        $statement = $database->query($sql);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listByPageId(string $page_id): array
    {
        $sql = <<<'SQL'
            SELECT d.* FROM domains d, pages_to_domains pd
            WHERE d.id = pd.domain_id
            AND pd.page_id = ?
            ORDER BY d.id;
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$page_id]);
        return self::fromDatabaseRows($statement->fetchAll());
    }
}
