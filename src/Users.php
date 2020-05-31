<?php

namespace taust;

use Minz\Response;

class Users
{
    public function create($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $username = $request->param('username');
        $password = $request->param('password');

        $user = models\User::init($username, $password);
        $errors = $user->validate();
        if ($errors) {
            return Response::text(400, 'Can’t create a user: ' . implode(' ', $errors));
        }

        $user_dao = new models\dao\User();
        $user_dao->save($user);

        return Response::text(200, "User {$user->username} created.");
    }
}
