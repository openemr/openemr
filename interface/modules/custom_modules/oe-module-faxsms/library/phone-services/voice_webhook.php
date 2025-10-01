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

local_log("RingCentral webhook accessed at " . date('Y-m-d H:i:s'));

// Handle RingCentral validation token
$validationToken = $_SERVER['HTTP_VALIDATION_TOKEN'] ?? '';
if (!empty($validationToken)) {
    header("Validation-Token: {$validationToken}");
    header('Content-Type: text/plain');
    echo $validationToken;
    http_response_code(200);
    local_log("RingCentral validation token returned: {$validationToken}");
    exit;
}

// Security: Verify webhook token
$ignoreAuth = true; // Ignore OpenEMR authentication for this webhook
require_once(__DIR__ . '/../../../../../globals.php');

$expectedToken = $_SESSION['ringcentral_voice_token'] ?? '';
$providedToken = $_GET['token'] ?? '';
if (empty($expectedToken) || $providedToken !== $expectedToken) {
    local_log("RingCentral webhook: Invalid or missing token");
    http_response_code(403);
    exit('Forbidden');
}

// Read and parse webhook payload
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    local_log("RingCentral webhook: Invalid JSON payload");
    http_response_code(400);
    exit('Bad Request');
}

// Log the event for debugging
//local_log("RingCentral webhook event received: " . json_encode($data, JSON_PRETTY_PRINT));

// Process the event
try {
    processRingCentralEvent($data);
} catch (Exception $e) {
    local_log("RingCentral webhook processing error: " . $e->getMessage());
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
        local_log("RingCentral: Unhandled event type: {$eventType}");
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

    local_log("Processing telephony session: {$sessionId} ({$telephonySessionId})");

    foreach ($parties as $party) {
        $direction = $party['direction'] ?? '';
        $status = $party['status']['code'] ?? '';
        $fromNumber = $party['from']['phoneNumber'] ?? '';
        $fromName = $party['from']['name'] ?? '';
        $toNumber = $party['to']['phoneNumber'] ?? '';
        $toName = $party['to']['name'] ?? '';
        $partyId = $party['id'] ?? '';
        $extensionId = $party['extensionId'] ?? '';

        local_log("Call Event - Direction: {$direction}, Status: {$status}, From: {$fromNumber} ({$fromName}), To: {$toNumber} ({$toName}), Party: {$partyId}");

        // Store call information in database
        // Uncomment the following line to enable database storage
        // Note: Ensure the ringcentral_call_events table has all necessary columns
        // This is commented out to prevent database writes in this example
        /*storeCallEvent([
            'session_id' => $sessionId,
            'telephony_session_id' => $telephonySessionId,
            'party_id' => $partyId,
            'extension_id' => $extensionId,
            'direction' => $direction,
            'status' => $status,
            'from_number' => $fromNumber,
            'from_name' => $fromName,
            'to_number' => $toNumber,
            'to_name' => $toName,
            'event_time' => $eventTime,
            'timestamp' => date('Y-m-d H:i:s'),
            'raw_data' => json_encode($event)
        ]);*/
/*
TODO: Add to modules upgrade and table.sql in module the following SQL to create the ringcentral_call_events table
 * and ringcentral_voicemails table if they do not exist.
 * Make sure to run this SQL in your OpenEMR database setup script or manually.
 *
CREATE TABLE IF NOT EXISTS `ringcentral_call_events` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `session_id` varchar(255) DEFAULT NULL,
     `direction` varchar(20) DEFAULT NULL,
     `status` varchar(50) DEFAULT NULL,
     `from_number` varchar(20) DEFAULT NULL,
     `to_number` varchar(20) DEFAULT NULL,
     `timestamp` datetime DEFAULT NULL,
     `raw_data` text DEFAULT NULL,
     `telephony_session_id` varchar(255) DEFAULT NULL,
     `party_id` varchar(255) DEFAULT NULL,
     `extension_id` varchar(255) DEFAULT NULL,
     `from_name` varchar(255) DEFAULT NULL,
     `to_name` varchar(255) DEFAULT NULL,
     `event_time` datetime DEFAULT NULL,
     PRIMARY KEY (`id`),
     UNIQUE KEY `session_id` (`session_id`),
     KEY `idx_session_id` (`session_id`),
     KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB COMMENT='RingCentral Call Events';

CREATE TABLE IF NOT EXISTS `ringcentral_voicemails` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id VARCHAR(255) UNIQUE,
    from_number VARCHAR(20),
    received_date DATETIME,
    transcription TEXT,
    raw_data TEXT,
    INDEX idx_from_number (from_number)
) ENGINE=InnoDB COMMENT='RingCentral Voicemails';
*/
        // Handle specific call states
        match ($status) {
            'Setup' => handleIncomingCall($fromNumber, $toNumber, $sessionId, $direction),
            'Proceeding' => handleCallInProgress($sessionId, $partyId),
            'Answered' => handleCallAnswered($sessionId, $partyId),
            'Disconnected' => handleCallEnded($sessionId, $partyId),
            'Hold' => handleCallOnHold($sessionId, $partyId),
            'Unhold' => handleCallOffHold($sessionId, $partyId),
            default => local_log("Unhandled call status: {$status}"),
        };
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

    local_log("Presence Event - Telephony Status: {$telephonyStatus}, Active Calls: " . count($activeCalls));

    foreach ($activeCalls as $call) {
        $direction = $call['direction'] ?? '';
        $fromNumber = $call['from'] ?? '';
        $toNumber = $call['to'] ?? '';
        $sessionId = $call['sessionId'] ?? '';

        local_log("Active Call - Direction: {$direction}, From: {$fromNumber}, To: {$toNumber}");
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

    local_log("Message Event - Type: {$messageType}, ID: {$messageId}, From: {$fromNumber}");

    switch ($messageType) {
        case 'VoiceMail':
            // TODO: Uncomment the following line to handle new voicemails
            //handleNewVoicemail($messageId, $fromNumber, $messageData);
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

    local_log("Recording Event - ID: {$recordingId}, Status: {$status}");

    if ($status === 'Completed') {
        downloadAndStoreRecording($recordingId);
    }
}

/**
 * Store call event in database
 */
function storeCallEvent($callData): void
{
    try {
        // Use all available columns now that we know the table structure
        sqlStatement(
            "INSERT INTO ringcentral_call_events
             (session_id, telephony_session_id, party_id, extension_id, direction, status,
              from_number, from_name, to_number, to_name, event_time, timestamp, raw_data)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $callData['session_id'],
                $callData['telephony_session_id'],
                $callData['party_id'],
                $callData['extension_id'],
                $callData['direction'],
                $callData['status'],
                $callData['from_number'],
                $callData['from_name'],
                $callData['to_number'],
                $callData['to_name'],
                $callData['event_time'],
                $callData['timestamp'],
                $callData['raw_data']
            ]
        );
    } catch (Exception $e) {
        local_log("Failed to store call event: " . $e->getMessage());
    }
}

/**
 * Handle incoming call setup
 */
function handleIncomingCall($fromNumber, $toNumber, $sessionId, $direction): void
{
    if ($direction === 'Inbound') {
        local_log("Processing incoming call from {$fromNumber} to {$toNumber}");

        // Look up patient by phone number
        $patient = sqlQuery(
            "SELECT * FROM patient_data WHERE phone_home = ? OR phone_cell = ? OR phone_biz = ?",
            [$fromNumber, $fromNumber, $fromNumber]
        );

        if ($patient) {
            local_log("Incoming call from known patient: " . $patient['fname'] . ' ' . $patient['lname']);
            // Could create automatic encounter, send notification to provider, etc.
        } else {
            local_log("Incoming call from unknown number: {$fromNumber}");
        }
    } else {
        local_log("Processing outgoing call from {$fromNumber} to {$toNumber}");
    }
}

/**
 * Handle call in progress
 */
function handleCallInProgress($sessionId, $partyId): void
{
    local_log("Call {$sessionId} (party {$partyId}) is proceeding");
    // Update call status, prepare for recording if needed, etc.
}

/**
 * Handle call answered
 */
function handleCallAnswered($sessionId, $partyId): void
{
    local_log("Call {$sessionId} (party {$partyId}) was answered");
    // Start call timer, begin recording if configured, etc.
}

/**
 * Handle call ended
 */
function handleCallEnded($sessionId, $partyId): void
{
    local_log("Call {$sessionId} (party {$partyId}) has ended");
    // Finalize call record, stop recording, calculate duration, etc.
}

/**
 * Handle call on hold
 */
function handleCallOnHold($sessionId, $partyId): void
{
    local_log("Call {$sessionId} (party {$partyId}) placed on hold");
}

/**
 * Handle call off hold
 */
function handleCallOffHold($sessionId, $partyId): void
{
    local_log("Call {$sessionId} (party {$partyId}) taken off hold");
}

/**
 * Handle new voicemail
 */
function handleNewVoicemail($messageId, $fromNumber, $messageData): void
{
    local_log("New voicemail from {$fromNumber}, message ID: {$messageId}");

    // Store voicemail information
    try {
        sqlStatement(
            "INSERT INTO ringcentral_voicemails (message_id, from_number, received_date, raw_data) VALUES (?, ?, NOW(), ?)",
            [$messageId, $fromNumber, json_encode($messageData)]
        );
    } catch (Exception $e) {
        local_log("Failed to store voicemail: " . $e->getMessage());
    }
}

/**
 * Handle new fax
 */
function handleNewFax($messageId, $fromNumber, $messageData): void
{
    local_log("New fax from {$fromNumber}, message ID: {$messageId}");
    // Process fax, store in document management, etc.
}

/**
 * Download and store recording
 */
function downloadAndStoreRecording($recordingId): void
{
    local_log("Processing completed recording: {$recordingId}");
    // Could download recording file and store it in OpenEMR's document system
}

function local_log($message): void
{
    // Custom logging function to handle event logging
    //error_log("[RingCentral] " . $message);
}
