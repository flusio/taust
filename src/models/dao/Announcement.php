<?php

namespace taust\models\dao;

class Announcement extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\Announcement::PROPERTIES);
        parent::__construct('announcements', 'id', $properties);
    }

    public function listByPageId($page_id)
    {
        $sql = <<<'SQL'
            SELECT * FROM announcements
            WHERE page_id = ?
            ORDER BY planned_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([$page_id]);
        return $statement->fetchAll();
    }
}
