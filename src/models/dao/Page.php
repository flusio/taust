<?php

namespace taust\models\dao;

class Page extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\Page::PROPERTIES);
        parent::__construct('pages', 'id', $properties);
    }

    public function listAllOrderByTitle()
    {
        $sql = 'SELECT * FROM pages ORDER BY title';

        $statement = $this->query($sql);
        return $statement->fetchAll();
    }
}
