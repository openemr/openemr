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
use Application\Model\SendtoTable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        /**
         * Determines if the module namespace should be prepended to the controller name.
         * This is the case if the route match contains a parameter key matching the MODULE_NAMESPACE constant.
         */
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // @see https://stackoverflow.com/a/21601229/7884612 for how to debug this.
        // UNCOMMENT THESE TWO LINES IF YOU WANT TO SEE THE REGISTERED FACTORIES FOR DEBUGGING
        // $config = $e->getApplication()->getServiceManager()->get('Config');
        // error_log("Factories: " . var_export(array_keys($config['service_manager']['factories']), true));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    // TODO: The zf3 autoloader should handle autoloading these classes by default but it's not right now
    // we need to figure out why that is so we can remove this unnecessary piece.
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
