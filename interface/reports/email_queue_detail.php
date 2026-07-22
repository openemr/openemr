<?php

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

declare(strict_types=1);

// Start output buffering to catch any errors
ob_start();

require_once("../globals.php");

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Reports\Email\EmailQueueService;
use RuntimeException;

// Clear any error output that may have occurred
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

try {
    // Verify user is authenticated
    $authUserId = filter_var($_SESSION['authUserID'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if (!is_int($authUserId)) {
        throw new RuntimeException("User not authenticated");
    }

    // ACL check - requires billing or admin access
    if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
        throw new RuntimeException("Access denied: insufficient permissions");
    }

    // Get email ID from request
    $emailId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    if (!is_int($emailId)) {
        throw new RuntimeException("Email ID is required");
    }

    // Initialize service and get email details
    $service = new EmailQueueService();
    $email = $service->getEmailById($emailId);

    if ($email === null) {
        throw new RuntimeException("Email not found");
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'email' => $email
    ]);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to load email details.'
    ]);
    ServiceContainer::getLogger()->error("Email queue detail fetch failed", ['message' => $e->getMessage()]);
}
