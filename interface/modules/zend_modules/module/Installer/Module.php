<?
//    +-----------------------------------------------------------------------------+ 
//    OpenEMR - Open Source Electronic Medical Record
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
//    Author:   Jacob T.Paul <jacob@zhservices.com>
//           Shalini Balakrishnan  <shalini@zhservices.com>
//
// +------------------------------------------------------------------------------+

namespace Installer;

// Add these import statements:
use Installer\Model\InstModule; 
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Installer\Model\InstModuleTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Installer\Model\InstModuleTable' =>  function($sm) {
                    $tableGateway = $sm->get('InstModuleTableGateway');
                    $table = new InstModuleTable($tableGateway);
                    return $table;
                },
                'InstModuleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new InstModule());
                    return new TableGateway('InstModule', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
  
    
    public function onBootstrap(\Zend\EventManager\EventInterface $e)
    {
    	$config = $e->getApplication()->getServiceManager()->get('Configuration');   
    	$sessionConfig = new SessionConfig();
    	$sessionConfig->setOptions($config['session']);
    	$sessionManager = new SessionManager($sessionConfig, null, null);
    	Container::setDefaultManager($sessionManager);    
    	$sessionManager->start();    
    }
}?>
