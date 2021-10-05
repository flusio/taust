<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

class Users
{
    public function show($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('users/show.phtml', [
            'email' => $current_user->email,
            'free_mobile_login' => $current_user->free_mobile_login,
            'free_mobile_key' => $current_user->free_mobile_key,
        ]);
    }

    public function update($request)
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
            return Response::badRequest('users/show.phtml', [
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
            return Response::badRequest('users/show.phtml', [
                'email' => $email,
                'free_mobile_login' => $free_mobile_login,
                'free_mobile_key' => $free_mobile_key,
                'errors' => $errors,
            ]);
        }

        $current_user->save();

        return Response::redirect('user');
    }
}
