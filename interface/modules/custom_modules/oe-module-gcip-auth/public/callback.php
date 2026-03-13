<?php

/**
 * GCIP OAuth2 Callback Handler
 * 
 * <!-- AI-Generated Content Start -->
 * This file handles the OAuth2 callback from Google Cloud Identity Platform,
 * processes the authorization code, validates the user, and completes the
 * authentication flow for OpenEMR integration.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once dirname(__FILE__, 6) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Modules\GcipAuth\Services\GcipAuthService;
use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;
use OpenEMR\Common\Auth\AuthUtils;

// Initialize module classes - AI-Generated
$classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
$classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", dirname(__DIR__) . '/src');

$configService = new GcipConfigService();
$authService = new GcipAuthService($configService);
$auditService = new GcipAuditService();

// Check if GCIP is enabled - AI-Generated
if (!$configService->isGcipEnabled()) {
    http_response_code(403);
    die('GCIP authentication is not enabled');
}

// Validate callback parameters - AI-Generated
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

// Handle OAuth2 errors - AI-Generated
if ($error) {
    $errorDescription = $_GET['error_description'] ?? 'Unknown error';
    $auditService->logFailedAuthentication('unknown', "OAuth2 error: {$error} - {$errorDescription}");
    
    // Redirect to login with error message - AI-Generated
    $loginUrl = $GLOBALS['webroot'] . '/interface/login/login.php?gcip_error=' . urlencode($errorDescription);
    header("Location: {$loginUrl}");
    exit;
}

// Validate required parameters - AI-Generated
if (!$code || !$state) {
    $auditService->logFailedAuthentication('unknown', 'Missing required OAuth2 parameters');
    
    $loginUrl = $GLOBALS['webroot'] . '/interface/login/login.php?gcip_error=' . urlencode('Invalid callback parameters');
    header("Location: {$loginUrl}");
    exit;
}

// Validate state parameter against session to prevent CSRF - AI-Generated
session_start();
if (!isset($_SESSION['gcip_oauth_state']) || $_SESSION['gcip_oauth_state'] !== $state) {
    $auditService->logSecurityEvent('CSRF_ATTEMPT', 'Invalid state parameter in OAuth2 callback', [
        'provided_state' => $state,
        'session_state' => $_SESSION['gcip_oauth_state'] ?? 'none'
    ]);
    
    unset($_SESSION['gcip_oauth_state']);
    
    $loginUrl = $GLOBALS['webroot'] . '/interface/login/login.php?gcip_error=' . urlencode('Security validation failed');
    header("Location: {$loginUrl}");
    exit;
}

// Clear the state from session - AI-Generated
unset($_SESSION['gcip_oauth_state']);

try {
    // Exchange authorization code for tokens - AI-Generated
    $tokenData = $authService->exchangeCodeForToken($code);
    
    if (!$tokenData) {
        throw new Exception('Failed to exchange authorization code for tokens');
    }
    
    if (isset($tokenData['error'])) {
        throw new Exception('Token exchange error: ' . ($tokenData['error_description'] ?? $tokenData['error']));
    }
    
    // Authenticate user with GCIP tokens - AI-Generated
    $authResult = $authService->authenticateUser($tokenData);
    
    if (!$authResult['success']) {
        throw new Exception($authResult['error'] ?? 'Authentication failed');
    }
    
    $user = $authResult['user'];
    $tokenPayload = $authResult['token_data'];
    
    // Start OpenEMR session - AI-Generated
    $authUser = $user['username'];
    $authGroup = $user['authorized'] ?? 1;
    $authProvider = 'gcip';
    
    // Set OpenEMR session variables - AI-Generated
    $_SESSION['authUser'] = $authUser;
    $_SESSION['authGroup'] = $authGroup;
    $_SESSION['authUserID'] = $user['id'];
    $_SESSION['authProvider'] = $authProvider;
    $_SESSION['userauthorized'] = $user['authorized'] ?? 1;
    
    // Store GCIP-specific session data - AI-Generated
    $_SESSION['gcip_authenticated'] = true;
    $_SESSION['gcip_email'] = $tokenPayload['email'] ?? '';
    $_SESSION['gcip_name'] = $tokenPayload['name'] ?? '';
    $_SESSION['gcip_picture'] = $tokenPayload['picture'] ?? '';
    
    // Update user's last login time - AI-Generated
    $currentTime = date('Y-m-d H:i:s');
    sqlStatementNoLog(
        "UPDATE users SET last_login_date = ? WHERE id = ?",
        [$currentTime, $user['id']]
    );
    
    // Update GCIP user mapping - AI-Generated
    $gcipUserId = $tokenPayload['sub'] ?? '';
    if ($gcipUserId) {
        sqlStatementNoLog(
            "INSERT INTO module_gcip_user_mapping 
             (openemr_user_id, gcip_user_id, gcip_email, gcip_name, gcip_picture, last_login) 
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
             gcip_email = VALUES(gcip_email),
             gcip_name = VALUES(gcip_name),
             gcip_picture = VALUES(gcip_picture),
             last_login = VALUES(last_login)",
            [
                $user['id'],
                $gcipUserId,
                $tokenPayload['email'] ?? '',
                $tokenPayload['name'] ?? '',
                $tokenPayload['picture'] ?? '',
                $currentTime
            ]
        );
    }
    
    // Log successful authentication - AI-Generated
    $auditService->logSuccessfulAuthentication($authUser, [
        'email' => $tokenPayload['email'] ?? '',
        'name' => $tokenPayload['name'] ?? '',
        'gcip_user_id' => $gcipUserId
    ]);
    
    // Redirect to main interface - AI-Generated
    $redirectUrl = $_SESSION['gcip_return_url'] ?? ($GLOBALS['webroot'] . '/interface/main/main_screen.php');
    unset($_SESSION['gcip_return_url']);
    
    header("Location: {$redirectUrl}");
    exit;
    
} catch (Exception $e) {
    // Log authentication failure - AI-Generated
    $auditService->logFailedAuthentication(
        $tokenPayload['email'] ?? 'unknown',
        $e->getMessage(),
        [
            'code_present' => !empty($code),
            'state_valid' => true,
            'exception' => $e->getMessage()
        ]
    );
    
    // Redirect to login with error - AI-Generated
    $loginUrl = $GLOBALS['webroot'] . '/interface/login/login.php?gcip_error=' . urlencode($e->getMessage());
    header("Location: {$loginUrl}");
    exit;
}