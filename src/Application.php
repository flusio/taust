<?php

namespace taust;

use Minz\Request;
use Minz\Response;

/**
 * @phpstan-import-type ResponseReturnable from Response
 *
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Application
{
    /**
     * @return ResponseReturnable
     */
    public function run(Request $request): mixed
    {
        if ($request->method() === 'CLI') {
            $this->initCli($request);
        } else {
            $this->initApp($request);
        }

        $response = \Minz\Engine::run($request);

        if ($response instanceof Response && $request->method() !== 'CLI') {
            /** @var string */
            $http_uri = $_SERVER['REQUEST_URI'];
            $response->setHeader('Turbolinks-Location', $http_uri);
            // This is useful only on Servers#show page, but because of Turbolinks, we must
            // to be sure that the correct CSP is sent on every page.
            $response->setContentSecurityPolicy('style-src', "'self' 'unsafe-inline'");
        }

        return $response;
    }

    private function initCli(Request $request): void
    {
        $router = Router::loadCli();

        $bin = $request->param('bin');
        $bin = $bin === 'cli' ? 'php cli' : $bin;

        $current_command = $request->path();
        $current_command = trim(str_replace('/', ' ', $current_command));

        \Minz\Engine::init($router, [
            'start_session' => false,
            'not_found_view_pointer' => 'cli/not_found.txt',
            'internal_server_error_view_pointer' => 'cli/internal_server_error.txt',
            'controller_namespace' => '\\taust\\cli',
        ]);

        \Minz\Output\View::declareDefaultVariables([
            'error' => null,
            'bin' => $bin,
            'current_command' => $current_command,
        ]);
    }

    private function initApp(Request $request): void
    {
        /** @var string */
        $app_hostname = $request->param('app_hostname', '');

        if ($app_hostname !== '') {
            $page = models\Page::findBy([
                'hostname' => $app_hostname,
            ]);
        } else {
            $page = null;
        }

        if ($page) {
            $parsed_hostname = parse_url($page->hostname);

            if (isset($parsed_hostname['host'])) {
                \Minz\Configuration::$url_options['host'] = $parsed_hostname['host'];
            } else {
                \Minz\Configuration::$url_options['host'] = $app_hostname;
            }

            if (isset($parsed_hostname['port'])) {
                \Minz\Configuration::$url_options['port'] = $parsed_hostname['port'];
            }

            $router = Router::loadForDedicatedHostname();
        } else {
            $router = Router::load();
        }

        \Minz\Output\View::$extensions_to_content_types['.atom.xml.phtml'] = 'application/xml';

        \Minz\Engine::init($router, [
            'start_session' => true,
            'not_found_view_pointer' => 'not_found.phtml',
            'internal_server_error_view_pointer' => 'internal_server_error.phtml',
            'controller_namespace' => '\\taust\\controllers',
        ]);

        if (!utils\CurrentUser::currentId()) {
            /** @var string */
            $user_id = $request->cookie('taust_session', '');
            utils\CurrentUser::set($user_id);
        }

        $available_locales = utils\Locale::availableLocales();
        if ($page && isset($available_locales[$page->locale])) {
            $locale = $page->locale;
        } else {
            /** @var string */
            $http_accept_language = $request->header('HTTP_ACCEPT_LANGUAGE', '');
            $locale = utils\Locale::best($http_accept_language);
        }
        utils\Locale::setCurrentLocale($locale);

        if ($page && !$request->param('id')) {
            $request->setParam('id', $page->id);
        }

        \Minz\Output\View::declareDefaultVariables([
            'environment' => \Minz\Configuration::$environment,
            'errors' => [],
            'error' => null,
            'current_user' => utils\CurrentUser::get(),
            'current_locale' => $locale,
            'navigation_active' => null,
            'is_app_page' => $page !== null,
            'csrf_token' => \Minz\Csrf::generate(),
        ]);
    }
}
