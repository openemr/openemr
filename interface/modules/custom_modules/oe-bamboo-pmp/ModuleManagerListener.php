<?php

use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Common\Logging\SystemLogger;

/**
 * Class to be called from Laminas Module Manager for reporting management actions.
 * Example is if the module is enabled, disabled or unregistered ect.
 *
 * The class is in the Laminas "Installer\Controller" namespace.
 * Currently, register isn't supported of which support should be a part of install.
 * If an error needs to be reported to user, return description of error.
 * However, whatever action trapped here has already occurred in Manager.
 * Catch any exceptions because chances are they will be overlooked in Laminas module.
 * Report them in the return value.
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/*
 * Do not declare a namespace
 * If you want Laminas manager to set namespace set it in getModuleNamespace
 * otherwise uncomment below and set path.
 *
 * */

/*
    $classLoader = new \OpenEMR\Core\ModulesClassLoader($GLOBALS['fileroot']);
    $classLoader->registerNamespaceIfNotExists("Juggernaut\\Modules\\Bamboo\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/

class ModuleManagerListener extends AbstractModuleActionListener
{
    public $service;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = new SystemLogger();
    }

    /**
     * @param        $methodName
     * @param        $modId
     * @param string $currentActionStatus
     * @return string On method success a $currentAction status should be returned or error string.
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        } else {
            return $currentActionStatus;
        }
    }

    /**
     * Required method to return namespace
     * If namespace isn't provided return an empty
     * and register namespace using example at top of this script.
     *
     * @return string
     */
    public static function getModuleNamespace(): string
    {
        return 'Juggernaut\\Modules\\Bamboo\\';
    }

    /**
     * Required method to return this class object,
     * so it is instantiated in Laminas Manager.
     *
     * @return ModuleManagerListener
     */
    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function enable($modId, $currentActionStatus): mixed
    {
        // Return the current action status from Module Manager in case of error from its action.

        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus)
    {
        // Return the current action status from Module Manager in case of error from its action.
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        sqlStatement("DROP TABLE IF EXISTS `module_bamboo_credentials`");
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function upgrade_sql($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }
}
