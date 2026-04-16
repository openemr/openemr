<?php
/**
 * MedEx Communication Module for OpenEMR
 *
 * Module wrapper for legacy MedEx API code
 * Uses the proven API-based architecture from library/MedEx
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace {
// Global namespace wrapper
error_log('[MedEx] Bootstrap file loaded - starting registration - SCRIPT: ' . ($_SERVER['SCRIPT_NAME'] ?? 'unknown') . ' - TIME: ' . date('Y-m-d H:i:s'));

// Load OEGlobalsBag polyfill first to prevent fatal errors
require_once __DIR__ . '/src/API/OEGlobalsBag_polyfill.php';

use OpenEMR\Menu\MenuEvent;
use OpenEMR\Events\Core\ModuleManagerEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\Globals\GlobalSetting;

// Initialize MedEx base URL from the single source of truth in MedExConfig.
// OpenEMR pre-loads the globals DB table into $GLOBALS, so medex_bank_url/medex_base_url
// will already be set if saved via Admin > Globals — MedExConfig::baseUrl() picks that up.
require_once __DIR__ . '/src/MedExConfig.php';
if (!isset($GLOBALS['medex_base_url'])) {
    $GLOBALS['medex_base_url'] = \OpenEMR\Modules\MedEx\MedExConfig::DEFAULT_BASE_URL;
    error_log('[MedEx] Initialized medex_base_url to: ' . $GLOBALS['medex_base_url']);
}

/**
 * Add MedEx as a top-level menu with conditional submenus
 * IMPORTANT: This function must be in global namespace for event dispatcher
 */
if (!function_exists('oe_module_medex_add_menu_item')) {
function oe_module_medex_add_menu_item(MenuEvent $event): MenuEvent
{
    error_log('[MedEx] Menu function called - adding top-level MedEx menu');
    $menu = $event->getMenu();
    error_log('[MedEx] Current menu count: ' . count($menu));

    // Get site ID and webroot
    $siteId = $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default');
    $webroot = $GLOBALS['webroot'] ?? '';
    error_log('[MedEx] webroot=' . $webroot . ', siteId=' . $siteId);

    $buildUrl = static function (string $path, array $params = []) use ($webroot, $siteId): string {
        $params = array_merge(['site' => $siteId], $params);
        $query = http_build_query($params);
        return $webroot . $path . ($query ? ('?' . $query) : '');
    };

    $medexBaseUrl = \OpenEMR\Modules\MedEx\MedExConfig::baseUrl();

    // Check enabled services from medex_prefs
    // ONLY use enabled_services — written by process_subscription.php (subscribe/cancel)
    // and by getEnabledServices() (always writes, even empty). Do NOT fall back to
    // last_services_result which can be stale and cause ghost menu items.
    $enabledServices = [];
    $rawEnabledServices = [];
    $hasCredentials = false;
    $hasLiveSessionToken = false;
    $moduleMenuEnabled = false;
    $medexGloballyEnabled = false;
    try {
        $moduleRow = sqlQuery(
            "SELECT mod_active, mod_ui_active
               FROM modules
              WHERE mod_directory = 'oe-module-medex'
              ORDER BY mod_id DESC
              LIMIT 1"
        );
        $moduleMenuEnabled = !empty($moduleRow) && (
            (int)($moduleRow['mod_active'] ?? 0) === 1 ||
            (int)($moduleRow['mod_ui_active'] ?? 0) === 1
        );

        $globalRow = sqlQuery(
            "SELECT gl_value
               FROM globals
              WHERE gl_name = 'medex_enable'
              LIMIT 1"
        );
        $medexGloballyEnabled = ((string)($globalRow['gl_value'] ?? '0') === '1');

        $medexPrefsColumns = [];
        $columnResult = sqlStatement("SHOW COLUMNS FROM medex_prefs");
        while ($columnRow = sqlFetchArray($columnResult)) {
            if (!empty($columnRow['Field'])) {
                $medexPrefsColumns[(string)$columnRow['Field']] = true;
            }
        }

        $selectFields = [
            'status',
            'ME_username',
            'ME_api_key',
            'MedEx_id',
        ];
        if (!empty($medexPrefsColumns['session_token'])) {
            $selectFields[] = 'session_token';
        } else {
            $selectFields[] = "NULL AS session_token";
        }
        if (!empty($medexPrefsColumns['session_token_expiry'])) {
            $selectFields[] = 'session_token_expiry';
        } else {
            $selectFields[] = "NULL AS session_token_expiry";
        }

        $statusRecord = sqlQuery(
            "SELECT " . implode(', ', $selectFields) . "
               FROM medex_prefs
              ORDER BY MedEx_lastupdated DESC LIMIT 1"
        );
        if (!empty($statusRecord['status'])) {
            $status = json_decode($statusRecord['status'], true);
            if (isset($status['enabled_services']) && is_array($status['enabled_services'])) {
                $enabledServices = $status['enabled_services'];
                $rawEnabledServices = $status['enabled_services'];
            }
        }

        // Guardrail: never expose paid/service menu items from stale cache when this
        // OpenEMR is not actively connected to MedEx. Require a usable credential row
        // and a non-expired session token before honoring enabled_services.
        $hasCredentials = !empty($statusRecord['ME_username'])
            && !empty($statusRecord['ME_api_key'])
            && !empty($statusRecord['MedEx_id']);
        $tokenExpiryTs = !empty($statusRecord['session_token_expiry'])
            ? strtotime((string)$statusRecord['session_token_expiry'])
            : 0;
        $hasLiveSessionToken = !empty($statusRecord['session_token']) && $tokenExpiryTs > time();
        if (!$hasCredentials || !$hasLiveSessionToken) {
            $enabledServices = [];
        }
    } catch (\Throwable $e) {
        error_log('[MedEx] Error fetching enabled services: ' . $e->getMessage());
    }

    $canInjectServiceMenus = $moduleMenuEnabled
        && $medexGloballyEnabled
        && $hasCredentials
        && $hasLiveSessionToken;
    if (!$canInjectServiceMenus) {
        $enabledServices = [];
    }
    
    // Helper function to check if service is enabled (handles both array and object formats)
    $isServiceEnabled = function($service) use ($enabledServices) {
        if (empty($enabledServices)) {
            return false;
        }
        if (is_array($enabledServices)) {
            // Object format: {"secure_chat": true} or {"secure_chat": 1}
            if (isset($enabledServices[$service])) {
                // Accept both boolean true and integer 1 (JSON sometimes converts true to 1)
                return $enabledServices[$service] === true || $enabledServices[$service] === 1;
            }
            // Array format: ["secure_chat", "medex_messages"]
            return in_array($service, $enabledServices);
        }
        return false;
    };
    
    $isAdmin = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super');
    $hasReminders = $canInjectServiceMenus && $isServiceEnabled('appointment_reminders');
    $hasSecureChat = $canInjectServiceMenus && $isServiceEnabled('secure_chat');
    $hasPdfManagement = $canInjectServiceMenus && $isServiceEnabled('pdf_management');
    $hasTeleHealth = $canInjectServiceMenus && ($isServiceEnabled('TeleHealth') || $isServiceEnabled('telehealth'));

    // Create top-level MedEx menu
    $medexTopMenu = new \stdClass();
    $medexTopMenu->requirement = 0;
    $medexTopMenu->target = 'med';
    $medexTopMenu->menu_id = 'medimg';
    $medexTopMenu->label = xlt("MedEx");
    // $medexTopMenu->icon = 'fa-comment-medical';
    $medexTopMenu->children = [];
    $medexTopMenu->acl_req = $canInjectServiceMenus ? ["patients", "demo"] : ["admin", "super"];

    if ($isAdmin) {
        $adminDashboardItem = new \stdClass();
        $adminDashboardItem->requirement = 0;
        $adminDashboardItem->target = 'med';
        $adminDashboardItem->menu_id = 'medex_admin';
        $adminDashboardItem->label = xlt("Admin Dashboard");
        $adminDashboardPath = $hasCredentials
            ? '/interface/modules/custom_modules/oe-module-medex/admin/index.php'
            : '/interface/modules/custom_modules/oe-module-medex/admin/splash.php';
        $adminDashboardItem->url = $buildUrl($adminDashboardPath, ['minimal' => 1]);
        $adminDashboardItem->acl_req = ["admin", "super"];
        $medexTopMenu->children[] = $adminDashboardItem;
    }

    // 1. SMS Bot (ONLY when appointment_reminders subscription exists)
    if ($hasReminders) {
        $smsBotItem = new \stdClass();
        $smsBotItem->requirement = 0;
        $smsBotItem->target = 'med';
        $smsBotItem->menu_id = 'medex_sms_bot';
        $smsBotItem->label = xlt("SMS Bot");
        $smsBotItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php');
        $smsBotItem->acl_req = ["patients", "demo"];
        $medexTopMenu->children[] = $smsBotItem;
    }

    // 2. Secure Chat (patient search → send link via text/email → chat)
    if ($hasSecureChat) {
        $secureChatItem = new \stdClass();
        $secureChatItem->requirement = 0;
        $secureChatItem->target = 'med';
        $secureChatItem->menu_id = 'medex_secure_chat';
        $secureChatItem->label = xlt("Secure Chat");
        $secureChatItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php');
        $secureChatItem->acl_req = ["patients", "demo"];
        $medexTopMenu->children[] = $secureChatItem;
        
        // Add Portal Messages viewer (shows synced messages from MedEx to OpenEMR portal)
        $portalMessagesItem = new \stdClass();
        $portalMessagesItem->requirement = 0;
        $portalMessagesItem->target = 'med';
        $portalMessagesItem->menu_id = 'medex_portal_messages';
        $portalMessagesItem->label = xlt("Portal Messages");
        $portalMessagesItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/portal_messages.php');
        $portalMessagesItem->acl_req = ["patients", "portal"];
        $medexTopMenu->children[] = $portalMessagesItem;
    }

    // 3. PDF Filler (ONLY when pdf_management subscription exists)
    if ($hasPdfManagement) {
        $pdfFillerItem = new \stdClass();
        $pdfFillerItem->requirement = 0;
        $pdfFillerItem->target = 'med';
        $pdfFillerItem->menu_id = 'medex_pdf_filler';
        $pdfFillerItem->label = xlt("PDF Filler");
        $pdfFillerItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/admin/pdf/index.php');
        $pdfFillerItem->acl_req = ["patients", "docs"];
        $medexTopMenu->children[] = $pdfFillerItem;
    }

    // 4. TeleHealth (when telehealth subscription exists)
    if ($hasTeleHealth) {
        $telehealthItem = new \stdClass();
        $telehealthItem->requirement = 0;
        $telehealthItem->target = 'med';
        $telehealthItem->menu_id = 'medex_telehealth';
        $telehealthItem->label = xlt("TeleHealth");
        $telehealthItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/telehealth.php');
        $telehealthItem->icon = 'fa-video';
        $telehealthItem->acl_req = ["encounters", "notes"];
        $medexTopMenu->children[] = $telehealthItem;
    }

    // 5. Calendar Feeds — visible to anyone with calendar ACL (patients/appt),
    // matching the same gate as the OpenEMR calendar and FullCalendar events API.
    // authorized=1 means "billing provider" and is NOT the right gate here;
    // front-desk staff and nurses with calendar access must also be able to subscribe.
    $hasCalendarAcl = \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'appt');
    if ($canInjectServiceMenus && $isServiceEnabled('calendar_export') && $hasCalendarAcl) {
        $calendarFeedsItem = new \stdClass();
        $calendarFeedsItem->requirement = 0;
        $calendarFeedsItem->target = 'med';
        $calendarFeedsItem->menu_id = 'medex_calendar_feeds';
        $calendarFeedsItem->label = xlt("Calendar Feeds");
        $calendarFeedsItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/calendar_feeds.php');
        $calendarFeedsItem->acl_req = ["patients", "demo"];
        $medexTopMenu->children[] = $calendarFeedsItem;
    }



    // Only add the MedEx menu if there are children
    if (!empty($medexTopMenu->children)) {
        // Append MedEx menu at the end (far right of the menu bar)
        $menu[] = $medexTopMenu;
        error_log('[MedEx] Added top-level MedEx menu with ' . count($medexTopMenu->children) . ' children at position ' . (count($menu) - 1));
    }

    // Keep miscellaneous items for backward compatibility
    foreach ($menu as $topItem) {
        if (isset($topItem->menu_id) && $topItem->menu_id === 'misimg') {
            // Same ACL gate as above: patients.appt, not authorized=1
            if ($isServiceEnabled('calendar_export') && \OpenEMR\Common\Acl\AclMain::aclCheckCore('patients', 'appt')) {
                $calendarFeedsItem = new \stdClass();
                $calendarFeedsItem->requirement = 0;
                $calendarFeedsItem->target = 'msc';
                $calendarFeedsItem->menu_id = 'mod_medex_calendar_feeds';
                $calendarFeedsItem->label = xlt("Calendar Feeds");
                $calendarFeedsItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/calendar_feeds.php');
                $calendarFeedsItem->children = [];
                $topItem->children[] = $calendarFeedsItem;
                error_log('[MedEx] Added Calendar Feeds to Miscellaneous menu');
            }
            
            break;
}
}

    $event->setMenu($menu);
    error_log('[MedEx] Menu items added! New menu count: ' . count($menu));

    return $event;
}
} // end function_exists check

if (!function_exists('oe_module_medex_add_user_settings')) {
function oe_module_medex_add_user_settings(GlobalsInitializedEvent $event): void
{
    $userId = $_SESSION['authUserID'] ?? null;
    if (empty($userId)) {
        return;
    }

    $service = $event->getGlobalsService();
    $settingsSection = 'Calendar';

    $legacy = sqlQuery(
        "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = 'medex_preferences'",
        [$userId]
    );
    $legacyPrefs = [];
    if (!empty($legacy['setting_value'])) {
        $decoded = json_decode((string)$legacy['setting_value'], true);
        if (is_array($decoded)) {
            $legacyPrefs = $decoded;
        }
    }

    $seed = static function (string $key, $fallback) use ($userId, $legacyPrefs): string {
        $existing = sqlQuery(
            "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = ?",
            [$userId, 'global:' . $key]
        );
        if (!empty($existing) && array_key_exists('setting_value', $existing)) {
            return (string)($existing['setting_value'] ?? '');
        }

        $value = $fallback;
        if ($key === 'medex_use_full_calendar' && array_key_exists('use_full_calendar', $legacyPrefs)) {
            $value = !empty($legacyPrefs['use_full_calendar']) ? '1' : '0';
        } elseif ($key === 'medex_calendar_theme') {
            if (!empty($legacyPrefs['inherit_openemr_theme'])) {
                $value = 'openemr';
            } elseif (!empty($legacyPrefs['calendar_theme'])) {
                $value = (string)$legacyPrefs['calendar_theme'];
            }
        }

        sqlStatement(
            "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?)",
            [$userId, 'global:' . $key, (string)$value]
        );
        return (string)$value;
    };

    $service->appendToSection($settingsSection, 'medex_use_full_calendar', new GlobalSetting(
        xlt('Use MedEx Full Calendar'),
        GlobalSetting::DATA_TYPE_BOOL,
        $seed('medex_use_full_calendar', '1'),
        xlt('Enable MedEx Full Calendar in place of OpenEMR calendar for your user.'),
        true
    ));
    $service->appendToSection($settingsSection, 'medex_calendar_theme', new GlobalSetting(
        xlt('MedEx Full Calendar Theme'),
        [
            'openemr' => xlt('Inherit OpenEMR Theme'),
            'classic' => xlt('Classic'),
            'compact' => xlt('Compact'),
            'high_contrast' => xlt('High Contrast'),
            'ocean' => xlt('Ocean'),
            'sunrise' => xlt('Sunrise'),
            'forest' => xlt('Forest'),
            'slate' => xlt('Slate')
        ],
        $seed('medex_calendar_theme', 'openemr'),
        xlt('Choose your Full Calendar theme. Select OpenEMR to inherit OpenEMR colors.'),
        true
    ));
}
}
} // end namespace

namespace OpenEMR\Modules\MedEx {

use OpenEMR\Events\Core\ModuleManagerEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Common\Session\SessionWrapperFactory;

require_once(__DIR__ . '/src/ModuleManagerListener.php');
require_once(__DIR__ . '/src/MedExDirectoryManager.php');

$medexOptionalListenerFiles = [
    __DIR__ . '/src/Listeners/MessagesPageListener.php',
    __DIR__ . '/src/Listeners/PatientTrackerListener.php',
    __DIR__ . '/src/Listeners/PatientTrackerInjectionListener.php',
];
foreach ($medexOptionalListenerFiles as $medexOptionalListenerFile) {
    if (is_file($medexOptionalListenerFile)) {
        require_once($medexOptionalListenerFile);
    } else {
        error_log('[MedEx] Optional listener missing, skipping load: ' . basename($medexOptionalListenerFile));
    }
}

// Register medex.api service in container when available, so core can fetch a stable service
if (!empty($GLOBALS['kernel'])) {
    try {
        $container = $GLOBALS['kernel']->getContainer();
        if ($container) {
            if (method_exists($container, 'set')) {
                $container->set('medex.api', function () {
                    return new MedExAPI();
                });
            } elseif (method_exists($container, 'register')) {
                $container->register('medex.api', function () {
                    return new MedExAPI();
                });
            } elseif (method_exists($container, 'offsetSet')) {
                $container['medex.api'] = function () {
                    return new MedExAPI();
                };
            }
            error_log('[MedEx] medex.api service registered in container');
        }
    } catch (\Throwable $t) {
        error_log('[MedEx] Failed to register medex.api service: ' . $t->getMessage());
    }
}

/**
 * Bootstrap MedEx Module
 */
if (!class_exists('OpenEMR\\Modules\\MedEx\\Bootstrap')) {
class Bootstrap
{
    const MODULE_NAME = 'MedEx Communication';
    const MODULE_VERSION = '1.1.0';
    const MINIMUM_OPENEMR_VERSION = '7.0.0';

    /**
     * Get module information
     */
    public static function getModuleInfo(): array
    {
        return [
            'name' => self::MODULE_NAME,
            'version' => self::MODULE_VERSION,
            'author' => 'MedEx Bank',
            'description' => 'HIPAA-compliant patient communication platform with secure messaging, SMS, email, and appointment reminders using MedEx API',
            'dependencies' => [],
            'acl' => [
                'MedEx Admin' => 'admin',
                'MedEx User' => 'write',
                'MedEx View Only' => 'read'
            ]
        ];
    }
}

function medex_auto_reset_smarty_cache_for_version(string $moduleVersion): void
{
    try {
        $siteDir = rtrim((string)($GLOBALS['OE_SITE_DIR'] ?? ''), '/');
        if ($siteDir === '' || !is_dir($siteDir)) {
            return;
        }
        $smartyDir = $siteDir . '/documents/smarty';
        if (!is_dir($smartyDir)) {
            return;
        }
        $markerFile = $smartyDir . '/.medex_smarty_reset_version';
        $lastVersion = '';
        if (is_file($markerFile)) {
            $lastVersion = trim((string)@file_get_contents($markerFile));
        }
        if ($lastVersion === $moduleVersion) {
            return;
        }

        $targets = [
            $smartyDir . '/main',
            $smartyDir . '/modules',
        ];
        foreach ($targets as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $files = glob($dir . '/%%*');
            if (!is_array($files)) {
                continue;
            }
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        @file_put_contents($markerFile, $moduleVersion);
        @chmod($markerFile, 0644);
    } catch (\Throwable $t) {
        error_log('[MedEx] Smarty auto-reset skipped: ' . $t->getMessage());
    }
}

// Register event listeners at file scope (critical for OpenEMR module loading)
// The $eventDispatcher variable is provided by OpenEMR's ModulesApplication
if (isset($eventDispatcher) && $eventDispatcher instanceof \Symfony\Component\EventDispatcher\EventDispatcherInterface) {
    // Two-level check:
    //   $isModuleInstalled — module is active in Module Manager (mod_active=1)
    //                        → always register admin menu so admin can reach
    //                          Subscriptions/Settings regardless of medex_enable
    //   $isModuleEnabled  — fully enabled (Module Manager + medex_enable global = '1')
    //                        → register service listeners (calendar sync, etc.)
    $isModuleInstalled = false;
    $isModuleEnabled   = false;
    try {
        if (function_exists('sqlQuery')) {
            $moduleStatus = sqlQuery("SELECT mod_active FROM modules WHERE mod_directory = 'oe-module-medex' LIMIT 1");
            $globalEnabled = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_enable' LIMIT 1");

            if ($moduleStatus && $moduleStatus['mod_active'] === '1') {
                $isModuleInstalled = true; // module loaded — admin menu always visible
                if ($globalEnabled && $globalEnabled['gl_value'] === '1') {
                    $isModuleEnabled = true; // fully enabled — service listeners active
                }
            }
        }
    } catch (\Throwable $t) {
        error_log('[MedEx] Failed to check module status: ' . $t->getMessage());
    }

    // Register Module lifecycle events independently of $isModuleEnabled
    // This ensures Install and Uninstall events are caught correctly
    if (class_exists(\OpenEMR\Events\Core\ModuleManagerEvent::class)) {
        $moduleListener = new ModuleManagerListener();
        $eventDispatcher->addListener(\OpenEMR\Events\Core\ModuleManagerEvent::EVENT_INSTALL, [$moduleListener, 'onModuleInstall']);
        $eventDispatcher->addListener(\OpenEMR\Events\Core\ModuleManagerEvent::EVENT_ENABLE, [$moduleListener, 'onModuleEnable']);
        $eventDispatcher->addListener(\OpenEMR\Events\Core\ModuleManagerEvent::EVENT_DISABLE, [$moduleListener, 'onModuleDisable']);
        $eventDispatcher->addListener(\OpenEMR\Events\Core\ModuleManagerEvent::EVENT_UNINSTALL, [$moduleListener, 'onModuleUninstall']);
    }

    // Patch Module Manager page behavior even before installation so the MedEx
    // help/install affordances can open the setup modal directly.
    register_shutdown_function(function () {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        // This patch is only for Module Manager installer pages.
        if (strpos($requestUri, '/interface/modules/zend_modules/public/Installer') === false) {
            return;
        }

        // Skip injection on XHR/AJAX requests — appending a <script> after a JSON
        // response body breaks JSON.parse() in action.js.
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return;
        }
        echo <<<'JS'
<script>
(function () {
    function getSite() {
        return new URLSearchParams(window.location.search).get('site') || 'default';
    }

    function getManageUrl() {
        return './Installer/manage?site=' + encodeURIComponent(getSite());
    }

    function getOnboardingUrl() {
        return (window.webroot_url || '') +
            '/interface/modules/custom_modules/oe-module-medex/admin/onboarding.php?site=' +
            encodeURIComponent(getSite()) + '&step=1&force_onboarding=1';
    }

    function getInterfaceFramePath(url) {
        if (!url) {
            return '';
        }
        var normalized = String(url);
        try {
            normalized = new URL(normalized, window.location.origin).pathname +
                (new URL(normalized, window.location.origin).search || '');
        } catch (e) {}
        var webroot = window.webroot_url || '';
        if (webroot && normalized.indexOf(webroot) === 0) {
            normalized = normalized.slice(webroot.length);
        }
        normalized = normalized.replace(/^\/+/, '/');
        if (normalized.indexOf('/interface/') === 0) {
            normalized = normalized.slice('/interface/'.length);
        }
        return normalized.replace(/^\/+/, '');
    }

    function openInMedexTarget(url) {
        try {
            if (window.top && window.top.left_nav && typeof window.top.left_nav.loadFrame === 'function') {
                var framePath = getInterfaceFramePath(url);
                if (framePath) {
                    window.top.left_nav.loadFrame('medex_onboarding', 'med', framePath);
                    return;
                }
            }
        } catch (e) {}
        try {
            if (window.top && window.top.frames && window.top.frames.med) {
                window.top.frames.med.location.href = url;
                return;
            }
        } catch (e) {}
        window.location.href = url;
    }

    function getMedexSetupUrl(rowId) {
        var url = (window.webroot_url || '') + '/interface/modules/custom_modules/oe-module-medex/show_help_setup.php?site=' + encodeURIComponent(getSite());
        if (rowId) {
            url += '&mod_id=' + encodeURIComponent(rowId);
        }
        return url;
    }

    function ensureProgressStyles() {
        if (document.getElementById('medex-install-progress-style')) {
            return;
        }
        var style = document.createElement('style');
        style.id = 'medex-install-progress-style';
        style.textContent = ''
            + '#install_upgrade_log.medex-progress-log{display:block !important;padding:0;background:transparent;border:0;box-shadow:none;}'
            + '.medex-progress-shell{max-width:980px;margin:12px auto 0;background:linear-gradient(180deg,#f8fbff 0%,#eef5ff 100%);'
            + 'border:1px solid #bfd7fb;border-radius:14px;box-shadow:0 16px 36px rgba(15,75,143,.12);overflow:hidden;}'
            + '.medex-progress-head{padding:14px 18px 10px;color:#0f3f75;font-size:20px;font-weight:700;}'
            + '.medex-progress-body{padding:0 18px 18px;}'
            + '.medex-progress-copy{color:#1e3a5f;font-size:14px;line-height:1.5;margin:0 0 14px;}'
            + '.medex-progress-track{position:relative;height:12px;background:#dbeafe;border-radius:999px;overflow:hidden;}'
            + '.medex-progress-bar{height:100%;width:10%;border-radius:999px;background:linear-gradient(90deg,#2563eb 0%,#0ea5e9 55%,#38bdf8 100%);'
            + 'transition:width .9s ease;}'
            + '.medex-progress-status{display:flex;align-items:center;gap:10px;padding:14px 0 6px;color:#0f172a;font-size:15px;font-weight:600;}'
            + '.medex-progress-status::before{content:\"\";width:18px;height:18px;border-radius:999px;border:3px solid #93c5fd;'
            + 'border-top-color:#1d4ed8;animation:medex-spinner .75s linear infinite;flex:0 0 auto;}'
            + '.medex-progress-status.done::before{border-color:#16a34a;background:#16a34a;animation:none;box-shadow:inset 0 0 0 4px #dcfce7;}'
            + '.medex-progress-status.error::before{border-color:#dc2626;background:#dc2626;animation:none;box-shadow:inset 0 0 0 4px #fee2e2;}'
            + '.medex-progress-notes{font-size:13px;color:#475569;line-height:1.5;}'
            + '.medex-row-busy{opacity:.72;}'
            + '.medex-row-busy a{pointer-events:none;}'
            + '@keyframes medex-spinner{to{transform:rotate(360deg);}}';
        document.head.appendChild(style);
    }

    function ensureProgressPanel() {
        ensureProgressStyles();
        var log = document.getElementById('install_upgrade_log');
        if (!log) {
            return null;
        }
        log.classList.add('medex-progress-log');
        log.style.display = 'block';
        var shell = log.querySelector('.medex-progress-shell');
        if (shell) {
            return {
                log: log,
                bar: shell.querySelector('.medex-progress-bar'),
                status: shell.querySelector('.medex-progress-status'),
                notes: shell.querySelector('.medex-progress-notes')
            };
        }
        log.innerHTML = ''
            + '<div class="medex-progress-shell">'
            + '  <div class="medex-progress-head">Installing MedEx</div>'
            + '  <div class="medex-progress-body">'
            + '    <p class="medex-progress-copy">MedEx is being installed and enabled now. Onboarding will open automatically when setup is ready.</p>'
            + '    <div class="medex-progress-track"><div class="medex-progress-bar"></div></div>'
            + '    <div class="medex-progress-status">Preparing MedEx installation...</div>'
            + '    <div class="medex-progress-notes">Please wait. This page will continue directly into onboarding.</div>'
            + '  </div>'
            + '</div>';
        return {
            log: log,
            bar: log.querySelector('.medex-progress-bar'),
            status: log.querySelector('.medex-progress-status'),
            notes: log.querySelector('.medex-progress-notes')
        };
    }

    function updateProgress(statusText, notesText, state, percent) {
        var panel = ensureProgressPanel();
        if (!panel) {
            return;
        }
        if (panel.bar && typeof percent === 'number') {
            panel.bar.style.width = Math.max(8, Math.min(100, percent)) + '%';
        }
        if (panel.status) {
            panel.status.textContent = statusText;
            panel.status.classList.remove('done', 'error');
            if (state === 'done' || state === 'error') {
                panel.status.classList.add(state);
            }
        }
        if (panel.notes) {
            panel.notes.textContent = notesText;
        }
    }

    function wait(ms) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, ms);
        });
    }

    function setRowBusy(row, isBusy) {
        if (!row) {
            return;
        }
        row.classList.toggle('medex-row-busy', !!isBusy);
        Array.prototype.forEach.call(row.querySelectorAll('a, button, input, select'), function (el) {
            if (isBusy) {
                el.dataset.medexDisabled = el.getAttribute('aria-disabled') || '';
                el.setAttribute('aria-disabled', 'true');
                if ('disabled' in el) {
                    el.disabled = true;
                }
            } else {
                if ('disabled' in el) {
                    el.disabled = false;
                }
                if (el.dataset.medexDisabled) {
                    el.setAttribute('aria-disabled', el.dataset.medexDisabled);
                } else {
                    el.removeAttribute('aria-disabled');
                }
                delete el.dataset.medexDisabled;
            }
        });
    }

    function getManagePayload(rowId, action) {
        var payload = {
            modId: rowId,
            modAction: action
        };
        var encMenu = document.getElementById('mod_enc_menu');
        var nickname = document.getElementById('mod_nick_name_' + rowId);
        payload.mod_enc_menu = encMenu ? encMenu.value : '';
        payload.mod_nick_name = nickname ? nickname.value : '';
        return payload;
    }

    function postManageAction(rowId, action) {
        if (typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
        return new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: 'POST',
                url: getManageUrl(),
                data: getManagePayload(rowId, action),
                beforeSend: function () {
                    jQuery('.modal').show();
                },
                success: function (data) {
                    var parsed;
                    try {
                        parsed = typeof data === 'string' ? JSON.parse(data) : data;
                    } catch (error) {
                        reject(new Error('MedEx installer returned an unexpected response.'));
                        return;
                    }
                    if (!parsed || String(parsed.status || '').toUpperCase() !== 'SUCCESS') {
                        reject(new Error(parsed && parsed.status ? parsed.status : 'MedEx ' + action + ' failed.'));
                        return;
                    }
                    resolve(parsed);
                },
                error: function (xhr) {
                    reject(new Error('MedEx ' + action + ' failed with HTTP ' + (xhr && xhr.status ? xhr.status : 'error') + '.'));
                },
                complete: function () {
                    jQuery('.modal').hide();
                }
            });
        });
    }

    function runMedexInstall(rowId, row) {
        if (!rowId || window.__medex_install_running) {
            return false;
        }
        window.__medex_install_running = true;
        setRowBusy(row, true);
        updateProgress('Installing MedEx...', 'Preparing module files and database objects now.', '', 24);
        postManageAction(rowId, 'install')
            .then(function () {
                updateProgress('Installed', 'MedEx install is complete. Starting enable next.', '', 56);
                return wait(550);
            })
            .then(function () {
                updateProgress('Enabling MedEx...', 'Finalizing module activation now.', '', 76);
                return postManageAction(rowId, 'enable');
            })
            .then(function () {
                updateProgress('Enabled', 'Module activation is complete. Preparing onboarding.', '', 92);
                return wait(650);
            })
            .then(function () {
                updateProgress('Opening onboarding...', 'MedEx is ready. Redirecting into onboarding now.', 'done', 100);
                window.setTimeout(function () {
                    openInMedexTarget(getOnboardingUrl());
                }, 1600);
            })
            .catch(function (error) {
                window.__medex_install_running = false;
                setRowBusy(row, false);
                updateProgress('MedEx setup needs attention.', error && error.message ? error.message : 'The install sequence did not complete.', 'error', 100);
            });
        return false;
    }

    function patchManage() {
        if (typeof window.manage !== 'function' || window.__medex_manage_patched) {
            return;
        }
        window.__medex_manage_patched = true;
        var originalManage = window.manage;
        window.manage = function (id, action) {
            var row = document.getElementById(String(id));
            var rowText = row ? (row.textContent || '').toLowerCase() : '';
            var isMedexRow = rowText.indexOf('oe-module-medex') !== -1 || rowText.indexOf('medex module') !== -1;
            if (isMedexRow && action === 'install') {
                return runMedexInstall(String(id), row);
            }
            return originalManage.apply(this, arguments);
        };
    }

    function patchConfigure() {
        if (typeof window.configure !== 'function' || window.__medex_configure_patched) { return; }
        window.__medex_configure_patched = true;
        var originalConfigure = window.configure;
        window.configure = function (id, imgpath) {
            var row = document.getElementById(String(id));
            var isMedexRow = false;
            if (row) {
                var rowText = (row.textContent || '').toLowerCase();
                isMedexRow = rowText.indexOf('oe-module-medex') !== -1 || rowText.indexOf('medex module') !== -1;
            }
            if (isMedexRow) {
                var setupUrl = getMedexSetupUrl(id);
                if (typeof window.openModuleHelp === 'function') {
                    window.openModuleHelp(setupUrl, 'MedEx Setup Help');
                } else {
                    window.location.href = setupUrl;
                }
                return false;
            }
            if (jQuery('#ConfigRow_' + id).css('display') !== 'none') {
                jQuery('.config').hide();
                jQuery('#ConfigRow_' + id).fadeOut();
            } else {
                jQuery.post('./Installer/configure?site=' + encodeURIComponent(getSite()), {mod_id: id},
                    function (data) {
                        jQuery('.config').hide();
                        jQuery('#ConfigRow_' + id).hide();
                        jQuery('#ConfigRow_' + id)
                            .html('<td colspan="10" style="background-color:var(--light);">' + data + '</td>')
                            .fadeIn();
                    }
                );
            }
        };
    }

    function patchMedexButtons() {
        if (window.__medex_help_button_patched) { return; }
        var rows = Array.prototype.slice.call(document.querySelectorAll('tr[id]'));
        var row = rows.find(function (tr) {
            var t = (tr.textContent || '').toLowerCase();
            return t.indexOf('oe-module-medex') !== -1 || t.indexOf('medex module') !== -1;
        });
        if (!row) { return; }

        var rowId = row.getAttribute('id') || '';
        var setupUrl = getMedexSetupUrl(rowId);

        var helpLink = row.querySelector("a[onclick*=\"help_requested\"]");
        if (helpLink && !helpLink.dataset.medexPatched) {
            helpLink.dataset.medexPatched = '1';
            helpLink.setAttribute('href', setupUrl);
            helpLink.setAttribute('target', '_self');
            helpLink.setAttribute('onclick', 'return false;');
            helpLink.onclick = function (event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                }
                if (typeof window.openModuleHelp === 'function') {
                    window.openModuleHelp(setupUrl, 'MedEx Setup Help');
                } else {
                    window.location.href = setupUrl;
                }
                return false;
            };
        }

        var installLink = row.querySelector("a[onclick*=\"'install'\"]");
        if (installLink && !installLink.dataset.medexPatched) {
            installLink.dataset.medexPatched = '1';
            installLink.setAttribute('href', '#');
            installLink.setAttribute('target', '_self');
            installLink.setAttribute('onclick', 'return false;');
            installLink.onclick = function (event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (typeof event.stopImmediatePropagation === 'function') {
                        event.stopImmediatePropagation();
                    }
                }
                return runMedexInstall(rowId, row);
            };
        }

        window.__medex_help_button_patched = true;
    }

    function installCaptureInterceptor() {
        if (window.__medex_capture_interceptor_installed) { return; }
        window.__medex_capture_interceptor_installed = true;
        document.addEventListener('click', function (event) {
            var target = event.target;
            if (!target || typeof target.closest !== 'function') { return; }
            var link = target.closest('a');
            if (!link) { return; }
            var row = link.closest('tr');
            if (!row) { return; }
            var rowText = (row.textContent || '').toLowerCase();
            var isMedexRow = rowText.indexOf('oe-module-medex') !== -1 || rowText.indexOf('medex module') !== -1;
            if (!isMedexRow) { return; }

            var onclickText = (link.getAttribute('onclick') || '').toLowerCase();
            var isHelp = onclickText.indexOf('help_requested') !== -1;
            var isInstall = onclickText.indexOf("'install'") !== -1;
            if (!isHelp && !isInstall) { return; }

            var rowId = row.getAttribute('id') || '';
            var setupUrl = getMedexSetupUrl(rowId);
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
            if (isInstall) {
                return runMedexInstall(rowId, row);
            }
            if (typeof window.openModuleHelp === 'function') {
                window.openModuleHelp(setupUrl, 'MedEx Setup Help');
            } else {
                window.location.href = setupUrl;
            }
            return false;
        }, true);
    }

    function patchInstallerPage() {
        patchManage();
        installCaptureInterceptor();
        patchConfigure();
        patchMedexButtons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', patchInstallerPage);
    } else {
        patchInstallerPage();
    }
    window.setTimeout(patchInstallerPage, 300);
    window.setTimeout(patchInstallerPage, 1200);
})();
</script>
JS;
    });

    // Register menu whenever the module is installed (even when medex_enable=0)
    // so admins can always reach the Admin Dashboard / Subscriptions page.
    if ($isModuleInstalled) {
        medex_auto_reset_smarty_cache_for_version(Bootstrap::MODULE_VERSION);
        $eventDispatcher->addListener(MenuEvent::MENU_UPDATE, 'oe_module_medex_add_menu_item');
        $eventDispatcher->addListener(
            \OpenEMR\Events\Globals\GlobalsInitializedEvent::EVENT_HANDLE,
            static function ($event): void {
                \oe_module_medex_add_user_settings($event);
            }
        );
        error_log('[MedEx] Menu listener registered');
    }

    if ($isModuleEnabled) {
        error_log('[MedEx] Registering event listeners...');

        $medexConfigured = false;
        $medexActive = false;
        $api = null;
        $webroot = $GLOBALS['webroot'] ?? '';

        // Load MedExAPI class
        require_once(__DIR__ . '/src/MedExAPI.php');

        // Page rendering events - ONLY register if MedEx is enabled AND configured with valid subscription
        // Skip entirely for unauthenticated requests (login page, etc.) — no session means no
        // point registering listeners, and getEnabledServices() would fire a network login() call
        // that adds 1-5s of latency to every pre-auth page load.
        // Determine auth user robustly across native + Symfony session wrappers.
        $authUserId = $_SESSION['authUserID'] ?? ($_SESSION['authUser'] ?? null);
        if (empty($authUserId)) {
            try {
                $sessionWrapper = SessionWrapperFactory::getInstance()->getActiveSession();
                if ($sessionWrapper) {
                    $authUserId = $sessionWrapper->get('authUserID') ?: $sessionWrapper->get('authUser');
                }
            } catch (\Throwable $sessionEx) {
                error_log('[MedEx] authUserID fallback lookup failed: ' . $sessionEx->getMessage());
            }
        }

        // For calendar requests, do not skip entitlement checks just because authUserID is missing.
        // Some runtimes may not populate this key consistently, while the module credentials remain valid.
        $currentScriptForAuth = $_SERVER['SCRIPT_NAME'] ?? '';
        $isCalendarRequestForAuth = strpos($currentScriptForAuth, '/main/calendar/index.php') !== false;

        try {
            if (empty($authUserId) && !$isCalendarRequestForAuth) {
                // Not logged in — skip API check entirely. Menu is already registered above
                // via the pure-DB menu function which needs no network call.
                error_log('[MedEx] No auth session — skipping getEnabledServices() check');
            } elseif (class_exists('\OpenEMR\Modules\MedEx\MedExAPI')) {
                error_log('[MedEx] MedExAPI class exists, creating instance...');
                $api = new \OpenEMR\Modules\MedEx\MedExAPI();

                // isActive() checks for global enable, active module, AND valid API credentials/session
                $medexActive = $api->isActive();
                error_log('[MedEx] isActive() returned: ' . ($medexActive ? 'true' : 'false'));
                if ($medexActive) {
                    $services = $api->getEnabledServices();
                    error_log('[MedEx] getEnabledServices() returned: ' . print_r($services, true));
                    // At minimum, one service must be enabled to register listeners
                    $medexConfigured = !empty($services);
                    error_log('[MedEx] medexConfigured set to: ' . ($medexConfigured ? 'true' : 'false'));

                    // Calendar sync (push to MedEx) removed — FullCalendar now serves
                    // directly from OpenEMR DB. No appointment push needed.
                } else {
                    error_log('[MedEx] Module not active or not configured - listeners will NOT be registered');
                }
            } else {
                error_log('[MedEx] MedExAPI class does NOT exist');
            }
        } catch (\Throwable $e) {
            error_log('[MedEx] Error checking MedEx subscription: ' . $e->getMessage());
            $medexConfigured = false;
        }

        // Compute live calendar entitlement once for this request.
        // Do not use DB status fallbacks here — stale DB cache can expose paid calendar UX
        // after disconnect/reset, which is misleading and blocks the native calendar flow.
        $hasCalendarEntitlementLive = false;
        if ($medexActive && $api instanceof \OpenEMR\Modules\MedEx\MedExAPI) {
            try {
                $hasCalendarEntitlementLive = $api->hasServiceEntitlement('calendar_full');
            } catch (\Throwable $e) {
                error_log('[MedEx Calendar] Live entitlement check failed: ' . $e->getMessage());
                $hasCalendarEntitlementLive = false;
            }
        }

        $currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        error_log('[MedEx] Current script: ' . $currentScript . ', URI: ' . $requestUri);

        // Check both SCRIPT_NAME and REQUEST_URI for calendar access
        // BUT exclude if already on MedEx calendar pages to prevent redirect loop
        // ALSO exclude if user has explicitly chosen OpenEMR calendar (stored in session)
        $isMedExCalendar = strpos($requestUri, '/oe-module-medex/public/calendar/') !== false;
        $isInWrapper = (isset($_GET['medex_wrapper']) && $_GET['medex_wrapper'] == '1') ||
                       (isset($_POST['medex_wrapper']) && $_POST['medex_wrapper'] == '1') ||
                       strpos($requestUri, 'medex_wrapper=1') !== false;
        $isOpenEMRCalendar = strpos($currentScript, '/main/calendar/index.php') !== false ||
                             strpos($requestUri, '/main/calendar/index.php') !== false ||
                             strpos($requestUri, 'module=PostCalendar') !== false;

        // Allow direct preference via GET param — bootstrap sets session inline, no
        // separate HTTP round-trip via set_calendar_preference.php required.
        //
        // IMPORTANT: globals.php opens the session with read_and_close=true, so any direct
        // $_SESSION assignment here is in-memory only and is LOST when calendar/index.php
        // calls SessionUtil::setSession() (which re-opens the session from disk). We must
        // use SessionUtil::setSession() to atomically write to disk before that happens.
        if (isset($_GET['medex_prefer'])) {
            if ($_GET['medex_prefer'] === 'openemr') {
                try {
                    \OpenEMR\Common\Session\SessionUtil::setSession('medex_use_openemr_calendar', true);
                } catch (\Throwable $e) {
                    // Fallback: direct assignment (works for current request even if not persisted)
                    $_SESSION['medex_use_openemr_calendar'] = true;
                }
                error_log('[MedEx] medex_prefer=openemr GET param — set session flag (committed to disk)');
            } elseif ($_GET['medex_prefer'] === 'medex') {
                try {
                    \OpenEMR\Common\Session\SessionUtil::unsetSession('medex_use_openemr_calendar');
                } catch (\Throwable $e) {
                    $_SESSION['medex_use_openemr_calendar'] = false;
                }
                error_log('[MedEx] medex_prefer=medex GET param — cleared session flag (committed to disk)');
            }
        }

        // Check if user has chosen to use OpenEMR calendar (stored in session)
        $userChoseOpenEMR = !empty($_SESSION['medex_use_openemr_calendar']);

        error_log('[MedEx] Calendar check - isOpenEMR: ' . ($isOpenEMRCalendar ? 'yes' : 'no') .
                  ', isMedEx: ' . ($isMedExCalendar ? 'yes' : 'no') .
                  ', isInWrapper: ' . ($isInWrapper ? 'yes' : 'no') .
                  ', userChoseOpenEMR: ' . ($userChoseOpenEMR ? 'yes' : 'no'));

        if ($isOpenEMRCalendar && !$isMedExCalendar && !$isInWrapper && !$userChoseOpenEMR) {
            // Calendar redirect must happen BEFORE any output, so check it here
            if (true) {
                error_log('[MedEx Calendar] Calendar page detected, checking for subscription...');
                // Use $enabledServices from database ONLY - do NOT call API to avoid stale cache
                // 'calendar_full' appears as a key in the enabled services array.
                try {
                    $hasCalendarSubscription = $hasCalendarEntitlementLive;
                    error_log('[MedEx Calendar] Has calendar_full subscription: ' . ($hasCalendarSubscription ? 'YES' : 'NO'));

                        if ($medexActive && $hasCalendarSubscription) {
                            // Require explicit per-user opt-in for MedEx Full Calendar.
                            // This prevents unexpected paid-calendar injection on login.
                            $userOptedIn = false;
                            $userId = $authUserId;
                            if ($userId) {
                                try {
                                    $userPrefRows = sqlStatement(
                                        "SELECT setting_label, setting_value
                                           FROM user_settings
                                          WHERE setting_user = ?
                                            AND (setting_label = 'global:medex_use_full_calendar' OR setting_label = 'medex_preferences')",
                                        [$userId]
                                    );
                                    $nativePref = null;
                                    $legacyPref = null;
                                    while ($row = sqlFetchArray($userPrefRows)) {
                                        if (($row['setting_label'] ?? '') === 'global:medex_use_full_calendar') {
                                            $nativePref = strtolower(trim((string)($row['setting_value'] ?? '')));
                                        } elseif (($row['setting_label'] ?? '') === 'medex_preferences') {
                                            $decoded = json_decode((string)($row['setting_value'] ?? ''), true);
                                            if (is_array($decoded) && array_key_exists('use_full_calendar', $decoded)) {
                                                $legacyPref = $decoded['use_full_calendar'];
                                            }
                                        }
                                    }

                                    if ($nativePref !== null) {
                                        $userOptedIn = in_array($nativePref, ['1', 'true', 'yes', 'on'], true);
                                    } elseif ($legacyPref !== null) {
                                        if (is_bool($legacyPref)) {
                                            $userOptedIn = $legacyPref;
                                        } elseif (is_int($legacyPref) || is_float($legacyPref)) {
                                            $userOptedIn = ((int)$legacyPref === 1);
                                        } elseif (is_string($legacyPref)) {
                                            $userOptedIn = in_array(strtolower(trim($legacyPref)), ['1', 'true', 'yes', 'on'], true);
                                        } else {
                                            $userOptedIn = !empty($legacyPref);
                                        }
                                    } else {
                                        // Default enabled when no explicit user setting exists.
                                        $userOptedIn = true;
                                    }

                                    if (!$userOptedIn) {
                                        error_log('[MedEx Calendar] User ' . $userId . ' has not opted in to Full Calendar');
                                    }
                                } catch (\Exception $e) {
                                    error_log('[MedEx Calendar] Error checking user preferences: ' . $e->getMessage());
                                }
                            }

                            // Note: Provider/facility authorization is enforced in calendar/index.php via MedEx API
                            if ($userOptedIn) {
                                // Redirect to MedEx calendar
                                $siteId = $_SESSION['site_id'] ?? 'default';
                                $redirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php?site=' . urlencode($siteId);

                                // Preserve OpenEMR calendar parameters
                                $preserveParams = [];

                                // Preserve date (jumpdate parameter from OpenEMR in YYYY-MM-DD format)
                                if (!empty($_GET['jumpdate'])) {
                                    $preserveParams['date'] = $_GET['jumpdate'];
                                } elseif (!empty($_GET['Date'])) {
                                    // Fallback to Date parameter if present (convert Ymd to Y-m-d)
                                    $date = $_GET['Date'];
                                    if (strlen($date) === 8 && ctype_digit($date)) {
                                        $preserveParams['date'] = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
                                    }
                                }

                                // Preserve view type (viewtype parameter from OpenEMR)
                                if (!empty($_GET['viewtype']) || !empty($_SESSION['viewtype'])) {
                                    $viewtype = $_GET['viewtype'] ?? $_SESSION['viewtype'] ?? 'week';
                                    $preserveParams['view'] = $viewtype;
                                }

                                // Preserve selected providers (pc_username[] array from OpenEMR)
                                $providers = [];
                                if (!empty($_GET['pc_username'])) {
                                    // OpenEMR uses pc_username[] array for multiple providers
                                    $providers = is_array($_GET['pc_username']) ? $_GET['pc_username'] : [$_GET['pc_username']];
                                } elseif (!empty($_SESSION['pc_username'])) {
                                    $providers = is_array($_SESSION['pc_username']) ? $_SESSION['pc_username'] : [$_SESSION['pc_username']];
                                }
                                if (!empty($providers)) {
                                    // FullCalendar expects comma-separated provider IDs
                                    $preserveParams['providers'] = implode(',', array_filter($providers));
                                }

                                // Preserve selected facility (pc_facility from OpenEMR)
                                if (!empty($_GET['pc_facility']) || !empty($_SESSION['pc_facility'])) {
                                    $facility = $_GET['pc_facility'] ?? $_SESSION['pc_facility'] ?? '';
                                    if ($facility) {
                                        $preserveParams['facility'] = $facility;
                                    }
                                }

                                // Add preserved parameters to URL
                                if (!empty($preserveParams)) {
                                    $redirectUrl .= '&' . http_build_query($preserveParams);
                                }

                                error_log('[MedEx Calendar] Redirecting to FullCalendar with params: ' . json_encode($preserveParams));
                                header('Location: ' . $redirectUrl);
                                exit;
                            }
                        }
                } catch (\Exception $e) {
                    error_log('[MedEx Calendar] Error checking calendar subscription: ' . $e->getMessage());
                }
            }
        }

        // If user chose OpenEMR calendar, inject view selector buttons into #bottomLeft.
        // Only when MedEx is active AND calendar_full is currently entitled.
        if ($userChoseOpenEMR && $isOpenEMRCalendar && $medexActive && $hasCalendarEntitlementLive) {
            error_log('[MedEx] User is on OpenEMR calendar with preference set - will inject view selector');
            register_shutdown_function(function () use ($webroot) {
                $output = ob_get_clean();

                // Inject view selector script before </body>
                $injection = <<<HTML
<script>
(function() {
    console.log('✓ MedEx: Injecting view selector into OpenEMR calendar');

    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('bottomLeft');
        if (!sidebar) {
            console.warn('✗ MedEx: Could not find sidebar');
            return;
        }

        const viewSelectorHTML = `
            <div id="medex-view-selector" style="background: white; border-radius: 5px; padding: 15px; margin-bottom: 15px;">
                <div style="font-size: 10px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Calendar View</div>
                <div style="display: flex; flex-direction: column; gap: 1px; border: 1px solid #ddd; border-radius: 3px; overflow: hidden;">
                    <button type="button" onclick="switchToMedExCalendar()" style="padding: 8px; font-size: 11px; border: none; cursor: pointer; text-align: left; background: white; color: #333; border-bottom: 1px solid #ddd;">Full Calendar</button>
                    <button type="button" style="padding: 8px; font-size: 11px; border: none; cursor: default; text-align: left; background: #007bff; color: white; font-weight: 500;">OpenEMR Calendar</button>
                </div>
            </div>
        `;

        sidebar.insertAdjacentHTML('afterbegin', viewSelectorHTML);
        console.log('✓ MedEx: View selector injected');
    });

    function switchToMedExCalendar() {
        // Get current OpenEMR calendar state from URL/session
        const urlParams = new URLSearchParams(window.location.search);
        const currentDate = urlParams.get('jumpdate') || urlParams.get('Date') || '';
        const currentViewType = urlParams.get('viewtype') || '';
        const currentFacility = urlParams.get('pc_facility') || '';

        // OpenEMR uses pc_username[] array notation for multiple providers
        const currentProviders = urlParams.getAll('pc_username[]');

        // Build URL with preserved parameters
        let url = '$webroot/interface/main/calendar/index.php?medex_prefer=medex';

        if (currentDate) {
            // If Date is in Ymd format (8 digits), convert to Y-m-d
            let dateToPass = currentDate;
            if (currentDate.length === 8 && /^\d{8}$/.test(currentDate)) {
                dateToPass = currentDate.substring(0,4) + '-' + currentDate.substring(4,6) + '-' + currentDate.substring(6,8);
            }
            url += '&date=' + encodeURIComponent(dateToPass);
        }
        if (currentViewType) {
            url += '&view=' + encodeURIComponent(currentViewType);
        }
        if (currentProviders.length > 0) {
            // Pass providers as comma-separated for FullCalendar
            url += '&providers=' + encodeURIComponent(currentProviders.join(','));
        }
        if (currentFacility) {
            url += '&facility=' + encodeURIComponent(currentFacility);
        }

        console.log('[MedEx] Switching to Full Calendar with params:', {currentDate, currentViewType, currentProviders, currentFacility});

        // Restore session first so OpenEMR's session token remains valid across navigation.
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
        window.location.href = url;
    }

    window.switchToMedExCalendar = switchToMedExCalendar;
})();
</script>
HTML;

                $output = str_replace('</body>', $injection . '</body>', $output);
                echo $output;
            });
            ob_start();
        } elseif ($isOpenEMRCalendar && (!$medexActive || !$hasCalendarEntitlementLive)) {
            // Clear stale preference when account is not active / not entitled.
            try {
                \OpenEMR\Common\Session\SessionUtil::unsetSession('medex_use_openemr_calendar');
            } catch (\Throwable $e) {
                unset($_SESSION['medex_use_openemr_calendar']);
            }
        }

        // Register shutdown function for script injection (only if configured)
        if ($medexConfigured) {
            // Register shutdown function to inject scripts at page end (for non-redirect pages)
            register_shutdown_function(function () use ($currentScript) {
                // Double check module status during shutdown to be absolutely safe
                try {
                    if (function_exists('sqlQuery')) {
                        $moduleStatus = sqlQuery("SELECT mod_active FROM modules WHERE mod_directory = 'oe-module-medex' LIMIT 1");
                        if (!$moduleStatus || $moduleStatus['mod_active'] !== '1') {
                            return;
                        }
                    }
                } catch (\Throwable $t) {
                    return;
                }

                // Check which page we're on
                if (strpos($currentScript, 'patient_tracker.php') !== false) {
                    // Inject Patient Tracker scripts
                    if (class_exists(__NAMESPACE__ . '\\Listeners\\PatientTrackerInjectionListener')) {
                        $listener = new Listeners\PatientTrackerInjectionListener();
                        $listener->injectScripts();
                    }
                } elseif (strpos($currentScript, 'messages.php') !== false) {
                    // Only inject on the main messages page, not on ?go= sub-routes
                    // (SMS_bot, Recalls, addRecall, etc. are standalone pages that exit early)
                    $go = $_REQUEST['go'] ?? '';
                    if ($go === '' && class_exists(__NAMESPACE__ . '\\Listeners\\MessagesPageListener')) {
                        $listener = new Listeners\MessagesPageListener();
                        $listener->injectScripts();
                    }
                } elseif (strpos($currentScript, '/main/calendar/index.php') !== false) {
                    // The view selector (top-left #bottomLeft injection) is handled by the
                    // ob_start shutdown registered above ($userChoseOpenEMR && $isOpenEMRCalendar).
                    // Do NOT echo anything here — a plain echo in a shutdown runs after ob_get_clean()
                    // has already flushed the buffer, causing HTML to appear after </html>.
                }
            });
            error_log('[MedEx] Module scripts will inject via shutdown function - module is enabled and configured');
        }

        // Check for critical updates (for admins only, once per session, only when configured/signed-up)
        if (\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')
            && empty($_SESSION['medex_critical_update_checked'])
            && !empty($api) && $api->isConfigured()
        ) {
            $_SESSION['medex_critical_update_checked'] = true;
            try {
                require_once(__DIR__ . '/src/UpdateManager.php');
                $criticalUpdate = \OpenEMR\Modules\MedEx\UpdateManager::checkCriticalUpdate();
                if ($criticalUpdate) {
                    // Inject critical update notification in shutdown
                    register_shutdown_function(function () use ($criticalUpdate) {
                        echo '<script>
                        (function() {
                            if (typeof jQuery !== "undefined" && !document.getElementById("medex-critical-update-modal")) {
                                var modal = `
                                    <div id="medex-critical-update-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center;">
                                        <div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                            <h2 style="margin: 0 0 15px 0; color: #dc3545;"><i class="fa fa-exclamation-circle"></i> Critical MedEx Update Required</h2>
                                            <p style="font-size: 16px; margin-bottom: 15px;">Version <strong>' . text($criticalUpdate['latest_version']) . '</strong> is available.</p>
                                            <div style="background: #fff3cd; padding: 12px; border-radius: 4px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
                                                ' . text($criticalUpdate['critical_message'] ?? 'This is a critical security update. Please install immediately.') . '
                                            </div>
                                            <div style="text-align: right;">
                                                <button onclick="document.getElementById(\'medex-critical-update-modal\').style.display=\'none\'" class="btn btn-secondary" style="margin-right: 10px;">Dismiss</button>
                                                <a href="' . $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/admin/backups.php" class="btn btn-danger">Install Update Now</a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                document.body.insertAdjacentHTML("beforeend", modal);
                            }
                        })();
                        </script>';
                    });
                }
            } catch (\Throwable $e) {
                error_log('[MedEx] Critical update check failed: ' . $e->getMessage());
            }
        }
    } else {
        error_log('[MedEx] Module is DISABLED or UNINSTALLED - skipping listener registration');
    }
}
} // end class_exists check for Bootstrap
} // end namespace OpenEMR\Modules\MedEx
namespace {
// Finalize
}
