<?php

namespace taust\utils;

use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class CurrentUser
{
    private static $instance;

    public static function get()
    {
        if (!isset($_SESSION['current_user_id'])) {
            return null;
        }

        if (self::$instance !== null) {
            return self::$instance;
        }

        $user = models\User::find($_SESSION['current_user_id']);
        if (!$user) {
            return null;
        }

        self::$instance = $user;
        return self::$instance;
    }

    public static function set($user_id)
    {
        $_SESSION['current_user_id'] = $user_id;
        self::$instance = null;
    }

    public static function reset()
    {
        unset($_SESSION['current_user_id']);
        self::$instance = null;
    }

    public static function currentId()
    {
        if (isset($_SESSION['current_user_id'])) {
            return $_SESSION['current_user_id'];
        } else {
            return null;
        }
    }
}
