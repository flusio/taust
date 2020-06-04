<?php

namespace taust;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Application
{
    private $engine;

    public function __construct()
    {
        include(\Minz\Configuration::$app_path . '/src/utils/view_helpers.php');

        $router = new \Minz\Router();
        $router->addRoute('get', '/', 'Dashboard#index', 'home');
        $router->addRoute('post', '/', 'Metrics#create', 'create metrics');

        $router->addRoute('get', '/login', 'Auth#login', 'login');
        $router->addRoute('post', '/login', 'Auth#createSession', 'create session');
        $router->addRoute('post', '/logout', 'Auth#deleteSession', 'logout');

        $router->addRoute('get', '/profile', 'Users#profile', 'profile');
        $router->addRoute('post', '/profile', 'Users#updateProfile', 'update profile');

        $router->addRoute('get', '/domains', 'Domains#index', 'domains');
        $router->addRoute('get', '/domains/new', 'Domains#new', 'new domain');
        $router->addRoute('post', '/domains/new', 'Domains#create', 'create domain');
        $router->addRoute('get', '/domains/:id', 'Domains#show', 'show domain');
        $router->addRoute('post', '/domains/:id/delete', 'Domains#delete', 'delete domain');

        $router->addRoute('get', '/servers', 'Servers#index', 'servers');
        $router->addRoute('get', '/servers/new', 'Servers#new', 'new server');
        $router->addRoute('post', '/servers/new', 'Servers#create', 'create server');
        $router->addRoute('get', '/servers/:id', 'Servers#show', 'show server');
        $router->addRoute('post', '/servers/:id/delete', 'Servers#delete', 'delete server');

        $router->addRoute('get', '/alarms', 'Alarms#index', 'alarms');

        $router->addRoute('cli', '/', 'System#usage');
        $router->addRoute('cli', '/system/setup', 'System#setup');
        $router->addRoute('cli', '/users/create', 'Users#create');
        $router->addRoute('cli', '/domains/heartbeats', 'Domains#heartbeats');
        $router->addRoute('cli', '/alarms/monitor', 'Alarms#monitor');
        $router->addRoute('cli', '/alarms/notify', 'Alarms#notify');

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);
    }

    public function run($request)
    {
        \Minz\Output\View::declareDefaultVariables([
            'environment' => \Minz\Configuration::$environment,
            'errors' => [],
            'error' => null,
            'current_user' => utils\CurrentUser::get(),
            'navigation_active' => null,
        ]);

        return $this->engine->run($request);
    }
}
