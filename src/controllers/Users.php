<?php

namespace taust\controllers;

use Minz\Request;
use Minz\Response;
use taust\auth;
use taust\forms;

/**
 * @author  Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Users extends BaseController
{
    /**
     * @response 302 /login
     *     If the user is not connected.
     * @response 200
     *     On success.
     */
    public function show(Request $request): Response
    {
        $current_user = auth\CurrentUser::require();

        return Response::ok('users/show.phtml', [
            'form' => new forms\User(model: $current_user),
        ]);
    }

    /**
     * @request_param string email
     * @request_param string free_mobile_login
     * @request_param string free_mobile_key
     * @request_param string csrf_token
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
        $current_user = auth\CurrentUser::require();

        $form = new forms\User(model: $current_user);
        $form->handleRequest($request);

        if (!$form->validate()) {
            return Response::badRequest('users/show.phtml', [
                'form' => $form,
            ]);
        }

        $user = $form->model();
        $user->save();

        return Response::redirect('user');
    }
}
