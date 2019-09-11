<?php

/**
 * ModulesApplication class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zend\Mvc\Application;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class ModulesApplication
{
    /**
     * The application reference pointer for the zend mvc modules application
     * @var Application
     *
     */
    private $application;

    const CUSTOM_MODULE_BOOSTRAP_NAME = 'openemr.bootstrap.php';

    public function __construct(Kernel $kernel, $webRootPath, $modulePath, $zendModulePath)
    {
        // Beware: default module path ends in a slash. Really should not but have to refactor to change..
        $zendConfigurationPath = $webRootPath . '/' . $modulePath . $zendModulePath;
        $customModulePath = $webRootPath . '/' . $modulePath . "custom_modules" . '/';
        $configuration = require $zendConfigurationPath . '/' . 'config/application.config.php';

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
        $listenersFromAppConfig = isset($configuration['listeners']) ? $configuration['listeners'] : [];
        $config = $serviceManager->get('config');
        $listenersFromConfigService = isset($config['listeners']) ? $config['listeners'] : [];

        $listeners = array_unique(array_merge($listenersFromConfigService, $listenersFromAppConfig));

        $this->application = $serviceManager->get('Application')->bootstrap($listeners);

        // we skip the audit log as it has no bearing on user activity and is core system related...
        $resultSet = sqlStatementNoLog($statement = "SELECT mod_name FROM modules WHERE mod_active = 1 AND type != 1 ORDER BY `mod_ui_order`, `date`");
        $db_modules = [];
        while ($row = sqlFetchArray($resultSet)) {
            $db_modules[] = $row["mod_name"];
        }

        $this->bootstrapCustomModules($kernel->getEventDispatcher(), $customModulePath);
    }

    private function bootstrapCustomModules($eventDispatcher, $customModulePath)
    {
        // we skip the audit log as it has no bearing on user activity and is core system related...
        $resultSet = sqlStatementNoLog($statement = "SELECT mod_name, mod_directory FROM modules WHERE mod_active = 1 AND type != 1 ORDER BY `mod_ui_order`, `date`");
        $db_modules = [];
        while ($row = sqlFetchArray($resultSet)) {
            $db_modules[] = ["name" => $row["mod_name"], "directory" => $row['mod_directory'], "path" => $customModulePath . $row['mod_directory']];
        }
        foreach ($db_modules as $module) {
            $this->loadCustomModule($module, $eventDispatcher);
        }
        // TODO: stephen we should fire an event saying we've now loaded all the modules here.
    }

    private function loadCustomModule($module, $eventDispatcher)
    {
        if (!is_readable($module['path'] . '/' . attr(self::CUSTOM_MODULE_BOOSTRAP_NAME))) {
            error_log("Custom module file path " . errorLogEscape($module['path'])
                . '/' . self::CUSTOM_MODULE_BOOSTRAP_NAME
                . " is not readable.  Check directory permissions");
        }
        try {
            // the only thing in scope here is $module and $eventDispatcher which is ok for our bootstrap piece.
            // do we really want to just include a file??  Should we go all zend and actually force a class instantiation
            // here and then inject the EventDispatcher or even possibly the Symfony Kernel here?
            include $module['path'] . '/' . attr(self::CUSTOM_MODULE_BOOSTRAP_NAME);
        } catch (Exception $exception) {
            error_log(errorLogEscape($exception->getMessage()));
        }
    }

    public function run()
    {
        $this->application->run();
    }
}
