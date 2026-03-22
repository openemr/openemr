<?php
// admin/api/ai_planner_proxy.php
// Proxy request to MedEx Brain API (Protected IP)

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../../../../../library/options.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;

// Security check
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';
$mode = $input['mode'] ?? 'chat';
$conversation = $input['conversation'] ?? [];

// MedEx Brain URL — always use the canonical base URL from MedExConfig
require_once(__DIR__ . '/../../src/MedExConfig.php');
$medexUrl = $GLOBALS['medex_api_url'] ?? \OpenEMR\Modules\MedEx\MedExConfig::baseUrl();
$apiKey   = $GLOBALS['medex_api_key'] ?? '';

$payload = [
    'message' => $userMessage,
    'mode' => $mode,
    'conversation' => $conversation,
    'context' => $_SESSION['ai_planner_context'] ?? [],
    'practice_id' => $GLOBALS['medex_practice_id'] ?? 0
];

// Call MedEx Brain 'ai_planner' endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $medexUrl . '/index.php?route=api/ai_planner');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-MedEx-Key: ' . $apiKey]); // If key needed

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);
    
    // Store conversation state in session
    if (isset($data['context'])) {
        $_SESSION['ai_planner_context'] = $data['context'];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'reply' => $data['text'] ?? "I heard that.",
        'audio' => $data['audio'] ?? null,
        'schedule_data' => $data['schedule_data'] ?? null
    ]);
} else {
    // Fallback if brain is offline
    echo json_encode(['reply' => "I heard '$userMessage'. (Brain Offline)"]);
}
exit;
?>