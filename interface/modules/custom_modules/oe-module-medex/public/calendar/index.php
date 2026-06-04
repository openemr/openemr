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

$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
$nativeCalendarFallbackUrl = ($GLOBALS['webroot'] ?? '') . '/interface/main/calendar/index.php?medex_prefer=openemr';
$moduleCalendarFallbackUrl = ($GLOBALS['webroot'] ?? '') . '/interface/modules/custom_modules/oe-module-medex/admin/splash.php?minimal=1&site=' . urlencode($siteId);

// Check if configured
if (!$api->isConfigured()) {
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . $moduleCalendarFallbackUrl);
    exit;
}

if (!$api->hasServiceEntitlement('calendar_full')) {
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . $moduleCalendarFallbackUrl);
    exit;
}

// Authenticate with MedEx API (validates IP, etc)
// On network failure with a stale cached token: continue loading so Full Calendar
// can still run against local OpenEMR data and cached entitlements.
$_medexAuthError = null;
$_medexUsingStaleAuth = false;
try {
    $loginResult = $api->login();
    if (!empty($loginResult['stale_fallback'])) {
        error_log('[MedEx Calendar] Network down (stale token fallback), continuing with cached MedEx session');
        $_medexUsingStaleAuth = true;
    }
} catch (\Exception $e) {
    // Also redirect on hard failure (e.g. no cached token at all).
    error_log('[MedEx Calendar] Login failed, redirecting to module fallback: ' . $e->getMessage());
    $_SESSION['medex_calendar_skip'] = true;
    header('Location: ' . $moduleCalendarFallbackUrl);
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
$isAdminUser = AclMain::aclCheckCore('admin', 'super');
$isProviderUser = ((int)$userAuthorized === 1);
$calendarActorRole = $isAdminUser ? 'admin' : ($isProviderUser ? 'provider' : 'secretary');

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
$showReschedulerPanels = $api->hasServiceEntitlement('calendar_ai');
$hasReschedulerService = $showReschedulerPanels || $api->hasServiceEntitlement('appointment_reminders');
$reschedulerPausedRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_rescheduler_paused' LIMIT 1");
$reschedulerPaused = (($reschedulerPausedRow['gl_value'] ?? '0') === '1');

$schedulingRulesRow = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_scheduling_rules' LIMIT 1");
$calSchedulingRules = json_decode((string)($schedulingRulesRow['gl_value'] ?? ''), true);
if (!is_array($calSchedulingRules)) {
    $calSchedulingRules = ['template_enforcement' => 'guideline', 'allow_double_booking' => true];
}
// Runtime gate: treat rescheduler as inactive if no future open template slots exist.
// Primary: slot registry available rows. Fallback: open template slots in the calendar (pc_pid=0).
if (!$reschedulerPaused) {
    $hasCapacity = false;
    $slotRegistryExists = sqlQuery("SHOW TABLES LIKE 'medex_slot_registry'");
    if ($slotRegistryExists) {
        $hasReschedulableSlot = sqlQuery(
            "SELECT 1 FROM medex_slot_registry WHERE reschedulable = 1 AND slot_state = 'available' AND event_date >= CURDATE() LIMIT 1"
        );
        if ($hasReschedulableSlot) {
            $hasCapacity = true;
        }
    }
    if (!$hasCapacity) {
        $openTemplateSlot = sqlQuery(
            "SELECT pc_eid FROM openemr_postcalendar_events
             WHERE (pc_pid IS NULL OR pc_pid <= 0)
               AND pc_recurrtype = 0
               AND pc_eventDate >= CURDATE()
             LIMIT 1"
        );
        if ($openTemplateSlot) {
            $hasCapacity = true;
        }
    }
    if (!$hasCapacity) {
        $reschedulerPaused = true;
    }
}
$reschedulerActiveForUI = $hasReschedulerService && !$reschedulerPaused;
$openStaffTitle = 'Open and available to fill by staff. These open slots are NOT visible to the Patient Rescheduler.';
if ($isAdminUser) {
    $openStaffTitle .= ' To make this available in the Patient Rescheduler, proceed to Admin Dashboard::Calendar Services::Slot Builder.';
}
$openPatientTitle = 'Open and available to fill by staff and patients using the Patient Rescheduler service.';

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

// Detect the active OpenEMR theme slug (e.g. 'manila', 'solar', 'dark')
// so the body can carry an oe-theme-* class for per-theme CSS overrides.
$oeThemeSlug = '';
$oeThemeCss = (string)($GLOBALS['css_header'] ?? '');
if (preg_match('/style_([a-z0-9_]+)\.css/i', $oeThemeCss, $_tm)) {
    $oeThemeSlug = strtolower($_tm[1]);
}
unset($_tm);
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

$appointmentCategoryLegend = [];

// Build category list from template/open-slot rows so sidebar filtering matches
// actual slot types used by templates (source of truth: pc_prefcatid).
$templateCategoryResult = sqlStatement(
    "SELECT DISTINCT pref.pc_catid, pref.pc_catname, pref.pc_catcolor
       FROM openemr_postcalendar_events pc
 INNER JOIN openemr_postcalendar_categories pref ON pref.pc_catid = pc.pc_prefcatid
      WHERE pref.pc_active = 1
        AND pc.pc_pid = 0
        AND pc.pc_prefcatid > 0
        AND (
             pc.pc_location LIKE 'MEDEX_%'
             OR pc.pc_title LIKE 'Open Slot - %'
             OR pc.pc_title LIKE 'In Office - %'
             OR pc.pc_title = 'Open Slot'
        )
      ORDER BY pref.pc_seq, pref.pc_catname"
);

while ($categoryRow = sqlFetchArray($templateCategoryResult)) {
    $catId = (int)($categoryRow['pc_catid'] ?? 0);
    $catName = trim((string)($categoryRow['pc_catname'] ?? ''));
    if ($catId <= 0 || $catName === '') {
        continue;
    }
    $rawColor = trim((string)($categoryRow['pc_catcolor'] ?? ''));
    if ($rawColor !== '' && $rawColor[0] !== '#') {
        $rawColor = '#' . $rawColor;
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) {
        $rawColor = '#3788d8';
    }

    $appointmentCategoryLegend[] = [
        'id' => $catId,
        'name' => $catName,
        'color' => $rawColor,
    ];
}

// Also include common non-template appointment types that practices use directly
// in scheduling workflows (for example Lunch/Reserved/Vacation).
$specialTypeResult = sqlStatement(
    "SELECT pc_catid, pc_catname, pc_catcolor
       FROM openemr_postcalendar_categories
      WHERE pc_active = 1
        AND (
             LOWER(pc_catname) LIKE '%lunch%'
             OR LOWER(pc_catname) LIKE '%reserved%'
             OR LOWER(pc_catname) LIKE '%vacation%'
        )
      ORDER BY pc_seq, pc_catname"
);
while ($categoryRow = sqlFetchArray($specialTypeResult)) {
    $catId = (int)($categoryRow['pc_catid'] ?? 0);
    $catName = trim((string)($categoryRow['pc_catname'] ?? ''));
    if ($catId <= 0 || $catName === '') {
        continue;
    }

    $alreadyPresent = false;
    foreach ($appointmentCategoryLegend as $existingLegend) {
        if ((int)($existingLegend['id'] ?? 0) === $catId) {
            $alreadyPresent = true;
            break;
        }
    }
    if ($alreadyPresent) {
        continue;
    }

    $rawColor = trim((string)($categoryRow['pc_catcolor'] ?? ''));
    if ($rawColor !== '' && $rawColor[0] !== '#') {
        $rawColor = '#' . $rawColor;
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) {
        $rawColor = '#3788d8';
    }

    $appointmentCategoryLegend[] = [
        'id' => $catId,
        'name' => $catName,
        'color' => $rawColor,
    ];
}

// Fallback: if no template/open-slot types are present yet, show active categories
// (excluding availability markers) so filter UI remains usable.
if (empty($appointmentCategoryLegend)) {
    $categoryResult = sqlStatement(
        "SELECT pc_catid, pc_catname, pc_catcolor
           FROM openemr_postcalendar_categories
          WHERE pc_active = 1
          ORDER BY pc_seq, pc_catname"
    );
    while ($categoryRow = sqlFetchArray($categoryResult)) {
        $catId = (int)($categoryRow['pc_catid'] ?? 0);
        if (in_array($catId, [2, 3], true)) {
            continue;
        }
        $catName = trim((string)($categoryRow['pc_catname'] ?? ''));
        if ($catName === '') {
            continue;
        }
        $rawColor = trim((string)($categoryRow['pc_catcolor'] ?? ''));
        if ($rawColor !== '' && $rawColor[0] !== '#') {
            $rawColor = '#' . $rawColor;
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $rawColor)) {
            $rawColor = '#3788d8';
        }

        $appointmentCategoryLegend[] = [
            'id' => $catId,
            'name' => $catName,
            'color' => $rawColor,
        ];
    }
}

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
        window.medexReschedulerActive = <?php echo $reschedulerActiveForUI ? 'true' : 'false'; ?>;
        window.medexSchedulingRules = <?php echo json_encode([
            'templateEnforcement' => (string)($calSchedulingRules['template_enforcement'] ?? 'guideline'),
            'allowDoubleBooking'  => (bool)($calSchedulingRules['allow_double_booking'] ?? true),
        ]); ?>;
        window.medexDragPolicy = <?php echo json_encode([
            'role' => $calendarActorRole,
            'isAdmin' => $isAdminUser,
            'isProvider' => $isProviderUser,
            // Start policy: all three roles can drag, server enforces secretary/category and double-book checks.
            'canDragDrop' => true,
            'canDoubleBook' => ($isAdminUser || $isProviderUser),
            'warnOnDoubleBook' => ($isAdminUser || $isProviderUser)
        ]); ?>;
    </script>

    <!-- Calendar initialization -->
    <script src='<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/calendar/calendar.js?v=<?php echo urlencode((string)filemtime(__DIR__ . '/calendar.js')); ?>' type="text/javascript"></script>

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
            --medex-open-slot-chip-width: 168px;
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
            /* Generic OpenEMR-inherit base — uses Bootstrap CSS vars from Header::setupHeader(). */
            --medex-bg: var(--light, #f6f7f9);
            --medex-text: var(--body-color, #1f2933);
            --medex-panel-bg: var(--white, #ffffff);
            --medex-panel-border: var(--gray300, #d6dbe1);
            --medex-accent: var(--primary, #2d75b6);
            --medex-accent-contrast: #ffffff;
            --medex-card-bg: var(--white, #ffffff);
            --medex-card-shadow: 0 1px 3px rgba(41,67,94,0.14);
            --medex-event-text: var(--body-color, #1f2933);
            --medex-button-bg: var(--primary, #315b82);
            --medex-button-text: #ffffff;
            --medex-button-hover: color-mix(in srgb, var(--primary, #315b82) 85%, black);
            --medex-button-border: color-mix(in srgb, var(--primary, #315b82) 70%, black);
            --medex-quick-bg: rgba(246, 247, 249, 0.95);
            --medex-quick-btn-bg: var(--gray200, #e6ebf0);
            --medex-quick-btn-text: var(--gray800, #2c3e50);
            --medex-quick-btn-border: var(--gray400, #a7b3bf);
        }

        /* Manila theme: btn-primary is #c9dbf2 (aqua) with black text.
           Override accent/button vars to match the native OpenEMR Manila appearance. */
        body.medex-theme-openemr.oe-theme-manila {
            --medex-accent: #c9dbf2;
            --medex-accent-contrast: #000000;
            --medex-button-bg: #c9dbf2;
            --medex-button-text: #000000;
            --medex-button-hover: #aec9e8;
            --medex-button-border: rgba(0, 0, 0, 0.45);
            --medex-quick-btn-bg: #c9dbf2;
            --medex-quick-btn-text: #000000;
            --medex-quick-btn-border: rgba(0, 0, 0, 0.3);
        }

        /* Sidebar styling */
        #sidebar {
            width: 220px;
            min-width: 220px;
            background: var(--medex-panel-bg);
            border-right: 1px solid var(--medex-panel-border);
            padding: 73px 10px 10px 10px;
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
            padding: 0;
            border-radius: 0;
        }
        #left-controls {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 250;
            background: var(--medex-panel-bg);
            border: 1px solid var(--medex-panel-border);
            border-radius: 10px;
            padding: 4px;
            box-shadow: var(--medex-card-shadow);
        }
        #left-controls-shield {
            position: fixed;
            top: 0;
            left: 0;
            box-sizing: border-box;
            width: 240px;
            height: 66px;
            z-index: 240;
            background: var(--medex-panel-bg);
            border-right: 1px solid var(--medex-panel-border);
        }
        body.sidebar-collapsed #left-controls {
            top: 16px;
            left: 16px;
        }
        body.sidebar-collapsed #left-controls-shield {
            width: 0;
            border-right: 0;
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
        #medex-modern-tooltip {
            position: fixed;
            z-index: 9999;
            pointer-events: none;
            background: #1c2d3a;
            color: #f0f8ff;
            border-radius: 9px;
            padding: 8px 12px;
            font-size: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.28);
            line-height: 1.55;
            max-width: 240px;
            width: max-content;
            opacity: 0;
            transition: opacity 0.12s ease;
            white-space: normal;
            word-break: break-word;
        }
        #medex-modern-tooltip.show {
            opacity: 1;
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
        #sidebar details.filter-section {
            margin-bottom: 12px;
            border: 1px solid #bcc8d6;
            border-radius: 4px;
            background: #f3f6fa;
            box-shadow: 0 1px 2px rgba(27, 45, 67, 0.08);
            overflow: hidden;
        }
        #sidebar details.filter-section > summary {
            list-style: none;
            cursor: pointer;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 700;
            color: var(--medex-text);
            border-bottom: 1px solid transparent;
            background: #eef3f8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }
        #sidebar details.filter-section > summary::-webkit-details-marker {
            display: none;
        }
        #sidebar details.filter-section > summary::after {
            content: '\25B8';
            font-size: 11px;
            color: #6b7280;
            transition: transform 0.15s ease;
            margin-left: 10px;
        }
        #sidebar details.filter-section[open] > summary {
            border-bottom-color: #c7d2e0;
            background: #e8eef5;
        }
        #sidebar details.filter-section[open] > summary::after {
            transform: rotate(90deg);
        }
        #sidebar .section-content {
            padding: 8px;
            background: #ffffff;
        }
        #sidebar .filter-bulk-actions {
            display: flex;
            gap: 6px;
            margin-bottom: 6px;
        }
        #sidebar .filter-bulk-action {
            flex: 1;
            padding: 4px 6px;
            border: 1px solid #c7d2e0;
            border-radius: 3px;
            background: #f3f6fa;
            color: #334155;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
        }
        #sidebar .filter-bulk-action:hover {
            background: #e8eef5;
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
            border: 1px solid #c7d2e0;
            border-radius: 3px;
            background: #f8fbff;
            box-shadow: inset 0 1px 2px rgba(35, 57, 82, 0.08);
        }
        #sidebar .checkbox-group::-webkit-scrollbar {
            width: 8px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-track {
            background: #e6ebf1;
            border-radius: 3px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb {
            background: #9aa9bb;
            border-radius: 3px;
            border: 1px solid #e6ebf1;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb:hover {
            background: #8698ad;
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
            background: #e9f2ff;
            color: #1f2937;
            box-shadow: none;
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
        .sidebar-section-label {
            font-size: 10px;
            color: var(--medex-text, #444);
            opacity: 0.65;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        body.medex-theme-openemr.oe-theme-manila .sidebar-section-label {
            opacity: 1;
            color: #444;
        }
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
            color: var(--medex-text, #1c2d3a);
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
            position: relative;
        }
        .fc-event-title {
            color: inherit;
            font-weight: 500;
        }
        .fc-event .appointment-comment-line {
            display: block;
            max-width: 100%;
            font-size: 10px;
            font-weight: 500;
            opacity: 0.95;
            color: inherit;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .fc-event .appointment-reason-inline {
            font-size: 10px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline;
            max-width: 100%;
        }
        /* Slot-state badge: sits above the title text, not floating over it.
           Both are visible even in 5-minute chips. */
        .fc-event .slot-state-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            height: 13px;
            border-radius: 999px;
            border: 1px solid rgba(0,0,0,0.22);
            font-size: 9px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0.2px;
            white-space: nowrap;
            flex: 0 0 auto;
            background: rgba(255,255,255,0.9);
            color: #1f2933;
            margin-bottom: 1px;
        }
        /* Stack badge above title in a column so neither clips the other. */
        .fc-event.open-slot-chip .fc-event-main {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            padding: 2px 4px !important;
            overflow: hidden;
        }
        /* Title text sits below the badge — always visible */
        .fc-event.open-slot-chip .fc-event-title {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            font-size: 10px;
            line-height: 1.2;
        }
        .fc-event .slot-state-indicator--filled {
            background: #ffffff;
            border-color: #9ca3af;
            color: #111827;
        }
        .fc-event .slot-state-indicator--open {
            background: #15803d;
            border-color: #166534;
            color: #ffffff;
        }
        .fc-event .slot-state-indicator--open_not_reschedulable {
            background: #15803d;
            border-color: #166534;
            color: #ffffff;
        }
        .fc-event .slot-state-indicator--open_reschedulable_full {
            background: #b45309;
            border-color: #92400e;
            color: #ffffff;
        }
        .fc-event .slot-state-indicator--held_staff {
            background: #2563eb;
            border-color: #1d4ed8;
            color: #ffffff;
        }
        .fc-event .slot-state-indicator--held_patient {
            background: #7c3aed;
            border-color: #5b21b6;
            color: #ffffff;
        }
        .fc-event .slot-state-indicator--open_reschedulable_available {
            background: #15803d;
            border-color: #166534;
            color: #ffffff;
        }
        /* Appointment status badge — prepended before .fc-event-time, top-left of chip */
        .fc-event:not(.open-slot-chip) .medex-appt-status-badge {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;       /* prevent flex-stretch from making it full width */
            width: fit-content;
            max-width: 80px;
            padding: 0 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 700;
            line-height: 1.5;
            white-space: nowrap;
            flex-shrink: 0;
            margin-bottom: 1px;
            background: #94a3b8;
            color: #fff;
        }
        .slot-state-legend {
            border: 1px solid #c7d2e0;
            border-radius: 4px;
            padding: 8px;
            background: #ffffff;
            box-shadow: inset 0 1px 2px rgba(35, 57, 82, 0.06);
        }
        .slot-state-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--medex-text);
            margin-bottom: 6px;
            line-height: 1.2;
        }
        .slot-state-legend-item:last-child {
            margin-bottom: 0;
        }
        .slot-state-legend-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 58px;
            height: 20px;
            border-radius: 999px;
            border: 1px solid rgba(0,0,0,0.22);
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #ffffff;
            flex: 0 0 auto;
            cursor: pointer;
        }
        .slot-state-legend-badge.filled { background: #ffffff; border-color: #9ca3af; color: #111827; }
        .slot-state-legend-badge.open { background: #15803d; border-color: #166534; }
        .slot-state-legend-badge.open_not_reschedulable { background: #15803d; border-color: #166534; }
        .slot-state-legend-badge.open_reschedulable_full { background: #b45309; border-color: #92400e; }
        .slot-state-legend-badge.held_staff { background: #2563eb; border-color: #1d4ed8; }
        .slot-state-legend-badge.held_patient { background: #7c3aed; border-color: #5b21b6; }
        .slot-state-legend-badge.open_reschedulable_available { background: #15803d; border-color: #166534; }
        .appointment-category-legend {
            border: 1px solid #c7d2e0;
            border-radius: 4px;
            padding: 8px;
            background: #ffffff;
            max-height: 180px;
            overflow-y: auto;
            box-shadow: inset 0 1px 2px rgba(35, 57, 82, 0.06);
        }
        .appointment-category-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--medex-text);
            margin-bottom: 6px;
            line-height: 1.2;
        }
        .appointment-category-item:last-child {
            margin-bottom: 0;
        }
        .appointment-category-swatch {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            border: 1px solid rgba(0,0,0,0.2);
            flex: 0 0 auto;
        }
        .appointment-category-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        /* Generated open slots: render as a short colored category chip so
           the remaining white row area visually communicates open capacity. */
        .fc-event.open-slot-chip.fc-timegrid-event {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-main {
            display: inline-flex;
            align-items: center;
            width: min(var(--medex-open-slot-chip-width), 100%);
            min-width: min(var(--medex-open-slot-chip-width), 100%);
            max-width: 100%;
            box-sizing: border-box;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.18);
            background: var(--medex-slot-type-color, #d9e8f9) !important;
            color: #0f2740 !important;
            padding: 1px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Month view: open-slot chips need explicit background/border since
           fc-daygrid-event doesn't inherit the timegrid chip rules. */
        .fc-event.open-slot-chip.fc-daygrid-event {
            background: var(--medex-slot-type-color, #d9e8f9) !important;
            border: 1px solid rgba(0,0,0,0.18) !important;
            border-radius: 4px !important;
            color: #0f2740 !important;
        }
        .fc-event.open-slot-chip.fc-daygrid-event .fc-event-main {
            background: transparent !important;
            color: inherit !important;
        }

        /* Default/taller slot mode: stack time over category so labels are readable. */
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .fc-event-main {
            flex-direction: column;
            align-items: flex-start;
            white-space: normal;
            line-height: 1.15;
            gap: 1px;
        }
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .slot-state-indicator {
            top: 2px;
            left: 2px;
        }
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .fc-event-main-frame {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .fc-event-time {
            margin-right: 0;
            margin-bottom: 1px;
            line-height: 1.05;
        }
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .fc-event-title-container,
        .fc-event.open-slot-chip.open-slot-chip-tall.fc-timegrid-event .fc-event-title {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            line-height: 1.1;
        }

        /* Short one-row slots: keep inline, but let the chip grow wider before trimming. */
        .fc-event.open-slot-chip.open-slot-chip-short.fc-timegrid-event .fc-event-main {
            flex-direction: row;
            align-items: center;
            white-space: nowrap;
        }
        .fc-event.open-slot-chip.open-slot-chip-short.fc-timegrid-event .fc-event-main-frame {
            display: flex;
            flex-direction: row;
            align-items: center;
            min-width: 0;
            width: 100%;
        }
        .fc-event.open-slot-chip.open-slot-chip-short.fc-timegrid-event .fc-event-title-container,
        .fc-event.open-slot-chip.open-slot-chip-short.fc-timegrid-event .fc-event-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-main-frame {
            display: flex;
            flex-direction: row;
            align-items: center;
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-time {
            margin-right: 4px;
            flex: 0 0 auto;
        }
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-time {
            display: none !important;
            margin-right: 0 !important;
        }
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-title-container,
        .fc-event.open-slot-chip.fc-timegrid-event .fc-event-title {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Patient appointments should stand out */
        .fc-event.patient-appointment {
            opacity: 1;
            font-weight: bold;
            border: 2px solid rgba(0,0,0,0.2);
        }

        /* Two-chip lane: keep slot anchors on the left and render booked
           appointments as the second chip on the right when they overlap. */
        .fc-timegrid-event.slot-anchor-chip {
            z-index: 2;
        }
        .fc-timegrid-event.appointment-second-chip.has-slot-anchor-overlap {
            z-index: 3;
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .fc-timegrid-event.slot-anchor-chip {
            left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .fc-timegrid-event.appointment-second-chip.has-slot-anchor-overlap .fc-event-main {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        /* FullCalendar positions the harness wrapper; enforce split there too. */
        .fc-timegrid-event-harness.slot-anchor-chip-harness,
        .fc-timegrid-event-harness.slot-anchor-overlap-harness {
            left: 0 !important;
            right: auto !important;
            width: max(58px, min(var(--medex-open-slot-chip-width), 16%, calc(100% - 10px))) !important;
            min-width: 58px !important;
            max-width: min(var(--medex-open-slot-chip-width), 16%, calc(100% - 10px)) !important;
        }
        .fc-timegrid-event-harness.appointment-second-chip-harness {
            left: calc(max(58px, min(var(--medex-open-slot-chip-width), 16%, calc(100% - 10px))) + 8px) !important;
            right: auto !important;
            width: calc(100% - max(58px, min(var(--medex-open-slot-chip-width), 16%, calc(100% - 10px))) - 10px) !important;
            max-width: calc(100% - max(58px, min(var(--medex-open-slot-chip-width), 16%, calc(100% - 10px))) - 10px) !important;
        }

        /* Override FullCalendar's default event styling for better contrast */
        .fc-event-main {
            color: inherit;
        }

        .fc-daygrid-event {
            white-space: normal !important;
        }

        .fc-daygrid-event .fc-event-main-frame {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fc-daygrid-event .fc-event-time {
            display: inline-flex !important;
            align-items: center;
            justify-content: flex-start;
            width: 74px;
            min-width: 74px;
            max-width: 74px;
            box-sizing: border-box;
            white-space: nowrap !important;
            overflow: hidden;
            text-overflow: clip;
            padding: 0 6px !important;
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
        .fc-event.short-appointment:not(.open-slot-chip) .fc-event-main-frame {
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
            display: inline-block;
            line-height: 1;
        }
        .fc-icon-chevron-left::before,
        .fc-icon-chevron-right::before {
            font-family: 'FontAwesome';
            font-size: 15px;
            font-weight: 900;
            speak: none;
            -webkit-font-smoothing: antialiased;
        }
        .fc-icon-chevron-left::before { content: '\f053'; }
        .fc-icon-chevron-right::before { content: '\f054'; }
        /* Prev/next buttons are slightly larger so the arrows are easy to hit */
        .fc-button-group .fc-button:has(.fc-icon-chevron-left),
        .fc-button-group .fc-button:has(.fc-icon-chevron-right) {
            padding: 4px 13px;
            font-size: 14px;
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
<body class="medex-theme-<?php echo attr($prefTheme); ?><?php echo $oeThemeSlug ? ' oe-theme-' . attr($oeThemeSlug) : ''; ?>">
    <div id="left-controls-shield" aria-hidden="true"></div>
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
            <div class="sidebar-section-label">
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

        <details id="sidebar-section-date" class="filter-section" open>
            <summary><?php echo xlt('Date'); ?></summary>
            <div class="section-content">
                <input type="date" id="calendar-date-picker" class="form-control" style="width: 100%; padding: 5px; border: 1px solid #dee2e6; border-radius: 3px; font-size: 11px;">
            </div>
        </details>

        <details id="sidebar-section-providers" class="filter-section" open>
            <summary><?php echo xlt('Providers'); ?></summary>
            <div class="section-content">
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
        </details>

        <details id="sidebar-section-facilities" class="filter-section" open>
            <summary><?php echo xlt('Facilities'); ?></summary>
            <div class="section-content">
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
        </details>

        <?php if ($showReschedulerPanels): ?>
            <details id="sidebar-section-slot-states" class="filter-section" open>
                <summary><?php echo xlt('Availability'); ?></summary>
                <div class="section-content">
                    <div class="filter-bulk-actions">
                        <button type="button" class="filter-bulk-action" data-target-filter="slot-state-filter" data-action="select-all"><?php echo xlt('Select All'); ?></button>
                        <button type="button" class="filter-bulk-action" data-target-filter="slot-state-filter" data-action="clear-all"><?php echo xlt('Clear All'); ?></button>
                    </div>
                    <div class="checkbox-group" id="slot-state-filter">
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="filled" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge filled" title="<?php echo attr(xla('Filled')); ?>">FILLED</span></span></label>
                    <?php if ($reschedulerActiveForUI): ?>
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="open_not_reschedulable" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge open_not_reschedulable" title="<?php echo attr(xla($openStaffTitle)); ?>">OPEN</span></span></label>
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="open_reschedulable_available" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge open_reschedulable_available" title="<?php echo attr(xla($openPatientTitle)); ?>">Open-P</span></span></label>
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="held_staff" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge held_staff" title="<?php echo attr(xla('Held by staff')); ?>">HELD-S</span></span></label>
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="held_patient" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge held_patient" title="<?php echo attr(xla('Held by patient')); ?>">HELD-P</span></span></label>
                    <?php else: ?>
                    <label class="checkbox-label"><input type="checkbox" name="slot_states[]" value="open" checked><span class="slot-state-legend-item"><span class="slot-state-legend-badge open_not_reschedulable" title="<?php echo attr(xla('Open')); ?>">OPEN</span></span></label>
                    <?php endif; ?>
                    </div>
                </div>
            </details>

            <details id="sidebar-section-appointment-categories" class="filter-section" open>
                <summary><?php echo xlt('Appointment Types'); ?></summary>
                <div class="section-content">
                    <div class="filter-bulk-actions">
                        <button type="button" class="filter-bulk-action" data-target-filter="appointment-category-filter" data-action="select-all"><?php echo xlt('Select All'); ?></button>
                        <button type="button" class="filter-bulk-action" data-target-filter="appointment-category-filter" data-action="clear-all"><?php echo xlt('Clear All'); ?></button>
                    </div>
                    <div class="checkbox-group" id="appointment-category-filter">
                    <?php if (!empty($appointmentCategoryLegend)) : ?>
                        <?php foreach ($appointmentCategoryLegend as $legendItem) : ?>
                            <label class="checkbox-label appointment-category-item" title="<?php echo attr($legendItem['name']); ?>">
                                <input type="checkbox" name="appointment_categories[]" value="<?php echo attr((string)$legendItem['id']); ?>" checked>
                                <span class="appointment-category-swatch" style="background: <?php echo attr($legendItem['color']); ?>;"></span>
                                <span class="appointment-category-name"><?php echo text($legendItem['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="appointment-category-item">
                            <span class="appointment-category-name"><?php echo xlt('No template slot types found'); ?></span>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </details>
        <?php endif; ?>
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
    <div id="medex-modern-tooltip" role="tooltip" aria-hidden="true"></div>

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
            // Use the actual rendered .btn-primary background FIRST — themes like Manila
            // override .btn-primary (to #c9dbf2) but leave --primary pointing to Bootstrap's
            // default #007bff, so CSS vars give the wrong color for those themes.
            const accentFromButton = pickColor(['.btn-primary', '.navbar .active a', '.nav-tabs .active a', '.nav-link.active'], 'backgroundColor', '');
            const accentFromVar = pickVar(['--primary', '--primary-color', '--bs-primary'], '');
            const accent = accentFromButton || accentFromVar || '#c9dbf2';
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
            const host = document.getElementById('main-content');
            if (!host) {
                return;
            }

            const isFullscreen = document.fullscreenElement === host;

            if (isFullscreen) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                return;
            }

            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
            }

            if (host.requestFullscreen) {
                host.requestFullscreen().catch(() => {
                    // Ignore if browser blocks fullscreen request.
                });
            }
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

            let providerUserId = '';
            if (providerId && window.providerData && window.providerData[providerId] && window.providerData[providerId].id) {
                providerUserId = String(window.providerData[providerId].id);
            } else if (/^\d+$/.test(String(providerId))) {
                providerUserId = String(providerId);
            }

            const duration = parseInt(window.calendarInterval || '15', 10) || 15;
            const url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?date=' + encodeURIComponent(dateStr) +
                '&starttimeh=9&starttimem=00&userid=' + encodeURIComponent(providerUserId) + '&duration=' + encodeURIComponent(duration);

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
