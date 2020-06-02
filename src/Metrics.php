<?php

namespace taust;

use Minz\Response;

class Metrics
{
    public function create($request)
    {
        $auth_token = $request->header('PHP_AUTH_PW');
        if (!$auth_token) {
            return Response::text(401, 'You must pass the server token as basic auth password');
        }

        $server_dao = new models\dao\Server();
        $metric_dao = new models\dao\Metric();

        $db_server = $server_dao->findBy([
            'auth_token' => $auth_token,
        ]);
        if (!$db_server) {
            return Response::text(400, 'The auth token matches with no servers, please check its value');
        }

        $payload = $request->param('@input');
        $metric = models\Metric::init($db_server['id'], $payload);
        $errors = $metric->validate();
        if ($errors) {
            $errors = array_column($errors, 'description');
            return Response::text(400, implode(' ', $errors));
        }

        $metric_dao->save($metric);

        return Response::text(200, 'OK');
    }
}
