<?php

namespace taust\models;

use taust\utils;

class Page extends \Minz\Model
{
    use DaoConnector;

    public const TITLE_MAX_LENGTH = 100;

    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'title' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Page::validateTitle',
        ],

        'hostname' => [
            'type' => 'string',
        ],

        'style' => [
            'type' => 'string',
        ],

        'locale' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function init($title)
    {
        return new self([
            'id' => utils\Random::timebased(),
            'title' => $title,
            'hostname' => '',
            'style' => '',
            'locale' => 'auto',
        ]);
    }

    public function domains()
    {
        return Domain::daoToList('listByPageId', $this->id);
    }

    public function servers()
    {
        return Server::daoToList('listByPageId', $this->id);
    }

    public function announcements()
    {
        return Announcement::daoToList('listByPageId', $this->id);
    }

    public function announcementsByYears()
    {
        $announcements = Announcement::daoToList('listByPageId', $this->id);
        $announcements_by_years = [];
        foreach ($announcements as $announcement) {
            $year = $announcement->planned_at->format('Y');
            $announcements_by_years[$year][] = $announcement;
        }

        return $announcements_by_years;
    }

    public function weekAnnouncements()
    {
        $after = \Minz\Time::relative('today -1 week');
        $announcements = Announcement::daoToList('listByPageIdAfter', $this->id, $after);

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

    public function tagUri()
    {
        $host = \Minz\Configuration::$url_options['host'];
        $date = $this->created_at->format('Y-m-d');
        return "tag:{$host},{$date}:pages/{$this->id}";
    }

    public function validate()
    {
        $formatted_errors = [];

        foreach (parent::validate() as $property => $error) {
            $code = $error['code'];

            if ($property === 'title' && $code === \Minz\Model::ERROR_REQUIRED) {
                $formatted_error = _('The title is required.');
            } elseif ($property === 'title') {
                $formatted_error = _f('The title must be less than %d characters.', self::TITLE_MAX_LENGTH);
            } else {
                $formatted_error = $error;
            }

            $formatted_errors[$property] = $formatted_error;
        }

        return $formatted_errors;
    }

    public static function validateTitle($title)
    {
        return mb_strlen($title) <= self::TITLE_MAX_LENGTH;
    }
}
