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
        $result = $statement->execute([$server_id]);
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
