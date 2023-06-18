<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\models;
use taust\utils;

class Auth
{
    /**
     * @response 302 /
     *     If the user is already connected.
     * @response 200
     *     On success.
     */
    public function login(): Response
    {
        if (utils\CurrentUser::get()) {
            return Response::redirect('home');
        }

        return Response::ok('auth/login.phtml', [
            'username' => '',
        ]);
    }

    /**
     * @request_param string username
     * @request_param string password
     * @request_param string csrf
     *
     * @response 400
     *     If one of the parameters is invalid.
     * @response 302 /
     *     On success, or if the user is already connected.
     */
    public function createSession(Request $request): Response
    {
        if (utils\CurrentUser::get()) {
            return Response::redirect('home');
        }

        /** @var string */
        $username = $request->param('username', '');

        /** @var string */
        $password = $request->param('password', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
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

    /**
     * @request_param string csrf
     *
     * @response 302 /
     *     If the CSRF is invalid.
     * @response 302 /login
     *     On success, or if the user is not connected.
     */
    public function deleteSession(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::redirect('home');
        }

        utils\CurrentUser::reset();

        $response = Response::redirect('login');
        $response->removeCookie('taust_session');
        return $response;
    }
}
