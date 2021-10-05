<?php

namespace taust;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Router
{
    /**
     * Get the application router (doesn't include CLI routes)
     *
     * @return \Minz\Router
     */
    public static function load()
    {
        $router = new \Minz\Router();

        $router->addRoute('get', '/', 'Dashboard#index', 'home');
        $router->addRoute('post', '/', 'Metrics#create', 'create metrics');

        $router->addRoute('get', '/login', 'Auth#login', 'login');
        $router->addRoute('post', '/login', 'Auth#createSession', 'create session');
        $router->addRoute('post', '/logout', 'Auth#deleteSession', 'logout');

        $router->addRoute('get', '/profile', 'Users#show', 'user');
        $router->addRoute('post', '/profile', 'Users#update', 'update user');

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

        return $router;
    }

    /**
     * Get the CLI router (includes application routes)
     *
     * @return \Minz\Router
     */
    public static function loadCli()
    {
        $router = self::load();

        $router->addRoute('cli', '/', 'Help#show');
        $router->addRoute('cli', '/help', 'Help#show');

        $router->addRoute('cli', '/system/setup', 'System#setup');
        $router->addRoute('cli', '/system/clear-old', 'System#clearOld');

        $router->addRoute('cli', '/users/create', 'Users#create');

        $router->addRoute('cli', '/domains/heartbeats', 'Domains#heartbeats');

        $router->addRoute('cli', '/alarms/monitor', 'Alarms#monitor');
        $router->addRoute('cli', '/alarms/notify', 'Alarms#notify');

        return $router;
    }
}
