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

// Check calendar access
if (!AclMain::aclCheckCore('patients', 'appt')) {
    die('Access denied. You do not have permission to access the calendar.');
}

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
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
    die('MedEx not configured. Please configure MedEx in <a href="../../admin/settings.php">Settings</a>.');
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

$userId = $_SESSION['authUserID'] ?? null;
$userAuthorized = $_SESSION['userauthorized'] ?? 0;

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
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('MedEx Full Calendar'); ?></title>

    <!-- FullCalendar v6 (local from writable documents directory) -->
    <script src='<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/assets/fullcalendar.php'></script>
    <script>
        console.log('FullCalendar script tag loaded, checking if library exists...');
        console.log('typeof FullCalendar:', typeof FullCalendar);
        // Pass webroot and schedule times to calendar.js
        window.webroot = '<?php echo $GLOBALS['webroot']; ?>';
        window.scheduleStart = '<?php echo $GLOBALS['schedule_start'] ?? '8'; ?>';
        window.scheduleEnd = '<?php echo $GLOBALS['schedule_end'] ?? '17'; ?>';
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
    </script>

    <!-- Calendar initialization -->
    <script src='<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/calendar/calendar.js' type="text/javascript"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: #f8f9fa;
        }

        /* Sidebar styling */
        #sidebar {
            width: 220px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 15px 10px;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
            position: relative;
            z-index: 100;
        }
        #sidebar.hidden {
            margin-left: -220px;
        }
        #sidebar h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: bold;
            color: #333;
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
            border: 1px solid #dee2e6;
            border-radius: 3px;
            background: white;
        }
        #sidebar .checkbox-group::-webkit-scrollbar {
            width: 8px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb {
            background: #0099CC;
            border-radius: 3px;
        }
        #sidebar .checkbox-group::-webkit-scrollbar-thumb:hover {
            background: #007799;
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
            background: #E8F4F8;
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
            border: 2px solid #0099CC;
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
            background: #0099CC;
            color: white;
        }
        #sidebar .checkbox-label:has(input[type="checkbox"]:checked)::before {
            background: #0099CC;
            border-color: #0099CC;
        }
        #sidebar .btn,
        #sidebar .switch-section .btn {
            width: 100%;
            padding: 8px;
            font-size: 11px;
            background: #2C3E50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        #sidebar .btn:hover,
        #sidebar .switch-section .btn:hover {
            background: #34495e;
        }

        /* View selector styling */
        .view-selector .view-option:not(.active):hover {
            background: #E8F4F8 !important;
        }

        /* Toggle button */
        #sidebar-toggle {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 60px;
            background: #2C3E50;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            z-index: 99;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: left 0.3s ease;
        }
        #sidebar-toggle.sidebar-visible {
            left: 220px;
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
        #sidebar-toggle:hover {
            background: #34495e;
        }

        /* Main content area */
        #main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: auto;
            padding: 10px;
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
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .provider-calendar-wrapper h3 {
            margin: 0;
            padding: 10px 15px;
            background: #0099CC;
            color: white;
            border-radius: 5px 5px 0 0;
            font-size: 13px;
            font-weight: 600;
        }

        /* Make event text more readable */
        .fc-event {
            cursor: pointer;
        }
        .fc-event-title {
            color: #000 !important;
            font-weight: 500;
        }
        .fc-event-time {
            color: #333 !important;
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
            color: #000 !important;
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
        }

        .fc-timegrid-slot-label {
            font-size: 11px !important;
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
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .fc-toolbar-chunk {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .fc-button-group {
            display: inline-flex;
            gap: 0;
        }
        .fc-header-toolbar .fc-button {
            background: #2C3E50;
            color: white;
            border: 1px solid #1a252f;
            padding: 4px 10px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            font-family: Arial, sans-serif;
        }
        .fc-header-toolbar .fc-button:hover {
            background: #34495e;
        }
        .fc-header-toolbar .fc-button:active,
        .fc-header-toolbar .fc-button-active {
            background: #1a252f;
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
            border-color: #e0e0e0 !important;
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
    </style>
</head>
<body>
    <!-- Sidebar toggle button -->
    <button id="sidebar-toggle" class="sidebar-visible" onclick="toggleSidebar()">
        <span id="toggle-icon">◄</span>
    </button>

    <!-- Sidebar -->
    <div id="sidebar">
        <!-- View switcher at top -->
        <div style="margin-bottom: 15px;">
            <div style="font-size: 10px; color: #666; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px;">
                <?php echo xlt('Calendar View'); ?>
            </div>
            <div class="view-selector" style="display: flex; flex-direction: column; gap: 1px; border: 1px solid #0099CC; border-radius: 3px; overflow: hidden;">
                <button class="view-option active" style="padding: 8px; font-size: 11px; background: #0099CC; color: white; border: none; cursor: default; text-align: left; font-weight: 500; border-bottom: 1px solid #0099CC;">
                    <?php echo xlt('Full Calendar'); ?>
                </button>
                <button class="view-option" onclick="switchToOpenEMRCalendar()" style="padding: 8px; font-size: 11px; background: white; color: #0099CC; border: none; border-top: 1px solid #0099CC; cursor: pointer; transition: background 0.2s; text-align: left;">
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
                    $checked = ($provider['username'] == $userProviderUsername) ? ' checked' : '';
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
                    $checked = ($facility['id'] == $userFacilityId) ? ' checked' : '';
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

    <script type="text/javascript">
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebar-toggle');
            const icon = document.getElementById('toggle-icon');

            sidebar.classList.toggle('hidden');
            toggle.classList.toggle('sidebar-visible');

            if (sidebar.classList.contains('hidden')) {
                icon.textContent = '►';
            } else {
                icon.textContent = '◄';
            }

            // Trigger calendar resize after animation
            setTimeout(() => {
                calendarInstances.forEach(cal => cal.updateSize());
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
            let savedDate = localStorage.getItem('medexCalendarDate');

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
            let url = '<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&func=view&viewtype=' + viewtype + '&medex_prefer=openemr';

            // Add date (OpenEMR uses jumpdate parameter in YYYY-MM-DD format)
            if (savedDate) {
                url += '&jumpdate=' + encodeURIComponent(savedDate);
            }

            // Add selected providers (OpenEMR uses pc_username[] array notation for multiple)
            if (selectedProviders.length > 0) {
                selectedProviders.forEach(providerId => {
                    url += '&pc_username[]=' + encodeURIComponent(providerId);
                });
            }

            // Add facility (OpenEMR calendar typically shows one facility at a time)
            if (selectedFacilities.length > 0) {
                url += '&pc_facility=' + encodeURIComponent(selectedFacilities[0]);
            }

            console.log('[MedEx] Switching to OpenEMR calendar with params:', {viewtype, date: savedDate, providers: selectedProviders, facilities: selectedFacilities});
            console.log('[MedEx] URL:', url);

            // Restore session first so OpenEMR's session token remains valid across navigation.
            // Wait for session restoration to complete before navigating
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
                top.restoreSession();
                // Give session restoration time to complete
                setTimeout(function() {
                    window.location.href = url;
                }, 100);
            } else {
                window.location.href = url;
            }
        }

        // Note: restoreFilterSelections() is now defined in calendar.js and called on DOMContentLoaded

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
