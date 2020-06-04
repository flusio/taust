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
        $result = $statement->execute([$domain_id]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false && count($result) === 1) {
            return $result[0];
        } elseif ($result !== false) {
            return null;
        } else {
            throw self::sqlStatementError($statement);
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
        $result = $statement->execute([$server_id, $type]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false && count($result) === 1) {
            return $result[0];
        } elseif ($result !== false) {
            return null;
        } else {
            throw self::sqlStatementError($statement);
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
        $result = $statement->execute([$three_minutes_ago]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }

    public function listOngoingOrderByDescCreatedAt()
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL
            ORDER BY created_at DESC;
        SQL;

        $statement = $this->query($sql);
        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
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
        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }

    public function listByDomainIdOrderByDescCreatedAt($domain_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE domain_id = ?
            ORDER BY created_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $result = $statement->execute([$domain_id]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }

    public function listByServerIdOrderByDescCreatedAt($server_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE server_id = ?
            ORDER BY created_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $result = $statement->execute([$server_id]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }
}
