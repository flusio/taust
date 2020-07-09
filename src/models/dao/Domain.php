<?php

namespace taust\models\dao;

class Domain extends \Minz\DatabaseModel
{
    use SaveHelper;

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
}
