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
*    @author  Remesh Babu S <remesh@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Application;

use Application\Model\ApplicationTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
      $e->getApplication()->getServiceManager()->get('translator');
      $eventManager        = $e->getApplication()->getEventManager();
      $moduleRouteListener = new ModuleRouteListener();
      $moduleRouteListener->attach($eventManager);
    }
    
    public function getControllerPluginConfig()
    {
      return array(
        'factories' => array(
          'CommonPlugin' => function($sm) {
            $sm = $sm->getServiceLocator();
            return new Plugin\CommonPlugin($sm);
          }
        )
      );
    }

    public function getServiceConfig()
    {
      return array(
        'factories' => array(
          'Application\Model\ApplicationTable' =>  function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $table = new ApplicationTable($dbAdapter);
            return $table;
          },
        ),
      );
    }

    public function getConfig()
    {
      return include __DIR__ . '/config/module.config.php';
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
