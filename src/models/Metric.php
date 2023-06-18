<?php

namespace taust\models;

use Minz\Database;

/**
 * @phpstan-type MetricPayload array{
 *     'at': int,
 *     'cpu_percent': float[],
 *     'memory_total': int,
 *     'memory_available': int,
 *     'disks': MetricDisk[],
 * }
 *
 * @phpstan-type MetricDisk array{
 *     'name': string,
 *     'total': int,
 *     'free': int,
 * }
 *
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'metrics')]
class Metric
{
    use Database\Recordable;

    #[Database\Column]
    public int $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    /** @var MetricPayload */
    #[Database\Column]
    public array $payload;

    #[Database\Column]
    public string $server_id;

    /**
     * @param MetricPayload $payload
     */
    public function __construct(string $server_id, array $payload)
    {
        $this->payload = $payload;
        $this->server_id = $server_id;
    }

    /**
     * @return float[]
     */
    public function cpuPercents(): array
    {
        return $this->payload['cpu_percent'];
    }

    public function memoryTotal(): int
    {
        return $this->payload['memory_total'];
    }

    public function memoryUsed(): int
    {
        return $this->payload['memory_total'] - $this->payload['memory_available'];
    }

    public function memoryUsedPercent(): float
    {
        return $this->memoryUsed() * 100 / $this->memoryTotal();
    }

    /**
     * @return MetricDisk[]
     */
    public function disks(): array
    {
        return $this->payload['disks'];
    }

    /**
     * @param MetricDisk $disk
     */
    public function diskUsed(array $disk): int
    {
        return $disk['total'] - $disk['free'];
    }

    /**
     * @param MetricDisk $disk
     */
    public function diskUsedPercent(array $disk): float
    {
        return $this->diskUsed($disk) * 100 / $disk['total'];
    }

    public static function findLastByServerId(string $server_id): ?self
    {
        $sql = <<<'SQL'
            SELECT * FROM metrics
            WHERE server_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$server_id]);

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
            DELETE FROM metrics
            WHERE created_at < ?;
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        return $statement->execute([
            $datetime->format(Database\Column::DATETIME_FORMAT),
        ]);
    }
}
