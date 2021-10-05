<?php

namespace taust\models;

class Metric extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'integer',
        ],

        'created_at' => [
            'type' => 'datetime',
        ],

        'payload' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\taust\models\Metric::validateJson',
        ],

        'server_id' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    private $payload_decoded;

    public static function init($server_id, $payload)
    {
        return new self([
            'payload' => $payload,
            'server_id' => $server_id,
        ]);
    }

    public function cpuPercents()
    {
        return $this->payload()->cpu_percent;
    }

    public function memoryTotal()
    {
        return $this->payload()->memory_total;
    }

    public function memoryUsed()
    {
        $payload = $this->payload();
        return $payload->memory_total - $payload->memory_available;
    }

    public function memoryUsedPercent()
    {
        return $this->memoryUsed() * 100 / $this->memoryTotal();
    }

    public function disks()
    {
        return $this->payload()->disks;
    }

    public function diskUsed($disk)
    {
        return $disk->total - $disk->free;
    }

    public function diskUsedPercent($disk)
    {
        return $this->diskUsed($disk) * 100 / $disk->total;
    }

    public function payload()
    {
        if (!$this->payload_decoded) {
            $this->payload_decoded = json_decode($this->payload);
        }
        return $this->payload_decoded;
    }

    public static function validateJson($json)
    {
        json_decode($json);
        $error_message = json_last_error_msg();
        if ($error_message === 'No error') {
            return true;
        } else {
            return $error_message;
        }
    }
}
