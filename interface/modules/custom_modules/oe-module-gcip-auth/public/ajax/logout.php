<?php

/**
 * GCIP Logout Handler
 * 
 * <!-- AI-Generated Content Start -->
 * This AJAX endpoint handles GCIP-specific logout operations including
 * token cleanup and session data removal for users authenticated
 * through Google Cloud Identity Platform.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 7) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Modules\GcipAuth\Services\GcipAuthService;
use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;

// Set JSON response headers - AI-Generated
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow POST requests - AI-Generated
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Start session - AI-Generated
session_start();

// Check if user is GCIP authenticated - AI-Generated
if (!isset($_SESSION['gcip_authenticated']) || !$_SESSION['gcip_authenticated']) {
    echo json_encode(['success' => true, 'message' => 'Not GCIP authenticated']);
    exit;
}

try {
    // Initialize module classes - AI-Generated
    $classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
    $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", dirname(__DIR__, 2) . '/src');

    $configService = new GcipConfigService();
    $authService = new GcipAuthService($configService);
    $auditService = new GcipAuditService();

    // Get current user info for cleanup - AI-Generated
    $username = $_SESSION['authUser'] ?? 'unknown';
    
    // Clean up GCIP session data - AI-Generated
    $authService->cleanupUserSession($username);
    
    // Remove GCIP-specific session variables - AI-Generated
    unset($_SESSION['gcip_authenticated']);
    unset($_SESSION['gcip_email']);
    unset($_SESSION['gcip_name']);
    unset($_SESSION['gcip_picture']);
    unset($_SESSION['gcip_oauth_state']);
    unset($_SESSION['gcip_return_url']);
    
    // Log logout event - AI-Generated
    $auditService->logLogout($username);
    
    echo json_encode(['success' => true, 'message' => 'GCIP logout completed']);
    
} catch (Exception $e) {
    error_log('GCIP logout error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Logout processing failed']);
}

exit;