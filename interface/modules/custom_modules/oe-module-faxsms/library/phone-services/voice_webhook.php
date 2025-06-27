<?php

/**
 * Web Hook for RingCentral Voice Events
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

error_log("RingCentral webhook accessed at " . date('Y-m-d H:i:s'));

// Handle RingCentral validation token
$validationToken = $_SERVER['HTTP_VALIDATION_TOKEN'] ?? '';
if (!empty($validationToken)) {
    header("Validation-Token: {$validationToken}");
    header('Content-Type: text/plain');
    echo $validationToken;
    http_response_code(200);
    error_log("RingCentral validation token returned: {$validationToken}");
    exit;
}

// Security: Verify webhook token
$ignoreAuth = true; // Ignore OpenEMR authentication for this webhook
require_once(__DIR__ . '/../../../../../globals.php');
$expectedToken = sqlQuery(
    "SELECT gl_value FROM globals WHERE gl_name = 'ringcentral_voice_token'"
)['gl_value'] ?? '';

$providedToken = $_GET['token'] ?? '';
if (empty($expectedToken) || $providedToken !== $expectedToken) {
    error_log("RingCentral webhook: Invalid or missing token");
    http_response_code(403);
    exit('Forbidden');
}

// Read and parse webhook payload
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("RingCentral webhook: Invalid JSON payload");
    http_response_code(400);
    exit('Bad Request');
}

// Process the event
try {
    processRingCentralEvent($data);
} catch (Exception $e) {
    error_log("RingCentral webhook processing error: " . $e->getMessage());
    // Still return 200 to prevent RingCentral from retrying
}

http_response_code(200);
exit('OK');

/**
 * Process RingCentral webhook event
 */
function processRingCentralEvent($event): void
{
    // The event type is in the "event" field, not "eventType"
    $eventType = $event['event'] ?? 'unknown';

    // Check if this is a telephony session event by pattern matching
    if (preg_match('#^/restapi/v1\.0/account/\d+/extension/\d+/telephony/sessions$#', $eventType)) {
        handleTelephonySession($event);
    } elseif (preg_match('#^/restapi/v1\.0/account/\d+/extension/\d+/presence$#', $eventType)) {
        handlePresenceEvent($event);
    } elseif (preg_match('#^/restapi/v1\.0/account/\d+/extension/\d+/message-store$#', $eventType)) {
        handleMessageStore($event);
    } elseif (preg_match('#^/restapi/v1\.0/account/\d+/extension/\d+/recording$#', $eventType)) {
        handleRecording($event);
    } else {
        error_log("RingCentral: Unhandled event type: {$eventType}");
    }
}

/**
 * Handle telephony session events (incoming/outgoing calls)
 */
function handleTelephonySession($event): void
{
    // The session data is directly in the "body" field
    $sessionData = $event['body'] ?? [];
    $parties = $sessionData['parties'] ?? [];
    $sessionId = $sessionData['sessionId'] ?? '';
    $telephonySessionId = $sessionData['telephonySessionId'] ?? '';
    $eventTime = $sessionData['eventTime'] ?? '';

    error_log("Processing telephony session: {$sessionId} ({$telephonySessionId})");

    foreach ($parties as $party) {
        $direction = $party['direction'] ?? '';
        $status = $party['status']['code'] ?? '';
        $fromNumber = $party['from']['phoneNumber'] ?? '';
        $fromName = $party['from']['name'] ?? '';
        $toNumber = $party['to']['phoneNumber'] ?? '';
        $toName = $party['to']['name'] ?? '';
        $partyId = $party['id'] ?? '';
        $extensionId = $party['extensionId'] ?? '';

        error_log("Call Event - Direction: {$direction}, Status: {$status}, From: {$fromNumber} ({$fromName}), To: {$toNumber} ({$toName}), Party: {$partyId}");

        // Handle specific call states
        switch ($status) {
            case 'Setup':
                handleIncomingCall($fromNumber, $toNumber, $sessionId, $direction);
                break;
            case 'Proceeding':
                handleCallInProgress($sessionId, $partyId);
                break;
            case 'Answered':
                handleCallAnswered($sessionId, $partyId);
                break;
            case 'Disconnected':
                handleCallEnded($sessionId, $partyId);
                break;
            case 'Hold':
                handleCallOnHold($sessionId, $partyId);
                break;
            case 'Unhold':
                handleCallOffHold($sessionId, $partyId);
                break;
            default:
                error_log("Unhandled call status: {$status}");
        }
    }
}

/**
 * Handle presence events (call state changes)
 */
function handlePresenceEvent($event): void
{
    $presenceData = $event['body'] ?? [];
    $telephonyStatus = $presenceData['telephonyStatus'] ?? '';
    $activeCalls = $presenceData['activeCalls'] ?? [];

    error_log("Presence Event - Telephony Status: {$telephonyStatus}, Active Calls: " . count($activeCalls));

    foreach ($activeCalls as $call) {
        $direction = $call['direction'] ?? '';
        $fromNumber = $call['from'] ?? '';
        $toNumber = $call['to'] ?? '';
        $sessionId = $call['sessionId'] ?? '';

        error_log("Active Call - Direction: {$direction}, From: {$fromNumber}, To: {$toNumber}");
    }
}

/**
 * Handle message store events (voicemail, fax)
 */
function handleMessageStore($event): void
{
    $messageData = $event['body'] ?? [];
    $messageType = $messageData['type'] ?? '';
    $messageId = $messageData['id'] ?? '';
    $fromNumber = $messageData['from']['phoneNumber'] ?? '';

    error_log("Message Event - Type: {$messageType}, ID: {$messageId}, From: {$fromNumber}");

    switch ($messageType) {
        case 'VoiceMail':
            handleNewVoicemail($messageId, $fromNumber, $messageData);
            break;
        case 'Fax':
            handleNewFax($messageId, $fromNumber, $messageData);
            break;
    }
}

/**
 * Handle recording events
 */
function handleRecording($event): void
{
    $recordingData = $event['body'] ?? [];
    $recordingId = $recordingData['id'] ?? '';
    $status = $recordingData['status'] ?? '';

    error_log("Recording Event - ID: {$recordingId}, Status: {$status}");

    if ($status === 'Completed') {
        downloadAndStoreRecording($recordingId);
    }
}

/**
 * Handle incoming call setup
 */
function handleIncomingCall($fromNumber, $toNumber, $sessionId, $direction): void
{
    if ($direction === 'Inbound') {
        error_log("Processing incoming call from {$fromNumber} to {$toNumber}");

        // Look up patient by phone number
        $patient = sqlQuery(
            "SELECT * FROM patient_data WHERE phone_home = ? OR phone_cell = ? OR phone_biz = ?",
            [$fromNumber, $fromNumber, $fromNumber]
        );

        if ($patient) {
            error_log("Incoming call from known patient: " . $patient['fname'] . ' ' . $patient['lname']);
            // Could create automatic encounter, send notification to provider, etc.
        } else {
            error_log("Incoming call from unknown number: {$fromNumber}");
        }
    } else {
        error_log("Processing outgoing call from {$fromNumber} to {$toNumber}");
    }
}

/**
 * Handle call in progress
 */
function handleCallInProgress($sessionId, $partyId): void
{
    error_log("Call {$sessionId} (party {$partyId}) is proceeding");
    // Update call status, prepare for recording if needed, etc.
}

/**
 * Handle call answered
 */
function handleCallAnswered($sessionId, $partyId): void
{
    error_log("Call {$sessionId} (party {$partyId}) was answered");
    // Start call timer, begin recording if configured, etc.
}

/**
 * Handle call ended
 */
function handleCallEnded($sessionId, $partyId): void
{
    error_log("Call {$sessionId} (party {$partyId}) has ended");
    // Finalize call record, stop recording, calculate duration, etc.
}

/**
 * Handle call on hold
 */
function handleCallOnHold($sessionId, $partyId): void
{
    error_log("Call {$sessionId} (party {$partyId}) placed on hold");
}

/**
 * Handle call off hold
 */
function handleCallOffHold($sessionId, $partyId): void
{
    error_log("Call {$sessionId} (party {$partyId}) taken off hold");
}

/**
 * Handle new voicemail
 */
function handleNewVoicemail($messageId, $fromNumber, $messageData): void
{
    error_log("New voicemail from {$fromNumber}, message ID: {$messageId}");

    // Store voicemail information
    try {
        sqlStatement(
            "INSERT INTO ringcentral_voicemails (message_id, from_number, received_date, raw_data) VALUES (?, ?, NOW(), ?)",
            [$messageId, $fromNumber, json_encode($messageData)]
        );
    } catch (Exception $e) {
        error_log("Failed to store voicemail: " . $e->getMessage());
    }
}

/**
 * Handle new fax
 */
function handleNewFax($messageId, $fromNumber, $messageData): void
{
    error_log("New fax from {$fromNumber}, message ID: {$messageId}");
    // Process fax, store in document management, etc.
}

/**
 * Download and store recording
 */
function downloadAndStoreRecording($recordingId): void
{
    error_log("Processing completed recording: {$recordingId}");
    // Could download recording file and store it in OpenEMR's document system
}
