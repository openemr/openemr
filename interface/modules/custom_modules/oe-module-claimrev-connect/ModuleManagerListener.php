<?php

/**
 * Class to be called from Laminas Module Manager for reporting management actions.
 * Example is if the module is enabled, disabled or unregistered etc.
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

declare(strict_types=1);

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevModuleSetup;

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
            $result = self::$methodName($modId, $currentActionStatus);
            return is_string($result) ? $result : $currentActionStatus;
        }

        // no reason to report, action method is missing.
        return $currentActionStatus;
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
        // Register background services
        QueryUtils::sqlStatementThrowException(
            "UPDATE `background_services` SET `active` = '1' WHERE `name` IN (?, ?, ?, ?, ?, ?)",
            ['ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive', 'ClaimRev_Watchdog', 'ClaimRev_Notifications', 'ClaimRev_Elig_Sweep']
        );
        ServiceContainer::getLogger()->info('ClaimRev background tasks enabled');

        // One-time persistence: opt the install into core SFTP claim flow if
        // it has never been configured. Respects an explicit '0' set by an
        // admin who has deliberately disabled core SFTP.
        ClaimRevModuleSetup::ensureCoreSftpEnabled();

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
        // Unregister background services
        QueryUtils::sqlStatementThrowException(
            "UPDATE `background_services` SET `active` = '0' WHERE `name` IN (?, ?, ?, ?, ?, ?)",
            ['ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive', 'ClaimRev_Watchdog', 'ClaimRev_Notifications', 'ClaimRev_Elig_Sweep']
        );
        ServiceContainer::getLogger()->info('ClaimRev background tasks disabled');
        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `background_services` WHERE `name` IN (?, ?, ?, ?, ?, ?)",
            ['ClaimRev_Send', 'ClaimRev_Receive', 'ClaimRev_Elig_Send_Receive', 'ClaimRev_Watchdog', 'ClaimRev_Notifications', 'ClaimRev_Elig_Sweep']
        );
        ServiceContainer::getLogger()->info('ClaimRev background tasks removed');
        return $currentActionStatus;
    }
}
