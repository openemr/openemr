<?php
namespace Ccr;
use Ccr\Model\Ccr;
use Ccr\Model\CcrTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;
use Zend\View\Helper\Openemr\Emr;
use Zend\View\Helper\Openemr\Menu;

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
	
    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controller->layout('ccr/layout/layout');
                $route = $controller->getEvent()->getRouteMatch();
                $controller->getEvent()->getViewModel()->setVariables(array(
                    'current_controller' => $route->getParam('controller'),
                    'current_action' => $route->getParam('action'),
                )); 
        }, 100);
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Ccr\Model\CcrTable' =>  function($sm) {
                    $tableGateway = $sm->get('CcrTableGateway');
                    $table = new CcrTable($tableGateway);
                    return $table;
                },
                'CcrTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Ccr());
                    return new TableGateway('module_menu', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                // the array key here is the name you will call the view helper by in your view scripts
                'emr_helper' => function($sm) {
                    $locator = $sm->getServiceLocator(); // $sm is the view helper manager, so we need to fetch the main service manager
                    return new Emr($locator->get('Request'));
                },
                'menu' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    return new Menu();
                },
            ),
        );
    }
}
?>
