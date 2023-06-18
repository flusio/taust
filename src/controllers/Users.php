<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\models;
use taust\utils;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Users
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
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

    /**
     * @request_param string email
     * @request_param string free_mobile_login
     * @request_param string free_mobile_key
     * @request_param string csrf
     *
     * @response 302 /login
     *     If the user is not connected.
     * @response 400
     *     If one of the parameter is invalid.
     * @response 200
     *     On success.
     */
    public function update(Request $request): Response
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        /** @var string */
        $email = $request->param('email', '');

        /** @var string */
        $free_mobile_login = $request->param('free_mobile_login', '');

        /** @var string */
        $free_mobile_key = $request->param('free_mobile_key', '');

        /** @var string */
        $csrf = $request->param('csrf', '');

        if (!\Minz\Csrf::validate($csrf)) {
            return Response::badRequest('users/show.phtml', [
                'email' => $email,
                'free_mobile_login' => $free_mobile_login,
                'free_mobile_key' => $free_mobile_key,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $current_user->email = \Minz\Email::sanitize($email);
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
