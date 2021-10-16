<?php

namespace taust\models\dao;

class Server extends \Minz\DatabaseModel
{
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

    public function listByPageId($page_id)
    {
        $sql = <<<'SQL'
            SELECT s.* FROM servers s, pages_to_servers ps
            WHERE s.id = ps.server_id
            AND ps.page_id = ?
            ORDER BY s.hostname;
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$page_id]);
        return $statement->fetchAll();
    }
}
