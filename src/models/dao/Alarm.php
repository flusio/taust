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

    public function listOngoingAndNotNotified()
    {
        $sql = <<<'SQL'
            SELECT * FROM alarms
            WHERE finished_at IS NULL AND notified_at IS NULL;
        SQL;

        $statement = $this->query($sql);
        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }
}
