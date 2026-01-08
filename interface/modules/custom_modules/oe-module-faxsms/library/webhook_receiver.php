<?php

/**
 * SignalWire Webhook Receiver
 * Handles incoming fax notifications from SignalWire
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Allow webhook access without authentication
$ignoreAuth = true;
$sessionAllowWrite = true;

// Set site from query parameter for multi-site support
$_GET['auth'] = 'portal';  // Enable site selection
$_GET['site'] ??= 'default';

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\oeHttp;
use OpenEMR\Common\Http\oeHttpRequest;
use OpenEMR\Modules\FaxSMS\Controller\FaxDocumentService;

// Capture raw input first (can only be read once)
$rawInput = file_get_contents('php://input');

// Get site ID from query parameter
$siteId = $_SESSION['site_id'] ?? $_GET['site'] ?? 'default';

// Handle JSON payloads (SignalWire sends JSON for some webhooks)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains((string) $contentType, 'application/json') && !empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Check if this is a SWML execute callback with fax data in vars
        if (isset($jsonData['call']) && isset($jsonData['vars'])) {
            $vars = $jsonData['vars'];

            // Check if fax was received (SWML execute sends fax data in vars)
            if (isset($vars['receive_fax_document'])) {
                // Map SWML vars to standard fax webhook format for processing
                $_POST = [
                    'FaxSid' => $jsonData['call']['call_id'] ?? uniqid('fax_'),
                    'Status' => ($vars['receive_fax_result'] === 'success') ? 'received' : 'failed',
                    'From' => $jsonData['call']['from'] ?? '',
                    'To' => $jsonData['call']['to'] ?? '',
                    'NumPages' => $vars['receive_fax_pages'] ?? 0,
                    'MediaUrl' => $vars['receive_fax_document'] ?? '',
                    'Direction' => 'inbound',
                    'ErrorCode' => $vars['receive_fax_result_code'] ?? '',
                    'ErrorMessage' => $vars['receive_fax_result_text'] ?? ''
                ];
            } else {
                // This is a call event without fax data (call setup/teardown)
                http_response_code(200);
                header('Content-Type: application/xml');
                echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
                exit();
            }
        }
        // If JSON contains traditional fax data, map it to $_POST for processing
        elseif (isset($jsonData['FaxSid']) || isset($jsonData['Sid'])) {
            $_POST = $jsonData;
        }
    }
}

// Get vendor type from query string
$vendor = $_GET['vendor'] ?? '';
$type = $_GET['type'] ?? '';

if ($vendor !== 'signalwire' || $type !== 'fax') {
    http_response_code(400);
    error_log("Invalid webhook vendor or type: vendor=$vendor, type=$type");
    exit('Invalid request');
}

// Get webhook payload - initialize all variables explicitly for PHPStan
$faxSid = $_POST['FaxSid'] ?? $_POST['Sid'] ?? '';
$status = $_POST['Status'] ?? $_POST['FaxStatus'] ?? 'unknown';
$from = $_POST['From'] ?? $_POST['RemoteStationId'] ?? '';
$to = $_POST['To'] ?? $_POST['OriginalTo'] ?? '';
$numPages = $_POST['NumPages'] ?? $_POST['Pages'] ?? 0;
$mediaUrl = $_POST['MediaUrl'] ?? '';
$direction = $_POST['Direction'] ?? 'inbound';
$errorCode = $_POST['ErrorCode'] ?? '';
$errorMessage = $_POST['ErrorMessage'] ?? '';

// Handle webhook validation/test (empty POST body)
if (empty($_POST) || empty($faxSid)) {
    // SignalWire sends validation pings with empty POST
    // Respond with 200 OK and TwiML to acknowledge webhook is working
    http_response_code(200);
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
    exit();
}

try {
    // Prepare fax data for storage
    $faxData = [
        'job_id' => $faxSid,
        'status' => $status,
        'from' => $from,
        'to' => $to,
        'num_pages' => $numPages,
        'media_url' => $mediaUrl,
        'direction' => $direction,
        'error_code' => $errorCode,
        'error_message' => $errorMessage,
        'raw_payload' => json_encode($_POST)
    ];

    // Check if this fax already exists in queue for this site
    $existingFax = QueryUtils::querySingleRow(
        "SELECT id, status, patient_id FROM oe_faxsms_queue WHERE job_id = ? AND site_id = ?",
        [$faxSid, $siteId]
    );

    if ($existingFax) {
        // Update existing fax with new status
        QueryUtils::sqlStatementThrowException(
            "UPDATE oe_faxsms_queue
             SET status = ?, details_json = ?, date = NOW()
             WHERE job_id = ? AND site_id = ?",
            [$status, json_encode($faxData), $faxSid, $siteId]
        );
    } else {
        // Insert new fax
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oe_faxsms_queue
             (job_id, calling_number, called_number, status, direction, details_json, site_id, date, receive_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $faxSid,
                $from,
                $to,
                $status,
                $direction,
                json_encode($faxData),
                $siteId
            ]
        );
    }

    // If fax is received and has media, download and store it
    if ($direction === 'inbound' && $status === 'received' && !empty($mediaUrl)) {
        downloadAndStoreFaxMedia($faxSid, $mediaUrl, $from, $siteId, $existingFax['patient_id'] ?? 0);
    }

    // Respond with success
    http_response_code(200);
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';

} catch (Throwable $e) {
    error_log("Error processing SignalWire webhook: " . $e->getMessage());
    http_response_code(500);
    exit('Internal server error');
}

/**
 * Download fax media from SignalWire and store using FaxDocumentService
 *
 * @param string $faxSid
 * @param string $mediaUrl
 * @param string $fromNumber
 * @param string $siteId
 * @param int $patientId Patient ID if already assigned
 * @return void
 */
function downloadAndStoreFaxMedia(
    string $faxSid,
    string $mediaUrl,
    string $fromNumber,
    string $siteId,
    int $patientId = 0
): void {
    try {
        // Get SignalWire credentials
        $vendor = '_signalwire';
        $credentials = QueryUtils::querySingleRow(
            "SELECT credentials FROM module_faxsms_credentials WHERE vendor = ? AND auth_user = 0",
            [$vendor]
        );

        if (empty($credentials)) {
            return;
        }

        $crypto = new CryptoGen();
        $decrypted = $crypto->decryptStandard($credentials['credentials']);
        $creds = json_decode($decrypted, true);

        $projectId = $creds['project_id'] ?? '';
        $apiToken = $creds['api_token'] ?? '';

        if (empty($projectId) || empty($apiToken)) {
            return;
        }

        // Download the fax media with authentication using oeHttp
        // SignalWire files.signalwire.com requires Bearer token, not Basic auth
        try {
            $httpRequest = oeHttpRequest::newArgs(oeHttp::client());

            // Set up headers based on URL type
            if (str_contains($mediaUrl, 'files.signalwire.com')) {
                // Use Bearer token authentication for SignalWire file downloads
                $httpRequest->usingHeaders([
                    'Authorization' => 'Bearer ' . $apiToken
                ]);
            } else {
                // Use Basic authentication for API endpoints
                $httpRequest->setOptions([
                    'auth' => [$projectId, $apiToken]
                ]);
            }

            $response = $httpRequest->get($mediaUrl);
            $httpCode = $response->status();
            $mediaContent = $response->body();
            $contentTypeHeader = $response->header('Content-Type');
            $contentType = !empty($contentTypeHeader) ? $contentTypeHeader : 'application/pdf';

            if ($httpCode !== 200 || empty($mediaContent)) {
                return;
            }
        } catch (\Exception $e) {
            error_log("SignalWire Webhook: HTTP request failed: " . $e->getMessage());
            return;
        }

        // Try to find patient by phone number if not assigned
        if ($patientId === 0) {
            $faxService = new FaxDocumentService($siteId);
            $patientId = $faxService->findPatientByPhone($fromNumber);
        }

        // Store fax using FaxDocumentService
        $faxService ??= new FaxDocumentService($siteId);
        $result = $faxService->storeFaxDocument(
            $faxSid,
            $mediaContent,
            $fromNumber,
            $patientId,
            $contentType
        );

        // Update queue with storage info
        QueryUtils::sqlStatementThrowException(
            "UPDATE oe_faxsms_queue
             SET patient_id = ?, document_id = ?, media_path = ?
             WHERE job_id = ? AND site_id = ?",
            [
                $result['patient_id'],
                $result['document_id'],
                $result['media_path'],
                $faxSid,
                $siteId
            ]
        );
    } catch (\Exception $e) {
        error_log("SignalWire Webhook: Error downloading/storing fax media: " . $e->getMessage());
    }
}
