<?php

namespace taust\models;

use Minz\Database;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'pages_to_domains')]
class PageToDomain
{
    use Database\Recordable;

    #[Database\Column]
    public int $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    public string $page_id;

    #[Database\Column]
    public string $domain_id;

    /**
     * @param string[] $domain_ids
     */
    public static function set(string $page_id, array $domain_ids): bool
    {
        $previous_attachments = self::listBy(['page_id' => $page_id]);
        $previous_domain_ids = array_column($previous_attachments, 'domain_id');
        $ids_to_attach = array_diff($domain_ids, $previous_domain_ids);
        $ids_to_detach = array_diff($previous_domain_ids, $domain_ids);

        $database = \Minz\Database::get();
        $database->beginTransaction();

        if ($ids_to_attach) {
            self::attach($page_id, $ids_to_attach);
        }

        if ($ids_to_detach) {
            self::detach($page_id, $ids_to_detach);
        }

        return $database->commit();
    }

    /**
     * @param string[] $domain_ids
     */
    public static function attach(string $page_id, array $domain_ids): string
    {
        $created_at = \Minz\Time::now();
        $values_as_question_marks = [];
        $values = [];
        foreach ($domain_ids as $domain_id) {
            $values_as_question_marks[] = '(?, ?, ?)';
            $values = array_merge($values, [
                $created_at->format(Database\Column::DATETIME_FORMAT),
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

        $database = Database::get();
        $statement = $database->prepare($sql);
        $result = $statement->execute($values);
        return $database->lastInsertId();
    }

    /**
     * @param string[] $domain_ids
     */
    public static function detach(string $page_id, array $domain_ids): bool
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

        $database = Database::get();
        $statement = $database->prepare($sql);
        return $statement->execute($values);
    }
}
