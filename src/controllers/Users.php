<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

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

        $user->save();

        return Response::text(200, "User {$user->username} created.");
    }

    public function profile($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('users/profile.phtml', [
            'email' => $current_user->email,
            'free_mobile_login' => $current_user->free_mobile_login,
            'free_mobile_key' => $current_user->free_mobile_key,
        ]);
    }

    public function updateProfile($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $email = $request->param('email');
        $free_mobile_login = $request->param('free_mobile_login');
        $free_mobile_key = $request->param('free_mobile_key');
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::badRequest('users/profile.phtml', [
                'email' => $email,
                'free_mobile_login' => $free_mobile_login,
                'free_mobile_key' => $free_mobile_key,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $current_user->email = utils\Email::sanitize($email);
        $current_user->free_mobile_login = $free_mobile_login ? trim($free_mobile_login) : '';
        $current_user->free_mobile_key = $free_mobile_key ? trim($free_mobile_key) : '';

        $errors = $current_user->validate();
        if ($errors) {
            return Response::badRequest('users/profile.phtml', [
                'email' => $email,
                'free_mobile_login' => $free_mobile_login,
                'free_mobile_key' => $free_mobile_key,
                'errors' => $errors,
            ]);
        }

        $current_user->save();

        return Response::redirect('profile');
    }
}
