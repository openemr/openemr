<?php

/**
 * GCIP Login Initiator
 * 
 * <!-- AI-Generated Content Start -->
 * This script initiates the GCIP OAuth2 authentication flow by redirecting
 * users to Google's authorization server with the appropriate parameters
 * and CSRF protection state.
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

// Initialize module classes - AI-Generated
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", dirname(__DIR__, 2) . '/src');

$configService = new GcipConfigService();
$authService = new GcipAuthService($configService);
$auditService = new GcipAuditService();

// Check if GCIP is enabled - AI-Generated
if (!$configService->isGcipEnabled()) {
    http_response_code(403);
    die('GCIP authentication is not enabled');
}

// Start session for state management - AI-Generated
session_start();

// Generate state parameter from request or create new one - AI-Generated
$state = $_GET['state'] ?? bin2hex(random_bytes(32));

// Store state in session for CSRF protection - AI-Generated
$_SESSION['gcip_oauth_state'] = $state;

// Store return URL if provided - AI-Generated
if (isset($_GET['return_url'])) {
    $_SESSION['gcip_return_url'] = $_GET['return_url'];
}

// Generate authorization URL - AI-Generated
$authUrl = $authService->getAuthorizationUrl($state);

if (!$authUrl) {
    $auditService->logSecurityEvent('CONFIG_ERROR', 'Failed to generate GCIP authorization URL');
    
    $loginUrl = $GLOBALS['webroot'] . '/interface/login/login.php?gcip_error=' . urlencode('GCIP configuration error');
    header("Location: {$loginUrl}");
    exit;
}

// Log authentication initiation - AI-Generated
$auditService->logAuthenticationAttempt('unknown', 'oauth_initiation', 'GCIP OAuth2 flow initiated', [
    'state' => $state,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);

// Redirect to Google OAuth2 authorization - AI-Generated
header("Location: {$authUrl}");
exit;