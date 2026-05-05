<?php
/**
 * FullCalendar JS Proxy
 * Serves FullCalendar from the writable documents directory
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . "/../../src/MedExAPI.php");

use OpenEMR\Common\Session\SessionWrapperFactory;

// Security: require authenticated user with an active calendar_full subscription
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
    http_response_code(403);
    exit;
}

$_fcAllowed = false;
try {
    $fcApi = new \OpenEMR\Modules\MedEx\MedExAPI();
    $_fcAllowed = $fcApi->hasServiceEntitlement('calendar_full');
} catch (\Throwable $e) { /* silent */ }

if (!$_fcAllowed) {
    http_response_code(403);
    header('Content-Type: application/javascript');
    echo '// Access denied: calendar_full subscription required';
    exit;
}

// Path to FullCalendar in writable documents directory
$siteId = $_SESSION['site_id'] ?? null;
if (empty($siteId) && $sessionWrapper) {
    $siteId = $sessionWrapper->get('site_id') ?: $sessionWrapper->get('site_id');
}
if (empty($siteId)) {
    $siteId = 'default';
}
$fullCalendarPath = $GLOBALS['OE_SITES_BASE'] . "/$siteId/documents/MedEx/fullCalendar/fullcalendar.min.js";

// Check if file exists
if (!file_exists($fullCalendarPath)) {
    // Try to download it if it doesn't exist
    $calendarDir = dirname($fullCalendarPath);
    if (!is_dir($calendarDir)) {
        mkdir($calendarDir, 0755, true);
    }

    // Download FullCalendar
    $url = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js';
    $content = file_get_contents($url);

    if ($content === false) {
        http_response_code(404);
        echo "// FullCalendar not found and could not download";
        exit;
    }

    file_put_contents($fullCalendarPath, $content);
}

// Serve the JavaScript file
header('Content-Type: application/javascript');
header('Cache-Control: public, max-age=86400'); // Cache for 1 day

readfile($fullCalendarPath);
