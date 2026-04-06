<?php
/**
 * MedEx Module - Callback Endpoint
 *
 * Secure endpoint for MedEx server to push/pull data
 * Replaces library/MedEx/MedEx.php functionality but within the module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}
$ignoreAuth = true;

require_once(__DIR__ . "/../../../../globals.php");

// Set JSON response header
header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');
if ($rawBody === false) {
    $rawBody = '';
}
$requestId = bin2hex(random_bytes(8));
error_log('[MedEx Callback][' . $requestId . '] Request from: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
error_log('[MedEx Callback][' . $requestId . '] Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));

/**
 * Read MedEx callback security setting from globals.
 */
function medexGetCallbackSetting(string $name, string $default = ''): string
{
    $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT gl_value FROM globals WHERE gl_name = ?",
        [$name]
    );
    $value = (string)($row['gl_value'] ?? '');
    return $value !== '' ? $value : $default;
}

/**
 * Require HMAC signature headers and prevent replay when enabled.
 *
 * Signature format:
 *   HMAC_SHA256(token, "{timestamp}\n{nonce}\n{rawBody}")
 */
function medexValidateSignature(string $token, string $rawBody, bool $requireSignature, string $requestId): bool
{
    $timestamp = trim((string)($_SERVER['HTTP_X_MEDEX_TIMESTAMP'] ?? ''));
    $nonce = trim((string)($_SERVER['HTTP_X_MEDEX_NONCE'] ?? ''));
    $signature = strtolower(trim((string)($_SERVER['HTTP_X_MEDEX_SIGNATURE'] ?? '')));
    $hasSignatureHeaders = ($timestamp !== '' || $nonce !== '' || $signature !== '');

    if (!$requireSignature && !$hasSignatureHeaders) {
        return true;
    }
    if ($timestamp === '' || $nonce === '' || $signature === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Missing signature headers');
        return false;
    }
    if (!ctype_digit($timestamp)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid signature timestamp');
        return false;
    }
    $tsInt = (int)$timestamp;
    if (abs(time() - $tsInt) > 300) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Signature timestamp out of window');
        return false;
    }
    if (!preg_match('/^[A-Za-z0-9_-]{12,128}$/', $nonce)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid signature nonce');
        return false;
    }

    sqlStatement("
        CREATE TABLE IF NOT EXISTS `medex_callback_nonce_log` (
            `nonce` VARCHAR(128) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`nonce`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    sqlStatement("DELETE FROM `medex_callback_nonce_log` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $existingNonce = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
        "SELECT nonce FROM medex_callback_nonce_log WHERE nonce = ? LIMIT 1",
        [$nonce]
    );
    if (!empty($existingNonce['nonce'])) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Replay nonce detected');
        return false;
    }
    sqlStatement("INSERT INTO `medex_callback_nonce_log` (`nonce`) VALUES (?)", [$nonce]);

    $expected = hash_hmac('sha256', $timestamp . "\n" . $nonce . "\n" . $rawBody, $token);
    if (!hash_equals($expected, $signature)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid request signature');
        return false;
    }

    return true;
}

/**
 * Validate callback token and optional signature policy.
 */
function validateCallbackAuth(string $rawBody, string $requestId): bool
{
    $allowQueryToken = medexGetCallbackSetting('medex_callback_allow_query_token', '1') === '1';
    $requireHeaderToken = medexGetCallbackSetting('medex_callback_require_header_token', '0') === '1';
    $requireSignature = medexGetCallbackSetting('medex_callback_require_signature', '0') === '1';

    $providedToken = trim((string)($_SERVER['HTTP_X_MEDEX_TOKEN'] ?? ''));
    if ($providedToken === '' && !$requireHeaderToken && $allowQueryToken) {
        $providedToken = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
    }
    if ($providedToken === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: No callback token provided');
        return false;
    }

    $storedToken = medexGetCallbackSetting('medex_callback_token', '');
    if ($storedToken === '') {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: No callback token configured');
        return false;
    }
    if (!hash_equals($storedToken, $providedToken)) {
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Invalid callback token');
        return false;
    }
    if (!medexValidateSignature($storedToken, $rawBody, $requireSignature, $requestId)) {
        return false;
    }

    return true;
}

/**
 * Get request data (handles both JSON and form data).
 */
function getRequestData(string $rawBody): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode($rawBody, true);
        return $data ?? [];
    }

    return array_merge($_GET, $_POST);
}

// Validate token first
if (!validateCallbackAuth($rawBody, $requestId)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized: Invalid or missing callback token'
    ]);
    exit;
}

// Get request data
$data = getRequestData($rawBody);
$action = $data['action'] ?? 'unknown';

error_log('[MedEx Callback][' . $requestId . '] Action: ' . $action);

// Allow token-auth ping even before module is fully enabled/configured.
if ($action !== 'ping' && ($GLOBALS['medex_enable'] ?? '0') != '1') {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'MedEx is not enabled'
    ]);
    exit;
}

// Route to appropriate handler based on action
switch ($action) {
    case 'ping':
        // Simple health check
        echo json_encode([
            'success' => true,
            'message' => 'OpenEMR MedEx module is active',
            'timestamp' => date('c'),
            'version' => '1.0.0'
        ]);
        break;

    case 'get_appointments':
        // MedEx requesting appointment data
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->getAppointments($data);
        echo json_encode($result);
        break;

    case 'get_status_changes':
        // MedEx requesting lightweight status snapshot (pc_eid + pc_apptstatus only)
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->getStatusChanges($data);
        echo json_encode($result);
        break;

    case 'update_appointment_status':
        // MedEx updating appointment status (confirmed, cancelled, etc)
        require_once(__DIR__ . '/../src/CallbackHandlers/AppointmentHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\AppointmentHandler();
        $result = $handler->updateAppointmentStatus($data);
        echo json_encode($result);
        break;

    case 'get_recalls':
        // MedEx requesting recall data
        require_once(__DIR__ . '/../src/CallbackHandlers/RecallHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\RecallHandler();
        $result = $handler->getRecalls($data);
        echo json_encode($result);
        break;

    case 'update_recall_status':
        // MedEx updating recall status
        require_once(__DIR__ . '/../src/CallbackHandlers/RecallHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\RecallHandler();
        $result = $handler->updateRecallStatus($data);
        echo json_encode($result);
        break;

    case 'log_message':
        // MedEx logging a sent message (SMS, email, voice)
        require_once(__DIR__ . '/../src/CallbackHandlers/MessageHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\MessageHandler();
        $result = $handler->logMessage($data);
        echo json_encode($result);
        break;

    case 'message_reply':
    case 'message_status':
        // MedEx sending message reply or status update (CONFIRMED, CALL, STOP, SENT, READ, FAILED, BOUNCE)
        require_once(__DIR__ . '/../src/Services/MessageReceiveService.php');
        $receiveService = new \OpenEMR\Modules\MedEx\Services\MessageReceiveService();
        $result = $receiveService->receive($data);
        echo json_encode($result);
        break;

    case 'get_patient':
        // MedEx requesting patient demographics
        require_once(__DIR__ . '/../src/CallbackHandlers/PatientHandler.php');
        $handler = new \OpenEMR\Modules\MedEx\CallbackHandlers\PatientHandler();
        $result = $handler->getPatient($data);
        echo json_encode($result);
        break;

    case 'get_preferences':
        // MedEx requesting practice preferences
        $prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs LIMIT 1", []);
        echo json_encode([
            'success' => true,
            'preferences' => $prefs
        ]);
        break;

    case 'get_provider_roster':
        // MedEx requesting current providers/facilities for admin scope controls
        require_once($GLOBALS['srcdir'] . '/patient.inc.php');
        $providers = [];
        $providerRows = getProviderInfo('%', true);
        if (is_array($providerRows)) {
            foreach ($providerRows as $row) {
                $id = (string)($row['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $name = trim(((string)($row['fname'] ?? '')) . ' ' . ((string)($row['lname'] ?? '')));
                $providers[] = [
                    'id' => $id,
                    'name' => $name !== '' ? $name : ((string)($row['username'] ?? ('Provider ' . $id))),
                    'active' => true
                ];
            }
        }

        $facilities = [];
        $facilityStmt = sqlStatement("SELECT id, name FROM facility ORDER BY id ASC");
        while ($frow = sqlFetchArray($facilityStmt)) {
            $fid = (string)($frow['id'] ?? '');
            if ($fid === '') {
                continue;
            }
            $facilities[] = [
                'id' => $fid,
                'name' => (string)($frow['name'] ?? ('Facility ' . $fid))
            ];
        }

        // Keep FullCalendar day-start/day-end aligned with OpenEMR Calendar globals.
        if (!isset($GLOBALS['schedule_start']) || !isset($GLOBALS['schedule_end'])) {
            $gStmt = sqlStatement("SELECT gl_name, gl_value FROM globals WHERE gl_name IN ('schedule_start', 'schedule_end')");
            while ($gRow = sqlFetchArray($gStmt)) {
                if (!empty($gRow['gl_name'])) {
                    $GLOBALS[(string)$gRow['gl_name']] = (string)($gRow['gl_value'] ?? '');
                }
            }
        }
        $scheduleStart = (int)($GLOBALS['schedule_start'] ?? 8);
        $scheduleEnd = (int)($GLOBALS['schedule_end'] ?? 17);
        if ($scheduleEnd <= $scheduleStart) {
            $scheduleEnd = min(23, $scheduleStart + 1);
        }

        echo json_encode([
            'success' => true,
            'providers' => $providers,
            'facilities' => $facilities,
            'schedule_start' => $scheduleStart,
            'schedule_end' => $scheduleEnd
        ]);
        break;

    default:
        error_log('[MedEx Callback][' . $requestId . '] ERROR: Unknown action: ' . $action);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action: ' . $action
        ]);
        break;
}

// Log successful completion
error_log('[MedEx Callback][' . $requestId . '] Request completed successfully');
