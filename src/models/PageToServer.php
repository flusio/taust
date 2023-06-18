<?php

namespace taust\models;

use Minz\Database;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'pages_to_servers')]
class PageToServer
{
    use Database\Recordable;

    #[Database\Column]
    public int $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    public string $page_id;

    #[Database\Column]
    public string $server_id;

    /**
     * @param string[] $server_ids
     */
    public static function set(string $page_id, array $server_ids): bool
    {
        $previous_attachments = self::listBy(['page_id' => $page_id]);
        $previous_server_ids = array_column($previous_attachments, 'server_id');
        $ids_to_attach = array_diff($server_ids, $previous_server_ids);
        $ids_to_detach = array_diff($previous_server_ids, $server_ids);

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
     * @param string[] $server_ids
     */
    public static function attach(string $page_id, array $server_ids): string
    {
        $created_at = \Minz\Time::now();
        $values_as_question_marks = [];
        $values = [];
        foreach ($server_ids as $server_id) {
            $values_as_question_marks[] = '(?, ?, ?)';
            $values = array_merge($values, [
                $created_at->format(Database\Column::DATETIME_FORMAT),
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

        $database = Database::get();
        $statement = $database->prepare($sql);
        $result = $statement->execute($values);
        return $database->lastInsertId();
    }

    /**
     * @param string[] $server_ids
     */
    public static function detach(string $page_id, array $server_ids): bool
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

        $database = Database::get();
        $statement = $database->prepare($sql);
        return $statement->execute($values);
    }
}
