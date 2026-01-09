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
 * If you want Lamina's manager to set namespace set it in getModuleNamespace
 * otherwise uncomment below and set path.
 *
 * */

/*
    $classLoader = new \OpenEMR\Core\ModulesClassLoader($GLOBALS['fileroot']);
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\DashboardContext\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\DashboardContext\Services\ModuleService;

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
     * If namespace isn't provided return empty
     * and register namespace at top of this script..
     *
     * @return string
     */
    public static function getModuleNamespace(): string
    {
        // Module Manager will register this namespace.
        return 'OpenEMR\\Modules\\DashboardContext\\';
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
        /* setting the active ui flag here will allow the config button to show
         * before enable. This is a good thing because it allows the user to
         * configure the module before enabling it. However, if the module is disabled
         * this flag is reset by MM.
        */
        self::setModuleState($modId, '0', '1');

        // Create dashboard context globals in the database if they don't exist
        $sql = "INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
                VALUES ('dashboard_context_show_widget', 0, '1')
                ON DUPLICATE KEY UPDATE `gl_value` = '1'";
        QueryUtils::sqlInsert($sql);

        $sql = "INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
                VALUES ('dashboard_context_user_can_switch', 0, '1')
                ON DUPLICATE KEY UPDATE `gl_value` = '1'";
        QueryUtils::sqlInsert($sql);

        // Set in the globals bag for immediate use
        OEGlobalsBag::getInstance()->set('dashboard_context_show_widget', 1);
        OEGlobalsBag::getInstance()->set('dashboard_context_user_can_switch', 1);

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
        self::setModuleState($modId, '1', '0');

        // Ensure globals are set in database
        $sql = "INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
                VALUES ('dashboard_context_show_widget', 0, '1')
                ON DUPLICATE KEY UPDATE `gl_value` = '1'";
        QueryUtils::sqlInsert($sql);

        $sql = "INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`)
                VALUES ('dashboard_context_user_can_switch', 0, '1')
                ON DUPLICATE KEY UPDATE `gl_value` = '1'";
        QueryUtils::sqlInsert($sql);

        // Set in the globals bag for immediate use
        OEGlobalsBag::getInstance()->set('dashboard_context_show_widget', 1);
        OEGlobalsBag::getInstance()->set('dashboard_context_user_can_switch', 1);

        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        // allow config button to show before enable.
        self::setModuleState($modId, '0', '1');
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
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
    public function getModuleRegistry($modId, $col = '*'): array
    {
        $registry = [];
        $sql = "SELECT $col FROM modules WHERE mod_id = ?";
        $results = QueryUtils::querySingleRow($sql, [$modId]);
        foreach ($results as $k => $v) {
            $registry[$k] = trim(((string)preg_replace('/\R/', '', (string)$v)));
        }

        return $registry;
    }

    /**
     * @param      $flag
     * @param      $serviceArray
     * @param bool $reset
     * @param bool $removeTask
     * @return void
     */
    private static function setTaskState($flag, $serviceArray, bool $reset = false, bool $removeTask = false): void
    {
    }

    /**
     * @param $modId   int|string module id or directory name
     * @param $flag    int|string 1 or 0 to activate or deactivate module.
     * @param $flag_ui int|string custom flag to activate or deactivate Manager UI button states.
     * @return array|bool|null
     */
    private static function setModuleState(int|string $modId, int|string $flag, int|string $flag_ui): array|bool|null
    {
        // set module state.
        $sql = "UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ? OR `mod_directory` = ?";
        return QueryUtils::querySingleRow($sql, [$flag, $flag_ui, $modId, $modId]);
    }

    private function reset_module($modId, $currentActionStatus): mixed
    {
        $rtn = true;
        $modService = new ModuleService();
        $logMessage = ''; // Initialize an empty string to store log messages

        if (!$modService::getModuleState($modId)) {
            $sql = "DELETE FROM `globals` WHERE `gl_name` LIKE 'dashboard%'";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DELETE FROM `globals`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            // database cleanup
            $sql = "DROP TABLE IF EXISTS `dashboard_context_audit_log`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_context_audit_log`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `dashboard_widget_order`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_widget_order`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `dashboard_context_facility_defaults`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_context_facility_defaults`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `dashboard_context_role_defaults`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_context_role_defaults`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `dashboard_context_assignments`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_context_assignments`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `dashboard_context_definitions`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `dashboard_context_definitions`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `user_dashboard_context_config`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `user_dashboard_context_config`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            $sql = "DROP TABLE IF EXISTS `user_dashboard_context`";
            $rtn = QueryUtils::querySingleRow($sql);
            $logMessage .= "DROP TABLE `user_dashboard_context`: " . (empty($rtn) ? "Success" : "Failed") . "\n";

            error_log(text($logMessage));
        }

        // return log messages to the MM to show user.
        return text($logMessage);
    }
}
