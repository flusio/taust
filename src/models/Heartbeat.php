<?php

namespace taust\models;

class Heartbeat extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'integer',
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'is_success' => [
            'type' => 'boolean',
        ],

        'details' => [
            'type' => 'string',
        ],

        'domain_id' => [
            'type' => 'string',
        ],
    ];

    public static function initSuccess($domain_id)
    {
        return new self([
            'domain_id' => $domain_id,
            'is_success' => true,
            'details' => 'OK',
        ]);
    }

    public static function initError($domain_id, $details)
    {
        return new self([
            'domain_id' => $domain_id,
            'is_success' => false,
            'details' => $details,
        ]);
    }
}
