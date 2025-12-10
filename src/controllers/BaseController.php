<?php

namespace taust\controllers;

use Minz\Controller;
use Minz\Request;
use Minz\Response;
use taust\auth;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class BaseController
{
    #[Controller\ErrorHandler(auth\MissingCurrentUserError::class)]
    public function redirectOnMissingCurrentUser(Request $request): Response
    {
        return Response::redirect('login');
    }

    #[Controller\ErrorHandler(\Minz\Errors\MissingRecordError::class)]
    public function failOnMissingRessource(
        Request $request,
        \Minz\Errors\MissingRecordError $error,
    ): Response {
        return Response::notFound('not_found.phtml', [
            'error' => $error,
        ]);
    }
}
