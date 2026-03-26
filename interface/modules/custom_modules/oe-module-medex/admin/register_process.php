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

function medexIsPrivateHost(string $host): bool
{
    $host = strtolower(trim($host));
    if ($host === '' || $host === 'localhost') {
        return true;
    }

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    return false;
}

function medexValidateCallbackUrl(string $url): array
{
    $url = trim($url);
    if ($url === '') {
        return [false, 'Callback URL is required'];
    }
    if (stripos($url, 'https://') !== 0) {
        return [false, 'Callback URL must use HTTPS'];
    }
    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    if ($host === '') {
        return [false, 'Callback URL host is invalid'];
    }
    if (medexIsPrivateHost($host)) {
        return [false, 'Callback URL cannot be a private or local host'];
    }
    return [true, 'ok'];
}

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token (subject-first signature on this OpenEMR build).
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', 'default')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

try {
    // Load MedEx API and Services
    require_once(__DIR__ . '/../src/MedExAPI.php');
    require_once(__DIR__ . '/../src/Services/PracticeService.php');

    // Validate required fields (only email and password - practice details come from facility sync)
    $required = ['email', 'password', 'callback_url'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
            exit;
        }
    }

    [$callbackOk, $callbackErr] = medexValidateCallbackUrl((string)($_POST['callback_url'] ?? ''));
    if (!$callbackOk) {
        echo json_encode(['success' => false, 'error' => $callbackErr]);
        exit;
    }

// Create API instance
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Get primary facility details as default practice info
$facility = sqlQuery("SELECT name, phone, street, city, state, postal_code, country_code FROM facility WHERE primary_business_entity = 1 ORDER BY id LIMIT 1");
if (!$facility) {
    $facility = sqlQuery("SELECT name, phone, street, city, state, postal_code, country_code FROM facility ORDER BY id LIMIT 1");
}

$practice_name = $facility['name'] ?? $GLOBALS['openemr_name'] ?? 'OpenEMR Practice';
$practice_phone = $facility['phone'] ?? '';
$practice_street = trim($facility['street'] ?? '');
$practice_city = trim($facility['city'] ?? '');
$practice_state = trim($facility['state'] ?? '');
$practice_postcode = trim($facility['postal_code'] ?? '');
$practice_country_code = strtoupper(trim($facility['country_code'] ?? 'US'));
$providerCountRow = sqlQuery("SELECT COUNT(*) AS c FROM users WHERE authorized = 1 AND active = 1");
$facilityCountRow = sqlQuery("SELECT COUNT(*) AS c FROM facility WHERE service_location = 1");
$insuranceCountRow = sqlQuery("SELECT COUNT(*) AS c FROM insurance_companies");
$siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ''));

// Prepare registration data
$data = [
    'email' => trim($_POST['email']),
    'password' => $_POST['password'],
    'practice_name' => $practice_name,
    'phone' => $practice_phone,
    'address' => $practice_street,
    'street' => $practice_street,
    'city' => $practice_city,
    'state' => $practice_state,
    'postcode' => $practice_postcode,
    'country_code' => $practice_country_code,
    'callback_url' => trim($_POST['callback_url']),
    'site_url' => $siteUrl,
    'provider_count' => (int)($providerCountRow['c'] ?? 0),
    'facility_count' => (int)($facilityCountRow['c'] ?? 0),
    'insurance_count' => (int)($insuranceCountRow['c'] ?? 0),
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
} elseif (!empty($result['pending_review'])) {
    $result['success'] = false;
    $result['error'] = $result['message'] ?? 'Signup pending review by MedEx support.';
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
