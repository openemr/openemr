<?php

/**
 * Backend API endpoint for HIPAAi Chat PIIPS integration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@example.com> // TODO: Update author info
 * @copyright Copyright (c) 2024 Your Name/Company
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Log script start
error_log("HIPAAi Chat API: Script started.");

use OpenEMR\Modules\HipaaiChat\GlobalConfig;

// Determine the correct path to globals.php based on the current file's location
// Corrected relative path
$openemr_globals_path = __DIR__ . '/../../../../../globals.php';
error_log("HIPAAi Chat API: Globals path check: " . $openemr_globals_path);

if (!file_exists($openemr_globals_path)) {
    error_log("HIPAAi Chat API: ERROR - Could not find OpenEMR globals.php at: " . $openemr_globals_path);
    http_response_code(500);
    echo json_encode(['error' => 'Could not find OpenEMR globals.php']);
    exit;
}

// Log before requiring globals
error_log("HIPAAi Chat API: Requiring globals.php...");
require_once($openemr_globals_path);
// Log after requiring globals (if require fails, this won't be logged)
error_log("HIPAAi Chat API: globals.php included successfully.");

// Check for required session/authentication if necessary for production
// For example: if (!isset($_SESSION['authUser']) || empty($_SESSION['userauthorized'])) { ... }

header('Content-Type: application/json');

// --- PIIPS Configuration ---
$piips_api_endpoint = 'https://pii-protection-service-production.up.railway.app/pii-guard-llm';

// --- Get PIIPS API Key ---
error_log("HIPAAi Chat API: Instantiating GlobalConfig...");
$moduleConfig = new GlobalConfig($GLOBALS);
error_log("HIPAAi Chat API: Attempting to get PIIPS API key...");
$apiKey = $moduleConfig->getPiipsApiKey(); // Use the new getter

if (empty($apiKey)) {
    // Log decryption failure / empty key
    $decryptionError = $moduleConfig->getGlobalSetting(GlobalConfig::CONFIG_PIIPS_API_KEY) ? "Decryption likely failed (HMAC?)" : "Setting not found or empty";
    error_log("HIPAAi Chat API: ERROR - PIIPS API Key is EMPTY or could not be decrypted. Reason: " . $decryptionError);
    http_response_code(400);
    echo json_encode(['error' => 'PIIPS API Key is not configured or could not be decrypted.']);
    exit;
} else {
    error_log("HIPAAi Chat API: PIIPS API key retrieved successfully (length: " . strlen($apiKey) . ").");
}

// --- Get Request Body (from frontend) ---
error_log("HIPAAi Chat API: Getting request body...");
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($input['messages']) || !is_array($input['messages'])) {
    error_log("HIPAAi Chat API: ERROR - Invalid request body. JSON Error: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body. Expected JSON with a "messages" array.']);
    exit;
}
error_log("HIPAAi Chat API: Request body parsed successfully. Message count: " . count($input['messages']));

// --- Format conversation history + prompt for PIIPS ---
$formatted_text = "";
foreach ($input['messages'] as $message) {
    if (isset($message['role']) && isset($message['content'])) {
        // Simple formatting, adjust if needed
        $formatted_text .= ucfirst($message['role']) . ": " . $message['content'] . "\n\n";
    }
}
$formatted_text = trim($formatted_text); // Remove trailing newline
error_log("HIPAAi Chat API: Formatted text for PIIPS (length: " . strlen($formatted_text) . ").");

// --- Prepare PIIPS Request Data ---
$data = [
    'text' => $formatted_text,
];
error_log("HIPAAi Chat API: Prepared data for PIIPS.");

// --- Send Request via cURL to PIIPS ---
error_log("HIPAAi Chat API: Initializing cURL for PIIPS...");
$ch = curl_init($piips_api_endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-api-key: ' . $apiKey // Changed header to lowercase
]);
// Add proxy settings if needed for your OpenEMR environment
// curl_setopt($ch, CURLOPT_PROXY, 'your_proxy_host:port');
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'user:password');

// It's recommended to configure CA certificates properly for production
// curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');
// For development/testing ONLY, you might temporarily disable SSL verification (NOT RECOMMENDED FOR PRODUCTION)
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

error_log("HIPAAi Chat API: Executing cURL request to PIIPS...");
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_errno = curl_errno($ch);
curl_close($ch);
error_log("HIPAAi Chat API: PIIPS cURL execution finished. HTTP Code: $httpcode, Curl Error: [$curl_errno] $curl_error");

// --- Handle PIIPS Response ---
if ($curl_error) {
    error_log("HIPAAi Chat API: ERROR - PIIPS cURL Error: [$curl_errno] " . $curl_error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to communicate with PII Protection Service. cURL error.']);
    exit;
}

$responseData = json_decode($response, true);
$json_last_error = json_last_error();

// Check for HTTP errors OR JSON parsing errors OR if the expected 'text' field is missing
if ($httpcode >= 400 || $json_last_error !== JSON_ERROR_NONE || !isset($responseData['text'])) {
    error_log("HIPAAi Chat API: ERROR - Invalid/Error response from PIIPS. HTTP: $httpcode, JSON Error: " . json_last_error_msg() . ", Response: " . $response);
    http_response_code($httpcode >= 400 ? $httpcode : 500);
    // Try to get error message from response if available
    $errorMessage = isset($responseData['error']) ? $responseData['error'] : (isset($responseData['detail']) ? $responseData['detail'] : 'Invalid response from PII Protection Service.');
    echo json_encode(['error' => $errorMessage]);
    exit;
}

// --- Success --- 
// Extract the AI's response text from the PIIPS response
$assistant_response_text = $responseData['text'];

// Format the response for the frontend (which expects the OpenAI structure)
$frontend_response = [
    'message' => [
        'role' => 'assistant',
        'content' => $assistant_response_text
    ]
];

error_log("HIPAAi Chat API: Successfully received and processed response from PIIPS.");
echo json_encode($frontend_response);
error_log("HIPAAi Chat API: Script finished successfully.");

?> 