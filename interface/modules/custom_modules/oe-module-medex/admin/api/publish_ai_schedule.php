<?php
/**
 * MedEx AI Schedule Publisher
 * Handles the creation of "Open Slot" events for the AI Rescheduler Bot
 * Supports Batch Processing for Multiple Providers/Facilities
 */

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../../../../../library/sql.inc.php');
require_once(__DIR__ . '/../../../../../../library/options.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

// Security Check
if (empty($_SESSION['authUserID'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Request']);
    exit;
}

// ---------------------------------------------------------
// 1. EXTRACT PARAMETERS (Multi-Select Support)
// ---------------------------------------------------------

// Providers: Array of IDs
$providerIds = $input['provider_ids'] ?? [];
if (empty($providerIds) && isset($input['provider_id'])) {
    $providerIds = [$input['provider_id']];
}

// Facilities: Array of IDs (Default to Primary if empty)
$facilityIds = $input['facility_ids'] ?? [];
if (empty($facilityIds) && isset($input['facility_id'])) {
    $facilityIds = [$input['facility_id']];
}
// Default to 1 (Main) if none provided
if (empty($facilityIds)) {
    $facilityIds = [1]; 
}

// Categories: Array of IDs
$categories = $input['categories'] ?? [];
if (empty($categories) && isset($input['category'])) {
    $categories = [$input['category']];
}
// Default to 1 (Office Visit) if none provided
if (empty($categories)) {
    $categories = [1];
}

$strategy = $input['strategy'] ?? 'linear';
$days = $input['days'] ?? []; // Array of ints (0=Sun, 6=Sat) or (1=Mon..7=Sun) depending on format
// Note: PHP 'w' is 0(Sun)-6(Sat), 'N' is 1(Mon)-7(Sun). 
// OpenEMR often uses 0=Sunday. Let's assume input matches PHP 'w' (0-6) or adjust.
// If input is from JS getDay(), it's 0-6.

$startTimeStr = $input['start_time']; // "08:00"
$endTimeStr   = $input['end_time'];   // "17:00"
$slotDuration = (int)$input['duration']; // minutes
$rangeMonths  = (int)$input['range'];
$locale       = $input['locale'] ?? 'en';

if (empty($providerIds)) {
    echo json_encode(['error' => 'At least one Provider is required']);
    exit;
}

// Rules
$rules = $input['rules'] ?? [];
$targetSlots = (int)($input['target_slots'] ?? 0); // optional cap

// Initialize Counters
$grandTotalCreated = 0;
$grandTotalSkipped = 0;
$providerStats = [];
$batchId = uniqid('AI_BATCH_');

// ---------------------------------------------------------
// 2. REMOTE INTELLIGENCE REQUEST (IP PROTECTION)
// ---------------------------------------------------------

// We do NOT calculate slots locally. We ask the MedExBrain.
// This prevents IP theft by keeping the scheduling logic on the MedEx server.

// Server-side URL: inside Docker, 'localhost' = this container, NOT MedEx.
$medexUrl = $GLOBALS['medex_api_url'] ?? null;
if (!$medexUrl) {
    if (file_exists('/.dockerenv') || getenv('HOSTNAME')) {
        $medexUrl = 'http://host.docker.internal/cart/upload';
    } else {
        $base = $GLOBALS['medex_base_url'] ?? 'https://medexbank.com';
        $medexUrl = rtrim($base, '/') . '/cart/upload';
    }
}
$apiKey   = $GLOBALS['medex_api_key'] ?? ''; // Should be configured in Globals

// Prepare Payload for MedEx Brain
$payload = [
    'action'       => 'generate_slots',
    'provider_ids' => $providerIds,
    'facility_ids' => $facilityIds,
    'categories'   => $categories,
    'strategy'     => $strategy,
    'days'         => $days,
    'start_time'   => $startTimeStr,
    'end_time'     => $endTimeStr,
    'duration'     => $slotDuration,
    'range_months' => $rangeMonths,
    'rules'        => $rules,
    'timezone'     => date_default_timezone_get(),
    'callback_url' => '', // Optional: if async
    // We send current "busy" slots so the brain knows gaps? 
    // Or does the brain ask for them? 
    // For MVP, we'll let the LOCAL script handle valid collision detection 
    // but the BRAIN decides the "Strategy" (Time, type, frequency).
];

// Execute Request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $medexUrl . '/index.php?route=api/ai_scheduler/generate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-MedEx-Key: ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handshake Validation
if ($httpCode !== 200 || !$response) {
    // Falls back to local 'dumb' linear generation if server unreachable? 
    // OR fail safe? 
    // For IP protection, we should probably FAIL or do very basic linear.
    echo json_encode(['error' => 'MedEx Brain Unreachable: ' . $httpCode]);
    exit;
}

$brainOutput = json_decode($response, true);
if (!isset($brainOutput['slots'])) {
    echo json_encode(['error' => 'Invalid Response from MedEx Brain']);
    exit;
}

// ---------------------------------------------------------
// 3. EXECUTE REMOTE INSTRUCTIONS
// ---------------------------------------------------------

$generatedSlots = $brainOutput['slots']; 
// Expected format: [['start' => '08:00', 'end' => '08:15', 'date' => '2023-10-10', 'cat' => 1, 'pid' => 2], ...]

foreach ($generatedSlots as $slot) {
    
    // Local Validation (Collision Check) matches safety requirements
    // Even if Brain says "Book it", we check our local DB for truth.
    
    $pId      = $slot['provider_id'];
    $sqlDate  = $slot['date'];
    $sqlStart = $slot['start'];
    $sqlEnd   = $slot['end'];
    
    // Check openemr_postcalendar_events for overlapping events
    $query = "SELECT pc_eid FROM openemr_postcalendar_events
              WHERE pc_pid = ? 
              AND pc_eventDate = ?
              AND (pc_startTime < ? AND pc_endTime > ?)";
              
    $collision = sqlQuery($query, [$pId, $sqlDate, $sqlEnd, $sqlStart]);
    
    if ($collision) {
        $grandTotalSkipped++;
    } else {
        // ... (Insert Logic matches previous)
        
        $insertSql = "INSERT INTO openemr_postcalendar_events (
            pc_pid, pc_title, pc_time, pc_eventDate, 
            pc_catid, pc_facility, pc_billing_location,
            pc_duration, pc_startTime, pc_endTime, 
            pc_apptstatus, pc_aid, pc_multiple
        ) VALUES (
            ?, ?, ?, ?, 
            ?, ?, ?,
            ?, ?, ?, 
            ?, ?, 0
        )";
        
        $durationSec = (strtotime($sqlEnd) - strtotime($sqlStart));
        $category    = $slot['category_id'] ?? $categories[0];
        $facility    = $slot['facility_id'] ?? $facilityIds[0];
        $title       = $slot['title'] ?? "AI Open Slot";

        sqlStatement($insertSql, [
            $pId, 
            $title, 
            $sqlStart,          
            $sqlDate,           
            $category,       
            $facility,  
            $facility,  
            $durationSec,       
            $sqlStart,          
            $sqlEnd,            
            '-',                
            $_SESSION['authUserID'] ?? 1
        ]);
        
        // Track this event in the AI batch table for undo support
        $newEid = sqlQuery("SELECT LAST_INSERT_ID() as eid");
        if (!empty($newEid['eid'])) {
            sqlStatement(
                "INSERT INTO medex_ai_batches (batch_id, pc_eid, provider_id, facility_id, event_date, created_by) VALUES (?, ?, ?, ?, ?, ?)",
                [$batchId, $newEid['eid'], $pId, $facility, $sqlDate, $_SESSION['authUserID'] ?? null]
            );
        }
        
        $grandTotalCreated++;
        
        // Track stats per provider
        if (!isset($providerStats[$pId])) $providerStats[$pId] = ['made'=>0, 'miss'=>0];
        $providerStats[$pId]['made']++;
    }
}


// ---------------------------------------------------------
// 5. OUTPUT
// ---------------------------------------------------------

echo json_encode([
    'success' => true,
    'batch_id' => $batchId,
    'created' => $grandTotalCreated,
    'skipped' => $grandTotalSkipped,
    'details' => $providerStats
]);
exit;
?>
