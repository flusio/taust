<?php

namespace taust\models;

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
            'id' => bin2hex(random_bytes(16)),
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
            'id' => bin2hex(random_bytes(16)),
            'planned_at' => $planned_at,
            'type' => 'maintenance',
            'status' => 'ongoing',
            'page_id' => $page_id,
            'title' => $title,
            'content' => $content,
        ]);
    }
}
