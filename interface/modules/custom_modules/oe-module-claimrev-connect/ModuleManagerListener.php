<?php

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
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\ClaimRevConnector\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/

use OpenEMR\Core\AbstractModuleActionListener;

/* Allows maintenance of background tasks depending on Module Manager action. */

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
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
            // no reason to report, action method is missing.
            return $currentActionStatus;
        }
    }

    /**
     * Required method to return namespace
     * If namespace isn't provided return empty string
     * and register namespace at top of this script..
     *
     * @return string
     */
    public static function getModuleNamespace(): string
    {
        // Module Manager will register this namespace.
        return 'OpenEMR\\Modules\\ClaimRevConnector\\';
    }

    /**
     * Required method to return this class object
     * so it will be instantiated in Laminas Manager.
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
    private function help_requested($modId, $currentActionStatus): mixed
    {
        // must call a script that implements a dialog to show help.
        // I can't find a way to override the Lamina's UI except using a dialog.
        if (file_exists(__DIR__ . '/show_help.php')) {
            include __DIR__ . '/show_help.php';
        }
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function enable($modId, $currentActionStatus): mixed
    {
        $logMessage = 'Claimrev Background tasks have been enabled';
        // Register background services
        $sql = "UPDATE `background_services` SET `active` = '1' WHERE `name` = ? OR `name` = ? OR `name` = ?";
        $status = sqlQuery($sql, array('ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive'));
        error_log($logMessage . ' ' . text($status));
        // Return the current action status from Module Manager in case of error from its action.
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        $logMessage = 'Claimrev Background tasks have been disabled';
        // Unregister background services
        $sql = "UPDATE `background_services` SET `active` = '0' WHERE `name` = ? OR `name` = ? OR `name` = ?";
        $status = sqlQuery($sql, array('ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive'));
        error_log($logMessage . ' ' . text($status));
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus)
    {
        $logMessage = 'Claimrev Background tasks have been removed'; // Initialize an empty string to store log messages
        $sql = "DELETE FROM `background_services` WHERE `name` = ? OR `name` = ? OR `name` = ?";
        $status = sqlQuery($sql, array('ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive'));
        error_log($logMessage . ' ' . text($status));
        return $currentActionStatus;
    }
}
