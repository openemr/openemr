<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace RestApiEventHook;

use OpenEMR\Events\RestApiExtend\RestApiExtendEvent;
use \RestConfig;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
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
        $oemrDispatcher->addListener(RestApiExtendEvent::EVENT_HANDLE, [$this, 'addRestAPIRouteToMap']);
    }

    /*
     * A function that adds new routes to a route map array
     * instead some_section need use a real name of section name
     * */
    public function addRestAPIRouteToMap($m){
        $extend_api = [
                        "GET /api/some_route" => function (){
                            RestConfig::authorization_check("some_section", "users");
                            return (new SomeClassWithSomeLogics())->getAll();
                        },
                        "GET /api/some_route/:rid" => function ($rid){
                            RestConfig::authorization_check("some_section", "users");
                            return (new SomeClassWithSomeLogics())->getByRouteId($rid);
                        }
                      ];

        foreach ($extend_api as $route => $action){
            $m->route_map_extended[$route] = $action;
        }

    }
}
