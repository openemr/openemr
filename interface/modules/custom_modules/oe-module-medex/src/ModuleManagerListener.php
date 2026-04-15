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
            // Ensure module tables/columns exist even on legacy installs.
            $this->createModuleTables();

            // Enable MedEx globally
            $this->setGlobal('medex_enable', '1');

            // Correct module registration to match the "MedEx Communication Manager" name
            // and ensure it shows "Disable" button by setting mod_ui_active=0
            QueryUtils::sqlStatementThrowException(
                "UPDATE modules SET mod_name = 'MedEx Module', mod_ui_name = 'Oe-module-medex', sql_version = '1.1.0', mod_ui_active = 0, mod_active = 1 WHERE mod_directory = 'oe-module-medex'"
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

            // Preserve shared MedEx schema on uninstall/reset. Only clear credentials/runtime state.
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM globals WHERE gl_name IN (
                    'medex_enable',
                    'medex_api_host',
                    'medex_api_key',
                    'medex_practice_id',
                    'medex_bad_actor_until',
                    'medex_bad_actor_message'
                )"
            );
            QueryUtils::sqlStatementThrowException("DELETE FROM medex_prefs");

            error_log('[MedEx Module] Uninstall cleanup complete (schema preserved)');
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
    public function help_requested($modId, $currentActionStatus): mixed
    {
        $modActive = 0;
        $modUiActive = 0;
        try {
            $row = QueryUtils::querySingleRow(
                "SELECT mod_active, mod_ui_active FROM modules WHERE mod_id = ?",
                [$modId]
            );
            $modActive = (int)($row['mod_active'] ?? 0);
            $modUiActive = (int)($row['mod_ui_active'] ?? 0);
        } catch (\Throwable $e) {
            error_log('[MedEx Module] help_requested state lookup failed: ' . $e->getMessage());
        }

        $webroot = $GLOBALS['webroot'] ?? '';
        $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default')));
        if ($siteId === '') {
            $siteId = 'default';
        }

        $showSetup = ($modActive !== 1);
        $helpUrl = $showSetup
            ? ($webroot . '/interface/modules/custom_modules/oe-module-medex/show_help_setup.php?site=' . urlencode($siteId))
            : ($webroot . '/interface/modules/custom_modules/oe-module-medex/admin/help_center.php?site=' . urlencode($siteId));
        $helpTitle = $showSetup ? 'MedEx Setup Help' : 'MedEx Help Center';

        // AJAX help clicks from Module Manager must return JSON that action.js can append,
        // allowing the modal launcher script from index.phtml to execute.
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $escapedUrl = htmlspecialchars($helpUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $escapedTitle = htmlspecialchars($helpTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $overlayId = 'medex-module-help-overlay';
            $output = <<<HTML
<div id="{$overlayId}" style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;">
  <div style="width:min(920px,96vw);height:min(720px,92vh);background:#fff;border-radius:10px;box-shadow:0 20px 40px rgba(0,0,0,.28);overflow:hidden;display:flex;flex-direction:column;">
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding:10px 12px;font-weight:700;">
      <span>{$escapedTitle}</span>
      <button type="button" style="border:1px solid #cbd5e1;background:#fff;border-radius:6px;padding:2px 8px;cursor:pointer;" onclick="var ov=document.getElementById('{$overlayId}');if(ov){ov.remove();}var log=document.getElementById('install_upgrade_log');if(log){log.innerHTML='';log.style.display='none';}">×</button>
    </div>
    <iframe src="{$escapedUrl}" title="{$escapedTitle}" style="border:0;width:100%;height:100%;"></iframe>
  </div>
</div>
HTML;
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'Success',
                'output' => $output
            ]);
            exit(0);
        }

        // Non-AJAX direct access should render the setup helper inline for pre-install/pre-enable
        // and fall back to redirecting to the help center for active installs.
        if ($showSetup && file_exists(__DIR__ . '/../show_help_setup.php')) {
            include __DIR__ . '/../show_help_setup.php';
            exit(0);
        }
        header('Location: ' . $helpUrl);
        exit(0);
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
                `terms_version` varchar(32) DEFAULT NULL,
                `terms_accepted_at` datetime DEFAULT NULL,
                `terms_accepted_ip` varchar(45) DEFAULT NULL,
                `baa_version` varchar(32) DEFAULT NULL,
                `baa_accepted_at` datetime DEFAULT NULL,
                `baa_accepted_ip` varchar(45) DEFAULT NULL,
                `agreement_user_agent` varchar(255) DEFAULT NULL,
                `otp_channel` varchar(20) DEFAULT NULL,
                `otp_house_account` varchar(50) DEFAULT NULL,
                `otp_house_cost` decimal(10,4) DEFAULT NULL,
                `comms_consent_at` datetime DEFAULT NULL,
                `comms_consent_ip` varchar(45) DEFAULT NULL,
                `comms_consent_channel` varchar(20) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Compatibility path for legacy medex_prefs schema (older installs missing newer columns).
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_username` varchar(100) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_api_key` text DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_facilities` varchar(50) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_providers` varchar(100) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_hipaa_default_override` varchar(3) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `PHONE_country_code` int(4) NOT NULL DEFAULT 1",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `MSGS_default_yes` varchar(3) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `POSTCARDS_local` varchar(3) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `POSTCARDS_remote` varchar(3) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `LABELS_local` varchar(3) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `LABELS_choice` varchar(50) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `combine_time` tinyint(4) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `postcard_top` varchar(255) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `status` text DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `bad_actor_until` timestamp NULL DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `bad_actor_message` varchar(500) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `sms_bot_phone_style` varchar(50) DEFAULT 'S8'",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `module_update_cache` text DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `module_update_checked` datetime DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `ME_server_url` varchar(255) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_version` varchar(32) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_at` datetime DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `terms_accepted_ip` varchar(45) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_version` varchar(32) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_at` datetime DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `baa_accepted_ip` varchar(45) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `agreement_user_agent` varchar(255) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_channel` varchar(20) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_house_account` varchar(50) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `otp_house_cost` decimal(10,4) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_at` datetime DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_ip` varchar(45) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_prefs` ADD COLUMN IF NOT EXISTS `comms_consent_channel` varchar(20) DEFAULT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `medex_prefs` SET `status` = `MedEx_status`
             WHERE (`status` IS NULL OR `status` = '') AND `MedEx_status` IS NOT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `medex_prefs` SET `ME_facilities` = CAST(`MedEx_facilities` AS CHAR)
             WHERE (`ME_facilities` IS NULL OR `ME_facilities` = '') AND `MedEx_facilities` IS NOT NULL",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `medex_prefs` SET `ME_providers` = CAST(`MedEx_providers` AS CHAR)
             WHERE (`ME_providers` IS NULL OR `ME_providers` = '') AND `MedEx_providers` IS NOT NULL",
            []
        );

        // Create medex_icons table (communication icons)
        QueryUtils::sqlStatementThrowException("
            CREATE TABLE IF NOT EXISTS `medex_icons` (
                `i_UID` int(11) NOT NULL AUTO_INCREMENT,
                `msg_type` varchar(50) NOT NULL,
                `msg_status` varchar(50) NOT NULL,
                `i_description` varchar(255) NOT NULL,
                `i_html` text NOT NULL,
                `i_blob` longblob,
                PRIMARY KEY (`i_UID`),
                UNIQUE KEY `msg_type` (`msg_type`,`msg_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Compatibility path for legacy medex_icons schema (older installs used i_type only).
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_icons` ADD COLUMN IF NOT EXISTS `msg_type` varchar(50) NOT NULL DEFAULT ''",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "ALTER TABLE `medex_icons` ADD COLUMN IF NOT EXISTS `msg_status` varchar(50) NOT NULL DEFAULT ''",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `medex_icons` SET `msg_type` = UPPER(`i_type`)
             WHERE (`msg_type` IS NULL OR `msg_type` = '') AND `i_type` IS NOT NULL AND `i_type` != ''",
            []
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `medex_icons` SET `msg_status` = 'LEGACY'
             WHERE `msg_status` IS NULL OR `msg_status` = ''",
            []
        );

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
