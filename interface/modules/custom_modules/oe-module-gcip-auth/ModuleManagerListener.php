<?php

/**
 * Module lifecycle handler for the GCIP Auth module.
 *
 * Handles install, enable, disable, and unregister actions from the
 * OpenEMR Module Manager admin interface.
 *
 * NOTE: This file must NOT have a namespace declaration — the Module Manager
 * expects it at the top level of the module directory.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\AbstractModuleActionListener;

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
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        if (method_exists(self::class, $methodName)) {
            /** @var string $result */
            $result = self::$methodName($modId, $currentActionStatus);
            return $result;
        }

        return $currentActionStatus;
    }

    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\GcipAuth\\';
    }

    public static function initListenerSelf(): self
    {
        return new self();
    }

    private function install(string $modId, string $currentActionStatus): string
    {
        // Show config UI button before enabling
        self::setModuleState($modId, '0', '1');
        return $currentActionStatus;
    }

    private function enable(string $modId, string $currentActionStatus): string
    {
        self::setModuleState($modId, '1', '0');
        return $currentActionStatus;
    }

    private function disable(string $modId, string $currentActionStatus): string
    {
        // Keep config UI accessible when disabled
        self::setModuleState($modId, '0', '1');
        return $currentActionStatus;
    }

    /**
     * Pre-check for help — returning a non-Failure status lets the
     * controller proceed to the actual help_requested action.
     */
    private function prehelp_requested(string $modId, string $currentActionStatus): string
    {
        return 'Success';
    }

    private function help_requested(string $modId, string $currentActionStatus): string
    {
        if (file_exists(__DIR__ . '/show_help.php')) {
            ob_start();
            include __DIR__ . '/show_help.php';
            $help = ob_get_clean();
            echo json_encode(['status' => 'Success', 'output' => $help]);
            exit(0);
        }

        return $currentActionStatus;
    }

    private function unregister(string $modId, string $currentActionStatus): string
    {
        return $currentActionStatus;
    }

    /**
     * @param string $modId
     * @param string $flagActive
     * @param string $flagUiActive
     */
    private static function setModuleState(string $modId, string $flagActive, string $flagUiActive): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ?',
            [$flagActive, $flagUiActive, $modId],
        );
    }
}
