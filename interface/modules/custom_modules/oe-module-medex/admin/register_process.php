<?php
/**
 * MedEx Module - Registration Processing
 *
 * Handles AJAX registration requests
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', $session)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Load MedEx API and Services
    require_once(__DIR__ . '/../src/MedExAPI.php');
    require_once(__DIR__ . '/../src/Services/PracticeService.php');

    // Validate required fields (only email and password - practice details come from facility sync)
    $required = ['email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            exit;
        }
    }

// Create API instance
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Get primary facility details as default practice info
$facility = sqlQuery("SELECT name, phone, street, city, state, postal_code FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
if (!$facility) {
    $facility = sqlQuery("SELECT name, phone, street, city, state, postal_code FROM facility ORDER BY id LIMIT 1");
}

$practice_name = $facility['name'] ?? $GLOBALS['openemr_name'] ?? 'OpenEMR Practice';
$practice_phone = $facility['phone'] ?? '';
$practice_address = trim(
    ($facility['street'] ?? '') . "\n" .
    ($facility['city'] ?? '') . ', ' . ($facility['state'] ?? '') . ' ' . ($facility['postal_code'] ?? '')
);

// Prepare registration data
$data = [
    'email' => trim($_POST['email']),
    'password' => $_POST['password'],
    'practice_name' => $practice_name,
    'phone' => $practice_phone,
    'address' => $practice_address,
    'ehr' => 'OpenEMR',
    'ehr_version' => $GLOBALS['v_major'] . '.' . $GLOBALS['v_minor'] . '.' . $GLOBALS['v_patch']
];

// Attempt registration
$result = $api->register($data);

// If registration successful, perform initial practice sync
if (!empty($result['success'])) {
    // Pre-fetch and DB-cache pricing immediately so the Services tab never hits the server on first open.
    // This is a fire-and-forget; failure is non-fatal — getPricing() has built-in defaults.
    try {
        $api->getPricing();
    } catch (\Exception $e) {
        error_log('[MedEx] Non-fatal: could not pre-cache pricing on registration: ' . $e->getMessage());
    }

    // Auto-configure all facilities and providers with calendars on first registration

    // Get all facilities
    $facility_records = \OpenEMR\Common\Database\QueryUtils::fetchRecords("SELECT id FROM facility WHERE service_location = 1 ORDER BY id");
    $facility_ids = [];
    foreach ($facility_records as $fac) {
        $facility_ids[] = $fac['id'];
    }

    // Get all providers who have calendars
    $provider_records = \OpenEMR\Common\Database\QueryUtils::fetchRecords("
        SELECT DISTINCT u.id
        FROM users u
        WHERE u.authorized = 1
        AND u.active = 1
        AND u.calendar = 1
        ORDER BY u.id
    ");
    $provider_ids = [];
    foreach ($provider_records as $prov) {
        $provider_ids[] = $prov['id'];
    }

    // medex_prefs row was already written by MedExAPI::register() with the full api_key.
    // Just update facilities/providers on that row (never overwrite api_key here — globals.gl_value
    // is varchar(255) and would truncate it; medex_prefs.ME_api_key is TEXT and holds the full key).
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "UPDATE medex_prefs SET
            ME_facilities = ?,
            ME_providers = ?,
            MedEx_lastupdated = NOW()
         WHERE ME_username = ?",
        [
            !empty($facility_ids) ? implode('|', $facility_ids) : '',
            !empty($provider_ids) ? implode('|', $provider_ids) : '',
            $data['email']
        ]
    );

    // Background services are not used by the module. External sync is managed outside OpenEMR.

    // Now perform initial sync with all facilities and providers
    if (!empty($facility_ids) || !empty($provider_ids)) {
        $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
        $syncResult = $practiceService->performInitialSync();

        // Add sync status to result
        $result['sync_performed'] = true;
        $result['sync_success'] = $syncResult['success'] ?? false;
        $result['facilities_synced'] = count($facility_ids);
        $result['providers_synced'] = count($provider_ids);

        if (!empty($syncResult['error'])) {
            $result['sync_error'] = $syncResult['error'];
        }
    } else {
        $result['sync_performed'] = false;
        $result['sync_message'] = 'No facilities or providers with calendars found to sync';
    }
}

    // Return result
    echo json_encode($result);

} catch (\Exception $e) {
    error_log("Registration process error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Registration error: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
} catch (\Error $e) {
    error_log("Registration process fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'debug' => $e->getFile() . ':' . $e->getLine()
    ]);
}
