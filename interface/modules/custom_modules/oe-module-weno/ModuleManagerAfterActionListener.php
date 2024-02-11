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

/* Allows maintenance of background tasks depending on Module Manager action. */

class ModuleManagerAfterActionListener extends AbstractModuleActionListener
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
            return "Module cleanup method $methodName does not exist.";
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
        return 'OpenEMR\\Modules\\WenoModule\\';
    }

    /**
     * Required method to return this class object,
     * so it is instantiated in Laminas Manager.
     *
     * @return ModuleManagerAfterActionListener
     */
    public static function initListenerSelf(): ModuleManagerAfterActionListener
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
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function enable($modId, $currentActionStatus): mixed
    {
        $rtn = $this->setTaskState('1');
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        $rtn = $this->setTaskState('0');
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

    /**
     * @param $flag
     * @return mixed
     */
    private function setTaskState($flag): mixed
    {
        $sql_next = "UPDATE `background_services` SET `active` = ? WHERE `name` = ? OR `name` = ?";
        return sqlQuery($sql_next, array($flag, 'WenoExchange', 'WenoExchangePharmacies'));
    }
}
