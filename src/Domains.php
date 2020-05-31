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
        $heartbeat_dao = new models\dao\Heartbeat();
        $alarm_dao = new models\dao\Alarm();

        $id = $request->param('id');
        $db_domain = $domain_dao->find($id);
        if (!$db_domain) {
            return Response::notFound('not_found.phtml');
        }

        $domain = new models\Domain($db_domain);
        $alarms = $alarm_dao->listBy([
            'domain_id' => $domain->id
        ]);
        return Response::ok('domains/show.phtml', [
            'domain' => $domain,
            'last_heartbeat' => $heartbeat_dao->findLastHeartbeat($domain->id),
            'alarms' => $alarms,
        ]);
    }

    public function delete($request)
    {
        $current_user = utils\CurrentUser::get();
        if (!$current_user) {
            return Response::redirect('login');
        }

        $id = $request->param('id');
        $csrf = new \Minz\CSRF();

        if (!$csrf->validateToken($request->param('csrf'))) {
            return Response::redirect('show domain', ['id' => $id]);
        }

        $domain_dao = new models\dao\Domain();
        $db_domain = $domain_dao->find($id);
        if (!$db_domain) {
            return Response::notFound('not_found.phtml');
        }

        $domain_dao->delete($db_domain['id']);

        return Response::redirect('home');
    }
    public function heartbeats($request)
    {
        if ($request->method() !== 'cli') {
            return Response::text(400, 'This endpoint must be called from command line.');
        }

        $domain_dao = new models\dao\Domain();
        $heartbeat_dao = new models\dao\Heartbeat();
        $db_domains = $domain_dao->listAll();

        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 5);

        $results = [];
        foreach ($db_domains as $db_domain) {
            $domain_id = $db_domain['id'];
            curl_setopt($curl_session, CURLOPT_URL, 'https://' . $domain_id);

            $result = curl_exec($curl_session);
            $http_code = curl_getinfo($curl_session, CURLINFO_RESPONSE_CODE);

            if ($http_code >= 200 && $http_code < 400) {
                $is_success = 1;
                $details = "OK";
            } else {
                $is_success = 0;
                $error = curl_error($curl_session);
                $details = "{$error} (code {$http_code})";
            }

            $heartbeat_dao->create([
                'created_at' => \Minz\Time::now()->format(\Minz\Model::DATETIME_FORMAT),
                'is_success' => $is_success,
                'details' => $details,
                'domain_id' => $domain_id,
            ]);

            $results[] = "{$domain_id}: {$details}";
        }

        curl_close($curl_session);

        return Response::text(200, implode("\n", $results));
    }
}
