<?php
/**
 * Module Lifecycle Manager
 *
 * Handles install/enable/disable/uninstall events for MedEx module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class ModuleManagerListener
{
    private OEGlobalsBag $globalsBag;

    public function __construct()
    {
        $this->globalsBag = OEGlobalsBag::getInstance();
    }

    /**
     * Handle module installation
     *
     * @param mixed $event
     * @return void
     */
    public function onModuleInstall(mixed $event): void
    {
        if ($event->getModuleName() !== 'oe-module-medex') {
            return;
        }

        error_log('[MedEx Module] Starting installation...');

        try {
            // Create module-specific tables
            $this->createModuleTables();

            // Background services are no longer used by the module.
            // The MedEx module manages external connections outside OpenEMR process boundaries.

            // Set default API host if not already set
            $currentHost = $this->globalsBag->get('medex_api_host') ?? '';
            if (empty($currentHost)) {
                $this->setGlobal('medex_api_host', 'MedExBank.com');
            }

        error_log('[MedEx Module] Installation complete');
        error_log('[MedEx Module] IMPORTANT: Run fix_module_registration.sql to correct module display name (MedEx Communication)');
        $event->setSuccess(true);

        } catch (\Exception $e) {
            error_log('[MedEx Module] Installation FAILED: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage($e->getMessage());
        }
    }

    /**
     * Handle module enable
     *
     * @param mixed $event
     * @return void
     */
    public function onModuleEnable(mixed $event): void
    {
        if ($event->getModuleName() !== 'oe-module-medex') {
            return;
        }

        error_log('[MedEx Module] Enabling module...');

        try {
            // Enable MedEx globally
            $this->setGlobal('medex_enable', '1');

            // Correct module registration to match the "MedEx Communication Manager" name
            // and ensure it shows "Disable" button by setting mod_ui_active=0
            QueryUtils::sqlStatementThrowException(
                "UPDATE modules SET mod_name = 'MedEx Communication Manager', mod_ui_name = 'MedEx Communication Manager', sql_version = '1.0.0', mod_ui_active = 0, mod_active = 1 WHERE mod_directory = 'oe-module-medex'"
            );

            // No background_services updates required; module manages external connections itself.

            error_log('[MedEx Module] Module enabled');
            $event->setSuccess(true);

        } catch (\Exception $e) {
            error_log('[MedEx Module] Enable FAILED: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage($e->getMessage());
        }
    }

    /**
     * Handle module disable
     *
     * @param mixed $event
     * @return void
     */
    public function onModuleDisable(mixed $event): void
    {
        if ($event->getModuleName() !== 'oe-module-medex') {
            return;
        }

        error_log('[MedEx Module] Disabling module...');

        try {
            // Disable MedEx globally
            $this->setGlobal('medex_enable', '0');

            // Reset module registration on disable to show "Enable" button correctly
            QueryUtils::sqlStatementThrowException(
                "UPDATE modules SET mod_ui_active = 1, mod_active = 0 WHERE mod_directory = 'oe-module-medex'"
            );

            // No background_services updates required on disable.

            error_log('[MedEx Module] Module disabled');
            $event->setSuccess(true);

        } catch (\Exception $e) {
            error_log('[MedEx Module] Disable FAILED: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage($e->getMessage());
        }
    }

    /**
     * Handle module uninstallation
     *
     * @param mixed $event
     * @return void
     */
    public function onModuleUninstall(mixed $event): void
    {
        if ($event->getModuleName() !== 'oe-module-medex') {
            return;
        }

        error_log('[MedEx Module] Starting uninstallation...');

        try {
            // Clean up downloaded assets from documents directory (e.g., FullCalendar)
            $this->cleanupDocumentsDirectory();

            // Drop module-specific tables
            $this->dropModuleTables();

            // Also remove medex_enable and medex_api_host to ensure they are completely wiped out
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM globals WHERE gl_name IN ('medex_enable', 'medex_api_host')"
            );

            error_log('[MedEx Module] Uninstallation complete with socket services cleanup');
            $event->setSuccess(true);

        } catch (\Exception $e) {
            error_log('[MedEx Module] Uninstallation FAILED: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage($e->getMessage());
        }
    }

    /**
     * Handle help request from Module Manager
     *
     * @param mixed $modId
     * @param mixed $currentActionStatus
     * @return mixed
     */
    private function help_requested($modId, $currentActionStatus): mixed
    {
        error_log('[MedEx Module] help_requested method called');
        // Include the show_help.php file which displays help in a dialog
        $helpFile = __DIR__ . '/../show_help.php';
        error_log('[MedEx Module] Looking for help file at: ' . $helpFile);
        if (file_exists($helpFile)) {
            error_log('[MedEx Module] Help file found, including it');
            include $helpFile;
        } else {
            error_log('[MedEx Module] Help file NOT found');
        }
        return $currentActionStatus;
    }

    /**
     * Create module-specific database tables
     *
     * @return void
     */
    private function createModuleTables(): void
    {
        // Migrate: add bad_actor_message column if upgrading from older install
        try {
            QueryUtils::sqlStatementThrowException(
                "ALTER TABLE medex_prefs ADD COLUMN IF NOT EXISTS bad_actor_message varchar(500) DEFAULT NULL",
                []
            );
        } catch (\Exception $e) {
            // Column may already exist or table may not exist yet — both OK
        }

        // Create medex_prefs table (module-only settings)
        QueryUtils::sqlStatementThrowException("
            CREATE TABLE IF NOT EXISTS `medex_prefs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `MedEx_id` varchar(20) DEFAULT NULL,
                `ME_api_key` varchar(50) DEFAULT NULL,
                `ME_username` varchar(100) DEFAULT NULL,
                `ME_facilities` text,
                `ME_providers` text,
                `ME_hipaa_default_override` varchar(3) DEFAULT 'YES',
                `PHONE_country_code` varchar(5) DEFAULT '1',
                `POSTCARDS_local` varchar(3) DEFAULT 'YES',
                `POSTCARDS_remote` varchar(3) DEFAULT 'NO',
                `LABELS_local` varchar(3) DEFAULT 'YES',
                `LABELS_choice` varchar(10) DEFAULT '5160',
                `combine_time` int(11) DEFAULT '2',
                `postcard_top` text,
                `MedEx_lastupdated` timestamp NOT NULL DEFAULT current_timestamp(),
                `status` text DEFAULT NULL,
                `bad_actor_until` timestamp NULL DEFAULT NULL,
                `bad_actor_message` varchar(500) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create medex_icons table (communication icons)
        QueryUtils::sqlStatementThrowException("
            CREATE TABLE IF NOT EXISTS `medex_icons` (
                `i_ID` int(11) NOT NULL AUTO_INCREMENT,
                `msg_type` varchar(50) NOT NULL,
                `msg_status` varchar(50) NOT NULL,
                `i_Description` varchar(255) NOT NULL,
                `i_html` text NOT NULL,
                `i_blob` longblob,
                PRIMARY KEY (`i_ID`),
                UNIQUE KEY `msg_type` (`msg_type`,`msg_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Create medex_outgoing table (message history)
        QueryUtils::sqlStatementThrowException("
            CREATE TABLE IF NOT EXISTS `medex_outgoing` (
                `medex_uid` int(11) NOT NULL AUTO_INCREMENT,
                `msg_pc_eid` varchar(100) NOT NULL,
                `msg_type` varchar(50) NOT NULL,
                `msg_reply` varchar(100) NOT NULL,
                `msg_extra_text` text,
                `msg_date` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`medex_uid`),
                KEY `msg_pc_eid` (`msg_pc_eid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        error_log('[MedEx Module] Module tables created successfully');
    }

    /**
     * Drop module-specific database tables and clear globals
     *
     * @return void
     */
    private function dropModuleTables(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `globals` WHERE `gl_name` IN ('medex_enable', 'medex_api_host', 'medex_practice_id', 'medex_bad_actor_until')");
        QueryUtils::sqlStatementThrowException("DROP TABLE IF EXISTS `medex_prefs`");
        QueryUtils::sqlStatementThrowException("DROP TABLE IF EXISTS `medex_icons`");
        QueryUtils::sqlStatementThrowException("DROP TABLE IF EXISTS `medex_outgoing`");
        error_log('[MedEx Module] Module tables and globals cleared successfully');
    }

    /**
     * Set or update a global configuration value
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    private function setGlobal(string $name, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO globals (gl_name, gl_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE gl_value = ?",
            [$name, $value, $value]
        );
    }

    /**
     * Clean up MedEx directory from documents (e.g., downloaded FullCalendar assets)
     *
     * @return void
     */
    private function cleanupDocumentsDirectory(): void
    {
        try {
            $sitesBase = $this->globalsBag->get('OE_SITES_BASE');

            // Clean up for all sites
            if (is_dir($sitesBase)) {
                $sites = scandir($sitesBase);
                foreach ($sites as $site) {
                    if ($site === '.' || $site === '..') {
                        continue;
                    }

                    $medexDir = $sitesBase . '/' . $site . '/documents/MedEx';
                    if (is_dir($medexDir)) {
                        $this->removeDirectory($medexDir);
                        error_log('[MedEx Module] Cleaned up MedEx directory for site: ' . $site);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('[MedEx Module] Error cleaning up documents directory: ' . $e->getMessage());
            // Don't throw - continue with uninstall even if cleanup fails
        }
    }

    /**
     * Recursively remove a directory and its contents
     *
     * @param string $dir
     * @return bool
     */
    private function removeDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
