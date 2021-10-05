<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;

class Metrics
{
    public function create($request)
    {
        $auth_token = $request->header('PHP_AUTH_PW');
        if (!$auth_token) {
            return Response::text(401, 'You must pass the server token as basic auth password');
        }

        $server = models\Server::findBy([
            'auth_token' => $auth_token,
        ]);
        if (!$server) {
            return Response::text(400, 'The auth token matches with no servers, please check its value');
        }

        $payload = $request->param('@input');
        $metric = models\Metric::init($server->id, $payload);
        $errors = $metric->validate();
        if ($errors) {
            $errors = array_column($errors, 'description');
            return Response::text(400, implode(' ', $errors));
        }

        $metric->save();

        return Response::text(200, 'OK');
    }
}
