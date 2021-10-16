<?php

namespace taust\models;

class PageToDomain extends \Minz\Model
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

        'domain_id' => [
            'type' => 'string',
            'required' => true,
        ],
    ];

    public static function set($page_id, $domain_ids)
    {
        $previous_attachments = self::listBy(['page_id' => $page_id]);
        $previous_domain_ids = array_column($previous_attachments, 'domain_id');
        $ids_to_attach = array_diff($domain_ids, $previous_domain_ids);
        $ids_to_detach = array_diff($previous_domain_ids, $domain_ids);

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
