<?php

namespace taust\models\dao;

class User extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(\taust\models\User::PROPERTIES);
        parent::__construct('users', 'id', $properties);
    }
}
