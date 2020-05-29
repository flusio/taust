<?php

namespace taust;

use Minz\Response;

class Dashboard
{
    public function index()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('dashboard/index.phtml');
    }
}
