<?php

namespace taust;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Application
{
    private $engine;

    private $page;

    public function __construct($app_hostname = null)
    {
        include(\Minz\Configuration::$app_path . '/src/utils/view_helpers.php');

        $pages_by_hostnames = models\Page::daoToList('listByHostnames');
        if (isset($pages_by_hostnames[$app_hostname])) {
            $this->page = $pages_by_hostnames[$app_hostname];
            $router = Router::loadForPage($this->page->id);
        } else {
            $router = Router::load();
        }

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);
    }

    public function run($request)
    {
        if (!utils\CurrentUser::currentId()) {
            $user_id = $request->cookie('taust_session');
            utils\CurrentUser::set($user_id);
        }

        if ($this->page && !$request->param('id')) {
            $request->setParam('id', $this->page->id);
        }

        \Minz\Output\View::declareDefaultVariables([
            'environment' => \Minz\Configuration::$environment,
            'errors' => [],
            'error' => null,
            'current_user' => utils\CurrentUser::get(),
            'navigation_active' => null,
            'is_app_page' => $this->page !== null,
        ]);

        return $this->engine->run($request, [
            'not_found_view_pointer' => 'not_found.phtml',
            'controller_namespace' => '\\taust\\controllers',
        ]);
    }
}
