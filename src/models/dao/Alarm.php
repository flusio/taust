<?php

namespace taust\models\dao;

class Alarm extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = ['id', 'created_at', 'notified_at', 'finished_at', 'domain_id'];
        parent::__construct('alarms', 'id', $properties);
    }

    public function findOngoing($domain_id)
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
}
