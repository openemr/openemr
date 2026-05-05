<?php

/**
 * MedEx Module Manager Listener
 *
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
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

/*
 * Do not declare a namespace
 * If you want Laminas manager to set namespace set it in getModuleNamespace
 * otherwise uncomment below and set path.
 */

/*
    $classLoader = new \OpenEMR\Core\ModulesClassLoader($GLOBALS['fileroot']);
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\MedEx\\", __DIR__ . DIRECTORY_SEPARATOR . 'src');
*/

use OpenEMR\Core\AbstractModuleActionListener;
require_once __DIR__ . '/src/MedExConfig.php';

/**
 * Allows maintenance of background tasks depending on Module Manager action.
 */
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
        error_log('[MedEx ModuleManagerListener] moduleManagerAction called: ' . $methodName);
        if (method_exists(self::class, $methodName)) {
            error_log('[MedEx ModuleManagerListener] Method exists, calling: ' . $methodName);
            return self::$methodName($modId, $currentActionStatus);
        } else {
            error_log('[MedEx ModuleManagerListener] Method does NOT exist: ' . $methodName);
            // no reason to report action method is missing.
            return $currentActionStatus;
        }
    }

    /**
     * Required method to return namespace
     * If namespace isn't provided return empty string
     * and register namespace at top of this script.
     *
     * @return string
     */
    public static function getModuleNamespace(): string
    {
        // Module Manager will register this namespace.
        return 'OpenEMR\\Modules\\MedEx\\';
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

    private static function getSiteId(): string
    {
        $siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
        $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', $siteId);
        return $siteId !== '' ? $siteId : 'default';
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private static function getModuleState($modId): array
    {
        $row = sqlQuery(
            "SELECT mod_id, mod_active, mod_ui_active, sql_run
               FROM modules
              WHERE mod_id = ?
              LIMIT 1",
            [$modId]
        ) ?: [];

        return [
            'mod_id' => (int)($row['mod_id'] ?? $modId ?? 0),
            'installed' => ((int)($row['sql_run'] ?? 0) === 1),
            'enabled' => ((int)($row['mod_active'] ?? 0) === 1),
            'ui_enabled' => ((int)($row['mod_ui_active'] ?? 0) === 1),
        ];
    }

    private static function renderInstallerHelpFragment($modId): string
    {
        $state = self::getModuleState($modId);
        $siteId = self::getSiteId();
        $webroot = (string)($GLOBALS['webroot'] ?? '');
        $moduleId = (string)$state['mod_id'];
        $needsInstall = !$state['installed'] ? 'true' : 'false';
        $needsEnable = !$state['enabled'] ? 'true' : 'false';
        $onboardingUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/admin/onboarding.php?step=1&force_onboarding=1&site=' . rawurlencode($siteId);

        return <<<HTML
<script>
(function () {
  var log = document.getElementById('install_upgrade_log');
  if (log) {
    log.style.display = 'block';
    log.style.height = 'auto';
    log.style.minHeight = '220px';
    log.style.overflowY = 'visible';
  }
  function medexSetStatus(text, note, state) {
    var status = document.getElementById('medex-mm-status');
    var notes = document.getElementById('medex-mm-notes');
    var bar = document.getElementById('medex-mm-progress-bar');
    var percent = arguments.length > 3 ? arguments[3] : null;
    if (status) {
      status.textContent = text;
      status.classList.remove('medex-mm-status-done', 'medex-mm-status-error');
      if (state === 'done' || state === 'error') {
        status.classList.add(state === 'done' ? 'medex-mm-status-done' : 'medex-mm-status-error');
      }
    }
    if (notes) {
      notes.textContent = note || '';
    }
    if (bar && typeof percent === 'number') {
      bar.style.width = Math.max(8, Math.min(100, percent)) + '%';
    }
  }
  function medexOpenTarget(url) {
    try {
      if (window.top && typeof window.top.navigateTab === 'function') {
        window.top.navigateTab(url, 'med', function () {
          try {
            if (typeof window.top.activateTabByName === 'function') {
              window.top.activateTabByName('med', true);
            }
          } catch (e) {}
        });
        return;
      }
    } catch (e) {}
    try {
      if (window.top && window.top.frames && window.top.frames.med) {
        window.top.frames.med.location.href = url;
        try {
          if (typeof window.top.activateTabByName === 'function') {
            window.top.activateTabByName('med', true);
          }
        } catch (e) {}
        return;
      }
    } catch (e) {}
    window.location.href = url;
  }
  window.medexRunSetupFlow = async function (moduleId, onboardingUrl, needsInstall, needsEnable, trigger) {
    if (!moduleId || !window.fetch) {
      return false;
    }
    medexSetStatus('Starting MedEx...', 'Preparing install and onboarding now.', '', 14);
    if (trigger) {
      trigger.disabled = true;
      trigger.style.display = 'none';
    }
    try {
      var site = new URLSearchParams(window.location.search).get('site') || 'default';
      var postAction = async function(actionName) {
        if (typeof top.restoreSession === 'function') {
          top.restoreSession();
        }
        var body = new URLSearchParams({
          modId: String(moduleId),
          modAction: actionName,
          mod_enc_menu: '',
          mod_nick_name: ''
        });
        var response = await fetch('./Installer/manage?site=' + encodeURIComponent(site), {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
          body: body.toString()
        });
        if (!response.ok) {
          throw new Error(actionName + ' failed');
        }
        var text = await response.text();
        try {
          var parsed = JSON.parse(text);
          if (parsed && parsed.status && String(parsed.status).toLowerCase() !== 'success') {
            throw new Error(String(parsed.status));
          }
        } catch (e) {
        }
      };
      if (needsInstall) {
        medexSetStatus('Installing MedEx...', 'Module files and database objects are being prepared.', '', 26);
        await postAction('install');
        medexSetStatus('Installed', 'MedEx install is complete. Starting enable next.', '', 58);
        await new Promise(function(resolve){ window.setTimeout(resolve, 550); });
      }
      if (needsEnable) {
        medexSetStatus('Enabling MedEx...', 'Install is complete. Activating MedEx now.', '', 78);
        await postAction('enable');
        medexSetStatus('Enabled', 'Module activation is complete. Preparing onboarding.', '', 92);
        await new Promise(function(resolve){ window.setTimeout(resolve, 650); });
      }
      medexSetStatus('Opening onboarding...', 'MedEx is ready. Moving into onboarding now.', 'done', 100);
      window.setTimeout(function () {
        medexOpenTarget(onboardingUrl);
      }, 1600);
    } catch (error) {
      medexSetStatus('MedEx setup needs attention.', error && error.message ? error.message : 'The automatic setup did not complete.', 'error', 100);
      if (trigger) {
        trigger.style.display = '';
        trigger.disabled = false;
        trigger.textContent = 'Retry';
      }
    }
    return false;
  };
  window.setTimeout(function () {
    window.medexRunSetupFlow('{$moduleId}', '{$onboardingUrl}', {$needsInstall}, {$needsEnable}, document.getElementById('medex-mm-retry'));
  }, 50);
})();
</script>
<style>
.medex-mm-wrap{max-width:720px;margin:14px auto;padding:24px 24px 22px;border:1px solid #cfe0fb;border-radius:18px;background:linear-gradient(180deg,#f8fbff 0%,#eef5ff 100%);box-shadow:0 18px 42px rgba(15,75,143,.10);color:#0f172a}
.medex-mm-head h2{margin:0 0 8px;font-size:28px;line-height:1.15;color:#0f4b8f}
.medex-mm-head p{margin:0;color:#475569;font-size:15px;line-height:1.6}
.medex-mm-progress{margin-top:18px;height:14px;border-radius:999px;overflow:hidden;background:#dbeafe;border:1px solid #c7ddff}
.medex-mm-progress-bar{width:10%;height:100%;border-radius:999px;background:linear-gradient(90deg,#2563eb 0%,#0ea5e9 55%,#38bdf8 100%);transition:width .9s ease}
.medex-mm-status{display:flex;align-items:center;gap:12px;margin-top:18px;font-size:17px;font-weight:700;color:#0f172a}
.medex-mm-status::before{content:"";width:20px;height:20px;border-radius:999px;border:3px solid #93c5fd;border-top-color:#1d4ed8;animation:medex-mm-spin .8s linear infinite;flex:0 0 auto}
.medex-mm-status.medex-mm-status-done::before{animation:none;border-color:#15803d;background:#15803d;box-shadow:inset 0 0 0 4px #dcfce7}
.medex-mm-status.medex-mm-status-error::before{animation:none;border-color:#b91c1c;background:#b91c1c;box-shadow:inset 0 0 0 4px #fee2e2}
.medex-mm-notes{margin-top:8px;color:#475569;font-size:14px;line-height:1.55;min-height:22px}
.medex-mm-actions{margin-top:18px}
.medex-mm-btn{display:none;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;border:1px solid #1d4ed8;background:#eff6ff;color:#1d4ed8 !important;text-decoration:none;font-weight:700;cursor:pointer}
@keyframes medex-mm-spin{to{transform:rotate(360deg)}}
</style>
<div class="medex-mm-wrap">
  <div class="medex-mm-head">
    <h2>Starting onboarding</h2>
    <p>MedEx is finishing installation in the background. Onboarding will open automatically.</p>
  </div>
  <div class="medex-mm-progress">
    <div id="medex-mm-progress-bar" class="medex-mm-progress-bar"></div>
  </div>
  <div id="medex-mm-status" class="medex-mm-status">Preparing MedEx...</div>
  <div id="medex-mm-notes" class="medex-mm-notes">Please wait while MedEx installs, enables, and moves into onboarding.</div>
  <div class="medex-mm-actions">
    <button id="medex-mm-retry" type="button" class="medex-mm-btn" onclick="return window.medexRunSetupFlow && window.medexRunSetupFlow('{$moduleId}','{$onboardingUrl}', {$needsInstall}, {$needsEnable}, this);">Retry</button>
  </div>
</div>
HTML;
    }

    private static function emitInstallerHelpFragment($modId): void
    {
        echo self::renderInstallerHelpFragment($modId);
        exit(0);
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    public function prehelp_requested($modId, $currentActionStatus): mixed
    {
        self::emitInstallerHelpFragment($modId);
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    public function help_requested($modId, $currentActionStatus): mixed
    {
        self::emitInstallerHelpFragment($modId);
    }

    // ---------------------------------------------------------------
    // Version & legacy detection helpers
    // ---------------------------------------------------------------

    /**
     * Detect whether this OpenEMR still ships the legacy library/MedEx/ code
     * (pre-PR, typically 7.x). Post-PR (>=8.0 with the removal PR merged)
     * will NOT have library/MedEx/API.php.
     */
    private static function hasLegacyMedEx(): bool
    {
        $fileroot = $GLOBALS['fileroot'] ?? '';
        return file_exists($fileroot . '/library/MedEx/API.php');
    }

    /**
     * Return the OpenEMR major version as an integer (e.g. 7 or 8).
     */
    private static function getOpenEmrMajorVersion(): int
    {
        if (isset($GLOBALS['v_major'])) {
            return (int)$GLOBALS['v_major'];
        }
        // Fallback: read from the version table
        try {
            $row = sqlQuery("SELECT v_major FROM version LIMIT 1");
            return (int)($row['v_major'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    // ---------------------------------------------------------------
    // Legacy MedEx background service management (v7 / pre-PR only)
    // ---------------------------------------------------------------

    /**
     * Deactivate the legacy MedEx background service so the module takes over.
     * Saves the previous state so it can be restored on uninstall.
     */
    private static function deactivateLegacyBackgroundService(): void
    {
        try {
            // Record original state so we can restore it later
            $row = sqlQuery("SELECT active, execute_interval FROM background_services WHERE name = 'MedEx'");
            if ($row) {
                // Store original values in the module's own table
                sqlStatement("CREATE TABLE IF NOT EXISTS medex_module_state (
                    state_key VARCHAR(100) PRIMARY KEY,
                    state_value TEXT NOT NULL,
                    updated_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                sqlStatement(
                    "INSERT INTO medex_module_state (state_key, state_value, updated_at)
                     VALUES ('legacy_bg_active', ?, NOW())
                     ON DUPLICATE KEY UPDATE state_value = VALUES(state_value), updated_at = NOW()",
                    [$row['active']]
                );
                sqlStatement(
                    "INSERT INTO medex_module_state (state_key, state_value, updated_at)
                     VALUES ('legacy_bg_interval', ?, NOW())
                     ON DUPLICATE KEY UPDATE state_value = VALUES(state_value), updated_at = NOW()",
                    [$row['execute_interval']]
                );

                // Deactivate the legacy background service
                sqlStatement("UPDATE background_services SET active = 0 WHERE name = 'MedEx'");
                error_log('[MedEx] Deactivated legacy MedEx background service (was active=' . $row['active'] . ')');
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to deactivate legacy background service: ' . $e->getMessage());
        }
    }

    /**
     * Restore the legacy MedEx background service to its pre-install state.
     */
    private static function restoreLegacyBackgroundService(): void
    {
        try {
            $activeRow = sqlQuery("SELECT state_value FROM medex_module_state WHERE state_key = 'legacy_bg_active'");
            $intervalRow = sqlQuery("SELECT state_value FROM medex_module_state WHERE state_key = 'legacy_bg_interval'");

            if ($activeRow) {
                $active = (int)$activeRow['state_value'];
                $interval = $intervalRow ? (int)$intervalRow['state_value'] : 0;

                sqlStatement(
                    "UPDATE background_services SET active = ?, execute_interval = ? WHERE name = 'MedEx'",
                    [$active, $interval]
                );
                error_log('[MedEx] Restored legacy MedEx background service (active=' . $active . ', interval=' . $interval . ')');
            }
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to restore legacy background service: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Install / Enable / Disable / Unregister
    // ---------------------------------------------------------------

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function install($modId, $currentActionStatus): mixed
    {
        // Setting the active ui flag here will allow the config button to show
        // before enable. This is a good thing because it allows the user to
        // configure the module before enabling it. However, if the module is disabled
        // this flag is reset by MM.
        self::setModuleActiveState($modId, '0', '1');

        // Set proper module name and version during install
        try {
            sqlStatement(
                "UPDATE modules SET
                    mod_name = 'MedEx Module',
                    mod_ui_name = 'Oe-module-medex',
                    sql_version = '1.1.0'
                 WHERE mod_id = ?",
                [$modId]
            );
            error_log('[MedEx] Module installed - display name set to "MedEx Module"');
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to set module name on install: ' . $e->getMessage());
        }

        // ------ Pre-PR (v7 / legacy) specific install steps ------
        if (self::hasLegacyMedEx()) {
            error_log('[MedEx] Legacy library/MedEx detected — deactivating background service');
            self::deactivateLegacyBackgroundService();
        }

        if ($currentActionStatus === 'Success') {
            self::emitInstallerHelpFragment($modId);
        }

        return $currentActionStatus;
    }

    /**
     * Handle configure request - display status as modal overlay
     *
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function configure($modId, $currentActionStatus): mixed
    {
        // Display status page as a centered modal with blurred background
        $statusUrl = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/status.php';

        // Return JavaScript that injects modal into document.body (not into ConfigRow)
        $html = <<<HTML
<script>
(function() {
    // Inject CSS for modal
    const style = document.createElement('style');
    style.textContent = `
        .medex-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: medexFadeIn 0.2s ease-out;
        }
        .medex-modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            max-height: 90vh;
            width: 90%;
            overflow: auto;
            position: relative;
            animation: medexSlideUp 0.3s ease-out;
        }
        .medex-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #f8f9fa;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 24px;
            line-height: 1;
            color: #666;
            transition: all 0.2s;
            z-index: 10;
        }
        .medex-modal-close:hover {
            background: #e9ecef;
            color: #333;
            transform: rotate(90deg);
        }
        .medex-modal-iframe {
            width: 100%;
            min-height: 400px;
            border: none;
            border-radius: 12px;
        }
        @keyframes medexFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes medexSlideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);

    // Create modal HTML
    const modal = document.createElement('div');
    modal.className = 'medex-modal-overlay';
    modal.id = 'medexStatusModal';
    modal.innerHTML = `
        <div class="medex-modal-content">
            <button class="medex-modal-close" onclick="document.getElementById('medexStatusModal').remove();" title="Close">×</button>
            <iframe class="medex-modal-iframe" src="{$statusUrl}"></iframe>
        </div>
    `;

    // Close modal on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });

    // Close modal on Escape key
    const escHandler = function(e) {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);

    // Append to body
    document.body.appendChild(modal);

    // Hide the ConfigRow since we're showing modal instead
    const configRow = document.getElementById('ConfigRow_' + {$modId});
    if (configRow) {
        configRow.style.display = 'none';
    }
})();
</script>
HTML;

        echo $html;
        return 'Success';
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
        self::setModuleActiveState($modId, '1', '0'); // mod_active=1, mod_ui_active=0 (shows Disable button)

        try {
            sqlStatement(
                "UPDATE modules SET
                    mod_name = 'MedEx Module',
                    mod_ui_name = 'Oe-module-medex',
                    sql_version = '1.1.0'
                 WHERE mod_id = ?",
                [$modId]
            );
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to enforce module metadata on enable: ' . $e->getMessage());
        }

        // Set medex_enable global to enable the module
        try {
            sqlStatement(
                "INSERT INTO globals (gl_name, gl_value) VALUES ('medex_enable', '1')
                 ON DUPLICATE KEY UPDATE gl_value = '1'"
            );
            error_log('[MedEx] Module enabled - medex_enable set to 1');
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to set medex_enable: ' . $e->getMessage());
        }

        // Run migrations to ensure all forms and tables are created
        self::runMigrations();

        // ------ Pre-PR (v7 / legacy) specific enable steps ------
        if (self::hasLegacyMedEx()) {
            // Ensure legacy background service stays deactivated
            self::deactivateLegacyBackgroundService();
            error_log('[MedEx] Legacy mode: background service confirmed deactivated on enable');
        }

        if ($currentActionStatus === 'Success') {
            self::emitInstallerHelpFragment($modId);
        }

        return $currentActionStatus;
    }

    /**
     * Run all pending migrations
     */
    private static function runMigrations(): void
    {
        $migrationsDir = __DIR__ . '/migrations';

        // Ensure migrations tracking table exists
        try {
            sqlStatement("CREATE TABLE IF NOT EXISTS medex_migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME NOT NULL,
                INDEX idx_migration_name (migration_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {
            error_log('[MedEx] Could not create migrations table: ' . $e->getMessage());
        }

        // Get migration files
        $files = glob($migrationsDir . '/*.php');
        if (empty($files)) {
            return;
        }

        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file);

            // Skip if already applied
            $applied = sqlQuery("SELECT id FROM medex_migrations WHERE migration_name = ?", [$migrationName]);
            if ($applied) {
                continue;
            }

            error_log("[MedEx] Running migration: $migrationName");

            try {
                // Include and run the migration
                include_once $file;

                // Extract number from filename (e.g., 003_create_telehealth_form.php -> 003)
                if (preg_match('/^(\d+)_/', $migrationName, $matches)) {
                    $funcName = 'run_migration_' . $matches[1];
                    if (function_exists($funcName)) {
                        $result = call_user_func($funcName);
                        if ($result) {
                            // Mark as applied
                            sqlStatement(
                                "INSERT INTO medex_migrations (migration_name, applied_at) VALUES (?, NOW())",
                                [$migrationName]
                            );
                            error_log("[MedEx] Migration $migrationName completed successfully");
                        } else {
                            error_log("[MedEx] Migration $migrationName returned false");
                        }
                    } else {
                        // Old-style migration (just include and run)
                        sqlStatement(
                            "INSERT INTO medex_migrations (migration_name, applied_at) VALUES (?, NOW())",
                            [$migrationName]
                        );
                        error_log("[MedEx] Migration $migrationName applied (legacy style)");
                    }
                }
            } catch (\Exception $e) {
                error_log("[MedEx] Migration $migrationName failed: " . $e->getMessage());
            }
        }
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function disable($modId, $currentActionStatus): mixed
    {
        // Set mod_active=0, mod_ui_active=1 (shows Enable button)
        self::setModuleActiveState($modId, '0', '1');

        // Set medex_enable global to disable the module
        try {
            sqlStatement(
                "UPDATE globals SET gl_value = '0' WHERE gl_name = 'medex_enable'"
            );
            error_log('[MedEx] Module disabled - medex_enable set to 0');
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to set medex_enable: ' . $e->getMessage());
        }

        // ------ Pre-PR (v7 / legacy): restore background service so legacy code resumes ------
        if (self::hasLegacyMedEx()) {
            self::restoreLegacyBackgroundService();
            error_log('[MedEx] Legacy mode: restored background service on disable');
        }

        return $currentActionStatus;
    }

    /**
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function unregister($modId, $currentActionStatus): mixed
    {
        // ------ Pre-PR (v7 / legacy): restore background service before we leave ------
        if (self::hasLegacyMedEx()) {
            self::restoreLegacyBackgroundService();
            error_log('[MedEx] Legacy mode: restored background service on unregister');
        }

        // Clear MedEx credentials and disable module when unregistering
        try {
            // Clear API credentials from globals
            sqlStatement("UPDATE globals SET gl_value = '' WHERE gl_name IN ('medex_api_key', 'medex_practice_id')");
            sqlStatement("UPDATE globals SET gl_value = '0' WHERE gl_name = 'medex_enable'");

            // Delete ALL rows from medex_prefs (should only be one, but ensure clean slate)
            sqlStatement("DELETE FROM medex_prefs");

            error_log('[MedEx] Module unregistered - all credentials and prefs deleted');
        } catch (\Exception $e) {
            error_log('[MedEx] Failed to clear credentials on unregister: ' . $e->getMessage());
        }

        // Clean up module state table
        try {
            sqlStatement("DROP TABLE IF EXISTS medex_module_state");
        } catch (\Exception $e) {
            // Ignore — table may not exist
        }

        return $currentActionStatus;
    }

    /**
     * Reset module to initial registered/uninstalled state while preserving display metadata.
     *
     * @param $modId
     * @param $currentActionStatus
     * @return mixed
     */
    private function reset_module($modId, $currentActionStatus): mixed
    {
        try {
            // Keep module in resettable state (registered, not installed, not enabled).
            sqlStatement(
                "UPDATE modules SET
                    mod_active = 0,
                    mod_ui_active = 0,
                    sql_run = 0,
                    mod_name = 'MedEx Module',
                    mod_ui_name = 'Oe-module-medex',
                    sql_version = '1.1.0'
                 WHERE mod_id = ?",
                [$modId]
            );

            // Clear runtime credentials/state for a clean start.
            sqlStatement("UPDATE globals SET gl_value = '' WHERE gl_name IN ('medex_api_key', 'medex_practice_id')");
            sqlStatement("UPDATE globals SET gl_value = '0' WHERE gl_name = 'medex_enable'");
            sqlStatement("DELETE FROM medex_prefs");
        } catch (\Exception $e) {
            error_log('[MedEx] reset_module metadata/state fix failed: ' . $e->getMessage());
        }

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
        $results = sqlQuery($sql, [$modId]);
        foreach ($results as $k => $v) {
            $registry[$k] = trim(((string) preg_replace('/\R/', '', (string) $v)));
        }

        return $registry;
    }
}
