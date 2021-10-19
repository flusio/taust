<?php

namespace taust\models;

use taust\utils;

class Server extends \Minz\Model
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

        $dns_A = dns_get_record($hostname, DNS_A);
        if ($dns_A) {
            $dns_A = $dns_A[0]['ip'];
        } else {
            $dns_A = null;
        }

        $dns_AAAA = dns_get_record($hostname, DNS_AAAA);
        if ($dns_AAAA) {
            $dns_AAAA = $dns_AAAA[0]['ipv6'];
        } else {
            $dns_AAAA = null;
        }

        return new self([
            'id' => utils\Random::timebased(),
            'hostname' => $hostname,
            'ipv4' => $dns_A,
            'ipv6' => $dns_AAAA,
            'auth_token' => utils\Random::hex(128),
        ]);
    }

    public function status()
    {
        $last_metric = Metric::daoToModel('findLastByServerId', $this->id);
        if ($last_metric) {
            if ($last_metric->created_at <= \Minz\Time::ago(1, 'minutes')) {
                return 'down';
            } else {
                return 'up';
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
