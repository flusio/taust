<?php

namespace taust\controllers;

use Minz\Controller;
use Minz\Request;
use Minz\Response;
use taust\models;
use taust\errors;
use taust\utils;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class BaseController
{
    public function requireCurrentUser(): models\User
    {
        $current_user = utils\CurrentUser::get();

        if (!$current_user) {
            throw new errors\MissingCurrentUserError();
        }

        return $current_user;
    }

    #[Controller\ErrorHandler(errors\MissingCurrentUserError::class)]
    public function redirectOnMissingCurrentUser(Request $request): Response
    {
        return Response::redirect('login');
    }

    #[Controller\ErrorHandler(errors\MissingResourceError::class)]
    public function failOnMissingRessource(Request $request): Response
    {
        return Response::notFound('not_found.phtml');
    }
}
