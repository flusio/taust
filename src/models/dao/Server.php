<?php

namespace taust\models\dao;

class Server extends \Minz\DatabaseModel
{
    use SaveHelper;

    public function __construct()
    {
        $properties = array_keys(\taust\models\Server::PROPERTIES);
        parent::__construct('servers', 'id', $properties);
    }

    public function listAllOrderById()
    {
        $sql = 'SELECT * FROM servers ORDER BY hostname';

        $statement = $this->query($sql);
        return $statement->fetchAll();
    }
}
