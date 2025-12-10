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
     */
    public static function load(): \Minz\Router
    {
        $router = new \Minz\Router();

        $router->addRoute('GET', '/', 'Dashboard#index', 'home');
        $router->addRoute('POST', '/', 'Metrics#create', 'create metrics');

        $router->addRoute('GET', '/login', 'Authentications#login', 'login');
        $router->addRoute('POST', '/login', 'Authentications#createSession', 'create session');
        $router->addRoute('POST', '/logout', 'Authentications#deleteSession', 'logout');

        $router->addRoute('GET', '/profile', 'Users#show', 'user');
        $router->addRoute('POST', '/profile', 'Users#update', 'update user');

        $router->addRoute('GET', '/domains', 'Domains#index', 'domains');
        $router->addRoute('GET', '/domains/new', 'Domains#new', 'new domain');
        $router->addRoute('POST', '/domains/new', 'Domains#create', 'create domain');
        $router->addRoute('GET', '/domains/:id', 'Domains#show', 'show domain');
        $router->addRoute('POST', '/domains/:id/delete', 'Domains#delete', 'delete domain');

        $router->addRoute('GET', '/servers', 'Servers#index', 'servers');
        $router->addRoute('GET', '/servers/new', 'Servers#new', 'new server');
        $router->addRoute('POST', '/servers/new', 'Servers#create', 'create server');
        $router->addRoute('GET', '/servers/:id', 'Servers#show', 'show server');
        $router->addRoute('POST', '/servers/:id/delete', 'Servers#delete', 'delete server');

        $router->addRoute('GET', '/alarms', 'Alarms#index', 'alarms');
        $router->addRoute('POST', '/alarms/:id/finish', 'Alarms#finish', 'finish alarm');

        $router->addRoute('GET', '/pages', 'Pages#index', 'pages');
        $router->addRoute('GET', '/pages/new', 'Pages#new', 'new page');
        $router->addRoute('POST', '/pages/new', 'Pages#create', 'create page');
        $router->addRoute('GET', '/pages/:id', 'Pages#show', 'show page');
        $router->addRoute('GET', '/pages/:id/feed', 'Pages#feed', 'page feed');
        $router->addRoute('GET', '/pages/:id/style', 'Pages#style', 'page style');
        $router->addRoute('POST', '/pages/:id', 'pages/Announcements#create', 'create announcement');
        $router->addRoute('GET', '/pages/:id/announcements', 'pages/Announcements#index', 'page announcements');
        $router->addRoute('GET', '/pages/:id/edit', 'Pages#edit', 'edit page');
        $router->addRoute('POST', '/pages/:id/edit', 'Pages#update', 'update page');
        $router->addRoute('POST', '/pages/:id/delete', 'Pages#delete', 'delete page');


        $router->addRoute('GET', '/a/:id', 'Announcements#show', 'show announcement');
        $router->addRoute('GET', '/a/:id/edit', 'Announcements#edit', 'edit announcement');
        $router->addRoute('POST', '/a/:id/edit', 'Announcements#update', 'update announcement');
        $router->addRoute('POST', '/a/:id/status', 'Announcements#updateStatus', 'update announcement status');
        $router->addRoute('POST', '/a/:id/delete', 'Announcements#delete', 'delete announcement');

        return $router;
    }

    /**
     * Get the application router for a page
     */
    public static function loadForDedicatedHostname(): \Minz\Router
    {
        $router = new \Minz\Router();

        $router->addRoute('GET', '/', 'Pages#show', 'home');
        $router->addRoute('GET', '/feed', 'Pages#feed', 'page feed');
        $router->addRoute('GET', '/style', 'Pages#style', 'page style');
        $router->addRoute('GET', '/a/:id', 'Announcements#show', 'show announcement');
        $router->addRoute('GET', '/announcements', 'pages/Announcements#index', 'page announcements');

        return $router;
    }

    /**
     * Get the CLI router (includes application routes)
     */
    public static function loadCli(): \Minz\Router
    {
        $router = self::load();

        $router->addRoute('CLI', '/', 'Help#show');
        $router->addRoute('CLI', '/help', 'Help#show');

        $router->addRoute('CLI', '/users/create', 'Users#create');

        $router->addRoute('CLI', '/migrations', 'Migrations#index');
        $router->addRoute('CLI', '/migrations/setup', 'Migrations#setup');
        $router->addRoute('CLI', '/migrations/rollback', 'Migrations#rollback');
        $router->addRoute('CLI', '/migrations/create', 'Migrations#create');

        $router->addRoute('CLI', '/jobs', 'Jobs#index');
        $router->addRoute('CLI', '/jobs/watch', 'Jobs#watch');
        $router->addRoute('CLI', '/jobs/run', 'Jobs#run');
        $router->addRoute('CLI', '/jobs/show', 'Jobs#show');
        $router->addRoute('CLI', '/jobs/unfail', 'Jobs#unfail');
        $router->addRoute('CLI', '/jobs/unlock', 'Jobs#unlock');

        return $router;
    }
}
