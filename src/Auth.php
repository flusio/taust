<?php

namespace taust;

use Minz\Response;

class Auth
{
    public function login()
    {
        return Response::ok('auth/login.phtml');
    }
}
