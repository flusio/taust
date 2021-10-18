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
        $router->addRoute('post', '/alarms/:id/finish', 'Alarms#finish', 'finish alarm');

        $router->addRoute('get', '/pages', 'Pages#index', 'pages');
        $router->addRoute('get', '/pages/new', 'Pages#new', 'new page');
        $router->addRoute('post', '/pages/new', 'Pages#create', 'create page');
        $router->addRoute('get', '/pages/:id', 'Pages#show', 'show page');
        $router->addRoute('post', '/pages/:id', 'pages/Announcements#create', 'create announcement');
        $router->addRoute('get', '/pages/:id/announcements', 'pages/Announcements#index', 'page announcements');
        $router->addRoute('get', '/pages/:id/edit', 'Pages#edit', 'edit page');
        $router->addRoute('post', '/pages/:id/edit', 'Pages#update', 'update page');
        $router->addRoute('post', '/pages/:id/delete', 'Pages#delete', 'delete page');


        $router->addRoute('get', '/a/:id', 'Announcements#show', 'show announcement');
        $router->addRoute('get', '/a/:id/edit', 'Announcements#edit', 'edit announcement');
        $router->addRoute('post', '/a/:id/edit', 'Announcements#update', 'update announcement');
        $router->addRoute('post', '/a/:id/status', 'Announcements#updateStatus', 'update announcement status');
        $router->addRoute('post', '/a/:id/delete', 'Announcements#delete', 'delete announcement');

        return $router;
    }

    /**
     * Get the application router for a page
     *
     * @param string $page_id
     *
     * @return \Minz\Router
     */
    public static function loadForPage($page_id)
    {
        $router = new \Minz\Router();

        $router->addRoute('get', '/', 'Pages#show', 'home');
        $router->addRoute('get', '/a/:id', 'Announcements#show', 'show announcement');
        $router->addRoute('get', '/announcements', 'pages/Announcements#index', 'page announcements');

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
