<?php

namespace taust\models\dao;

class Metric extends \Minz\DatabaseModel
{
    use SaveHelper;

    public function __construct()
    {
        $properties = array_keys(\taust\models\Metric::PROPERTIES);
        parent::__construct('metrics', 'id', $properties);
    }

    public function findLastByServerId($server_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM metrics
            WHERE server_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$server_id]);
        $result = $statement->fetchAll();
        if (count($result) === 1) {
            return $result[0];
        } else {
            return null;
        }
    }
}
