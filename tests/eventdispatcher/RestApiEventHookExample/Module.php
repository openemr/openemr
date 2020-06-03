<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace RestApiEventHookExample;

use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use RestConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Llaminas\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,

                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * load global variables foe every controllers
     * @param ModuleManager $manager
     */
    public function init(ModuleManager $manager)
    {
    }

    /**
     * @param MvcEvent $e
     *
     * Register our event listeners here
     */
    public function onBootstrap(MvcEvent $e)
    {
        // Get application service manager and get instance of event dispatcher
        $serviceManager = $e->getApplication()->getServiceManager();
        $oemrDispatcher = $serviceManager->get(EventDispatcherInterface::class);

        // listen for view events for routes in zend_modules
        $oemrDispatcher->addListener(RestApiCreateEvent::EVENT_HANDLE, [$this, 'addRestAPIRouteToMap']);
    }

    /*
     * A function that adds new routes to a route map array
     * instead some_section need use a real name of section name
     * Examples to tests/api/InternalApiTest.php:
     * echo HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/some_route', "GET", 'direct-json');
     * echo "<br/>";
     * echo HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/some_route/1', "GET", 'direct-json');
     * */
    public function addRestAPIRouteToMap($m)
    {
        $extend_route_map = [
                        "GET /api/some_route" => function () {
                            //RestConfig::authorization_check("some_section", "users");
                            return ["1","2","3"];
                        },
                        "GET /api/some_route/:rid" => function ($rid) {
                            //RestConfig::authorization_check("some_section", "users");
                            return [$rid];
                        }
                      ];

        $extend_fhir_route_map = [];

        if (count($extend_route_map) > 0) {
            foreach ($extend_route_map as $route => $action) {
                $m->addToRouteMap($route, $action);
            }
        }

        if (count($extend_fhir_route_map) > 0) {
            foreach ($extend_fhir_route_map as $route => $action) {
                $m->addToFHIRRouteMap($route, $action);
            }
        }
    }
}
