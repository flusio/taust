<?php

namespace taust;

use Minz\Response;

class Users
{
    public function create($request)
    {
        $username = $request->param('username');
        $password = $request->param('password');

        $user = models\User::init($username, $password);
        $errors = $user->validate();
        if ($errors) {
            $errors = implode(' ', array_column($errors, 'description'));
            return Response::text(400, 'Can’t create a user: ' . $errors);
        }

        $user_dao = new models\dao\User();
        $user_dao->save($user);

        return Response::text(200, "User {$user->username} created.");
    }
}
