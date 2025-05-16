<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\forms;
use taust\models;
use taust\utils;

class Auth extends BaseController
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
            'form' => new forms\Login(),
        ]);
    }

    /**
     * @request_param string username
     * @request_param string password
     * @request_param string csrf_token
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

        $form = new forms\Login();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('auth/login.phtml', [
                'form' => $form,
            ]);
        }

        $user = $form->user();

        utils\CurrentUser::set($user->id);

        $response = Response::redirect('home');
        $response->setCookie('taust_session', $user->id, [
            'expires' => \Minz\Time::fromNow(7, 'days')->getTimestamp(),
        ]);
        return $response;
    }

    /**
     * @request_param string csrf_token
     *
     * @response 302 /
     *     If the CSRF is invalid.
     * @response 302 /login
     *     On success, or if the user is not connected.
     */
    public function deleteSession(Request $request): Response
    {
        $this->requireCurrentUser();

        $form = new forms\BaseForm();
        $form->handleRequest($request);

        if (!$form->validate()) {
            return \Minz\Response::redirect('home');
        }

        utils\CurrentUser::reset();

        $response = Response::redirect('login');
        $response->removeCookie('taust_session');
        return $response;
    }
}
