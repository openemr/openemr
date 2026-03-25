<?php

/**
 * AJAX endpoint for fetching MedEx campaign status for patient tracker appointments
 *
 * Returns campaign status icons HTML for a specific appointment
 * Uses the original MedEx icon building logic from patient_tracker.php
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\MedEx\MedExAPI;

$globalsBag = OEGlobalsBag::getInstance();
require_once($globalsBag->get('srcdir') . '/appointments.inc.php');

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Verify user has access to patient data
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$entitlementApi = new MedExAPI();
if (!$entitlementApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Appointment reminders service is not enabled']);
    exit;
}

// Get parameters
$eid = $_POST['eid'] ?? null;
$pid = $_POST['pid'] ?? null;
$date = $_POST['date'] ?? null;

if (!$eid || !$pid || !$date) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Check if MedEx is enabled and configured
$medexEnabled = $globalsBag->get('medex_enable') ?? '0';
if ($medexEnabled != '1') {
    echo json_encode(['success' => false, 'error' => 'MedEx not enabled']);
    exit;
}

// Load MedEx icons from database (same as original code)
$icons = [];
$query2 = "SELECT * FROM medex_icons";
$iconRows = QueryUtils::fetchRecords($query2);
foreach ($iconRows as $icon) {
    $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
}

// Query MedEx outgoing messages for this appointment
$query = "SELECT * FROM medex_outgoing WHERE msg_pc_eid = ? ORDER BY medex_uid ASC";
$medexRows = QueryUtils::fetchRecords($query, [$eid]);

// Initialize variables (same as original code)
$icon_here = [];
$icon2_here = '';
$icon_extra = '';
$icon_4_CALL = '';
$appointment = [];

// Build icons using original logic
$patientMessages = []; // Collect patient messages for speech bubble
foreach ($medexRows as $row) {
    if ($row['msg_reply'] == 'Other') {
        // Collect patient message for speech bubble display
        $patientMessages[] = [
            'date' => oeFormatShortDate($row['msg_date']),
            'text' => $row['msg_extra_text']
        ];
        continue;
    } elseif ($row['msg_reply'] == 'CANCELLED') {
        $appointment[$row['msg_type']]['stage'] = "CANCELLED";
        $icon_here[$row['msg_type']] = '';
    } elseif ($row['msg_reply'] == "FAILED") {
        $appointment[$row['msg_type']]['stage'] = "FAILED";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'] ?? '';
    } elseif (($row['msg_reply'] == "CONFIRMED") || ($appointment[$row['msg_type']]['stage'] ?? '') == "CONFIRMED") {
        $appointment[$row['msg_type']]['stage'] = "CONFIRMED";
        $icon_here[$row['msg_type']]  = $icons[$row['msg_type']]['CONFIRMED']['html'] ?? '';
    } elseif (($row['msg_reply'] == "READ") || ($appointment[$row['msg_type']]['stage'] ?? '') == "READ") {
        $appointment[$row['msg_type']]['stage'] = "READ";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'] ?? '';
    } elseif (($row['msg_reply'] == "SENT") || ($appointment[$row['msg_type']]['stage'] ?? '') == "SENT") {
        $appointment[$row['msg_type']]['stage'] = "SENT";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'] ?? '';
    } elseif (($row['msg_reply'] == "To Send") || empty($appointment[$row['msg_type']]['stage'] ?? '')) {
        if (!in_array($appointment[$row['msg_type']]['stage'] ?? '', ["CONFIRMED", "READ", "SENT", "FAILED"])) {
            $appointment[$row['msg_type']]['stage'] = "QUEUED";
            $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'] ?? '';
        }
    }

    // Additional icons
    if ($row['msg_reply'] == "CALL") {
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['CALL']['html'] ?? '';
        // Check if patient allows SMS for the CALL icon
        $patQuery = "SELECT allow_sms, phone_cell FROM patient_data WHERE pid = ?";
        $patData = QueryUtils::querySingleRow($patQuery, [$row['msg_pid']]);
        if (($patData['allow_sms'] ?? '') != "NO" && !empty($patData['phone_cell'])) {
            $icon_4_CALL = "<span class='input-group-addon' onclick='SMS_bot(" . attr_js($row['msg_pid']) . ");'><i class='fas fa-sms'></i></span>";
        }
    } elseif ($row['msg_reply'] == "STOP") {
        $icon2_here .= $icons[$row['msg_type']]['STOP']['html'] ?? '';
    } elseif ($row['msg_reply'] == "Other") {
        $icon2_here .= $icons[$row['msg_type']]['Other']['html'] ?? '';
    }
}

// Build patient message speech bubble if there are any messages
if (!empty($patientMessages)) {
    $messageText = '';
    foreach ($patientMessages as $msg) {
        $messageText .= '📅 ' . $msg['date'] . "\n";
        $messageText .= $msg['text'] . "\n\n";
    }
    $messageText = trim($messageText);

    // Create speech bubble tooltip icon
    $icon_extra = '<span class="medex-patient-message-icon" data-tooltip="' . attr($messageText) . '" style="margin-left:5px;position:relative;">
                        <i class="fas fa-comment-dots fa-2x" style="color:#9C27B0;"></i>
                   </span>';
}

// Combine campaign icons (same as original: implode icon_here array + icon2_here + icon_extra + icon_4_CALL)
$campaignHtml = implode('', $icon_here) . $icon2_here . $icon_extra . $icon_4_CALL;

// Build possibleModalities icons (SMS/AVM/EMAIL capability for this patient)
// These show what contact methods are available, matching original possibleModalities() logic
$modalityHtml = '';
$patQuery = "SELECT phone_cell, phone_home, email, hipaa_allowsms, hipaa_voice, hipaa_allowemail FROM patient_data WHERE pid = ?";
$patData = QueryUtils::fetchRecords($patQuery, [$pid]);
if (!empty($patData)) {
    $pat = $patData[0];
    $modIcons = [];

    // SMS
    if (empty($pat['phone_cell']) || ($pat['hipaa_allowsms'] ?? '') === 'NO') {
        $modIcons['SMS'] = $icons['SMS']['NotAllowed']['html'] ?? '';
    } else {
        $modIcons['SMS'] = $icons['SMS']['ALLOWED']['html'] ?? '';
    }

    // AVM (voice)
    if ((empty($pat['phone_home']) && empty($pat['phone_cell'])) || ($pat['hipaa_voice'] ?? '') === 'NO') {
        $modIcons['AVM'] = $icons['AVM']['NotAllowed']['html'] ?? '';
    } else {
        $modIcons['AVM'] = $icons['AVM']['ALLOWED']['html'] ?? '';
    }

    // EMAIL
    if (empty($pat['email']) || ($pat['hipaa_allowemail'] ?? '') === 'NO') {
        $modIcons['EMAIL'] = $icons['EMAIL']['NotAllowed']['html'] ?? '';
    } else {
        $modIcons['EMAIL'] = $icons['EMAIL']['ALLOWED']['html'] ?? '';
    }

    $modalityHtml = implode('', $modIcons);
}

// Return JSON response with both campaign icons and modality icons
// JS decides which to show based on context (campaign icons take priority)
echo json_encode([
    'success' => true,
    'html' => $campaignHtml,
    'modalities' => $modalityHtml
]);
