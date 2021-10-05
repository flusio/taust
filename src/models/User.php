<?php

namespace taust\models;

class User extends \Minz\Model
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

        'username' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\User::validateUsername',
        ],

        'password_hash' => [
            'type' => 'string',
            'required' => true,
        ],

        'email' => [
            'type' => 'string',
            'validator' => '\taust\utils\Email::validate',
        ],

        'free_mobile_login' => [
            'type' => 'string',
            'validator' => '\taust\models\User::validateFreeMobileLogin',
        ],

        'free_mobile_key' => [
            'type' => 'string',
            'validator' => '\taust\models\User::validateFreeMobileKey',
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

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password_hash);
    }

    public function validate()
    {
        $formatted_errors = [];

        foreach (parent::validate() as $property => $error) {
            $code = $error['code'];

            if ($property === 'username') {
                if ($code === \Minz\Model::ERROR_REQUIRED) {
                    $formatted_error = _('The username is required.');
                } else {
                    $formatted_error = _('This username is invalid.');
                }
            } elseif ($property === 'password_hash') {
                $formatted_error = _('The password is required.');
            } elseif ($property === 'email') {
                $formatted_error = _('This email is invalid.');
            } elseif ($property === 'free_mobile_login') {
                $formatted_error = _('This login is invalid.');
            } elseif ($property === 'free_mobile_key') {
                $formatted_error = _('This key is invalid.');
            } else {
                $formatted_error = $error;
            }

            $formatted_errors[$property] = $formatted_error;
        }

        return $formatted_errors;
    }

    public static function validateUsername($username)
    {
        return preg_match('/^[0-9a-zA-Z_\-]{1,}$/', $username) === 1;
    }

    public static function validateFreeMobileLogin($login)
    {
        return preg_match('/^[\d]{8}$/', $login) === 1;
    }

    public static function validateFreeMobileKey($key)
    {
        return preg_match('/^[0-9a-zA-Z]{14}$/', $key) === 1;
    }
}
