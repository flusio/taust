<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Metrics extends BaseController
{
    /**
     * @request_header string PHP_AUTH_PW
     * @request_param array @input
     *
     * @response 401
     *     If the auth token is not passed.
     * @response 400
     *     If the auth token or the payload are invalid.
     * @response 200
     *     On success.
     */
    public function create(Request $request): Response
    {
        $auth_token = $this->getAuthToken($request);

        if (!$auth_token) {
            return Response::text(401, 'You must pass the server token as basic auth password');
        }

        $server = models\Server::findBy([
            'auth_token' => $auth_token,
        ]);

        if (!$server) {
            return Response::text(400, 'The auth token matches with no servers, please check its value');
        }

        $payload = $request->parameters->getJson('@input');
        if ($payload === null) {
            return Response::text(400, 'The payload is not a valid JSON.');
        }

        $cleanPayload = [
            'at' => $payload['at'] ?? \Minz\Time::now()->getTimestamp(),
            'cpu_percent' => $payload['cpu_percent'] ?? [],
            'memory_total' => $payload['memory_total'] ?? 0,
            'memory_available' => $payload['memory_available'] ?? 0,
            'disks' => $payload['disks'] ?? [],
        ];

        // @phpstan-ignore-next-line
        $metric = new models\Metric($server->id, $cleanPayload);
        $metric->save();

        return Response::text(200, 'OK');
    }

    private function getAuthToken(Request $request): ?string
    {
        $authorization_header = $request->headers->getString('Authorization', '');

        $result = preg_match('/^Basic (?P<token>[\w=]+)$/', $authorization_header, $matches);
        if ($result === false || !isset($matches['token'])) {
            return null;
        }

        $token = $matches['token'];
        $decoded_token = base64_decode($token);

        $decoded_token_parts = explode(':', $decoded_token);
        if (count($decoded_token_parts) !== 2) {
            return null;
        }

        return $decoded_token_parts[1];
    }
}
