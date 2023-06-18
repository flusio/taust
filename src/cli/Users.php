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
        /** @var string */
        $username = $request->param('username', '');

        /** @var string */
        $password = $request->param('password', '');

        $user = new models\User($username, $password);

        /** @var array<string, string> */
        $errors = $user->validate();
        if ($errors) {
            return Response::text(400, 'Can’t create a user: ' . implode(' ', $errors));
        }

        $user->save();

        return Response::text(200, "User {$user->username} created.");
    }
}
