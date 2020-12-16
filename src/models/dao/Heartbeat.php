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
        $statement->execute([$domain_id]);
        $result = $statement->fetchAll();
        if (count($result) === 1) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function deleteOlderThan($datetime)
    {
        $sql = <<<SQL
            DELETE FROM heartbeats
            WHERE created_at < ?;
        SQL;

        $when = $datetime->format(\Minz\Model::DATETIME_FORMAT);
        $statement = $this->prepare($sql);
        return $statement->execute([$when]);
    }
}
