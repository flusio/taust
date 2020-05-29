<?php

namespace taust\models;

class User extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'username' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\User::validateUsername',
        ],

        'password_hash' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function init($username, $password)
    {
        return new self([
            'id' => bin2hex(random_bytes(16)),
            'username' => trim($username),
            'password_hash' => $password ? password_hash($password, PASSWORD_BCRYPT) : '',
        ]);
    }

    public static function validateUsername($username)
    {
        return preg_match('/^[0-9a-zA-Z_\-]{1,}$/', $username) === 1;
    }
}
