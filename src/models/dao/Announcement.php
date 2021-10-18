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

    public function listByPageIdAfter($page_id, $after)
    {
        $sql = <<<'SQL'
            SELECT * FROM announcements
            WHERE page_id = ?
            AND planned_at >= ?
            ORDER BY planned_at DESC
        SQL;

        $statement = $this->prepare($sql);
        $statement->execute([
            $page_id,
            $after->format(\Minz\Model::DATETIME_FORMAT),
        ]);
        return $statement->fetchAll();
    }
}
