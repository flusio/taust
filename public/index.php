<?php

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */

$app_path = realpath(__DIR__ . '/..');

assert($app_path !== false);

include $app_path . '/vendor/autoload.php';

\Minz\Configuration::load('dotenv', $app_path);

$request = \Minz\Request::initFromGlobals();
$request->setParam('app_hostname', $_SERVER['HTTP_HOST']);

$application = new \taust\Application();
$response = $application->run($request);

$http_uri = $_SERVER['REQUEST_URI'];
$response->setHeader('Turbolinks-Location', $http_uri);
// This is useful only on Servers#show page, but because of Turbolinks, we must
// to be sure that the correct CSP is sent on every page.
$response->setContentSecurityPolicy('style-src', "'self' 'unsafe-inline'");

$is_head = strtoupper($_SERVER['REQUEST_METHOD']) === 'HEAD';
\Minz\Response::sendByHttp($response, echo_output: !$is_head);
