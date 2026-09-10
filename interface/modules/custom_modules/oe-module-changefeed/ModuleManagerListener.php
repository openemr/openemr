<?php

/**
 * Module Manager lifecycle listener for the Change Feed module.
 *
 * The changefeed_log table is created by the install SQL; the change-capture
 * triggers are (re)installed on enable and dropped on disable/reset, because
 * CREATE TRIGGER cannot be run through the #If* SQL install parser.
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Modules\ChangeFeed\TriggerManager;

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $methodName
     * @param string $modId
     * @param string $currentActionStatus
     * @return string On success return the action status, otherwise an error string.
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        }

        return $currentActionStatus;
    }

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\ChangeFeed\\';
    }

    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    /**
     * @param string $modId
     * @param string $currentActionStatus
     */
    private function install($modId, $currentActionStatus): mixed
    {
        // Show the config/enable UI before the module is enabled. Triggers are
        // installed on enable (the log table exists by then).
        self::setModuleState($modId, '0', '1');

        return $currentActionStatus;
    }

    /**
     * @param string $modId
     * @param string $currentActionStatus
     */
    private function enable($modId, $currentActionStatus): mixed
    {
        self::setModuleState($modId, '1', '0');

        try {
            (new TriggerManager())->install();
        } catch (\Throwable $e) {
            error_log('ChangeFeed: failed to install change-capture triggers: ' . $e->getMessage());
            return xl('Change Feed could not install its change-capture triggers; see the server error log.');
        }

        return $currentActionStatus;
    }

    /**
     * @param string $modId
     * @param string $currentActionStatus
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        // Keep the config UI available after disable.
        self::setModuleState($modId, '0', '1');

        try {
            (new TriggerManager())->uninstall();
        } catch (\Throwable $e) {
            error_log('ChangeFeed: failed to drop change-capture triggers: ' . $e->getMessage());
        }

        return $currentActionStatus;
    }

    /**
     * @param string $modId
     * @param string $currentActionStatus
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        return $currentActionStatus;
    }

    /**
     * Full teardown: drop the triggers first, then the log table (order matters
     * - a trigger referencing a dropped table would break writes to the watched
     * table).
     *
     * @param string $modId
     * @param string $currentActionStatus
     */
    private function reset_module($modId, $currentActionStatus): mixed
    {
        try {
            (new TriggerManager())->uninstall();
            QueryUtils::sqlStatementThrowException(
                'DROP TABLE IF EXISTS `' . TriggerManager::LOG_TABLE . '`'
            );
        } catch (\Throwable $e) {
            error_log('ChangeFeed: reset failed: ' . $e->getMessage());
            return xl('Change Feed reset encountered an error; see the server error log.');
        }

        return $currentActionStatus;
    }

    /**
     * @param int|string $modId
     * @param int|string $flag
     * @param int|string $flagUi
     */
    private static function setModuleState(int|string $modId, int|string $flag, int|string $flagUi): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ? OR `mod_directory` = ?',
            [$flag, $flagUi, $modId, $modId]
        );
    }
}
