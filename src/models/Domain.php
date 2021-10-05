<?php

namespace taust\models;

class Domain extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Domain::validateDomain',
        ],

        'created_at' => [
            'type' => 'datetime',
        ],
    ];

    public static function init($url)
    {
        $url_components = parse_url($url);
        return new self([
            'id' => isset($url_components['host']) ? $url_components['host'] : $url,
        ]);
    }

    public function status()
    {
        $last_heartbeat = Heartbeat::daoToModel('findLastHeartbeatByDomainId', $this->id);
        if ($last_heartbeat) {
            if ($last_heartbeat->created_at <= \Minz\Time::ago(5, 'minutes')) {
                return 'unknown';
            } elseif ($last_heartbeat->is_success) {
                return 'up';
            } else {
                return 'down';
            }
        } else {
            return 'unknown';
        }
    }

    public function validate()
    {
        $formatted_errors = [];

        foreach (parent::validate() as $property => $error) {
            $code = $error['code'];

            if ($property === 'id') {
                if ($code === \Minz\Model::ERROR_REQUIRED) {
                    $formatted_error = _('The domain is required.');
                } else {
                    $formatted_error = _('This domain is invalid.');
                }
            } else {
                $formatted_error = $error;
            }

            $formatted_errors[$property] = $formatted_error;
        }

        return $formatted_errors;
    }

    public static function validateDomain($domain)
    {
        $filtered = filter_var($domain, FILTER_VALIDATE_DOMAIN, [
            'flags' => FILTER_FLAG_HOSTNAME,
        ]);
        return $filtered !== false;
    }
}
