<?php

namespace taust\models;

use Minz\Database;
use Minz\Translatable;
use Minz\Validable;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[Database\Table(name: 'pages')]
class Page
{
    use Database\Recordable;
    use Requirable;
    use Validable;

    public const TITLE_MAX_LENGTH = 100;

    #[Database\Column]
    public string $id;

    #[Database\Column]
    public \DateTimeImmutable $created_at;

    #[Database\Column]
    #[Validable\Presence(
        message: new Translatable('The title is required.'),
    )]
    #[Validable\Length(
        max: self::TITLE_MAX_LENGTH,
        message: new Translatable('Enter a title of maximum {max} characters.'),
    )]
    public string $title;

    #[Database\Column]
    public string $hostname;

    #[Database\Column]
    public string $style;

    #[Database\Column]
    public string $locale;

    public function __construct()
    {
        $this->id = \Minz\Random::timebased();
        $this->title = '';
        $this->hostname = '';
        $this->style = '';
        $this->locale = 'auto';
    }

    /**
     * @return Domain[]
     */
    public function domains(): array
    {
        return Domain::listByPageId($this->id);
    }

    /**
     * @return Server[]
     */
    public function servers(): array
    {
        return Server::listByPageId($this->id);
    }

    /**
     * @return Announcement[]
     */
    public function announcements(): array
    {
        return Announcement::listByPageId($this->id);
    }

    /**
     * @return array<string, Announcement[]>
     */
    public function announcementsByYears(): array
    {
        $announcements = Announcement::listByPageId($this->id);
        $announcements_by_years = [];
        foreach ($announcements as $announcement) {
            /** @var string */
            $year = $announcement->planned_at->format('Y');

            if (!isset($announcements_by_years[$year])) {
                $announcements_by_years[$year] = [];
            }

            $announcements_by_years[$year][] = $announcement;
        }

        return $announcements_by_years;
    }

    /**
     * @return array<string, Announcement[]>
     */
    public function weekAnnouncements(): array
    {
        $after = \Minz\Time::relative('today -1 week');
        $announcements = Announcement::listByPageIdAfter($this->id, $after);

        $tomorrow = \Minz\Time::relative('tomorrow');
        $announcements_by_days = [];
        foreach ($announcements as $announcement) {
            if ($announcement->planned_at >= $tomorrow) {
                $day = 'future';
            } else {
                $day = $announcement->planned_at->format('Y-m-d');
            }
            $announcements_by_days[$day][] = $announcement;
        }

        return $announcements_by_days;
    }

    public function tagUri(): string
    {
        $host = \Minz\Configuration::$url_options['host'];
        $date = $this->created_at->format('Y-m-d');
        return "tag:{$host},{$date}:pages/{$this->id}";
    }

    /**
     * @return self[]
     */
    public static function listAllOrderByTitle(): array
    {
        $sql = 'SELECT * FROM pages ORDER BY title';

        $database = Database::get();
        $statement = $database->query($sql);
        return self::fromDatabaseRows($statement->fetchAll());
    }

    #[Validable\Check]
    public function checkHostnameIsNotBaseUrl(): void
    {
        $base_url = \Minz\Url::baseUrl();
        if ($base_url === $this->hostname) {
            $this->addError('hostname', 'is_base_url', _('The hostname cannot be the taust’s host.'));
        }
    }
}
