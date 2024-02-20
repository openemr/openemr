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

use OpenEMR\Common\Acl\AccessDeniedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Laminas\Mvc\Application;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

class ModulesApplication
{
    const MODULE_TYPE_CUSTOM = 0;
    const MODULE_TYPE_LAMINAS = 1;

    /**
     * The application reference pointer for the zend mvc modules application
     *
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
        // We customize this and skip using the static Laminas\Mvc\Application::init in order to inject the
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

        // let's get our autoloader that people can tie into if they need it
        $autoloader = new ModulesClassLoader($webRootPath);
        $this->bootstrapCustomModules($autoloader, $kernel->getEventDispatcher(), $webRootPath, $customModulePath);
    }

    /**
     * Checks to see if the currently called script is a module script and whether it is allowed to be executed.
     * It relies on the $_SERVER['SCRIPT_NAME'] path which is established by the server not the calling client. It
     * checks against both laminas and custom modules. If the script is not allowed it throws an AccessDeniedException
     *
     * @param $modType The type of module this is (laminas or custom)
     * @param $webRootPath The root filepath for the directory where OpenEMR is installed
     * @param $modulePath The path for the module folder location (laminas or custom)
     * @throws AccessDeniedException Thrown if this is a file in a module script directory and the module is not enabled.
     */
    public static function checkModuleScriptPathForEnabledModule($modType, $webRootPath, $modulePath)
    {
        // as we do this we are going to do a security check against the current script name
        // if we are in a module
        $scriptName = $webRootPath . $_SERVER['SCRIPT_NAME'];
        if (str_starts_with($scriptName, $modulePath)) {
            // the script being called is a custom module directory.
            $type = $modType == self::MODULE_TYPE_LAMINAS ? self::MODULE_TYPE_LAMINAS : '';

            $truncatedPath = substr($scriptName, strlen($modulePath));
            $folderName = strtok($truncatedPath, '/');
            if ($folderName !== false) {
                $resultSet = sqlStatementNoLog($statement = "SELECT mod_name, mod_directory FROM modules "
                . " WHERE mod_active = 1 AND type = ? AND mod_directory = ? ", [$type, $folderName]);
                $row = sqlFetchArray($resultSet);
                if (empty($row)) {
                    throw new AccessDeniedException("admin", "super", "Access to module path for disabled module is denied");
                }
            }
        }
    }


    /**
     * Grabs the actively enabled modules from the database and injects them into the system.
     * For the list of active modules you can see them from the modules installer tab, or by querying the modules table
     * Otherwise the modules are found inside the modules/zend_modules folder.  The uninstalled script will dynamically find them
     * in the filesystem.
     */
    public static function oemr_zend_load_modules_from_db($webRootPath, $zendConfigurationPath)
    {
        $zendConfigurationPathCheck = $zendConfigurationPath . DIRECTORY_SEPARATOR . "module";
        self::checkModuleScriptPathForEnabledModule(self::MODULE_TYPE_LAMINAS, $webRootPath, $zendConfigurationPathCheck);
        // we skip the audit log as it has no bearing on user activity and is core system related...
        $resultSet = sqlStatementNoLog($statement = "SELECT mod_name FROM modules WHERE mod_active = 1 AND type = 1 ORDER BY `mod_ui_order`, `date`");
        $db_modules = [];
        while ($row = sqlFetchArray($resultSet)) {
            $db_modules[] = $row["mod_name"];
        }
        return $db_modules;
    }

    private function bootstrapCustomModules(ModulesClassLoader $classLoader, $eventDispatcher, $webRootPath, $customModulePath)
    {
        self::checkModuleScriptPathForEnabledModule(self::MODULE_TYPE_CUSTOM, $webRootPath, $customModulePath);
        // we skip the audit log as it has no bearing on user activity and is core system related...
        $resultSet = sqlStatementNoLog($statement = "SELECT mod_name, mod_directory FROM modules WHERE mod_active = 1 AND type != 1 ORDER BY `mod_ui_order`, `date`");
        $db_modules = [];
        while ($row = sqlFetchArray($resultSet)) {
            if (is_readable($customModulePath . $row['mod_directory'] . '/' . attr(self::CUSTOM_MODULE_BOOSTRAP_NAME))) {
                $db_modules[] = ["name" => $row["mod_name"], "directory" => $row['mod_directory'], "path" => $customModulePath . $row['mod_directory']];
            } else {
                // no reason to try and include a missing bootstrap.
                // notify user, turn off module and move on...
                error_log("Custom module " . errorLogEscape($customModulePath . $row['mod_directory'])
                    . '/' . self::CUSTOM_MODULE_BOOSTRAP_NAME
                    . " is enabled but missing bootstrap.php script. Install and enable in module manager. This is the only warning.");
                // disable to prevent flooding log with this error
                $error = sqlQueryNoLog("UPDATE `modules` SET `mod_active` = '0' WHERE `modules`.`mod_name` = ? AND `modules`.`mod_directory` = ?", array($row['mod_name'], $row['mod_directory']));
                // tell user we did it.
                if (!$error) {
                    error_log("Custom module " . errorLogEscape($row['mod_name']) . " has been disabled");
                }
            }
        }
        foreach ($db_modules as $module) {
            $this->loadCustomModule($classLoader, $module, $eventDispatcher);
        }
        // TODO: stephen we should fire an event saying we've now loaded all the modules here.
        // Unsure who'd be listening or care.
    }

    private function loadCustomModule(ModulesClassLoader $classLoader, $module, $eventDispatcher)
    {
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

    public function getServiceManager(): ServiceManager
    {
        return $this->application->getServiceManager();
    }

    /**
     * Given a list of module files (javascript, css, etc) make sure they are locked down to be just inside the modules
     * folder.  The intent is to prevent module writers from including files outside the modules installation directory.
     * If the file exists and is inside the modules installation path it will be returned.  Otherwise it is filtered out
     * of the array list
     * @param $files The list of files to safely filter
     * @return array
     */
    public static function filterSafeLocalModuleFiles(array $files): array
    {
        if (is_array($files) && !empty($files)) {
            // for safety we only allow the scripts to be from the local filesystem for now
            $filteredFiles = array_filter(array_map(function ($scriptSrc) {
                // scripts that have any kind of parameters in them such as a cache buster mess up finding the real path
                // we need to strip that out and then check against the real path
                $scriptSrcPath = parse_url($scriptSrc, PHP_URL_PATH);
                // need to remove the web root as that is included in the $scriptSrc and also in the fileroot
                $pos = stripos($scriptSrcPath, $GLOBALS['web_root']);
                if ($pos !== false) {
                    $scriptSrcPathWithoutWebroot = substr_replace($scriptSrcPath, '', $pos, strlen($GLOBALS['web_root']));
                } else {
                    $scriptSrcPathWithoutWebroot = $scriptSrcPath;
                }
                $realPath = realpath($GLOBALS['fileroot'] . $scriptSrcPathWithoutWebroot);
                $moduleRootLocation = realpath($GLOBALS['fileroot'] . DIRECTORY_SEPARATOR . 'interface' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR);

                // make sure we haven't left our root path ie interface folder
                if (strpos($realPath, $moduleRootLocation) === 0 && file_exists($realPath)) {
                    return $scriptSrc;
                }
                return null;
            }, $files), function ($script) {
                return !empty($script);
            });
        } else {
            $filteredFiles = [];
        }
        return $filteredFiles;
    }
}
