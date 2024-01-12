<?php

use OpenEMR\Modules\FaxSMS\BootstrapService;

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
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\FaxSMS\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/


class ModuleManagerAfterActionListener
{
    public const FAX_SERVICE = 3;
    public const SMS_SERVICE = 2;
    public $service;

    public function __construct()
    {
        $this->service = new BootstrapService();
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
        return 'OpenEMR\\Modules\\FaxSMS\\';
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
        if (empty($this->service)) {
            $this->service = new BootstrapService();
        }
        $globals = $this->service->getVendorGlobals();

        foreach ($globals as $k => $v) {
            if ($k == 'oefax_enable_sms') {
                $globals[$k] = self::SMS_SERVICE;
            } elseif ($k == 'oefax_enable_fax') {
                $globals[$k] = self::FAX_SERVICE;
            }
            // leave others as is.
        }

        $this->service->saveModuleListenerGlobals($globals);

        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        if (empty($this->service)) {
            $this->service = new BootstrapService();
        }
        $globals = $this->service->getVendorGlobals();
        foreach ($globals as $k => $v) {
            if ($k == 'oefax_enable_sms' || $k == 'oefax_enable_fax') {
                $globals[$k] = 0;
            }
        }

        $this->service->saveModuleListenerGlobals($globals);

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
}
