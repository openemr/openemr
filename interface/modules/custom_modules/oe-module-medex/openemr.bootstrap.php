<?php
/**
 * Minimal MedEx bootstrap for OpenEMR.
 *
 * The base module only owns installation, onboarding, reconnect, settings, and
 * the admin dashboard shell. Service-specific menus, listeners, and settings are
 * loaded only from optional component manifests under components/*.
 */

namespace {

require_once __DIR__ . '/src/API/OEGlobalsBag_polyfill.php';
require_once __DIR__ . '/src/MedExConfig.php';
require_once __DIR__ . '/src/ComponentLoader.php';
require_once __DIR__ . '/src/MedExAPI.php';

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\MedEx\ComponentLoader;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\MedExConfig;
use OpenEMR\Services\Globals\GlobalSetting;

if (!isset($GLOBALS['medex_base_url'])) {
    $GLOBALS['medex_base_url'] = MedExConfig::DEFAULT_BASE_URL;
}

/**
 * @return array<string,mixed>
 */
function medexBootstrapState(): array
{
    $state = [
        'module_installed' => false,
        'module_enabled' => false,
        'has_credentials' => false,
        'has_live_session_token' => false,
        'enabled_services' => [],
        'site_id' => (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default')),
        'webroot' => (string)($GLOBALS['webroot'] ?? ''),
    ];

    try {
        $demoEntitledServices = [];
        $moduleRow = sqlQuery(
            "SELECT mod_active, mod_ui_active
               FROM modules
              WHERE mod_directory = 'oe-module-medex'
              ORDER BY mod_id DESC
              LIMIT 1"
        );
        $state['module_installed'] = !empty($moduleRow) && (
            (int)($moduleRow['mod_active'] ?? 0) === 1 ||
            (int)($moduleRow['mod_ui_active'] ?? 0) === 1
        );

        $globalRow = sqlQuery(
            "SELECT gl_value
               FROM globals
              WHERE gl_name = 'medex_enable'
              LIMIT 1"
        );
        $state['module_enabled'] = ((string)($globalRow['gl_value'] ?? '0') === '1');

        $columns = [];
        $columnResult = sqlStatement('SHOW COLUMNS FROM medex_prefs');
        while ($columnRow = sqlFetchArray($columnResult)) {
            if (!empty($columnRow['Field'])) {
                $columns[(string)$columnRow['Field']] = true;
            }
        }

        $selectFields = [
            'status',
            'ME_username',
            'ME_api_key',
            'MedEx_id',
            !empty($columns['session_token']) ? 'session_token' : 'NULL AS session_token',
            !empty($columns['session_token_expiry']) ? 'session_token_expiry' : 'NULL AS session_token_expiry',
        ];
        $prefs = sqlQuery(
            'SELECT ' . implode(', ', $selectFields) . '
               FROM medex_prefs
              ORDER BY MedEx_lastupdated DESC
              LIMIT 1'
        );

        $state['has_credentials'] = !empty($prefs['ME_username'])
            && !empty($prefs['ME_api_key'])
            && !empty($prefs['MedEx_id']);

        $expiryTs = !empty($prefs['session_token_expiry']) ? strtotime((string)$prefs['session_token_expiry']) : 0;
        $state['has_live_session_token'] = !empty($prefs['session_token']) && $expiryTs > time();

        if (!empty($prefs['status'])) {
            $status = json_decode((string)$prefs['status'], true);
            if (is_array($status) && !empty($status['enabled_services']) && is_array($status['enabled_services'])) {
                $state['enabled_services'] = $status['enabled_services'];
            }
            if (is_array($status)) {
                $pricingCache = is_array($status['pricing_cache'] ?? null) ? $status['pricing_cache'] : [];
                $pricingTier = is_array($pricingCache['pricing_tier'] ?? null) ? $pricingCache['pricing_tier'] : [];
                $customerGroupId = (int)($pricingTier['customer_group_id'] ?? ($pricingCache['customer_group_id'] ?? 0));
                if (in_array($customerGroupId, [3, 7], true) && !empty($pricingCache['services']) && is_array($pricingCache['services'])) {
                    foreach ($pricingCache['services'] as $serviceKey => $serviceMeta) {
                        if (!is_array($serviceMeta) || empty($serviceMeta['available'])) {
                            continue;
                        }
                        $normalized = strtolower(str_replace([' ', '-'], '_', trim((string)$serviceKey)));
                        if ($normalized === 'calendar_service' || $normalized === 'calendar_services') {
                            $normalized = 'calendar_ai';
                        } elseif ($normalized === 'fullcalendar') {
                            $normalized = 'calendar_full';
                        }
                        if ($normalized !== '') {
                            $demoEntitledServices[$normalized] = true;
                        }
                    }
                }
            }
        }

        if ($state['has_credentials']) {
            $cacheKey = 'medex_bootstrap_enabled_services';
            $cached = $_SESSION[$cacheKey] ?? null;
            $cacheFresh = is_array($cached)
                && !empty($cached['ts'])
                && ((int)($cached['ts'] ?? 0) + 300) > time()
                && isset($cached['data'])
                && is_array($cached['data']);
            if ($cacheFresh) {
                $state['enabled_services'] = $cached['data'];
            } else {
                try {
                    $api = new MedExAPI();
                    $refreshedServices = $api->getEnabledServices(true);
                    if (is_array($refreshedServices) && !empty($refreshedServices)) {
                        $state['enabled_services'] = $refreshedServices;
                        $_SESSION[$cacheKey] = [
                            'ts' => time(),
                            'data' => $refreshedServices,
                        ];
                    }
                } catch (\Throwable $inner) {
                    error_log('[MedEx] Bootstrap enabled_services refresh failed: ' . $inner->getMessage());
                }
            }
        }

        if (!$state['has_credentials']) {
            $state['enabled_services'] = [];
        }
        if (!empty($demoEntitledServices)) {
            $state['enabled_services'] = array_values(array_unique(array_merge(
                array_map('strval', (array)$state['enabled_services']),
                array_keys($demoEntitledServices)
            )));
        }
    } catch (\Throwable $e) {
        error_log('[MedEx] Bootstrap state lookup failed: ' . $e->getMessage());
    }

    return $state;
}

function medexBuildModuleUrl(string $path, array $params = []): string
{
    $state = medexBootstrapState();
    $params = array_merge(['site' => $state['site_id']], $params);
    $query = http_build_query($params);
    return $state['webroot'] . $path . ($query !== '' ? ('?' . $query) : '');
}

/**
 * @param array<int|string,mixed> $enabledServices
 */
function medexHasEnabledService(array $enabledServices, array $candidates): bool
{
    foreach ($candidates as $candidate) {
        $candidate = strtolower(trim((string)$candidate));
        if ($candidate === '') {
            continue;
        }
        if (isset($enabledServices[$candidate]) && ($enabledServices[$candidate] === true || $enabledServices[$candidate] === 1)) {
            return true;
        }
        if (in_array($candidate, $enabledServices, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Load only the bootstrap files for currently enabled optional services.
 *
 * @param array<int|string,mixed> $enabledServices
 */
function medexLoadEnabledComponentBootstraps(array $enabledServices): void
{
    foreach (ComponentLoader::manifests() as $manifest) {
        $candidates = array_merge(
            [(string)($manifest['key'] ?? '')],
            array_values((array)($manifest['aliases'] ?? []))
        );
        $alwaysLoad = !empty($manifest['bootstrap_always']);
        if (!$alwaysLoad && !medexHasEnabledService($enabledServices, $candidates)) {
            continue;
        }

        foreach ((array)($manifest['bootstrap'] ?? []) as $relativePath) {
            $cleanPath = ltrim(str_replace('..', '', (string)$relativePath), '/');
            $baseDir = (string)($manifest['component_dir'] ?? '');
            if ($cleanPath === '' || $baseDir === '') {
                continue;
            }
            $path = $baseDir . '/' . $cleanPath;
            if (is_file($path)) {
                require_once $path;
            }
        }
    }
}

/**
 * @return array{0:string,1:string}
 */
function medexCalendarFeedsAclReq(array $state): array
{
    $default = ['patients', 'appt'];
    if (empty($state['module_enabled']) || empty($state['has_credentials']) || empty($state['has_live_session_token'])) {
        return $default;
    }

    $cacheKey = 'medex_calendar_feeds_acl_cache';
    $cached = $_SESSION[$cacheKey] ?? null;
    if (is_array($cached) && !empty($cached['ts']) && ((int)$cached['ts'] + 300) > time() && !empty($cached['data']) && is_array($cached['data'])) {
        return [
            (string)($cached['data'][0] ?? $default[0]),
            (string)($cached['data'][1] ?? $default[1]),
        ];
    }

    try {
        $api = new MedExAPI();
        $prefs = $api->getServicePreferences('calendar_export');
        $raw = strtolower(trim((string)($prefs['menu_acl'] ?? '')));
        $resolved = $default;
        if ($raw === 'admin|super') {
            $resolved = ['admin', 'super'];
        } elseif ($raw === 'patients|appt') {
            $resolved = ['patients', 'appt'];
        }
        $_SESSION[$cacheKey] = [
            'ts' => time(),
            'data' => $resolved,
        ];
        return $resolved;
    } catch (\Throwable $e) {
        error_log('[MedEx] Calendar Feeds ACL preference lookup failed: ' . $e->getMessage());
    }

    return $default;
}

function medexCalendarFeedsVisibleForCurrentUser(array $state, array $aclPair): bool
{
    if (\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) {
        return true;
    }

    return \OpenEMR\Common\Acl\AclMain::aclCheckCore((string)$aclPair[0], (string)$aclPair[1]);
}

/**
 * @param array<string,mixed> $definition
 */
function medexCreateMenuItem(array $definition, callable $buildUrl): ?\stdClass
{
    $label = trim((string)($definition['label'] ?? ''));
    $path = trim((string)($definition['path'] ?? ''));
    $menuId = trim((string)($definition['menu_id'] ?? ''));
    if ($label === '' || $path === '' || $menuId === '') {
        return null;
    }

    $item = new \stdClass();
    $item->requirement = 0;
    $item->target = (string)($definition['target'] ?? 'med');
    $item->menu_id = $menuId;
    $item->label = xlt($label);
    $item->url = $buildUrl($path, (array)($definition['params'] ?? []));
    $item->acl_req = array_values(array_filter(array_map(static function ($acl): string {
        return trim((string)$acl);
    }, (array)($definition['acl'] ?? ['admin', 'super']))));
    if (!empty($definition['icon'])) {
        $item->icon = (string)$definition['icon'];
    }
    $item->children = [];

    return $item;
}

if (!function_exists('oe_module_medex_add_menu_item')) {
    function oe_module_medex_add_menu_item(MenuEvent $event): MenuEvent
    {
        $state = medexBootstrapState();
        if (empty($state['module_installed'])) {
            return $event;
        }

        $menu = $event->getMenu();
        $buildUrl = static function (string $path, array $params = []) use ($state): string {
            $params = array_merge(['site' => $state['site_id']], $params);
            $query = http_build_query($params);
            return $state['webroot'] . $path . ($query !== '' ? ('?' . $query) : '');
        };

        $medexTopMenu = new \stdClass();
        $medexTopMenu->requirement = 0;
        $medexTopMenu->target = 'med';
        $medexTopMenu->menu_id = 'medimg';
        $medexTopMenu->label = xlt('MedEx');
        $medexTopMenu->children = [];
        $medexTopMenu->acl_req = [];

        $isAdmin = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super');
        if ($isAdmin) {
            $dashboardPath = $state['has_credentials']
                ? '/interface/modules/custom_modules/oe-module-medex/admin/index.php'
                : '/interface/modules/custom_modules/oe-module-medex/admin/splash.php';
            $dashboardItem = new \stdClass();
            $dashboardItem->requirement = 0;
            $dashboardItem->target = 'med';
            $dashboardItem->menu_id = 'medex_admin';
            $dashboardItem->label = xlt('Admin Dashboard');
            $dashboardItem->url = $buildUrl($dashboardPath, ['minimal' => 1]);
            $dashboardItem->acl_req = ['admin', 'super'];
            $dashboardItem->children = [];
            $medexTopMenu->children[] = $dashboardItem;
        }

        $canInjectServiceMenus = !empty($state['module_enabled'])
            && !empty($state['has_credentials'])
            && (!empty($state['has_live_session_token']) || !empty($state['enabled_services']));
        $componentMenus = $canInjectServiceMenus
            ? ComponentLoader::menusForEnabledServices((array)$state['enabled_services'])
            : [];

        $calendarBundleEnabled = !empty($state['module_enabled'])
            && !empty($state['has_credentials'])
            && medexHasEnabledService(
            (array)$state['enabled_services'],
            ['calendar_ai', 'calendar_services', 'calendar_export']
        );

        if ($calendarBundleEnabled) {
            $calendarFeedsAclReq = medexCalendarFeedsAclReq($state);
            $calendarFeedsAllowed = medexCalendarFeedsVisibleForCurrentUser($state, $calendarFeedsAclReq);
        } else {
            $calendarFeedsAllowed = false;
            $calendarFeedsAclReq = ['patients', 'appt'];
        }

        if ($calendarBundleEnabled && $calendarFeedsAllowed) {
            $calendarFeedsItem = new \stdClass();
            $calendarFeedsItem->requirement = 0;
            $calendarFeedsItem->target = 'med';
            $calendarFeedsItem->menu_id = 'medex_calendar_feeds';
            $calendarFeedsItem->label = xlt('Calendar Feeds');
            $calendarFeedsItem->url = $buildUrl('/interface/modules/custom_modules/oe-module-medex/public/calendar_feeds.php');
            $calendarFeedsItem->acl_req = [];
            $calendarFeedsItem->children = [];
            $medexTopMenu->children[] = $calendarFeedsItem;
        }

        foreach ($componentMenus as $definition) {
            if (!is_array($definition)) {
                continue;
            }
            $locations = (array)($definition['locations'] ?? [$definition['location'] ?? 'top']);
            $item = medexCreateMenuItem($definition, $buildUrl);
            if (!$item) {
                continue;
            }
            if (in_array('top', $locations, true)) {
                $medexTopMenu->children[] = $item;
            }
            if (in_array('misc', $locations, true)) {
                foreach ($menu as $topItem) {
                    if (($topItem->menu_id ?? '') !== 'misimg') {
                        continue;
                    }
                    if (!isset($topItem->children) || !is_array($topItem->children)) {
                        $topItem->children = [];
                    }
                    $miscItem = clone $item;
                    $miscItem->target = 'msc';
                    $topItem->children[] = $miscItem;
                    break;
                }
            }
        }

        if (!empty($medexTopMenu->children)) {
            $menu[] = $medexTopMenu;
        }

        $event->setMenu($menu);
        return $event;
    }
}

if (!function_exists('oe_module_medex_add_user_settings')) {
    function oe_module_medex_add_user_settings(GlobalsInitializedEvent $event): void
    {
        $userId = $_SESSION['authUserID'] ?? null;
        if (empty($userId)) {
            return;
        }

        foreach (ComponentLoader::userSettings() as $setting) {
            $key = trim((string)($setting['key'] ?? ''));
            $label = trim((string)($setting['label'] ?? ''));
            if ($key === '' || $label === '') {
                continue;
            }

            $section = trim((string)($setting['section'] ?? 'MedEx'));
            $defaultValue = (string)($setting['default'] ?? '');
            $dataType = $setting['data_type'] ?? GlobalSetting::DATA_TYPE_TEXT;
            $description = trim((string)($setting['description'] ?? ''));
            $persist = array_key_exists('persist', $setting) ? (bool)$setting['persist'] : true;

            $existing = sqlQuery(
                'SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = ?',
                [$userId, 'global:' . $key]
            );
            $value = array_key_exists('setting_value', (array)$existing)
                ? (string)($existing['setting_value'] ?? '')
                : $defaultValue;
            if (!array_key_exists('setting_value', (array)$existing)) {
                sqlStatement(
                    'INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?)',
                    [$userId, 'global:' . $key, $defaultValue]
                );
            }

            $event->getGlobalsService()->appendToSection(
                $section,
                $key,
                new GlobalSetting(xlt($label), $dataType, $value, xlt($description), $persist)
            );
        }
    }
}
}

namespace OpenEMR\Modules\MedEx {

use OpenEMR\Events\Core\ModuleManagerEvent;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;

require_once __DIR__ . '/src/MedExAPI.php';
require_once __DIR__ . '/src/ModuleManagerListener.php';
require_once __DIR__ . '/src/ComponentLoader.php';

$medexBootstrapState = \medexBootstrapState();
\medexLoadEnabledComponentBootstraps((array)($medexBootstrapState['enabled_services'] ?? []));

if (!class_exists('OpenEMR\\Modules\\MedEx\\Bootstrap')) {
    class Bootstrap
    {
        public const MODULE_NAME = 'MedEx Communication';
        public const MODULE_VERSION = '2.0.0';
        public const MINIMUM_OPENEMR_VERSION = '7.0.0';

        /**
         * @return array<string,mixed>
         */
        public static function getModuleInfo(): array
        {
            return [
                'name' => self::MODULE_NAME,
                'version' => self::MODULE_VERSION,
                'author' => 'MedEx Bank',
                'description' => 'Minimal MedEx bootstrap module with SaaS onboarding and optional service components.',
                'dependencies' => [],
                'acl' => [
                    'MedEx Admin' => 'admin',
                    'MedEx User' => 'write',
                    'MedEx View Only' => 'read',
                ],
            ];
        }
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
        $lastVersion = is_file($markerFile) ? trim((string)@file_get_contents($markerFile)) : '';
        if ($lastVersion === $moduleVersion) {
            return;
        }

        foreach ([$smartyDir . '/main', $smartyDir . '/modules'] as $dir) {
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
        }
    } catch (\Throwable $t) {
        error_log('[MedEx] Failed to register medex.api service: ' . $t->getMessage());
    }
}

if (isset($eventDispatcher) && $eventDispatcher instanceof \Symfony\Component\EventDispatcher\EventDispatcherInterface) {
    $isModuleInstalled = false;
    try {
        if (function_exists('sqlQuery')) {
            $moduleStatus = sqlQuery("SELECT mod_active, mod_ui_active FROM modules WHERE mod_directory = 'oe-module-medex' LIMIT 1");
            $isModuleInstalled = !empty($moduleStatus) && (
                (int)($moduleStatus['mod_active'] ?? 0) === 1 ||
                (int)($moduleStatus['mod_ui_active'] ?? 0) === 1
            );
        }
    } catch (\Throwable $t) {
        error_log('[MedEx] Failed to check module status: ' . $t->getMessage());
    }

    if (class_exists(ModuleManagerEvent::class)) {
        $moduleListener = new ModuleManagerListener();
        $eventDispatcher->addListener(ModuleManagerEvent::EVENT_INSTALL, [$moduleListener, 'onModuleInstall']);
        $eventDispatcher->addListener(ModuleManagerEvent::EVENT_ENABLE, [$moduleListener, 'onModuleEnable']);
        $eventDispatcher->addListener(ModuleManagerEvent::EVENT_DISABLE, [$moduleListener, 'onModuleDisable']);
        $eventDispatcher->addListener(ModuleManagerEvent::EVENT_UNINSTALL, [$moduleListener, 'onModuleUninstall']);
    }

    register_shutdown_function(function (): void {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/interface/modules/zend_modules/public/Installer') === false) {
            return;
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
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
            var parsed = new URL(normalized, window.location.origin);
            normalized = parsed.pathname + (parsed.search || '');
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
            + 'transition:width 1.4s ease;}'
            + '.medex-progress-status{display:flex;align-items:center;gap:10px;padding:14px 0 6px;color:#0f172a;font-size:15px;font-weight:600;}'
            + '.medex-progress-status::before{content:"";width:18px;height:18px;border-radius:999px;border:3px solid #93c5fd;'
            + 'border-top-color:#1d4ed8;animation:medex-spinner .95s linear infinite;flex:0 0 auto;}'
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
            + '    <p class="medex-progress-copy">MedEx is being installed, enabled, and sent directly into onboarding.</p>'
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
        var payload = { modId: rowId, modAction: action };
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
                return wait(900);
            })
            .then(function () {
                updateProgress('Enabling MedEx...', 'Finalizing module activation now.', '', 76);
                return postManageAction(rowId, 'enable');
            })
            .then(function () {
                updateProgress('Enabled', 'Module activation is complete. Preparing onboarding.', '', 92);
                return wait(1300);
            })
            .then(function () {
                updateProgress('Opening onboarding...', 'MedEx is ready. Redirecting into onboarding now.', 'done', 100);
                window.setTimeout(function () {
                    openInMedexTarget(getOnboardingUrl());
                }, 1800);
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
        if (typeof window.configure !== 'function' || window.__medex_configure_patched) {
            return;
        }
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
            return originalConfigure.apply(this, arguments);
        };
    }

    function patchMedexButtons() {
        var rows = Array.prototype.slice.call(document.querySelectorAll('tr[id]'));
        var row = rows.find(function (tr) {
            var text = (tr.textContent || '').toLowerCase();
            return text.indexOf('oe-module-medex') !== -1 || text.indexOf('medex module') !== -1;
        });
        if (!row) {
            return;
        }

        var rowId = row.getAttribute('id') || '';
        var setupUrl = getMedexSetupUrl(rowId);
        var helpLink = row.querySelector("a[onclick*=\"help_requested\"]");
        if (helpLink) {
            helpLink.onclick = function (event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
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
        if (installLink) {
            installLink.onclick = function (event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                return runMedexInstall(rowId, row);
            };
        }
    }

    function patchInstallerPage() {
        patchManage();
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

    if ($isModuleInstalled) {
        medex_auto_reset_smarty_cache_for_version(Bootstrap::MODULE_VERSION);
        $eventDispatcher->addListener(MenuEvent::MENU_UPDATE, '\\oe_module_medex_add_menu_item');
        $eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, static function ($event): void {
            \oe_module_medex_add_user_settings($event);
        });
    }
}
}
