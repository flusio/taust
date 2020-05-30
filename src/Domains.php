<?php

namespace taust;

use Minz\Response;

class Domains
{
    public function new()
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        return Response::ok('domains/new.phtml', [
            'id' => '',
        ]);
    }

    public function create($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = new \Minz\CSRF();

        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'error' => _('A security verification failed: you should retry to submit the form.'),
            ]);
        }

        $domain = models\Domain::init($id);
        $errors = $domain->validate();
        if ($errors) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'errors' => $errors,
            ]);
        }

        $domain_dao = new models\dao\Domain();
        $exist = $domain_dao->find($domain->id) !== null;
        if ($exist) {
            return Response::badRequest('domains/new.phtml', [
                'id' => $id,
                'errors' => [
                    'id' => _('This domain already exists.'),
                ],
            ]);
        }

        $domain_dao->save($domain);

        return Response::redirect('show domain', [
            'id' => $domain->id,
        ]);
    }

    public function show($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $domain_dao = new models\dao\Domain();
        $id = $request->param('id');
        $db_domain = $domain_dao->find($id);
        if (!$db_domain) {
            return Response::notFound('not_found.phtml');
        }

        $domain = new models\Domain($db_domain);
        return Response::ok('domains/show.phtml', [
            'domain' => $domain,
        ]);
    }
}
