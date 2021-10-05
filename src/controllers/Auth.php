<?php

namespace taust\controllers;

use Minz\Response;
use taust\models;
use taust\utils;

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
        $csrf = $request->param('csrf');

        if (!\Minz\CSRF::validate($csrf)) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $user = models\User::findBy(['username' => trim($username)]);
        if (!$user) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('Wrong credentials!'),
            ]);
        }

        if (!$user->verifyPassword($password)) {
            return Response::badRequest('auth/login.phtml', [
                'username' => $username,
                'error' => _('Wrong credentials!'),
            ]);
        }

        utils\CurrentUser::set($user->id);

        $response = Response::redirect('home');
        $response->setCookie('taust_session', $user->id, [
            'expires' => \Minz\Time::fromNow(7, 'days')->getTimestamp(),
        ]);
        return $response;
    }

    public function deleteSession($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $csrf = $request->param('csrf');
        if (!\Minz\CSRF::validate($csrf)) {
            return Response::redirect('home');
        }

        utils\CurrentUser::reset();

        $response = Response::redirect('login');
        $response->removeCookie('taust_session');
        return $response;
    }
}
