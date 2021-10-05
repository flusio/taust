<?php

namespace taust\models;

class Alarm extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'integer',
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'notified_at' => [
            'type' => 'datetime',
        ],

        'finished_at' => [
            'type' => 'datetime',
        ],

        'type' => [
            'type' => 'string',
            'required' => true,
        ],

        'details' => [
            'type' => 'string',
        ],

        'domain_id' => [
            'type' => 'string',
        ],

        'server_id' => [
            'type' => 'string',
        ],
    ];

    public static function initFromHeartbeat($heartbeat)
    {
        return new self([
            'domain_id' => $heartbeat->domain_id,
            'type' => 'heartbeat',
            'details' => $heartbeat->details,
        ]);
    }

    public static function initForServer($server_id, $type, $details)
    {
        return new self([
            'server_id' => $server_id,
            'type' => $type,
            'details' => $details,
        ]);
    }

    public function notify()
    {
        $this->notified_at = \Minz\Time::now();
    }

    public function finish()
    {
        $this->finished_at = \Minz\Time::now();
    }
}
