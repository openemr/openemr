<?php

/**
 * MedEx Secure Chat Message Receiver
 * 
 * Receives messages from MedEx secure chat and stores them in OpenEMR's portal messaging system
 * Called via HTTP POST from MedEx RedisChat when dual-write is enabled
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ray Magauran
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Minimal bootstrap - skip full session/auth since this is an API endpoint
$ignoreAuth = true;
$sessionAllowWrite = false;

require_once(__DIR__ . "/../../../../globals.php");
require_once($GLOBALS['srcdir'] . "/patient.inc.php");
require_once(__DIR__ . "/../../.././../portal/lib/portal_mail.inc.php");
require_once(__DIR__ . '/../src/MedExHmac.php');

use OpenEMR\Modules\MedEx\MedExHmac;

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload']);
    exit;
}

// Validate required fields
$required = ['token', 'practice_id', 'pid', 'message', 'from', 'msg_uid'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
        exit;
    }
}

$token = $data['token'];
$practice_id = (int)$data['practice_id'];
$pid = $data['pid'];
$message = $data['message'];
$from = $data['from']; // 'PATIENT' or 'PROVIDER'
$msg_uid = $data['msg_uid'];

// Fetch expected API key
$sql = "SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ? LIMIT 1";
$result = sqlQuery($sql, [$practice_id]);

if (empty($result['ME_api_key'])) {
    error_log("[MedEx Chat Receiver] No API key configured for practice {$practice_id}");
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'API key not configured']);
    exit;
}

// HMAC validation — replay-safe, timing-safe
[$hmacOk, $hmacErr] = MedExHmac::validate($input, $result['ME_api_key']);
if (!$hmacOk) {
    error_log("[MedEx Chat Receiver] HMAC validation failed for practice {$practice_id}: {$hmacErr}");
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Authentication failed']);
    exit;
}

// Get patient data
$patientData = sqlQuery("SELECT fname, lname, pid as patient_pid FROM patient_data WHERE pid = ?", [$pid]);
if (!$patientData) {
    error_log("[MedEx Chat Receiver] Patient not found: pid={$pid}");
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Patient not found']);
    exit;
}

// Get practice/facility name for provider messages
$facilityData = sqlQuery("SELECT name FROM facility WHERE id = ?", [$practice_id]);
$facilityName = $facilityData['name'] ?? 'Practice';

// Construct message body with timestamp
$timestamp = date('Y-m-d H:i:s');
$patientName = $patientData['fname'] . ' ' . $patientData['lname'];

// Determine sender and recipient based on message direction
if ($from === 'PATIENT') {
    $sender_id = (string)$pid;
    $sender_name = $patientName;
    $recipient_id = 'facility_' . $practice_id;
    $recipient_name = $facilityName;
    $owner = (string)$pid; // Patient's portal username is usually their PID
} else {
    // Provider message
    $sender_id = 'facility_' . $practice_id;
    $sender_name = $facilityName;
    $recipient_id = (string)$pid;
    $recipient_name = $patientName;
    $owner = (string)$pid; // Still goes to patient's mailbox
}

$title = 'Secure Chat Message';
$message_status = 'New';

try {
    // Insert message into onsite_mail table
    $mail_id = addPortalMailboxMail(
        $owner,
        $message,
        '1', // authorized
        '1', // activity
        $title,
        '', // assigned_to
        $timestamp,
        $message_status,
        '0', // master_note (will auto-generate)
        $sender_id,
        $sender_name,
        $recipient_id,
        $recipient_name,
        0 // replyid
    );

    // Store reference to MedEx message UID for deduplication
    sqlInsert(
        "INSERT INTO medex_chat_sync (medex_msg_uid, openemr_mail_id, practice_id, pid, sync_date) 
         VALUES (?, ?, ?, ?, NOW())",
        [$msg_uid, $mail_id, $practice_id, $pid]
    );

    error_log("[MedEx Chat Receiver] Synced message msg_uid={$msg_uid} to mail_id={$mail_id} for pid={$pid}");

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'mail_id' => $mail_id,
        'synced_at' => $timestamp
    ]);
} catch (Exception $e) {
    error_log("[MedEx Chat Receiver] Error syncing message: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to sync message: ' . $e->getMessage()
    ]);
}
