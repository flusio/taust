<?php

namespace taust\cli;

use Minz\Response;
use taust\models;

class Users
{
    public function create($request)
    {
        $username = $request->param('username');
        $password = $request->param('password');

        $user = models\User::init($username, $password);
        $errors = $user->validate();
        if ($errors) {
            return Response::text(400, 'Can’t create a user: ' . implode(' ', $errors));
        }

        $user->save();

        return Response::text(200, "User {$user->username} created.");
    }
}
