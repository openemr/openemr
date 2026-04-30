<?php

/**
 * MedEx Full Calendar View
 * Local FullCalendar implementation with OpenEMR data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <https://medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Common\Session\SessionWrapperFactory;

// Check calendar access
if (!AclMain::aclCheckCore('patients', 'appt')) {
    die('Access denied. You do not have permission to access the calendar.');
}

// Verify user is authenticated (support both native $_SESSION and session wrapper backends)
$sessionWrapper = null;
try {
    $sessionWrapper = SessionWrapperFactory::getInstance()->getActiveSession();
} catch (\Throwable $e) {
    $sessionWrapper = null;
}

$authUserId = $_SESSION['authUserID'] ?? null;
if (empty($authUserId) && $sessionWrapper) {
    $authUserId = $sessionWrapper->get('authUserID') ?: $sessionWrapper->get('authUser');
}
if (empty($authUserId)) {
    die('Access denied. Please log in to OpenEMR.');
}

// Ensure calendar globals are loaded
if (!isset($GLOBALS['schedule_start']) || !isset($GLOBALS['schedule_end']) || !isset($GLOBALS['calendar_interval'])) {
    $result = sqlStatement("SELECT gl_name, gl_value FROM globals WHERE gl_name IN ('schedule_start', 'schedule_end', 'calendar_interval')");
    while ($row = sqlFetchArray($result)) {
        if (!isset($GLOBALS[$row['gl_name']])) {
            $GLOBALS[$row['gl_name']] = $row['gl_value'];
        }
    }
    // Set defaults if still not set
    $GLOBALS['schedule_start'] = $GLOBALS['schedule_start'] ?? 8;
    $GLOBALS['schedule_end'] = $GLOBALS['schedule_end'] ?? 17;
    $GLOBALS['calendar_interval'] = $GLOBALS['calendar_interval'] ?? 15;
}

// Load PostCalendar module settings
$calendarDefaultView = 'day'; // default
$calendarTimeIncrement = 5;   // default
$calendar24Hours = false;      // default
$calendarFirstDayOfWeek = 0;  // Sunday (OpenEMR default)

$moduleSettings = sqlStatement("SELECT pn_name, pn_value FROM openemr_module_vars WHERE pn_modname = 'PostCalendar' AND pn_name IN ('pcDefaultView', 'pcTimeIncrement', 'pcTime24Hours', 'pcFirstDayOfWeek')");
while ($setting = sqlFetchArray($moduleSettings)) {
    if ($setting['pn_name'] === 'pcDefaultView') {
        $calendarDefaultView = $setting['pn_value'];
    } elseif ($setting['pn_name'] === 'pcTimeIncrement') {
        $calendarTimeIncrement = (int)$setting['pn_value'];
    } elseif ($setting['pn_name'] === 'pcTime24Hours') {
        $calendar24Hours = ($setting['pn_value'] === '1' || $setting['pn_value'] === 1 || $setting['pn_value'] === true);
    } elseif ($setting['pn_name'] === 'pcFirstDayOfWeek') {
        $calendarFirstDayOfWeek = (int)$setting['pn_value'];
    }
}

error_log('[MedEx Calendar] Loaded settings - defaultView: ' . $calendarDefaultView . ', 24Hours: ' . ($calendar24Hours ? 'true' : 'false') . ', firstDayOfWeek: ' . $calendarFirstDayOfWeek);

// Map OpenEMR view types to FullCalendar view types
$viewMapping = [
    'day' => 'timeGridDay',
    'week' => 'timeGridWeek',
    'month' => 'dayGridMonth',
    'year' => 'dayGridMonth' // FullCalendar doesn't have year view, use month
];
$fullCalendarDefaultView = $viewMapping[$calendarDefaultView] ?? 'timeGridWeek';

// Load MedEx API
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Check if configured
if (!$api->isConfigured()) {
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . ($GLOBALS['webroot'] ?? '') . '/interface/main/calendar/index.php');
    exit;
}

if (!$api->hasServiceEntitlement('calendar_full')) {
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . ($GLOBALS['webroot'] ?? '') . '/interface/main/calendar/index.php');
    exit;
}

// Authenticate with MedEx API (validates IP, etc)
// On network failure: redirect to native OpenEMR calendar rather than showing a crippled view.
$_medexAuthError = null;
try {
    $loginResult = $api->login();
    if (!empty($loginResult['stale_fallback'])) {
        // DNS/network is down — only a stale cached token is available.
        // Redirect to native OpenEMR calendar so users can still work.
        error_log('[MedEx Calendar] Network down (stale token fallback), redirecting to native calendar');
        $_SESSION['medex_calendar_skip'] = true;
        header('Location: ' . ($GLOBALS['webroot'] ?? '') . '/interface/main/calendar/index.php');
        exit;
    }
} catch (\Exception $e) {
    // Also redirect on hard failure (e.g. no cached token at all).
    error_log('[MedEx Calendar] Login failed, redirecting to native calendar: ' . $e->getMessage());
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . ($GLOBALS['webroot'] ?? '') . '/interface/main/calendar/index.php');
    exit;
}

// ============================================================
// OpenEMR-native provider/facility access control
// Respects $GLOBALS['restrict_user_facility'], users_facility
// table, and authorized/calendar flags — same logic as the
// built-in PostCalendar.
// ============================================================

require_once($GLOBALS['incdir'] . "/../library/calendar.inc.php");
require_once($GLOBALS['incdir'] . "/../library/patient.inc.php");

$userId = $authUserId;
$userAuthorized = $_SESSION['userauthorized'] ?? ($sessionWrapper ? (int)($sessionWrapper->get('userauthorized') ?? 0) : 0);

// Get user info
$userInfo = sqlQuery("SELECT id, username, facility_id FROM users WHERE id = ?", [$userId]);
$userProviderId = $userInfo['id'] ?? null;
$userProviderUsername = $userInfo['username'] ?? null;
$userFacilityId = $userInfo['facility_id'] ?? null;

// Determine which facilities this user can access (OpenEMR native logic)
// - Authorized providers OR restrict_user_facility OFF → all facilities
// - Non-authorized staff with restrict_user_facility ON → only assigned facilities
$userFacilities = getUserFacilities($userId);

// Determine which providers this user can see
// Uses getProviderInfo() which respects facility + authorized=1 + calendar=1
// If no facility restriction, show all calendar-enabled providers
$visibleProviders = [];
if (!$userAuthorized && $GLOBALS['restrict_user_facility']) {
    // Staff user with facility restriction — only show providers at their facilities
    foreach ($userFacilities as $fac) {
        $facProviders = getProviderInfo('%', true, $fac['id']);
        if ($facProviders) {
            foreach ($facProviders as $p) {
                $visibleProviders[$p['id']] = $p; // dedup by id
            }
        }
    }
    $visibleProviders = array_values($visibleProviders);
} else {
    // Provider or unrestricted user — show all calendar-enabled providers
    $visibleProviders = getProviderInfo('%', true) ?? [];
}

// Get MedEx subscription status (for UI elements only, not access control)
$subscriptions = $api->getSubscriptions();
$calendarSub = $subscriptions['calendar_full'] ?? null;
$calendarFullServicePrefs = [];
try {
    $calendarFullServicePrefs = $api->getServicePreferences('calendar_full');
} catch (\Throwable $e) {
    error_log('[MedEx Calendar] Unable to load calendar_full service preferences: ' . $e->getMessage());
}

$displayScheduleStart = (int)($GLOBALS['schedule_start'] ?? 8);
$displayScheduleEnd = (int)($GLOBALS['schedule_end'] ?? 17);
if ($displayScheduleEnd <= $displayScheduleStart) {
    $displayScheduleEnd = min(23, $displayScheduleStart + 1);
}

// Load MedEx user preferences (native Edit User Settings keys first, legacy JSON fallback).
$medexPrefs = [];
$userSettingRows = sqlStatement(
    "SELECT setting_label, setting_value
       FROM user_settings
      WHERE setting_user = ?
        AND (setting_label = 'medex_preferences' OR setting_label LIKE 'global:medex_%')",
    [$userId]
);
$nativePrefs = [];
while ($prefRow = sqlFetchArray($userSettingRows)) {
    $label = (string)($prefRow['setting_label'] ?? '');
    $value = (string)($prefRow['setting_value'] ?? '');
    if ($label === 'medex_preferences') {
        $decodedPrefs = json_decode($value, true);
        if (is_array($decodedPrefs)) {
            $medexPrefs = $decodedPrefs;
        }
        continue;
    }
    if (str_starts_with($label, 'global:medex_')) {
        $nativePrefs[$label] = $value;
    }
}
$allowedThemes = ['classic', 'compact', 'high_contrast', 'ocean', 'sunrise', 'forest', 'slate', 'openemr', 'inherit'];
$prefTheme = trim((string)($nativePrefs['global:medex_calendar_theme'] ?? ''));
if ($prefTheme === '') {
    if (!empty($medexPrefs['inherit_openemr_theme'])) {
        $prefTheme = 'openemr';
    } elseif (!empty($medexPrefs['calendar_theme'])) {
        $prefTheme = (string)$medexPrefs['calendar_theme'];
    } else {
        $prefTheme = 'openemr';
    }
}
if ($prefTheme === 'inherit') {
    $prefTheme = 'openemr';
}
if (!in_array($prefTheme, $allowedThemes, true)) {
    $prefTheme = 'openemr';
}
$inheritOpenEmrTheme = ($prefTheme === 'openemr');
$prefDefaultProviders = isset($medexPrefs['default_provider_usernames']) && is_array($medexPrefs['default_provider_usernames'])
    ? array_values(array_unique(array_map('strval', $medexPrefs['default_provider_usernames'])))
    : [];
$prefDefaultFacilities = isset($medexPrefs['default_facility_ids']) && is_array($medexPrefs['default_facility_ids'])
    ? array_values(array_unique(array_map('strval', $medexPrefs['default_facility_ids'])))
    : [];
$visibleProviderUsernames = array_map(static function ($p) {
    return (string)($p['username'] ?? '');
}, $visibleProviders);
$visibleFacilityIds = array_map(static function ($f) {
    return (string)($f['id'] ?? '');
}, $userFacilities);
$prefDefaultProviders = array_values(array_intersect($prefDefaultProviders, $visibleProviderUsernames));
$prefDefaultFacilities = array_values(array_intersect($prefDefaultFacilities, $visibleFacilityIds));
$usePrefProviders = !empty($prefDefaultProviders);
$usePrefFacilities = !empty($prefDefaultFacilities);

// Allow explicit hand-off back to native OpenEMR calendar.
$openEmrCalendarCompatible = true;
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Full Calendar'); ?></title>

    <!-- FullCalendar v6 (local from writable documents directory) -->
    <script src='<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/assets/fullcalendar.php'></script>
    <script>
        console.log('FullCalendar script tag loaded, checking if library exists...');
        console.log('typeof FullCalendar:', typeof FullCalendar);
        // Pass webroot and schedule times to calendar.js
        window.webroot = '<?php echo $GLOBALS['webroot']; ?>';
        window.scheduleStart = '<?php echo (int)$displayScheduleStart; ?>';
        window.scheduleEnd = '<?php echo (int)$displayScheduleEnd; ?>';
        window.calendarInterval = '<?php echo $GLOBALS['calendar_interval'] ?? '15'; ?>';
        window.calendarDefaultView = '<?php echo $fullCalendarDefaultView; ?>';
        window.calendarTimeIncrement = '<?php echo $calendarTimeIncrement; ?>';
        window.calendar24Hours = <?php echo $calendar24Hours ? 'true' : 'false'; ?>;
        window.calendarFirstDayOfWeek = <?php echo (int)$calendarFirstDayOfWeek; ?>;

        // Pass facility data for multi-facility view (from OpenEMR ACL)
        window.authorizedFacilities = <?php
            $facilityData = [];
            foreach ($userFacilities as $fac) {
                $facilityData[] = [
                    'id' => $fac['id'],
                    'title' => $fac['name']
                ];
            }
            echo json_encode($facilityData);
        ?>;

        console.log('Loaded from PHP globals - scheduleStart:', window.scheduleStart, 'scheduleEnd:', window.scheduleEnd, 'calendarInterval:', window.calendarInterval);
        console.log('Calendar settings - defaultView:', window.calendarDefaultView, 'timeIncrement:', window.calendarTimeIncrement, '24Hours:', window.calendar24Hours, 'firstDayOfWeek:', window.calendarFirstDayOfWeek);
        console.log('Authorized facilities:', window.authorizedFacilities);
        window.medexUserPrefs = <?php echo json_encode([
            'theme' => $prefTheme,
            'inheritOpenEmrTheme' => $inheritOpenEmrTheme,
            'defaultProviders' => $prefDefaultProviders,
            'defaultFacilities' => $prefDefaultFacilities
        ]); ?>;
        window.medexOpenEmrCalendarCompatible = <?php echo $openEmrCalendarCompatible ? 'true' : 'false'; ?>;
    </script>

    <!-- Calendar initialization -->
    <script src='<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/calendar/calendar.js' type="text/javascript"></script>

    <style>
        body {
            --medex-bg: #f8f9fa;
            --medex-text: #1f2933;
            --medex-panel-bg: #f8f9fa;
            --medex-panel-border: #dee2e6;
            --medex-accent: #0099cc;
            --medex-accent-contrast: #ffffff;
            --medex-card-bg: #ffffff;
            --medex-card-shadow: 0 1px 3px rgba(0,0,0,0.1);
            --medex-event-text: #111111;
            --medex-button-bg: #2c3e50;
            --medex-button-text: #ffffff;
            --medex-button-hover: #34495e;
            --medex-button-border: #1a252f;
            --medex-quick-bg: rgba(248, 249, 250, 0.95);
            --medex-quick-btn-bg: #e9edf2;
            --medex-quick-btn-text: #2f3a4a;
            --medex-quick-btn-border: #9aa4b1;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: var(--medex-bg);
            color: var(--medex-text);
        }
        body.medex-theme-compact {
            font-size: 11px;
        }
        body.medex-theme-high_contrast {
            --medex-bg: #111111;
            --medex-text: #f5f5f5;
            --medex-panel-bg: #1a1a1a;
            --medex-panel-border: #555555;
            --medex-accent: #2ea3ff;
            --medex-card-bg: #1e2732;
            --medex-card-shadow: 0 1px 3px rgba(0,0,0,0.45);
            --medex-event-text: #f5f5f5;
            --medex-button-bg: #1f1f1f;
            --medex-button-hover: #303030;
            --medex-button-border: #4a4a4a;
            --medex-quick-bg: rgba(26, 26, 26, 0.95);
            --medex-quick-btn-bg: #222b36;
            --medex-quick-btn-text: #f5f5f5;
            --medex-quick-btn-border: #4d5f73;
        }
        body.medex-theme-ocean {
            --medex-bg: #eef7fb;
            --medex-text: #15384f;
            --medex-panel-bg: #f5fbff;
            --medex-panel-border: #bfdceb;
            --medex-accent: #0077a6;
            --medex-card-bg: #ffffff;
            --medex-card-shadow: 0 1px 3px rgba(0,67,100,0.2);
            --medex-event-text: #0f3246;
            --medex-button-bg: #115a78;
            --medex-button-hover: #0d4b64;
            --medex-button-border: #0a3a4d;
            --medex-quick-bg: rgba(232, 245, 252, 0.95);
            --medex-quick-btn-bg: #d7ecf7;
            --medex-quick-btn-text: #18445c;
            --medex-quick-btn-border: #9ec5da;
        }
        body.medex-theme-sunrise {
            --medex-bg: #fff6ef;
            --medex-text: #4d2b1f;
            --medex-panel-bg: #fffaf5;
            --medex-panel-border: #f0d7c4;
            --medex-accent: #d86a1f;
            --medex-card-bg: #fffdfb;
            --medex-card-shadow: 0 1px 3px rgba(119,63,26,0.16);
            --medex-event-text: #4d2b1f;
            --medex-button-bg: #a34f1f;
            --medex-button-hover: #8f4419;
            --medex-button-border: #783713;
            --medex-quick-bg: rgba(255, 243, 232, 0.95);
            --medex-quick-btn-bg: #fbe3d1;
            --medex-quick-btn-text: #5b2f1a;
            --medex-quick-btn-border: #d9b496;
        }
        body.medex-theme-forest {
            --medex-bg: #f2f7f2;
            --medex-text: #1f3a28;
            --medex-panel-bg: #f8fcf8;
            --medex-panel-border: #c8ddcc;
            --medex-accent: #2a7b43;
            --medex-card-bg: #ffffff;
            --medex-card-shadow: 0 1px 3px rgba(30,70,41,0.16);
            --medex-event-text: #1f3a28;
            --medex-button-bg: #285f37;
            --medex-button-hover: #234f2f;
            --medex-button-border: #1a3d24;
            --medex-quick-bg: rgba(241, 248, 242, 0.95);
            --medex-quick-btn-bg: #dcebdc;
            --medex-quick-btn-text: #244d30;
            --medex-quick-btn-border: #a8c7b0;
        }
        body.medex-theme-slate {
            --medex-bg: #eef1f5;
            --medex-text: #223041;
            --medex-panel-bg: #f6f8fb;
            --medex-panel-border: #c9d1da;
            --medex-accent: #3b6ea8;
            --medex-card-bg: #ffffff;
            --medex-card-shadow: 0 1px 3px rgba(40,62,87,0.18);
            --medex-event-text: #203246;
            --medex-button-bg: #344a63;
            --medex-button-hover: #2d4055;
            --medex-button-border: #253445;
            --medex-quick-bg: rgba(238, 241, 245, 0.95);
            --medex-quick-btn-bg: #dde3ea;
            --medex-quick-btn-text: #2e4258;
            --medex-quick-btn-border: #aab5c1;
        }
        body.medex-theme-openemr {
            --medex-bg: #f6f7f9;
            --medex-text: #1f2933;
            --medex-panel-bg: #ffffff;
            --medex-panel-border: #d6dbe1;
            --medex-accent: #2d75b6;
            --medex-card-bg: #ffffff;
            --medex-card-shadow: 0 1px 3px rgba(41,67,94,0.14);
            --medex-event-text: #1f2933;
            --medex-button-bg: #315b82;
            --medex-button-hover: #264967;
            --medex-button-border: #1f3c56;
            --medex-quick-bg: rgba(246, 247, 249, 0.95);
            --medex-quick-btn-bg: #e6ebf0;
            --medex-quick-btn-text: #2c3e50;
            --medex-quick-btn-border: #a7b3bf;
        }

        /* Sidebar styling */
        #sidebar {
            width: 220px;
            min-width: 220px;
            background: var(--medex-panel-bg);
            border-right: 1px solid var(--medex-panel-border);
            padding: 58px 10px 10px 10px;
            overflow-y: auto;
            transition: width 0.3s ease, min-width 0.3s ease, padding 0.3s ease, border-right-width 0.3s ease;
            position: relative;
            z-index: 100;
        }
        #sidebar.hidden {
            width: 0;
            min-width: 0;
            padding: 0;
            border-right-width: 0;
            overflow: hidden;
        }
        #sidebar h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: bold;
            color: var(--medex-text);
        }
        .quick-actions {
            display: inline-flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 8px;
            background: transparent;
            padding: 4px;
            border-radius: 6px;
        }
        #left-controls {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 250;
        }
        body.sidebar-collapsed #left-controls {
            top: 16px;
            left: 16px;
        }
        #medex-status-toast {
            position: fixed;
            top: 64px;
            left: 50%;
            transform: translate(-50%, -6px);
            z-index: 1200;
            background: rgba(23, 77, 43, 0.96);
            color: #fff;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
        }
        #medex-status-toast.show {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        #medex-status-toast.error {
            background: rgba(146, 27, 49, 0.96);
        }
        .quick-btn {
            min-width: 32px;
            height: 32px;
            border: 1px solid var(--medex-quick-btn-border);
            background: var(--medex-quick-btn-bg);
            color: var(--medex-quick-btn-text);
            border-radius: 4px;
            font-size: 14px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .quick-btn:hover {
            filter: brightness(0.95);
        }
        .hamburger4 {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 3px;
            width: 16px;
            height: 16px;
        }
        .hamburger4 span {
            display: block;
            width: 14px;
            height: 2px;
            border-radius: 1px;
            background: currentColor;
        }
        #sidebar .filter-section {
            margin-bottom: 20px;
        }
        #sidebar .switch-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        #sidebar .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
            max-height: 200px;
            overflow-y: auto;
            padding: 5px;
            border: 1px solid var(--medex-panel-border);
            border-radius: 3px;
            background: #ffffff;
        }
        #sidebar .checkbox-group::-webkit-scrollbar {
            width: 8px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb {
            background: var(--medex-accent);
            border-radius: 3px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb:hover {
            filter: brightness(0.9);
        }
        #sidebar .checkbox-label {
            display: flex;
            align-items: center;
            font-size: 11px;
            cursor: pointer;
            padding: 5px 8px;
            border-radius: 3px;
            transition: background 0.2s;
            position: relative;
        }
        #sidebar .checkbox-label:hover {
            background: #e8f4f8;
        }
        #sidebar .checkbox-label input[type="checkbox"] {
            display: none;
        }
        #sidebar .checkbox-label::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border: 2px solid var(--medex-accent);
            border-radius: 3px;
            background: white;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        #sidebar .checkbox-label input[type="checkbox"]:checked + span::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 8px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        #sidebar .checkbox-label:has(input[type="checkbox"]:checked) {
            background: var(--medex-accent);
            color: var(--medex-accent-contrast);
        }
        #sidebar .checkbox-label:has(input[type="checkbox"]:checked)::before {
            background: var(--medex-accent);
            border-color: var(--medex-accent);
        }
        #sidebar .btn,
        #sidebar .switch-section .btn {
            width: 100%;
            padding: 8px;
            font-size: 11px;
            background: var(--medex-button-bg);
            color: var(--medex-button-text);
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        #sidebar .btn:hover,
        #sidebar .switch-section .btn:hover {
            background: var(--medex-button-hover);
        }

        /* View selector styling */
        .view-selector {
            display: flex;
            flex-direction: column;
            gap: 1px;
            border: 1px solid var(--medex-accent);
            border-radius: 3px;
            overflow: hidden;
            background: var(--medex-card-bg);
        }
        .view-selector .view-option {
            padding: 8px;
            font-size: 11px;
            border: none;
            text-align: left;
            transition: background 0.2s;
        }
        .view-selector .view-option.active {
            background: var(--medex-accent);
            color: var(--medex-accent-contrast);
            cursor: default;
            font-weight: 600;
            border-bottom: 1px solid var(--medex-accent);
        }
        .view-selector .view-option:not(.active) {
            background: var(--medex-card-bg);
            color: var(--medex-accent);
            cursor: pointer;
            border-top: 1px solid var(--medex-accent);
        }
        .view-selector .view-option:not(.active):hover {
            background: #e8f4f8 !important;
        }

        /* Truncate long names with ellipsis */
        #sidebar .checkbox-label {
            max-width: 100%;
        }
        #sidebar .checkbox-label span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
        }
        /* Main content area */
        #main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: auto;
            padding: 10px;
            background: var(--medex-bg);
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 24px;
            background: #ccc;
            border-radius: 12px;
            transition: background 0.3s;
        }
        .toggle-switch.active {
            background: #28a745;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: left 0.3s;
        }
        .toggle-switch.active::after {
            left: 28px;
        }
        #calendars-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-start;
        }
        .provider-calendar-wrapper {
            flex: 1 1 0;
            min-width: 300px;
            background: var(--medex-card-bg);
            border-radius: 5px;
            box-shadow: var(--medex-card-shadow);
        }
        .provider-calendar-wrapper h3 {
            margin: 0;
            padding: 10px 15px;
            background: var(--medex-accent);
            color: var(--medex-accent-contrast);
            border-radius: 5px 5px 0 0;
            font-size: 13px;
            font-weight: 600;
        }

        /* Make event text more readable */
        .fc-event {
            cursor: pointer;
        }
        .fc-event-title {
            color: inherit;
            font-weight: 500;
        }
        .fc-event-time {
            color: inherit;
        }
        .fc-timegrid-event.has-slot-type-color .fc-event-time {
            background: var(--medex-slot-type-color, transparent);
            border-radius: 3px;
            padding: 0 4px;
            margin-right: 6px;
            display: inline-block;
        }

        /* Style for availability blocks (In Office / Out of Office) */
        .fc-event.availability-block {
            opacity: 0.4;
            background-pattern: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,.1) 10px,
                rgba(255,255,255,.1) 20px
            );
        }

        /* Patient appointments should stand out */
        .fc-event.patient-appointment {
            opacity: 1;
            font-weight: bold;
            border: 2px solid rgba(0,0,0,0.2);
        }

        /* Override FullCalendar's default event styling for better contrast */
        .fc-event-main {
            color: inherit;
        }

        .fc-daygrid-event {
            white-space: normal !important;
        }

        .fc-daygrid-event-dot {
            display: none;
        }

        /* Compact calendar headers */
        .fc-toolbar-title {
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .fc-button {
            padding: 4px 10px !important;
            font-size: 12px !important;
        }

        .fc-col-header-cell {
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 8px 4px !important;
            color: var(--medex-text);
        }

        .fc-timegrid-slot-label {
            font-size: 11px !important;
        }
        .fc .fc-timegrid-axis,
        .fc .fc-timegrid-slot-label {
            background: var(--medex-card-bg) !important;
        }
        .fc .fc-timegrid-axis-cushion,
        .fc .fc-timegrid-slot-label-cushion {
            color: var(--medex-text) !important;
            opacity: 1 !important;
            font-weight: 600 !important;
        }

        /* Compact event display */
        .fc-event {
            font-size: 11px !important;
            padding: 2px 4px !important;
        }

        /* Force nowrap for short appointments in CSS */
        .fc-event.short-appointment {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        .fc-event.short-appointment .fc-event-title-container {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
        }

        .fc-event.short-appointment .fc-event-title {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            display: block !important;
        }

        .fc-event.short-appointment .fc-event-time {
            display: inline !important;
            white-space: nowrap !important;
        }

        .fc-event.short-appointment .fc-event-title * {
            display: inline !important;
            white-space: nowrap !important;
        }

        /* Override FullCalendar's flex layout for short appointments */
        .fc-event.short-appointment .fc-event-main-frame {
            flex-direction: column !important;
        }

        /* Tighter spacing */
        .fc .fc-toolbar {
            margin-bottom: 10px !important;
        }

        /* Hide individual calendar toolbars */
        .provider-calendar-wrapper .fc-toolbar {
            display: none !important;
        }

        /* Unified toolbar styling */
        .fc-header-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: var(--medex-panel-bg);
            border: 1px solid var(--medex-panel-border);
            border-radius: 4px;
            min-height: 52px;
        }
        .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        body.sidebar-collapsed .fc-header-toolbar .fc-toolbar-chunk:first-child {
            margin-left: 220px;
        }
        .fc-button-group {
            display: inline-flex;
            gap: 0;
        }
        .fc-header-toolbar .fc-button {
            background: var(--medex-button-bg);
            color: var(--medex-button-text);
            border: 1px solid var(--medex-button-border);
            padding: 4px 10px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            font-family: Arial, sans-serif;
        }
        .fc-header-toolbar .fc-button:hover {
            background: var(--medex-button-hover);
        }
        .fc-header-toolbar .fc-button:active,
        .fc-header-toolbar .fc-button-active {
            background: var(--medex-button-border);
        }
        .fc-button-group .fc-button:not(:last-child) {
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .fc-button-group .fc-button:not(:first-child) {
            border-radius: 0 4px 4px 0;
        }
        .fc-button-group .fc-button:not(:first-child):not(:last-child) {
            border-radius: 0;
        }
        .fc-icon {
            font-family: Arial, sans-serif;
            font-size: 14px;
            display: inline-block;
        }
        .fc-icon-chevron-left::before {
            content: '‹';
        }
        .fc-icon-chevron-right::before {
            content: '›';
        }

        /* Better contrast for today */
        .fc-day-today {
            background: #fff9e6 !important;
        }

        /* Cleaner grid lines */
        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: var(--medex-panel-border) !important;
        }
        .fc-theme-standard .fc-scrollgrid,
        .fc .fc-timegrid-slot,
        .fc .fc-daygrid-day {
            background: var(--medex-card-bg);
        }

        /* Make time slots taller to fit appointment text */
        .fc-timegrid-slot {
            height: 3em !important;
        }

        .fc-timegrid-event {
            overflow: visible !important;
        }

        /* Ensure short appointments don't get cut off vertically */
        .fc-event.short-appointment.fc-timegrid-event {
            min-height: 22px !important;
        }

        .fc-event-title-container {
            padding: 2px 4px;
            line-height: 1.3;
        }

        .loading {
            text-align: center;
            padding: 50px;
        }

        @media print {
            body {
                height: auto !important;
                overflow: visible !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
            .quick-actions,
            #left-controls,
            #sidebar,
            .fc-header-toolbar {
                display: none !important;
            }
            #main-content {
                overflow: visible !important;
                padding: 0 !important;
                background: #ffffff !important;
            }
            #calendars-container {
                overflow: visible !important;
            }
            .provider-calendar-wrapper {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="medex-theme-<?php echo attr($prefTheme); ?>">
    <div id="left-controls">
        <div class="quick-actions">
            <button type="button" class="quick-btn" title="<?php echo xla('Toggle Sidebar'); ?>" onclick="toggleSidebar()">
                <span class="hamburger4" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            <button type="button" class="quick-btn" title="<?php echo xla('New Appointment'); ?>" onclick="openNewAppointmentFromToolbar()">
                <i class="fa fa-plus" aria-hidden="true"></i>
            </button>
            <button type="button" class="quick-btn" title="<?php echo xla('Search Appointment'); ?>" onclick="openSearchFromToolbar()">
                <i class="fa fa-search" aria-hidden="true"></i>
            </button>
            <button type="button" class="quick-btn" title="<?php echo xla('Print'); ?>" onclick="printCurrentCalendar()">
                <i class="fa fa-print" aria-hidden="true"></i>
            </button>
            <button type="button" class="quick-btn" title="<?php echo xla('Open In Dedicated Window'); ?>" onclick="openDedicatedCalendarWindow()">
                <i class="fa fa-expand" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <!-- View switcher at top -->
        <div style="margin-bottom: 15px;">
            <div style="font-size: 10px; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <?php echo xlt('Calendar View'); ?>
            </div>
            <div class="view-selector">
                <button class="view-option active">
                    <?php echo xlt('Full Calendar'); ?>
                </button>
                <button class="view-option" onclick="switchToOpenEMRCalendar()">
                    <?php echo xlt('OpenEMR Calendar'); ?>
                </button>
            </div>
        </div>

        <div class="filter-section" style="margin-bottom: 20px;">
            <h4><?php echo xlt('Date'); ?></h4>
            <input type="date" id="calendar-date-picker" class="form-control" style="width: 100%; padding: 5px; border: 1px solid #dee2e6; border-radius: 3px; font-size: 11px;">
        </div>

        <div class="filter-section">
            <h4><?php echo xlt('Providers'); ?></h4>
            <div class="checkbox-group" id="provider-filter">
                <?php
                // Show providers visible to this user (OpenEMR ACL)
                foreach ($visibleProviders as $provider) {
                    $checked = $usePrefProviders
                        ? (in_array((string)$provider['username'], $prefDefaultProviders, true) ? ' checked' : '')
                        : (($provider['username'] == $userProviderUsername) ? ' checked' : '');
                    $displayName = $provider['lname'] . ', ' . $provider['fname'];
                    echo '<label class="checkbox-label" title="' . attr($displayName) . '">';
                    // Use username (not id) to match OpenEMR calendar's pc_username parameter
                    echo '<input type="checkbox" name="providers[]" value="' . attr($provider['username']) . '"' . $checked . '> ';
                    echo '<span>' . text($displayName) . '</span>';
                    echo '</label>';
                }
                ?>
            </div>
        </div>

        <div class="filter-section">
            <h4><?php echo xlt('Facilities'); ?></h4>
            <div class="checkbox-group" id="facility-filter">
                <?php
                // Show facilities accessible to this user (OpenEMR ACL)
                foreach ($userFacilities as $facility) {
                    $checked = $usePrefFacilities
                        ? (in_array((string)$facility['id'], $prefDefaultFacilities, true) ? ' checked' : '')
                        : (($facility['id'] == $userFacilityId) ? ' checked' : '');
                    echo '<label class="checkbox-label" title="' . attr($facility['name']) . '">';
                    echo '<input type="checkbox" name="facilities[]" value="' . attr($facility['id']) . '"' . $checked . '>';
                    echo '<span>' . text($facility['name']) . '</span>';
                    echo '</label>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <div id="main-content">
        <!-- Unified toolbar for all calendars -->
        <div class="fc-header-toolbar">
            <div class="fc-toolbar-chunk">
                <div class="fc-button-group">
                    <button class="fc-button" onclick="navigateAllCalendars('prev')">
                        <span class="fc-icon fc-icon-chevron-left"></span>
                    </button>
                    <button class="fc-button" onclick="navigateAllCalendars('next')">
                        <span class="fc-icon fc-icon-chevron-right"></span>
                    </button>
                </div>
                <button class="fc-button" onclick="navigateAllCalendars('today')" style="margin-left: 10px;">Today</button>
            </div>
            <div class="fc-toolbar-chunk">
                <h2 class="fc-toolbar-title" id="unified-title">Loading...</h2>
            </div>
            <div class="fc-toolbar-chunk">
                <div class="fc-button-group">
                    <button class="fc-button" id="btn-month" onclick="changeAllCalendarsView('dayGridMonth')">Month</button>
                    <button class="fc-button" id="btn-week" onclick="changeAllCalendarsView('timeGridWeek')">Week</button>
                    <button class="fc-button" id="btn-day" onclick="changeAllCalendarsView('timeGridDay')">Day</button>
                </div>
            </div>
        </div>
        <div id="calendars-container"></div>
    </div>
    <div id="medex-status-toast" aria-live="polite"></div>

    <script type="text/javascript">
        function getContrastTextColor(colorValue) {
            if (!colorValue) {
                return '#ffffff';
            }
            const value = String(colorValue).trim();
            const rgbMatch = value.match(/^rgba?\(([^)]+)\)$/i);
            if (rgbMatch) {
                const parts = rgbMatch[1].split(',').map((part) => parseFloat(part.trim()));
                if (parts.length >= 3 && parts.every((num) => !Number.isNaN(num))) {
                    const [r, g, b] = parts;
                    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                    return luminance > 0.62 ? '#1f2933' : '#ffffff';
                }
            }
            const hex = value.replace('#', '');
            if (/^[0-9a-f]{3}$/i.test(hex)) {
                const r = parseInt(hex[0] + hex[0], 16);
                const g = parseInt(hex[1] + hex[1], 16);
                const b = parseInt(hex[2] + hex[2], 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance > 0.62 ? '#1f2933' : '#ffffff';
            }
            if (/^[0-9a-f]{6}$/i.test(hex)) {
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance > 0.62 ? '#1f2933' : '#ffffff';
            }
            return '#ffffff';
        }

        function applyThemeContrastColors() {
            const body = document.body;
            const computed = window.getComputedStyle(body);
            const accent = computed.getPropertyValue('--medex-accent');
            const buttonBg = computed.getPropertyValue('--medex-button-bg');
            const quickButtonBg = computed.getPropertyValue('--medex-quick-btn-bg');
            const accentText = getContrastTextColor(accent);
            const buttonText = getContrastTextColor(buttonBg);
            const quickButtonText = getContrastTextColor(quickButtonBg);
            body.style.setProperty('--medex-accent-contrast', accentText);
            body.style.setProperty('--medex-button-text', buttonText);
            body.style.setProperty('--medex-quick-btn-text', quickButtonText);
        }

        function applyInheritedOpenEmrTheme() {
            const body = document.body;
            if (!body.classList.contains('medex-theme-openemr')) {
                applyThemeContrastColors();
                return;
            }

            const isOpaque = (value) => !!value && value !== 'transparent' && value !== 'rgba(0, 0, 0, 0)';
            const docs = [];
            try {
                if (window.top && window.top.document && window.top.document !== document) {
                    docs.push(window.top.document);
                }
            } catch (e) {
                // Cross-origin access is not expected in our setup; ignore safely if blocked.
            }
            docs.push(document);

            const pickColor = (selectors, prop, fallback) => {
                for (const docRef of docs) {
                    for (const selector of selectors) {
                        const el = docRef.querySelector(selector);
                        if (!el) {
                            continue;
                        }
                        const styleGetter = (docRef.defaultView && typeof docRef.defaultView.getComputedStyle === 'function')
                            ? docRef.defaultView.getComputedStyle.bind(docRef.defaultView)
                            : window.getComputedStyle.bind(window);
                        const value = styleGetter(el)[prop];
                        if (isOpaque(value)) {
                            return value;
                        }
                    }
                }
                return fallback;
            };
            const pickVar = (varNames, fallback) => {
                for (const docRef of docs) {
                    const root = docRef.documentElement;
                    if (!root) {
                        continue;
                    }
                    const styleGetter = (docRef.defaultView && typeof docRef.defaultView.getComputedStyle === 'function')
                        ? docRef.defaultView.getComputedStyle.bind(docRef.defaultView)
                        : window.getComputedStyle.bind(window);
                    const style = styleGetter(root);
                    for (const varName of varNames) {
                        const value = (style.getPropertyValue(varName) || '').trim();
                        if (isOpaque(value) || /^#[0-9a-f]{3,8}$/i.test(value)) {
                            return value;
                        }
                    }
                }
                return fallback;
            };

            const bg = pickColor(['body', 'html', '.container-fluid', '.card', '.panel'], 'backgroundColor', '#f6f7f9');
            const text = pickColor(['body', 'html', '.container-fluid', '.card'], 'color', '#1f2933');
            const accent = pickVar(['--primary', '--primary-color', '--bs-primary'], pickColor(['.btn-primary', '.navbar .active a', '.nav-tabs .active a', '.nav-link.active'], 'backgroundColor', '#c9dbf2'));
            const panelBg = pickColor(['.card', '.panel', '.container-fluid', 'body'], 'backgroundColor', '#ffffff');
            const panelBorder = pickColor(['.card', '.panel', '.form-control', '.table'], 'borderColor', '#d6dbe1');

            body.style.setProperty('--medex-bg', bg);
            body.style.setProperty('--medex-panel-bg', panelBg);
            body.style.setProperty('--medex-panel-border', panelBorder);
            body.style.setProperty('--medex-card-bg', panelBg);
            body.style.setProperty('--medex-text', text);
            body.style.setProperty('--medex-accent', accent);
            body.style.setProperty('--medex-button-bg', accent);
            body.style.setProperty('--medex-button-hover', '#b8cde8');
            body.style.setProperty('--medex-button-border', '#9fb8d6');
            body.style.setProperty('--medex-quick-bg', accent);
            body.style.setProperty('--medex-quick-btn-bg', accent);
            body.style.setProperty('--medex-quick-btn-text', getContrastTextColor(accent));
            body.style.setProperty('--medex-quick-btn-border', '#9fb8d6');
            applyThemeContrastColors();
        }

        function printCurrentCalendar() {
            window.print();
        }

        let medexToastTimer = null;
        function showMedexStatusToast(message, level = 'success', durationMs = 1600) {
            const toast = document.getElementById('medex-status-toast');
            if (!toast || !message) {
                return;
            }
            toast.textContent = message;
            toast.classList.remove('error', 'show');
            if (level === 'error') {
                toast.classList.add('error');
            }
            if (medexToastTimer) {
                clearTimeout(medexToastTimer);
            }
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });
            medexToastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, durationMs);
        }

        function openDedicatedCalendarWindow() {
            saveFilterSelections();

            const params = new URLSearchParams(window.location.search);
            const selectedProviders = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
            const selectedFacilities = Array.from(document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')).map(cb => cb.value);

            const activeCalendar = (typeof calendarInstances !== 'undefined' && calendarInstances.length > 0) ? calendarInstances[0] : null;
            const currentView = activeCalendar ? activeCalendar.view.type : (localStorage.getItem('medexCalendarView') || 'timeGridWeek');
            const currentDateObj = activeCalendar ? activeCalendar.getDate() : null;
            const currentDate = currentDateObj
                ? currentDateObj.toISOString().split('T')[0]
                : ((localStorage.getItem('medexCalendarDate') || '').split('T')[0] || '');

            localStorage.setItem('medexCalendarView', currentView);
            if (currentDate) {
                localStorage.setItem('medexCalendarDate', currentDate);
            }

            params.set('view', currentView);
            if (currentDate) {
                params.set('date', currentDate);
            }
            if (selectedProviders.length > 0) {
                params.set('providers', selectedProviders.join(','));
            } else {
                params.delete('providers');
            }
            if (selectedFacilities.length > 0) {
                params.set('facilities', selectedFacilities.join(','));
            } else {
                params.delete('facilities');
            }

            const targetUrl = window.location.pathname + '?' + params.toString();

            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }
            window.open(targetUrl, '_blank', 'noopener,noreferrer,width=1600,height=900');
        }

        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isHidden = sidebar.classList.toggle('hidden');
            document.body.classList.toggle('sidebar-collapsed', isHidden);

            // Trigger calendar resize after animation
            setTimeout(() => {
                calendarInstances.forEach(cal => cal.updateSize());
                window.dispatchEvent(new Event('resize'));
            }, 300);
        }

        // Save filter selections before switching views
        function saveFilterSelections() {
            const providers = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
            const facilities = Array.from(document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')).map(cb => cb.value);
            localStorage.setItem('medexSelectedProviders', JSON.stringify(providers));
            localStorage.setItem('medexSelectedFacilities', JSON.stringify(facilities));
            console.log('Saved selections - Providers:', providers, 'Facilities:', facilities);
        }

        function switchToOpenEMRCalendar() {
            saveFilterSelections();

            // Get current view state
            const savedView = localStorage.getItem('medexCalendarView') || 'timeGridWeek';
            const activeCalendar = (typeof calendarInstances !== 'undefined' && calendarInstances.length > 0) ? calendarInstances[0] : null;
            let savedDate = activeCalendar ? activeCalendar.getDate().toISOString().split('T')[0] : localStorage.getItem('medexCalendarDate');

            // Normalize savedDate to YYYY-MM-DD format if it's an ISO timestamp
            if (savedDate) {
                // Check if it's a full ISO timestamp (contains T)
                if (savedDate.includes('T')) {
                    savedDate = savedDate.split('T')[0];
                }
                // Store normalized date back to localStorage
                localStorage.setItem('medexCalendarDate', savedDate);
            }

            // Map FullCalendar view to OpenEMR PostCalendar view
            let viewtype = 'week';
            if (savedView === 'dayGridMonth') {
                viewtype = 'month';
            } else if (savedView === 'timeGridDay') {
                viewtype = 'day';
            } else if (savedView === 'timeGridWeek') {
                viewtype = 'week';
            }

            // Get selected providers and facilities
            const selectedProviders = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
            const selectedFacilities = Array.from(document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')).map(cb => cb.value);

            // Build URL with all parameters
            let url = '<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/calendar/openemr_calendar_wrapper.php?module=PostCalendar&func=view&viewtype=' + viewtype;

            // Add date (OpenEMR uses jumpdate parameter in YYYY-MM-DD format)
            if (savedDate) {
                const compactDate = savedDate.replace(/-/g, '');
                url += '&Date=' + encodeURIComponent(compactDate);
                url += '&jumpdate=' + encodeURIComponent(savedDate);
            }

            // Add selected provider (single explicit provider avoids empty first render states).
            if (selectedProviders.length > 0) {
                url += '&pc_username=' + encodeURIComponent(selectedProviders[0]);
            }

            // Add facility (OpenEMR calendar typically shows one facility at a time)
            if (selectedFacilities.length > 0) {
                url += '&pc_facility=' + encodeURIComponent(selectedFacilities[0]);
            }

            const preferenceUrl =
                '<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/calendar/set_calendar_preference.php' +
                '?site=<?php echo urlencode($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default')); ?>&preference=openemr&redirect=' + encodeURIComponent(url);

            console.log('[MedEx] Switching to OpenEMR calendar with params:', {viewtype, date: savedDate, providers: selectedProviders, facilities: selectedFacilities});
            console.log('[MedEx] URL:', url);

            // Restore session first so OpenEMR's session token remains valid across navigation.
            // Wait for session restoration to complete before navigating
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
                // Give session restoration time to complete
                setTimeout(function() {
                    window.location.href = preferenceUrl;
                }, 100);
            } else {
                window.location.href = preferenceUrl;
            }
        }

        function openSearchFromToolbar() {
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
                setTimeout(function() {
                    window.location.href = '<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&func=search&medex_prefer=openemr';
                }, 100);
            } else {
                window.location.href = '<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&func=search&medex_prefer=openemr';
            }
        }

        function openNewAppointmentFromToolbar() {
            let startDate = new Date();
            if (calendarInstances.length > 0) {
                startDate = calendarInstances[0].getDate();
            } else {
                const savedDate = localStorage.getItem('medexCalendarDate');
                if (savedDate) {
                    const parsed = new Date(savedDate);
                    if (!isNaN(parsed.getTime())) {
                        startDate = parsed;
                    }
                }
            }

            const year = startDate.getFullYear();
            const month = String(startDate.getMonth() + 1).padStart(2, '0');
            const day = String(startDate.getDate()).padStart(2, '0');
            const dateStr = `${year}${month}${day}`;

            const selectedProviders = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
            let providerId = selectedProviders.length > 0 ? selectedProviders[0] : '';
            if (!providerId && window.providerData) {
                const allProviders = Object.keys(window.providerData);
                if (allProviders.length > 0) {
                    providerId = allProviders[0];
                }
            }

            const duration = parseInt(window.calendarInterval || '15', 10) || 15;
            const url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?date=' + encodeURIComponent(dateStr) +
                '&starttimeh=9&starttimem=00&userid=' + encodeURIComponent(providerId) + '&duration=' + encodeURIComponent(duration);

            const callbackName = 'medexRefreshAllCalendars';
            if (!window[callbackName]) {
                window[callbackName] = function() {
                    calendarInstances.forEach(cal => cal.refetchEvents());
                };
            }

            if (window.parent && typeof window.parent.dlgopen !== 'undefined') {
                window.parent.dlgopen(url, '_blank', 850, 600, '', '', { onClosed: callbackName });
            } else if (typeof dlgopen !== 'undefined') {
                dlgopen(url, '_blank', 850, 600, '', '', { onClosed: callbackName });
            } else {
                window.open(url, '_blank', 'width=850,height=600');
            }
        }

        // Note: restoreFilterSelections() is now defined in calendar.js and called on DOMContentLoaded
        applyInheritedOpenEmrTheme();

        // Provider data for calendar titles (OpenEMR ACL)
        window.providerData = <?php
            $providerData = [];
            foreach ($visibleProviders as $provider) {
                $providerData[$provider['username']] = [
                    'id' => $provider['id'],
                    'username' => $provider['username'],
                    'name' => $provider['lname'] . ', ' . $provider['fname']
                ];
            }
            echo json_encode($providerData);
        ?>;

        // Facility data for calendar titles (OpenEMR ACL)
        window.facilityData = <?php
            $facilityDataMap = [];
            foreach ($userFacilities as $facility) {
                $facilityDataMap[$facility['id']] = [
                    'id' => $facility['id'],
                    'name' => $facility['name']
                ];
            }
            echo json_encode($facilityDataMap);
        ?>;
    </script>
</body>
</html>
