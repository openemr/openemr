<?php
/**
 * Manual Sync Trigger
 *
 * Triggers immediate practice data sync to MedEx
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF token - check JSON body first, then POST param, then header
$csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? $_POST['csrf_token_form'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

// Debug logging
error_log("[MedEx Sync] CSRF token received: " . substr($csrfToken, 0, 20) . "...");
error_log("[MedEx Sync] Input: " . json_encode($input));

if (!CsrfUtils::verifyCsrfToken($csrfToken, 'default')) {
    error_log("[MedEx Sync] CSRF validation failed for token: " . substr($csrfToken, 0, 20));
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    // Load MedEx API and PracticeService
    require_once(__DIR__ . '/../../src/MedExAPI.php');
    require_once(__DIR__ . '/../../src/Services/PracticeService.php');

    $api = new \OpenEMR\Modules\MedEx\MedExAPI();

    if (!$api->isConfigured()) {
        throw new Exception('MedEx not configured');
    }

    if (!$api->isActive()) {
        throw new Exception('MedEx is not active. Please check your subscription.');
    }

    // Refuse to sync when there are no active subscriptions — prevents idle practices
    // from generating unnecessary server traffic.
    $enabledServices = $api->getEnabledServices(true);
    if (empty($enabledServices)) {
        http_response_code(402);
        echo json_encode([
            'success' => false,
            'error'   => 'No active MedEx subscriptions. Please subscribe to a service before syncing.',
        ]);
        exit;
    }

    // Perform sync
    $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
    $result = $practiceService->performInitialSync();

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Practice data synced successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        $errorMsg = $result['error'] ?? 'Sync failed';
        if (is_array($errorMsg)) {
            $errorMsg = json_encode($errorMsg);
        }
        throw new Exception($errorMsg);
    }

} catch (\Exception $e) {
    error_log('[MedEx] Manual sync error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
