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

// Log all incoming requests for debugging
error_log('[MedEx Callback] Request from: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
error_log('[MedEx Callback] Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('[MedEx Callback] Data: ' . file_get_contents('php://input'));

/**
 * Validate callback token
 */
function validateCallbackToken(): bool
{
    // Get token from request (header or query param)
    $provided_token = $_SERVER['HTTP_X_MEDEX_TOKEN'] ?? $_GET['token'] ?? $_POST['token'] ?? null;

    if (empty($provided_token)) {
        error_log('[MedEx Callback] ERROR: No token provided');
        return false;
    }

    // Get stored token
    $stored_token_row = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT gl_value FROM globals WHERE gl_name = 'medex_callback_token'", []);
    $stored_token = $stored_token_row['gl_value'] ?? null;

    if (empty($stored_token)) {
        error_log('[MedEx Callback] ERROR: No callback token configured');
        return false;
    }

    // Constant-time comparison to prevent timing attacks
    if (!hash_equals($stored_token, $provided_token)) {
        error_log('[MedEx Callback] ERROR: Invalid token provided');
        return false;
    }

    return true;
}

/**
 * Get request data (handles both JSON and form data)
 */
function getRequestData(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return $data ?? [];
    }

    return array_merge($_GET, $_POST);
}

// Validate token first
if (!validateCallbackToken()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized: Invalid or missing callback token'
    ]);
    exit;
}

// Check if MedEx is enabled
if ($GLOBALS['medex_enable'] != '1') {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'MedEx is not enabled'
    ]);
    exit;
}

// Get request data
$data = getRequestData();
$action = $data['action'] ?? 'unknown';

error_log('[MedEx Callback] Action: ' . $action);

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

    default:
        error_log('[MedEx Callback] ERROR: Unknown action: ' . $action);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action: ' . $action
        ]);
        break;
}

// Log successful completion
error_log('[MedEx Callback] Request completed successfully');
