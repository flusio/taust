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
        $router->addRoute('get', '/', 'Auth#login', 'login');

        $router->addRoute('cli', '/', 'System#usage');
        $router->addRoute('cli', '/system/setup', 'System#setup');
        $router->addRoute('cli', '/users/create', 'Users#create');

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);
    }

    public function run($request)
    {
        return $this->engine->run($request);
    }
}
