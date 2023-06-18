<?php

namespace taust\models;

use Minz\Database;
use Minz\Translatable;
use Minz\Validable;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'announcements')]
class Announcement
{
    use Database\Recordable;
    use Validable;

    #[Database\Column]
    public string $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Enter a date.'),
    )]
    public \DateTimeImmutable $planned_at;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Select a type from the list.'),
    )]
    #[Validable\Inclusion(
        in: ['incident', 'maintenance'],
        message: new Translatable('Select a type from the list.'),
    )]
    public string $type;

    #[Database\Column]
    public string $status;

    #[Database\Column]
    public string $page_id;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Select a title.'),
    )]
    public string $title;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('Select the content.'),
    )]
    public string $content;

    public static function initIncident(
        string $page_id,
        \DateTimeImmutable $planned_at,
        string $title,
        string $content,
    ): self {
        $announcement = new self();

        $announcement->id = \Minz\Random::timebased();
        $announcement->planned_at = $planned_at;
        $announcement->type = 'incident';
        $announcement->status = 'ongoing';
        $announcement->page_id = $page_id;
        $announcement->title = $title;
        $announcement->content = $content;

        return $announcement;
    }

    public static function initMaintenance(
        string $page_id,
        \DateTimeImmutable $planned_at,
        string $title,
        string $content,
    ): self {
        $announcement = new self();

        $announcement->id = \Minz\Random::timebased();
        $announcement->planned_at = $planned_at;
        $announcement->type = 'maintenance';
        $announcement->status = 'ongoing';
        $announcement->page_id = $page_id;
        $announcement->title = $title;
        $announcement->content = $content;

        return $announcement;
    }

    public function htmlContent(): string
    {
        $parsedown = new \Parsedown();
        return $parsedown->text($this->content);
    }

    public function tagUri(): string
    {
        $host = \Minz\Configuration::$url_options['host'];
        $date = $this->created_at->format('Y-m-d');
        return "tag:{$host},{$date}:announcements/{$this->id}";
    }

    public function page(): Page
    {
        $page = Page::find($this->page_id);

        if (!$page) {
            throw new \Exception("Announcement #{$this->id} has invalid page.");
        }

        return $page;
    }

    /**
     * @return self[]
     */
    public static function listByPageId(string $page_id): array
    {
        $sql = <<<'SQL'
            SELECT * FROM announcements
            WHERE page_id = ?
            ORDER BY planned_at DESC
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([$page_id]);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    /**
     * @return self[]
     */
    public static function listByPageIdAfter(string $page_id, \DateTimeImmutable $after): array
    {
        $sql = <<<'SQL'
            SELECT * FROM announcements
            WHERE page_id = ?
            AND planned_at >= ?
            ORDER BY planned_at DESC
        SQL;

        $database = Database::get();
        $statement = $database->prepare($sql);
        $statement->execute([
            $page_id,
            $after->format(Database\Column::DATETIME_FORMAT),
        ]);
        return self::fromDatabaseRows($statement->fetchAll());
    }
}
