<?php

$db_host = $dotenv->pop('DB_HOST');
$db_port = $dotenv->pop('DB_PORT');
$db_name = 'taust_development';

return [
    'app_name' => 'taust',

    'secret_key' => $dotenv->pop('APP_SECRET_KEY'),

    'url_options' => [
        'host' => $dotenv->pop('APP_HOST'),
        'port' => intval($dotenv->pop('APP_PORT')),
    ],

    'database' => [
        'dsn' => "pgsql:host={$db_host};port={$db_port};dbname={$db_name}",
        'username' => $dotenv->pop('DB_USERNAME'),
        'password' => $dotenv->pop('DB_PASSWORD'),
    ],
];
