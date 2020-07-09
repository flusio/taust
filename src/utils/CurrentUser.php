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

        $user_dao = new models\dao\User();
        $db_user = $user_dao->find($_SESSION['current_user_id']);
        if (!$db_user) {
            return null;
        }

        self::$instance = new models\User($db_user);
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
