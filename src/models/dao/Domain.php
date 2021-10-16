<?php

namespace taust\models\dao;

class Domain extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\Domain::PROPERTIES);
        parent::__construct('domains', 'id', $properties);
    }

    public function listAllOrderById()
    {
        $sql = 'SELECT * FROM domains ORDER BY id';

        $statement = $this->query($sql);
        return $statement->fetchAll();
    }

    public function listByPageId($page_id)
    {
        $sql = <<<'SQL'
            SELECT d.* FROM domains d, pages_to_domains pd
            WHERE d.id = pd.domain_id
            AND pd.page_id = ?
            ORDER BY d.id;
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$page_id]);
        return $statement->fetchAll();
    }
}
