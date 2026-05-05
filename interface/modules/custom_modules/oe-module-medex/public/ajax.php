<?php

/**
 * MedEx Module AJAX Endpoints
 * Handles recall board campaign status and LOB.com integration
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . "/../src/Services/ModalityService.php");
require_once(__DIR__ . "/../src/MedExAPI.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\Services\ModalityService;

// Ensure csrf_private_key exists — may be absent when arriving via MedEx SSO
// without going through the normal OpenEMR login flow.
if ($session && empty($session->get('csrf_private_key', null))) {
    CsrfUtils::setupCsrfKey($session);
}

// CSRF check
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

// ACL check - require calendar access for recall board features
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$entitlementApi = new MedExAPI();
if (!$entitlementApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Appointment reminders service is not enabled']);
    exit;
}

// MedEx validation - only proceed if properly configured with API key
$medexConfigured = false;
try {
    $medexPrefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs LIMIT 1", []);
    if (!empty($medexPrefs) && !empty($medexPrefs['ME_api_key'])) {
        $medexConfigured = true;
    }
} catch (\Exception $e) {
    // medex_prefs table doesn't exist - MedEx not configured
    $medexConfigured = false;
}

if (!$medexConfigured) {
    // Return empty campaigns array - no injection will occur
    http_response_code(200);
    echo json_encode([]);
    exit;
}

$action = $_POST['action'] ?? '';

/**
 * Get campaign status for multiple patients
 * Returns MedEx campaign tracking data for recall board injection
 */
if ($action === 'get_campaign_status') {
    $pids = json_decode($_POST['pids'] ?? '[]', true);
    $campaigns = [];

    $modalityService = new ModalityService();

    foreach ($pids as $pid) {
        // Get recall record
        $recall = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT r.*, p.* FROM patient_recalls r
             LEFT JOIN patient_data p ON r.r_pid = p.pid
             WHERE r.r_pid = ?",
            [$pid]
        );

        if (!$recall) {
            continue;
        }

        // Get MedEx events for this recall
        $events_sql = "SELECT * FROM medex_outgoing WHERE msg_pc_eid = ? ORDER BY msg_date DESC";
        $events_result = \OpenEMR\Common\Database\QueryUtils::fetchRecords($events_sql, ['recall_' . $pid]);

        // Get possible modalities (SMS/EMAIL/AVM permissions) - returns icon HTML
        $modalities = $modalityService->getPossibleModalities($recall);

        $campaign_data = [
            'pid' => $pid,
            'modalities' => $modalities,
            'events' => [],
            'statusClass' => 'whitish' // default
        ];

        // Process campaign events (only SMS/EMAIL/AVM - not manual actions like postcards/notes)
        $has_active_campaign = false;
        foreach ($events_result as $event) {
            // Only include actual MedEx campaign events (SMS/EMAIL/AVM)
            // Skip manual recall board actions (postcards, labels, notes, phone)
            if (!in_array($event['msg_type'], ['SMS', 'EMAIL', 'AVM'])) {
                continue;
            }

            $event_data = [
                'type' => $event['msg_type'],
                'status' => $event['msg_reply'],
                'date' => $event['msg_date'],
                'campaign_uid' => $event['campaign_uid']
            ];

            $campaign_data['events'][] = $event_data;

            // Determine row color based on campaign status
            if ($event['msg_reply'] === 'READ') {
                $campaign_data['statusClass'] = 'greenish';
            } elseif ($event['msg_reply'] === 'SENT' && $campaign_data['statusClass'] !== 'greenish') {
                $campaign_data['statusClass'] = 'yellowish';
            } elseif ($event['msg_reply'] === 'FAILED') {
                $campaign_data['statusClass'] = 'reddish';
            }

            $has_active_campaign = true;
        }

        // Always return data - modalities should show even without campaign events
        $campaigns[$pid] = $campaign_data;
    }

    header('Content-Type: application/json');
    echo json_encode($campaigns);
    exit;
}

/**
 * Toggle campaign on/off for a recall
 */
if ($action === 'toggle_campaign') {
    $pid = (int)($_POST['pid'] ?? 0);
    $type = $_POST['type'] ?? ''; // SMS, EMAIL, or AVM
    $enabled = $_POST['enabled'] === 'true' || $_POST['enabled'] === true;

    if (!in_array($type, ['SMS', 'EMAIL', 'AVM'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid campaign type']);
        exit;
    }

    if ($enabled) {
        // Enable campaign - create scheduled campaign event
        $sql = "INSERT INTO medex_outgoing (msg_pid, msg_type, msg_reply, msg_date, campaign_uid)
                VALUES (?, ?, 'SCHEDULED', NOW(), ?)";
        $campaign_uid = 'recall_' . $pid . '_' . strtolower($type);
        sqlStatement($sql, [$pid, $type, $campaign_uid]);
    } else {
        // Disable campaign - remove scheduled/pending campaigns
        $sql = "DELETE FROM medex_outgoing
                WHERE msg_pid = ? AND msg_type = ?
                AND msg_reply IN ('SCHEDULED', 'QUEUED')";
        sqlStatement($sql, [$pid, $type]);
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

/**
 * Get MedEx authentication token for SMS Bot
 * Returns token and server info for opening SMS Bot.
 * Uses MedExAPI (api/oemr/login) — the module's standard auth path.
 */
if ($action === 'get_medex_token') {
    try {
        require_once(__DIR__ . '/../src/MedExAPI.php');
        require_once(__DIR__ . '/../src/MedExConfig.php');

        $medexApi  = new \OpenEMR\Modules\MedEx\MedExAPI();
        $loginData = $medexApi->login(true);           // force-refresh token
        $token     = $loginData['token'] ?? null;

        if (empty($token)) {
            throw new \RuntimeException('MedEx login returned no token');
        }

        // Public URL for browser-facing redirect (rewrites k8s internal DNS)
        $publicUrl       = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl();
        $redirectServer  = preg_replace('#/cart/upload/?$#', '', $publicUrl);

        // Provider UID
        $providerUID = null;
        $currentUser = $_SESSION['authUserID'] ?? null;
        if ($currentUser) {
            $userInfo    = sqlQuery("SELECT username FROM users WHERE id = ?", [$currentUser]);
            $providerUID = $userInfo['username'] ?? null;
        }

        // Phone style
        $prefs      = sqlQuery("SELECT sms_bot_phone_style FROM medex_prefs LIMIT 1");
        $phoneStyle = $prefs['sms_bot_phone_style'] ?? 'S8';
        $allowedPhoneStyles = ['S8', 'iPhone14', 'iPhone4', 'Pixel8', 'minimal'];
        if (!in_array($phoneStyle, $allowedPhoneStyles)) {
            $phoneStyle = 'S8';
        }

        $response = [
            'success'      => true,
            'token'        => $token,
            'display'      => '',
            'server'       => $redirectServer,
            'provider_uid' => $providerUID,
            'phone_style'  => $phoneStyle,
        ];
    } catch (\Exception $e) {
        $response = [
            'success' => false,
            'error'   => 'Error connecting to MedEx: ' . $e->getMessage(),
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Send postcard/label via LOB.com
 * TODO: Implement actual LOB.com API integration
 */
if ($action === 'send_via_lob') {
    $pid = (int)($_POST['pid'] ?? 0);
    $type = $_POST['type'] ?? '';

    // TODO: Implement LOB.com API call
    // For now, return placeholder response

    $response = [
        'success' => false,
        'error' => 'LOB.com integration not yet implemented'
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Unknown action
http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
exit;
