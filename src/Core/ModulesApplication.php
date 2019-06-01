<?php

/**
 * MainMenuRole class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use OpenEMR\Core\Kernel;
use Zend\Mvc\Application;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModulesApplication
{
    
    /**
     * The application reference pointer for the zend mvc modules application
     * @var \Zend\Mvc\Application
     */
    private $application;

    public function __construct(Kernel $kernel, $webRootPath, $modulePath, $zendModulePath)
    {
        $zendConfigurationPath = $webRootPath . DIRECTORY_SEPARATOR . $modulePath . DIRECTORY_SEPARATOR . $zendModulePath;
        $configuration = require $zendConfigurationPath . DIRECTORY_SEPARATOR . 'config/application.config.php';
        
        // Prepare the service manager
        // We customize this and skip using the static Zend\Mvc\Application::init in order to inject the
        // Symfony Kernel's EventListener that way we can bridge the two frameworks.
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : [];
        $smConfig = new ServiceManagerConfig($smConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->setService(EventDispatcherInterface::class, $kernel->getEventDispatcher());

        // Load modules
        $serviceManager->get('ModuleManager')->loadModules();

        // Prepare list of listeners to bootstrap
        $listenersFromAppConfig     = isset($configuration['listeners']) ? $configuration['listeners'] : [];
        $config                     = $serviceManager->get('config');
        $listenersFromConfigService = isset($config['listeners']) ? $config['listeners'] : [];

        $listeners = array_unique(array_merge($listenersFromConfigService, $listenersFromAppConfig));

        $this->application = $serviceManager->get('Application')->bootstrap($listeners);
    }

    public function run()
    {
        $this->application->run();
    }
}
