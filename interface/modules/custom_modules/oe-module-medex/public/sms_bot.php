<?php

/**
 * MedEx SMS Bot Interface
 * Displays MedEx SMS communication interface via API
 * Fixed: Proper authentication flow and phone style handling
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

require_once("../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\MedEx\MedExAPI;

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_GET['csrf_token_form'] ?? '', 'default')) {
    CsrfUtils::csrfNotVerified(true, true, false);
}

// Get patient ID
$pid = $_REQUEST['pid'] ?? null;

if (empty($pid)) {
    die("Error: Patient ID required");
}

// Initialize MedEx API
$medexApi = new MedExAPI();

// Server-authoritative entitlement check (do not trust stale local cache)
if (!$medexApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    echo "<html><body>";
    echo "<h3>MedEx Service Not Enabled</h3>";
    echo "<p>SMS Bot requires an active subscription.</p>";
    echo "</body></html>";
    exit;
}

// Check if MedEx is configured and enabled
if (!$medexApi->isActive()) {
    echo "<html><body>";
    echo "<h3>MedEx Not Available</h3>";
    if (!$medexApi->isEnabled()) {
        echo "<p>MedEx is disabled. Enable it in Global Configuration.</p>";
    } else {
        echo "<p>MedEx is not configured. Please check your credentials in <a href='" . $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-medex/admin/settings.php'>MedEx Settings</a>.</p>";
    }
    echo "</body></html>";
    exit;
}

// Get authenticated session token
try {
    $sessionData = $medexApi->login();
    if (empty($sessionData['token'])) {
        throw new \Exception('No session token received');
    }
} catch (\Exception $e) {
    echo "<html><body>";
    echo "<h3>MedEx Authentication Failed</h3>";
    echo "<p>Please check your MedEx credentials in <a href='" . $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-medex/admin/settings.php'>MedEx Settings</a>.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
    exit;
}

// Prepare request fields
$fields = $_REQUEST;
$fields['pid'] = $pid;
$fields['openemr_webroot'] = $GLOBALS['web_root'];

// Get phone style preference from medex_prefs with validation
$prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT sms_bot_phone_style FROM medex_prefs LIMIT 1", []);
$allowedPhoneStyles = ['S8', 'iPhone14', 'iPhone4', 'Pixel8', 'minimal'];
$phoneStyle = $prefs['sms_bot_phone_style'] ?? 'S8';
if (!in_array($phoneStyle, $allowedPhoneStyles)) {
    $phoneStyle = 'S8';
}
$fields['phone_style'] = $phoneStyle;

// Get current user info for provider mapping
$currentUser = $_SESSION['authUserID'] ?? null;
if ($currentUser) {
    $userInfo = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT username FROM users WHERE id = ?", [$currentUser]);
    $fields['P_UID'] = $userInfo['username'] ?? null;
}

// Sync patient data with MedEx if needed
if (!empty($pid) && $pid !== 'find') {
    try {
        $medexApi->syncPatient($pid);
    } catch (\Exception $e) {
        error_log("MedEx patient sync failed: " . $e->getMessage());
        // Continue anyway - SMS bot can still work
    }
}

// Build correct API endpoint for SMS bot
$apiEndpoint = 'index.php?route=information/SmsHub' . 
               '&token=' . urlencode($sessionData['token']) . 
               '&r=' . urlencode($sessionData['display'] ?? '') .
               '&pid=' . urlencode($pid);

if (!empty($fields['phone_style'])) {
    $apiEndpoint .= '&phone_style=' . urlencode($fields['phone_style']);
}
if (!empty($fields['P_UID'])) {
    $apiEndpoint .= '&P_UID=' . urlencode($fields['P_UID']);
}

// Call MedEx API to get SMS Bot HTML
try {
    $response = $medexApi->makeRequest($apiEndpoint, $fields, 'GET');
    
    if (isset($response['html']) || isset($response['success'])) {
        // Display SMS Bot interface from MedEx
        echo $response['html'] ?? $response['success'];
    } elseif (isset($response['error'])) {
        echo "<html><body>";
        echo "<h3>Error Loading SMS Bot</h3>";
        echo "<p>" . htmlspecialchars($response['error']) . "</p>";
        echo "</body></html>";
    } else {
        echo "<html><body>";
        echo "<h3>Error Loading SMS Bot</h3>";
        echo "<p>Unexpected response from MedEx API.</p>";
        echo "<p>Debug: " . htmlspecialchars(json_encode($response)) . "</p>";
        echo "</body></html>";
    }
} catch (\Exception $e) {
    echo "<html><body>";
    echo "<h3>Connection Error</h3>";
    echo "<p>Failed to connect to MedEx server: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
}
