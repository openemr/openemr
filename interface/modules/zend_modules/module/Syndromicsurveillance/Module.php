<?php

namespace Syndromicsurveillance;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\ClassMapAutoloader' => array(
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

    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e) {
            $controller = $e->getTarget();
            $controller->layout('syndromicsurveillance/layout/layout');
                $route = $controller->getEvent()->getRouteMatch();
                $controller->getEvent()->getViewModel()->setVariables(array(
                    'current_controller' => $route->getParam('controller'),
                    'current_action' => $route->getParam('action'),
                ));
        }, 100);
    }
}
