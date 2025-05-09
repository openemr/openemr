<?php

/**
 * FHIR/Module  Handles the module instantiation for the FHIR module.  Note that because of the way laminas loads the
 * modules the namespace for this module is 'FHIR'.  However to avoid namespace clashes we have defined the namespace
 * to be under the OpenEMR namespace as seen in the getAutoloaderConfig method.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace FHIR;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use OpenEMR\ZendModules\FHIR\Listener\UuidMappingEventsSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Module
{
    const NAMESPACE_NAME = 'FHIR';

    public function getAutoloaderConfig()
    {
        // TODO: verify that we need this namespace autoloader... it should be on by default...
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'OpenEMR\\ZendModules\\' . __NAMESPACE__ => __DIR__ . '/src/' . self::NAMESPACE_NAME,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        // we grab the OpenEMR event listener (which is injected as Laminas has its own dispatcher)
        $serviceManager = $e->getApplication()->getServiceManager();
        $oemrDispatcher = $serviceManager->get(EventDispatcherInterface::class);

        // now we can listen to our module events
        $menuSubscriber = $serviceManager->get(UuidMappingEventsSubscriber::class);
        $oemrDispatcher->addSubscriber($menuSubscriber);
    }

    public function getServiceConfig()
    {
        return array();
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
