<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Basil PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Documents;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\ModuleManager;
use Documents\Model\DocumentsTable;

class Module implements AutoloaderProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
      $eventManager        = $e->getApplication()->getEventManager();
      $moduleRouteListener = new ModuleRouteListener();
      $moduleRouteListener->attach($eventManager);
    }
		
		public function init(ModuleManager $mm)
    {
			$mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__, 'dispatch', function($e) {
				$controller 			= $e->getTarget();
				$route 						= $controller->getEvent()->getRouteMatch();
				$controller_name 	= $route->getParam('controller');
				switch($controller_name) {
					default:
						$controller->layout('documents/layout');
				};
				$controller->getEvent()->getViewModel()->setVariables(array(
						    'current_controller' => $route->getParam('controller'),
						    'current_action' 		 => $route->getParam('action'),
						));
			});
    }

    public function getConfig()
    {
      return include __DIR__ . '/config/module.config.php';
    }
		    
    public function getServiceConfig()
    {
      return array(
        'factories' => array(
          'Documents\Model\DocumentsTable' =>  function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $table = new DocumentsTable($dbAdapter);
            return $table;
          },
        ),
      );
    }
    
    public function getControllerPluginConfig()
    {
      return array(
        'factories' => array(
          'Documents' => function($sm) {
            $sm = $sm->getServiceLocator();
            return new Plugin\Documents($sm);
          }
        )
      );
    }

    public function getAutoloaderConfig()
    {
      return array(
        'Zend\Loader\StandardAutoloader' => array(
          'namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
          ),
        ),
      );
    }
}
