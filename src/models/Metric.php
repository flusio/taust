<?php

namespace taust\models;

class Metric extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => [
            'type' => 'integer',
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'payload' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Metric::validateJson',
        ],

        'server_id' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function init($server_id, $payload)
    {
        return new self([
            'payload' => $payload,
            'server_id' => $server_id,
        ]);
    }

    public function payload()
    {
        return json_decode($this->payload);
    }

    public static function validateJson($json)
    {
        json_decode($json);
        $error_message = json_last_error_msg();
        if ($error_message === 'No error') {
            return true;
        } else {
            return $error_message;
        }
    }
}
