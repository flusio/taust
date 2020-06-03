<?php

namespace taust\models\dao;

class Heartbeat extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = ['id', 'created_at', 'is_success', 'details', 'domain_id'];
        parent::__construct('heartbeats', 'id', $properties);
    }

    public function findLastHeartbeatByDomainId($domain_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM heartbeats
            WHERE domain_id = ?
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
