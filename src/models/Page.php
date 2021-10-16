<?php

namespace taust\models;

class Page extends \Minz\Model
{
    use DaoConnector;

    public const TITLE_MAX_LENGTH = 100;

    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'title' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Page::validateTitle',
        ],
    ];

    public static function init($title)
    {
        return new self([
            'id' => bin2hex(random_bytes(16)),
            'title' => $title,
        ]);
    }

    public function domains()
    {
        return Domain::daoToList('listByPageId', $this->id);
    }

    public function servers()
    {
        return Server::daoToList('listByPageId', $this->id);
    }

    public function validate()
    {
        $formatted_errors = [];

        foreach (parent::validate() as $property => $error) {
            $code = $error['code'];

            if ($property === 'title' && $code === \Minz\Model::ERROR_REQUIRED) {
                $formatted_error = _('The title is required.');
            } elseif ($property === 'title') {
                $formatted_error = _f('The title must be less than %d characters.', self::TITLE_MAX_LENGTH);
            } else {
                $formatted_error = $error;
            }

            $formatted_errors[$property] = $formatted_error;
        }

        return $formatted_errors;
    }

    public static function validateTitle($title)
    {
        return mb_strlen($title) <= self::TITLE_MAX_LENGTH;
    }
}
