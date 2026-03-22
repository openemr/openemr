<?php
// admin/api/ai_planner_audio_proxy.php
// Proxy audio file upload to MedEx Brain API for STT (Speech-to-Text)

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../../../../../library/options.inc.php');

// Security check for authenticated user
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 1. Check for uploaded file
if (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No audio file uploaded or upload error']);
    exit;
}

$tmpFile = $_FILES['audio_file']['tmp_name'];
$fileType = $_FILES['audio_file']['type'];
$fileName = $_FILES['audio_file']['name'];

// 2. Prepare request to MedEx Brain (STT Endpoint)
// Server-side URL: inside Docker, 'localhost' = this container, NOT MedEx.
// Use host.docker.internal to reach the host where MedEx is exposed on port 80.
$medexUrl = $GLOBALS['medex_api_url'] ?? null;
if (!$medexUrl) {
    if (file_exists('/.dockerenv') || getenv('HOSTNAME')) {
        $medexUrl = 'http://host.docker.internal/cart/upload';
    } else {
        $base = $GLOBALS['medex_base_url'] ?? 'https://medexbank.com';
        $medexUrl = rtrim($base, '/') . '/cart/upload';
    }
}

$sttEndpoint = $medexUrl . '/index.php?route=api/ai_planner/stt'; 
// Note: Ensure this endpoint exists on the MedEx server!

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sttEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Create a CURLFile object
$cFile = new CURLFile($tmpFile, $fileType, $fileName);

$postData = [
    'audio' => $cFile,
    'context' => json_encode($_SESSION['ai_planner_context'] ?? [])
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// 3. Execute and Return Response
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 && $response) {
    header('Content-Type: application/json');
    echo $response; // Expecting { "transcript": "Hello world" }
} else {
    // Fallback/Error Handling if MedEx connection fails
    http_response_code(502);
    echo json_encode([
        'error' => 'STT Service Unreachable',
        'debug_http_code' => $httpCode,
        'debug_response' => $response,
        'debug_curl_error' => $curlError
    ]);
}
