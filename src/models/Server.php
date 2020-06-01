<?php

namespace taust\models;

class Server extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'hostname' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Server::validateHostname',
        ],

        'ipv4' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Server::validateIpV4',
        ],

        'ipv6' => [
            'type' => 'string',
            'validator' => '\taust\models\Server::validateIpV6',
        ],

        'auth_token' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function init($hostname)
    {
        $url_components = parse_url($hostname);
        $hostname = isset($url_components['host']) ? $url_components['host'] : $hostname;
        $dns = dns_get_record($hostname);
        $dns_A = '';
        $dns_AAAA = '';
        foreach ($dns as $entry) {
            if ($entry['type'] === 'A') {
                $dns_A = $entry['ip'];
            }
            if ($entry['type'] === 'AAAA') {
                $dns_AAAA = $entry['ipv6'];
            }
        }

        return new self([
            'id' => bin2hex(random_bytes(16)),
            'hostname' => $hostname,
            'ipv4' => $dns_A,
            'ipv6' => $dns_AAAA,
            'auth_token' => bin2hex(random_bytes(64)),
        ]);
    }

    public function validate()
    {
        $formatted_errors = [];

        foreach (parent::validate() as $property => $error) {
            $code = $error['code'];

            if ($property === 'hostname' && $code === \Minz\Model::ERROR_REQUIRED) {
                $formatted_error = _('The hostname is required.');
            } elseif ($property === 'hostname') {
                $formatted_error = _('This hostname is invalid.');
            } elseif ($property === 'ipv4' && $code === \Minz\Model::ERROR_REQUIRED) {
                $formatted_error = _('This server doesn’t declare any DNS A record.');
            } elseif ($property === 'ipv4') {
                $formatted_error = _('This server declares an invalid DNS A record.');
            } elseif ($property === 'ipv6') {
                $formatted_error = _('This server declares an invalid DNS AAAA record.');
            } else {
                $formatted_error = $error;
            }

            $formatted_errors[$property] = $formatted_error;
        }

        return $formatted_errors;
    }

    public static function validateHostname($hostname)
    {
        $filtered = filter_var($hostname, FILTER_VALIDATE_DOMAIN, [
            'flags' => FILTER_FLAG_HOSTNAME,
        ]);
        return $filtered !== false;
    }

    public static function validateIpV4($ip)
    {
        $filtered = filter_var($ip, FILTER_VALIDATE_IP, [
            'flags' => FILTER_FLAG_IPV4,
        ]);
        return $filtered !== false;
    }

    public static function validateIpV6($ip)
    {
        $filtered = filter_var($ip, FILTER_VALIDATE_IP, [
            'flags' => FILTER_FLAG_IPV6,
        ]);
        return $filtered !== false;
    }
}