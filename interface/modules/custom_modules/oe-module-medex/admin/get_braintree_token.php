<?php
/**
 * MedEx Module - Get Braintree Client Token
 *
 * Retrieves Braintree client token from MedEx API for initializing payment form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access (GET endpoint — ACL is sufficient, no CSRF needed)
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

try {
    // Call the MedEx server via MedExAPI (uses stored credentials and session token)
    require_once(__DIR__ . '/../src/MedExAPI.php');
    $api  = new \OpenEMR\Modules\MedEx\MedExAPI();
    $data = $api->makeRequest('index.php?route=api/oemr/braintree_token', [], 'GET');

    // oemr.php braintree_token() returns 'token' key
    $clientToken = $data['clientToken'] ?? $data['token'] ?? null;
    if (!isset($data['success']) || !$data['success'] || !$clientToken) {
        throw new Exception('MedEx API error: ' . ($data['error'] ?? 'Unknown error'));
    }

    echo json_encode([
        'success'     => true,
        'clientToken' => $clientToken
    ]);

} catch (\Exception $e) {
    error_log('[MedEx] Braintree token error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to initialize payment system',
        'message' => $e->getMessage()
    ]);
}
