<?php

namespace Ccr;

use Laminas\ModuleManager\ModuleManager;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;

class Module
{
    public function getAutoloaderConfig()
    {
        return [
            \Laminas\Loader\ClassMapAutoloader::class => [
                __DIR__ . '/autoload_classmap.php',
            ],
            \Laminas\Loader\StandardAutoloader::class => [
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
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $userId = is_string($session->get('authUserID')) ? $session->get('authUserID') : '';
            if (
                !AclMain::zhAclCheck($userId, 'send_to_hie')
                && !AclMain::aclCheckCore('admin', 'super')
            ) {
                AccessDeniedHelper::deny('CCR module access denied');
            }

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
