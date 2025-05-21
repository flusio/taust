<?php

namespace taust\controllers;

use Minz\Controller;
use Minz\Request;
use Minz\Response;
use taust\models;
use taust\errors;
use taust\utils;

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

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @return T
     */
    public function requireResource(string $class, string $pk_value): object
    {
        $model = null;
        if (is_callable([$class, 'find'])) {
            $model = $class::find($pk_value);
        }

        if (!$model) {
            throw new errors\MissingResourceError();
        }

        return $model;
    }

    #[Controller\ErrorHandler(errors\MissingResourceError::class)]
    public function failOnMissingRessource(Request $request): Response
    {
        return Response::notFound('not_found.phtml');
    }
}
