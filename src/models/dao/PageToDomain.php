<?php

namespace taust\models\dao;

class PageToDomain extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\PageToDomain::PROPERTIES);
        parent::__construct('pages_to_domains', 'id', $properties);
    }

    public function attach($page_id, $domain_ids)
    {
        $created_at = \Minz\Time::now();
        $values_as_question_marks = [];
        $values = [];
        foreach ($domain_ids as $domain_id) {
            $values_as_question_marks[] = '(?, ?, ?)';
            $values = array_merge($values, [
                $created_at->format(\Minz\Model::DATETIME_FORMAT),
                $page_id,
                $domain_id,
            ]);
        }
        $values_placeholder = implode(", ", $values_as_question_marks);

        $sql = <<<SQL
            INSERT INTO pages_to_domains (created_at, page_id, domain_id)
            VALUES {$values_placeholder}
            ON CONFLICT DO NOTHING;
        SQL;

        $statement = $this->prepare($sql);
        $result = $statement->execute($values);
        return $this->lastInsertId();
    }

    public function detach($page_id, $domain_ids)
    {
        $values_as_question_marks = [];
        $values = [];
        foreach ($domain_ids as $domain_id) {
            $values_as_question_marks[] = '(page_id = ? AND domain_id = ?)';
            $values = array_merge($values, [$page_id, $domain_id]);
        }
        $values_placeholder = implode(' OR ', $values_as_question_marks);

        $sql = <<<SQL
            DELETE FROM pages_to_domains
            WHERE {$values_placeholder};
        SQL;

        $statement = $this->prepare($sql);
        return $statement->execute($values);
    }
}
