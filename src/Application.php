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
        $router = new \Minz\Router();
        $router->addRoute('get', '/', 'Dashboard#index', 'home');

        $router->addRoute('get', '/login', 'Auth#login', 'login');
        $router->addRoute('post', '/login', 'Auth#createSession', 'create session');
        $router->addRoute('post', '/logout', 'Auth#deleteSession', 'logout');

        $router->addRoute('get', '/profile', 'Users#profile', 'profile');
        $router->addRoute('post', '/profile', 'Users#updateProfile', 'update profile');

        $router->addRoute('get', '/domains/new', 'Domains#new', 'new domain');
        $router->addRoute('post', '/domains/new', 'Domains#create', 'create domain');
        $router->addRoute('get', '/domains/:id', 'Domains#show', 'show domain');

        $router->addRoute('cli', '/', 'System#usage');
        $router->addRoute('cli', '/system/setup', 'System#setup');
        $router->addRoute('cli', '/users/create', 'Users#create');
        $router->addRoute('cli', '/domains/heartbeats', 'Domains#heartbeats');
        $router->addRoute('cli', '/alarms/monitor', 'Alarms#monitor');

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
        ]);

        return $this->engine->run($request);
    }
}
