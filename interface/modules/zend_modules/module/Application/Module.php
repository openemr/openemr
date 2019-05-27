<?php
/**
 * interface/modules/zend_modules/module/Application/Module.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
