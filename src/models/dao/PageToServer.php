<?php

namespace taust\models\dao;

class PageToServer extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\PageToServer::PROPERTIES);
        parent::__construct('pages_to_servers', 'id', $properties);
    }

    public function attach($page_id, $server_ids)
    {
        $created_at = \Minz\Time::now();
        $values_as_question_marks = [];
        $values = [];
        foreach ($server_ids as $server_id) {
            $values_as_question_marks[] = '(?, ?, ?)';
            $values = array_merge($values, [
                $created_at->format(\Minz\Model::DATETIME_FORMAT),
                $page_id,
                $server_id,
            ]);
        }
        $values_placeholder = implode(", ", $values_as_question_marks);

        $sql = <<<SQL
            INSERT INTO pages_to_servers (created_at, page_id, server_id)
            VALUES {$values_placeholder}
            ON CONFLICT DO NOTHING;
        SQL;

        $statement = $this->prepare($sql);
        $result = $statement->execute($values);
        return $this->lastInsertId();
    }

    public function detach($page_id, $server_ids)
    {
        $values_as_question_marks = [];
        $values = [];
        foreach ($server_ids as $server_id) {
            $values_as_question_marks[] = '(page_id = ? AND server_id = ?)';
            $values = array_merge($values, [$page_id, $server_id]);
        }
        $values_placeholder = implode(' OR ', $values_as_question_marks);

        $sql = <<<SQL
            DELETE FROM pages_to_servers
            WHERE {$values_placeholder};
        SQL;

        $statement = $this->prepare($sql);
        return $statement->execute($values);
    }
}
