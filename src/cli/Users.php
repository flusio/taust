<?php

namespace taust\cli;

use Minz\Request;
use Minz\Response;
use taust\models;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Users
{
    /**
     * @request_param string username
     *     Only ASCII letters, numbers, underscore and dash
     * @request_param string password
     *
     * @response 400
     *     If the username or the password are incorrect.
     * @response 200
     *     On success.
     */
    public function create(Request $request): Response
    {
        $username = $request->parameters->getString('username', '');
        $password = $request->parameters->getString('password', '');

        $user = new models\User($username, $password);

        if (!$user->validate()) {
            return Response::text(400, 'Can’t create a user: ' . implode(' ', $user->errors()));
        }

        $user->save();

        return Response::text(200, "User {$user->username} created.");
    }
}
