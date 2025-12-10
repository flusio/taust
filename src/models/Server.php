<?php

namespace taust\models;

use Minz\Database;
use Minz\Translatable;
use Minz\Validable;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'servers')]
class Server
{
    use Database\Recordable;
    use Database\Resource;
    use Validable;

    #[Database\Column]
    public string $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Enter a hostname.'),
    )]
    #[Check\Domain(
        message: new Translatable('Enter a valid hostname.'),
    )]
    public string $hostname = '';

    #[Database\Column]
    #[Check\Ip(
        version: 'v4',
        message: new Translatable('This server declares an invalid DNS A record.'),
    )]
    public string $ipv4 = '';

    #[Database\Column]
    #[Check\Ip(
        version: 'v6',
        message: new Translatable('This server declares an invalid DNS AAAA record.'),
    )]
    public string $ipv6 = '';

    #[Database\Column]
    public string $auth_token;

    public function __construct()
    {
        $this->id = \Minz\Random::timebased();
        $this->auth_token = \Minz\Random::hex(128);
    }

    public function setHostname(string $hostname): void
    {
        $url_components = parse_url($hostname);
        $hostname = $url_components['host'] ?? $hostname;

        $dns_A = @dns_get_record($hostname, DNS_A);
        if ($dns_A) {
            $dns_A = $dns_A[0]['ip'];
        } else {
            $dns_A = '';
        }

        $dns_AAAA = @dns_get_record($hostname, DNS_AAAA);
        if ($dns_AAAA) {
            $dns_AAAA = $dns_AAAA[0]['ipv6'];
        } else {
            $dns_AAAA = '';
        }

        $this->hostname = $hostname;
        $this->ipv4 = $dns_A;
        $this->ipv6 = $dns_AAAA;
    }

    public function status(): string
    {
        $last_metric = Metric::findLastByServerId($this->id);
        if ($last_metric) {
            if ($last_metric->created_at <= \Minz\Time::ago(1, 'minutes')) {
                return 'down';
            } else {
                return 'up';
            }
        } else {
            return 'unknown';
        }
    }

    /**
     * @return self[]
     */
    public static function listAllOrderById(): array
    {
        $sql = 'SELECT * FROM servers ORDER BY hostname';

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
            SELECT s.* FROM servers s, pages_to_servers ps
            WHERE s.id = ps.server_id
            AND ps.page_id = ?
            ORDER BY s.hostname;
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$page_id]);
        return self::fromDatabaseRows($statement->fetchAll());
    }
}
