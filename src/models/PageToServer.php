<?php

namespace taust\models;

class PageToServer extends \Minz\Model
{
    use DaoConnector;

    public const PROPERTIES = [
        'id' => [
            'type' => 'string',
            'required' => true,
        ],

        'created_at' => 'datetime',

        'page_id' => [
            'type' => 'string',
            'required' => true,
        ],

        'server_id' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function set($page_id, $server_ids)
    {
        $previous_attachments = self::listBy(['page_id' => $page_id]);
        $previous_server_ids = array_column($previous_attachments, 'server_id');
        $ids_to_attach = array_diff($server_ids, $previous_server_ids);
        $ids_to_detach = array_diff($previous_server_ids, $server_ids);

        $database = \Minz\Database::get();
        $database->beginTransaction();

        if ($ids_to_attach) {
            self::daoCall('attach', $page_id, $ids_to_attach);
        }

        if ($ids_to_detach) {
            self::daoCall('detach', $page_id, $ids_to_detach);
        }

        return $database->commit();
    }
}
