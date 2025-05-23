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
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\WenoModule\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/

use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Modules\WenoModule\Services\ModuleService;

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
            // no reason to report action method is missing.
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
        return 'OpenEMR\\Modules\\WenoModule\\';
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
    private function install($modId, $currentActionStatus): mixed
    {
        $modService = new ModuleService();
        /* setting the active ui flag here will allow the config button to show
         * before enable. This is a good thing because it allows the user to
         * configure the module before enabling it. However, if the module is disabled
         * this flag is reset by MM.
        */
        $modService::setModuleState($modId, '0', '1');
        return $currentActionStatus;
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
    private function preenable($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function enable($modId, $currentActionStatus): mixed
    {
        $modService = new ModuleService();
        if ($modService->isWenoConfigured()) {
            $modService::setModuleState($modId, '1', '0');
            return $currentActionStatus;
        }
        $modService::setModuleState($modId, '1', '1');
        return xlt("Weno eRx Service is not configured. Please configure Weno eRx Service in the Weno Module Setup.");
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        // allow config button to show before enable.
        ModuleService::setModuleState($modId, '0', '1');
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        $sql = "DELETE FROM `background_services` WHERE `name` = ? OR `name` = ?";
        sqlQuery($sql, array('WenoExchange', 'WenoExchangePharmacies'));
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function reset_module($modId, $currentActionStatus): mixed
    {
        $rtn = true;
        $modService = new ModuleService();
        $logMessage = ''; // Initialize an empty string to store log messages

        if (!$modService::getModuleState($modId)) {
            $sql = "DELETE FROM `user_settings` WHERE `setting_label` LIKE 'global:weno%'";
            $rtn = sqlQuery($sql);
            $logMessage .= "DELETE FROM `user_settings`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DELETE FROM `globals` WHERE `gl_name` LIKE 'weno%'";
            $rtn = sqlQuery($sql);
            $logMessage .= "DELETE FROM `globals`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `weno_pharmacy`";
            $rtn = sqlQuery($sql);
            $logMessage .= "DROP TABLE `weno_pharmacy`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `weno_assigned_pharmacy`";
            $rtn = sqlQuery($sql);
            $logMessage .= "DROP TABLE `weno_assigned_pharmacy`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `weno_download_log`";
            $rtn = sqlQuery($sql);
            $logMessage .= "DROP TABLE `weno_download_log`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            error_log(text($logMessage));
        }

        // return log messages to the MM to show user.
        return text($logMessage);
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function install_sql($modId, $currentActionStatus): mixed
    {
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

    /**
     * Grab all Module setup or columns values.
     *
     * @param        $modId
     * @param string $col
     * @return array
     */
    function getModuleRegistry($modId, $col = '*'): array
    {
        $registry = [];
        $sql = "SELECT $col FROM modules WHERE mod_id = ?";
        $results = sqlQuery($sql, array($modId));
        foreach ($results as $k => $v) {
            $registry[$k] = trim((preg_replace('/\R/', '', $v)));
        }

        return $registry;
    }
}
