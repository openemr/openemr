<?php

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\BootstrapService;
use OpenEMR\Modules\FaxSMS\ModuleLifecycleState;
use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;

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

class ModuleManagerListener extends AbstractModuleActionListener
{
    /**
     * module_faxsms_credentials vendor key that records which reminder tasks
     * were active when the module was disabled.
     */
    private const PERSISTED_TASKS_VENDOR = '_persisted_tasks';

    public $service;
    private $authUser;

    public function __construct()
    {
        parent::__construct();
        $this->authUser = (int)$this->getSession('authUserID');
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
     * If namespace isn't provided return an empty
     * and register namespace using example at top of this script.
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
        // Register the SMS and email reminder tasks so they are listed in
        // background services from the start. They are created inactive; an
        // admin turns them on from the module's notification services page.
        // Registration keeps an existing task's on/off state on reinstall.
        $taskManager = new NotificationTaskManager();
        $taskManager->manageService('sms');
        $taskManager->manageService('email');

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
        $globals = $this->service->fetchPersistedSetupSettings() ?? '';
        if (empty($globals)) {
            $globals = $this->service->getVendorGlobals();
        }
        $this->service->saveModuleListenerGlobals($globals);
        $this->restoreReminderTasks();

        return $currentActionStatus;
    }

    /**
     * Stop the reminder tasks while the module is disabled (their code cannot
     * load), remembering which ones were running so enable() can restart them.
     */
    private function suspendReminderTasks(): void
    {
        $active = ModuleLifecycleState::taskNames(QueryUtils::fetchRecords(
            "SELECT `name` FROM `background_services` WHERE `active` = 1 AND `name` IN (?, ?)",
            ModuleLifecycleState::REMINDER_TASKS
        ));
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `module_faxsms_credentials` (`auth_user`, `vendor`, `credentials`) VALUES (0, ?, ?)
                ON DUPLICATE KEY UPDATE `credentials` = VALUES(`credentials`), `updated` = NOW()",
            [self::PERSISTED_TASKS_VENDOR, json_encode($active)]
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `background_services` SET `active` = 0 WHERE `name` IN (?, ?)",
            ModuleLifecycleState::REMINDER_TASKS
        );
    }

    /**
     * Restart the reminder tasks that were running when the module was
     * disabled. Tasks that were already off stay off.
     */
    private function restoreReminderTasks(): void
    {
        $row = QueryUtils::querySingleRow(
            "SELECT `credentials` FROM `module_faxsms_credentials` WHERE `auth_user` = 0 AND `vendor` = ?",
            [self::PERSISTED_TASKS_VENDOR]
        );
        $stored = is_array($row) ? ($row['credentials'] ?? null) : null;
        foreach (ModuleLifecycleState::tasksToRestore($stored) as $name) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE `background_services` SET `active` = 1 WHERE `name` = ?",
                [$name]
            );
        }
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus)
    {
        if (empty($this->service)) {
            $this->service = new BootstrapService();
        }
        // fetch current.
        $globals = $this->service->getVendorGlobals();
        // persist current for enable action.
        $rid = $this->service->persistSetupSettings($globals);
        foreach ($globals as $k => $v) {
            if ($k == 'oefax_enable_sms' || $k == 'oefax_enable_fax') {
                // force disable of services
                OEGlobalsBag::getInstance()->set($k, 0);
            }
        }
        // save new disabled settings.
        $this->service->saveModuleListenerGlobals($globals);
        // The loop above only changes the in-memory globals; $globals still
        // holds the enabled values, so the flags stayed on in the database and
        // main.php kept polling the disabled module. Store them as off. The
        // values persisted above are what enable() restores.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `globals` SET `gl_value` = '0' WHERE `gl_name` IN ('oefax_enable_sms', 'oefax_enable_fax')",
            []
        );
        $this->suspendReminderTasks();
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
