<?php

namespace taust\models;

use taust\errors;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
trait Requirable
{
    public static function require(string $pk_value): self
    {
        $model = self::find($pk_value);

        if ($model === null) {
            throw new errors\MissingResourceError();
        }

        return $model;
    }
}
