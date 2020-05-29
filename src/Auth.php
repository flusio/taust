<?php

namespace taust;

use Minz\Response;

class Auth
{
    public function login()
    {
        if (utils\CurrentUser::get()) {
            return Response::redirect('home');
        }

        return Response::ok('auth/login.phtml', [
            'username' => '',
        ]);
    }

    public function createSession($request)
    {
        if (utils\CurrentUser::get()) {
            return Response::redirect('home');
        }

        $username = $request->param('username');
        $password = $request->param('password');
        $csrf = new \Minz\CSRF();

        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $user_dao = new models\dao\User();

        $db_user = $user_dao->findBy(['username' => trim($username)]);
        if (!$db_user) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('Wrong credentials!'),
            ]);
        }

        $user = new models\User($db_user);
        if (!$user->verifyPassword($password)) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('Wrong credentials!'),
            ]);
        }

        utils\CurrentUser::set($user->id);

        return Response::redirect('home');
    }

    public function deleteSession($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $csrf = new \Minz\CSRF();
        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::redirect('home');
        }

        utils\CurrentUser::reset();

        return Response::redirect('login');
    }
}
