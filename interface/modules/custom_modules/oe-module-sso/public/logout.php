<?php

/**
 * SSO Logout Endpoint - Handles single logout
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Load OpenEMR bootstrap (no auth required for logout handling)
$ignoreAuth = true;
require_once dirname(__FILE__, 5) . '/globals.php';

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\SSO\Services\ProviderRegistry;
use OpenEMR\Modules\SSO\Services\SessionBridge;

$logger = new SystemLogger();
$sessionBridge = new SessionBridge();

try {
    // Check if this is an SSO session
    if (!$sessionBridge->isSsoSession()) {
        // Not an SSO session, just redirect to normal logout
        header('Location: ' . $GLOBALS['webroot'] . '/interface/logout.php');
        exit;
    }

    $providerType = $sessionBridge->getSessionProvider();
    $userId = $_SESSION['authUserID'] ?? null;

    // Log logout
    $registry = new ProviderRegistry();
    $providerId = $registry->getProviderDbId($providerType);
    $sessionBridge->logAuditEvent('logout', $providerId, $userId);

    // Get provider for logout URL
    $provider = $registry->getProvider($providerType);

    // Build post-logout redirect URL
    $postLogoutRedirect = $GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . '/interface/login/login.php';

    // Destroy local session first
    $sessionBridge->destroySession();

    // If provider supports single logout, redirect there
    if ($provider && $provider->isEnabled()) {
        $logoutUrl = $provider->buildLogoutUrl($postLogoutRedirect);
        header('Location: ' . $logoutUrl);
        exit;
    }

    // Fallback to local logout
    header('Location: ' . $postLogoutRedirect);
    exit;
} catch (Exception $e) {
    $logger->errorLogCaller('SSO logout error: ' . $e->getMessage());

    // Ensure session is destroyed even on error
    $sessionBridge->destroySession();

    // Redirect to login
    header('Location: ' . $GLOBALS['webroot'] . '/interface/login/login.php');
    exit;
}
