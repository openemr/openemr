<?php
declare(strict_types=1);

/**
 * Email Queue Detail Endpoint
 * Written with Warp-Terminal
 * Returns email details by ID for modal display
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated for PoppyBilling
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Start output buffering to catch any errors
ob_start();

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Reports\Email\EmailQueueService;
use Throwable;

// Clear any error output that may have occurred
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    // Verify user is authenticated
    if (!isset($_SESSION['authUserID']) || (int) $_SESSION['authUserID'] <= 0) {
        throw new Exception("User not authenticated");
    }

    // ACL check - requires billing or admin access
    if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
        throw new Exception("Access denied: insufficient permissions");
    }

    // Get email ID from request
    $emailId = (int)($_GET['id'] ?? 0);
    if ($emailId <= 0) {
        throw new Exception("Email ID is required");
    }

    // Initialize service and get email details
    $service = new EmailQueueService();
    $email = $service->getEmailById($emailId);

    if ($email === null) {
        throw new Exception("Email not found");
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'email' => $email
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to load email details.'
    ]);
    (new SystemLogger())->error("Email queue detail fetch failed", ['message' => $e->getMessage()]);
}
