<?php
namespace Carecoordination;
use Carecoordination\Model\CarecoordinationTable;
use Carecoordination\Model\SetupTable;
use Carecoordination\Model\EncounterccdadispatchTable;
use Carecoordination\Model\EncountermanagerTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;
use Zend\View\Helper\Openemr\Emr;
use Zend\View\Helper\Openemr\Menu;
use Carecoordination\Model\Progressnote;
use Carecoordination\Model\ProgressnoteTable;
use Carecoordination\Model\Continuitycaredocument;
use Carecoordination\Model\ContinuitycaredocumentTable;
use Carecoordination\Model\CcdTable;

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
            $controller->layout('carecoordination/layout/layout');
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
                'Carecoordination\Model\CarecoordinationTable' =>  function($sm) {
                    $tableGateway = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CarecoordinationTable($tableGateway);
                    return $table;
                },		
		
		'Carecoordination\Model\EncounterccdadispatchTable' =>  function($sm) {
                    $tableGateway = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EncounterccdadispatchTable($tableGateway);
                    return $table;
                },
		
		'Carecoordination\Model\EncountermanagerTable' =>  function($sm) {
                    $tableGateway = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EncountermanagerTable($tableGateway);
                    return $table;
                },
		
		'Carecoordination\Model\SetupTable' =>  function($sm) {
                    $tableGateway = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SetupTable($tableGateway);
                    return $table;
                },
                        
		'Carecoordination\Model\CcdTable' =>  function($sm) {
                    $tableGateway = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CcdTable($tableGateway);
                    return $table;
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
