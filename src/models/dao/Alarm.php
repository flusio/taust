<?php

namespace taust\models\dao;

class Alarm extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = [
            'id',
            'created_at',
            'notified_at',
            'finished_at',
            'type',
            'details',
            'domain_id',
            'server_id',
        ];
        parent::__construct('alarms', 'id', $properties);
    }

    public function findOngoingByDomainId($domain_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE domain_id = ? AND finished_at IS NULL
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$domain_id]);
        $result = $statement->fetchAll();
        if (count($result) === 1) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function findOngoingByServerIdAndType($server_id, $type)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE server_id = ? AND type = ? AND finished_at IS NULL
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$server_id, $type]);
        $result = $statement->fetchAll();
        if (count($result) === 1) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function listToNotify()
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL
            AND notified_at IS NULL
            AND created_at < ?;
        SQL;

        // we don't want to notify immediatly because the alarm could be
        // temporary (e.g. a sudden spike in memory consumption)
        $three_minutes_ago = \Minz\Time::ago(3, 'minutes')->format(\Minz\Model::DATETIME_FORMAT);
        $statement = $this->prepare($sql);
        $statement->execute([$three_minutes_ago]);
        return $statement->fetchAll();
    }

    public function listOngoingOrderByDescCreatedAt()
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL
            ORDER BY created_at DESC;
        SQL;

        $statement = $this->query($sql);
        return $statement->fetchAll();
    }

    public function listLastFinished()
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NOT NULL
            ORDER BY created_at DESC
            LIMIT 50;
        SQL;

        $statement = $this->query($sql);
        return $statement->fetchAll();
    }

    public function listByDomainIdOrderByDescCreatedAt($domain_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE domain_id = ?
            ORDER BY created_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$domain_id]);
        return $statement->fetchAll();
    }

    public function listByServerIdOrderByDescCreatedAt($server_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE server_id = ?
            ORDER BY created_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$server_id]);
        return $statement->fetchAll();
    }
}
