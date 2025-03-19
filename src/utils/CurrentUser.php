<?php

namespace taust\utils;

use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class CurrentUser
{
    private static ?models\User $instance = null;

    public static function get(): ?models\User
    {
        $current_user_id = self::currentId();

        if (!is_string($current_user_id)) {
            return null;
        }

        if (self::$instance !== null) {
            return self::$instance;
        }

        $user = models\User::find($current_user_id);
        if (!$user) {
            return null;
        }

        self::$instance = $user;
        return self::$instance;
    }

    public static function set(string $user_id): void
    {
        $_SESSION['current_user_id'] = $user_id;
        self::$instance = null;
    }

    public static function reset(): void
    {
        unset($_SESSION['current_user_id']);
        self::$instance = null;
    }

    public static function currentId(): ?string
    {
        $current_user_id = $_SESSION['current_user_id'] ?? null;
        if (is_string($current_user_id)) {
            return $current_user_id;
        } else {
            return null;
        }
    }
}
