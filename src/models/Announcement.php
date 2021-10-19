<?php

namespace taust\models;

use taust\utils;

class Announcement extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'planned_at' => [
            'type' => 'datetime',
        ],

        'type' => [
            'type' => 'string',
            'required' => true,
        ],

        'status' => [
            'type' => 'string',
            'required' => true,
        ],

        'page_id' => [
            'type' => 'string',
            'required' => true,
        ],

        'title' => [
            'type' => 'string',
            'required' => true,
        ],

        'content' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function initIncident($page_id, $planned_at, $title, $content)
    {
        return new self([
            'id' => utils\Random::timebased(),
            'planned_at' => $planned_at,
            'type' => 'incident',
            'status' => 'ongoing',
            'page_id' => $page_id,
            'title' => $title,
            'content' => $content,
        ]);
    }

    public static function initMaintenance($page_id, $planned_at, $title, $content)
    {
        return new self([
            'id' => utils\Random::timebased(),
            'planned_at' => $planned_at,
            'type' => 'maintenance',
            'status' => 'ongoing',
            'page_id' => $page_id,
            'title' => $title,
            'content' => $content,
        ]);
    }

    public function htmlContent()
    {
        $parsedown = new \Parsedown();
        return $parsedown->text($this->content);
    }

    public function tagUri()
    {
        $host = \Minz\Configuration::$url_options['host'];
        $date = $this->created_at->format('Y-m-d');
        return "tag:{$host},{$date}:announcements/{$this->id}";
    }
}
