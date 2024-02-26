<?php

/**
 * Extended Class to be called from Laminas Module Manager for reporting management actions.
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
 * Do not declare a namespace in class extending this abstract class.
 * If you want Laminas manager to set namespace, set it in getModuleNamespace
 * otherwise use below at top of class to register namespace.
 * $classLoader = new \OpenEMR\Core\ModulesClassLoader($GLOBALS['fileroot']);
 * $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\PortalPlugins\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
 * */

namespace OpenEMR\Core;

/**
 *
 */
abstract class AbstractModuleActionListener
{
    use AbstractModuleActionTrait;

    private $_request;
    private $_query;
    private $_post;
    private $_server;
    private $_cookies;
    private $_session;

    public function __construct()
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session = &$_SESSION;
    }

    /**
     * @param string $methodName          Action name e.g. enable, disable etc..
     * @param string $modId               Module id
     * @param string $currentActionStatus Current action status from Laminas event.
     * @return string On method success a $currentAction status should be returned or error string.
     */
    abstract public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string;

    /**
     * Required method to return namespace
     * If namespace isn't provided return an empty
     * and register namespace using example at top of this script.
     *
     * @return string
     */
    abstract protected static function getModuleNamespace(): string;

    /**
     * Required method to return this class object,
     * so it is instantiated in Laminas Manager.
     *
     * @return
     */
    abstract protected static function initListenerSelf();

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
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
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
     * Set enable/disable module state.
     * If the mod_ui_active flag is set to 1, then the module config button
     * is allowed in modules disabled state. In this state calling the config
     * script will be in the Laminas namespace and not the module namespace.
     * So remember to set namespace in the config script.
     *
     * @param $modId   string|int module id or directory name
     * @param $flag    string|int 1 or 0 to activate or deactivate module.
     * @param $flag_ui string|int custom flag to activate or deactivate Manager UI button states.
     * @return array|bool|null
     */
    public static function setModuleActiveState($modId, $flag, $flag_ui): array|bool|null
    {
        // set module state.
        $sql = "UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ? OR `mod_directory` = ?";
        return sqlQuery($sql, array($flag, $flag_ui, $modId, $modId));
    }
}
