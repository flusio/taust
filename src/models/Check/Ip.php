<?php

namespace taust\models\Check;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Ip extends \Minz\Validable\PropertyCheck
{
    public string $version;

    public function __construct(string $version, string $message)
    {
        parent::__construct($message);
        $this->version = $version;
    }

    public function assert(): bool
    {
        $value = $this->value();

        if ($value === null || $value === '') {
            return true;
        }

        if ($this->version === 'v4') {
            $flag = FILTER_FLAG_IPV4;
        } else {
            $flag = FILTER_FLAG_IPV6;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_IP, [
            'flags' => $flag,
        ]);

        return $filtered !== false;
    }
}
