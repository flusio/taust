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

    public function listByHostnames()
    {
        $sql = "SELECT * FROM pages WHERE hostname != ''";

        $statement = $this->query($sql);
        $db_pages = $statement->fetchAll();
        $db_pages_by_hostnames = [];

        foreach ($db_pages as $db_page) {
            $db_pages_by_hostnames[$db_page['hostname']] = $db_page;
        }

        return $db_pages_by_hostnames;
    }
}
