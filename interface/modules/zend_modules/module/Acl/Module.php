<?php
// +-----------------------------------------------------------------------------+
//OpenEMR - Open Source Electronic Medical Record
//    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
// Author:  Jacob T.Paul <jacob@zhservices.com>
//          Basil PT <basil@zhservices.com>  
//
// +------------------------------------------------------------------------------+


namespace Acl;

use Acl\Model\Acl;
use Acl\Model\AclTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;
use Zend\View\Helper\Openemr\Emr;
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
	
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Acl\Model\AclTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AclTable($dbAdapter);
                    return $table;
                },
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
            $controller->layout('acl/layout/layout');
            $route = $controller->getEvent()->getRouteMatch();
            $controller->getEvent()->getViewModel()->setVariables(array(
                'current_controller' => $route->getParam('controller'),
                'current_action' => $route->getParam('action'),
            )); 
        }, 100);
    }
    

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'emr_helper' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    return new Emr($locator->get('Request'));
                },
            ),
        );
    }
}
?>
