<?php

namespace Ccr;

use Laminas\Loader\ClassMapAutoloader;
use Laminas\Loader\StandardAutoloader;
use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function getAutoloaderConfig()
    {
        return [
            ClassMapAutoloader::class => [
                __DIR__ . '/autoload_classmap.php',
            ],
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,

                ],
            ],
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e): void {
            $controller = $e->getTarget();
            $controller->layout('ccr/layout/layout');
                $route = $controller->getEvent()->getRouteMatch();
                $controller->getEvent()->getViewModel()->setVariables([
                    'current_controller' => $route->getParam('controller'),
                    'current_action' => $route->getParam('action'),
                ]);
        }, 100);
    }
}
