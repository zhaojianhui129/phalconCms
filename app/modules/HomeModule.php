<?php
namespace MyApp\Modules;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Events\Manager as EventsManager;
use MyApp\Plugins\SecurityPlugin;
use MyApp\Plugins\NotFoundPlugin;
use MyApp\Plugins\ExceptionsPlugin;

class HomeModule implements ModuleDefinitionInterface
{
    /**
     * Registers the module auto-loader
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $config = $di['config'];
        $loader = new Loader();

        /**
         * We're a registering a set of directories taken from the configuration file
         */
        $loader->registerDirs([
            $config->application->controllersDir,
            $config->application->pluginsDir,
            $config->application->libraryDir,
            $config->application->modelsDir,
            $config->application->formsDir,
        ])->register();

        $loader->registerNamespaces(
            [
                'MyApp\Home\Controllers' => APP_PATH . '/app/controllers/home',
                'MyApp\Home\Forms'       => APP_PATH . '/app/forms/home',
                'MyApp\Models'           => $config->application->modelsDir,
                'MyApp\Library'          => $config->application->libraryDir,
                'MyApp\Plugins'          => $config->application->pluginsDir,
                'MyApp\Service'          => $config->application->serviceDir,
            ]
        );

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        $config = $di['config'];
        // Registering a dispatcher
        $di->set('dispatcher', function () {
            $eventsManager = new EventsManager;

            /**
             * Check if the user is allowed to access certain action using the SecurityPlugin
             */
            //权限验证,在进入循环调度后触发
            //$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin('home'));

            /**
             * Handle exceptions and not-found exceptions using NotFoundPlugin
             */
            //错误页面,在调度器抛出任意异常前触发
            //$eventsManager->attach('dispatch:beforeNotFoundAction', new NotFoundPlugin);
            //$eventsManager->attach('dispatch:beforeException', new ExceptionsPlugin);

            $dispatcher = new Dispatcher;
            $dispatcher->setDefaultNamespace('MyApp\Home\Controllers\\');
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        // Registering the view component
        $di->set('view', function () use ($config) {
            $view = new View();
            $view->setViewsDir($config->application->viewsDir . 'home/');

            $view->registerEngines(array(
                '.volt'  => function ($view, $di) use ($config) {

                    $volt = new VoltEngine($view, $di);

                    $volt->setOptions(array(
                        'compiledPath'      => $config->application->cacheDir,
                        'compiledSeparator' => '_',
                    ));

                    return $volt;
                },
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php', // Generate Template files uses PHP itself as the template engine
            ));
            return $view;
        });

    }
}
