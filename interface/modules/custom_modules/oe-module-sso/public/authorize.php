<?php

/**
 * SSO Authorization Endpoint - Initiates the OIDC authorization flow
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Load OpenEMR bootstrap (no auth required for SSO initiation)
$ignoreAuth = true;
$sessionAllowWrite = true;  // Allow session writes if needed
require_once dirname(__FILE__, 5) . '/globals.php';

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\SSO\Services\ProviderRegistry;
use OpenEMR\Modules\SSO\Services\SessionBridge;
use OpenEMR\Modules\SSO\Services\TokenService;

$logger = new SystemLogger();

try {
    // Get provider ID from request
    $providerId = $_GET['provider'] ?? '';
    if (empty($providerId)) {
        throw new RuntimeException(xl('No provider specified'));
    }

    // Get provider
    $registry = new ProviderRegistry();
    $provider = $registry->getProvider($providerId);

    if (!$provider) {
        throw new RuntimeException(xl('Invalid provider') . ': ' . $providerId);
    }

    if (!$provider->isEnabled()) {
        throw new RuntimeException(xl('Provider is not enabled') . ': ' . $providerId);
    }

    // Generate PKCE values and state
    $tokenService = new TokenService();
    $state = $tokenService->generateState();
    $nonce = $tokenService->generateNonce();
    $codeVerifier = $tokenService->generateCodeVerifier();
    $codeChallenge = $tokenService->generateCodeChallenge($codeVerifier);

    // Store state for validation in callback
    $sessionBridge = new SessionBridge();
    $providerDbId = $registry->getProviderDbId($providerId);

    if (!$providerDbId) {
        throw new RuntimeException(xl('Provider not found in database'));
    }

    // Get site_id to preserve through OAuth flow
    $siteId = $_SESSION['site_id'] ?? $_GET['site'] ?? 'default';

    $sessionBridge->storeAuthState($providerDbId, $state, $nonce, $codeVerifier, $siteId);

    // Build authorization URL
    $authUrl = $provider->buildAuthorizationUrl($state, $nonce, $codeChallenge);

    // Log the authorization attempt
    $sessionBridge->logAuditEvent('auth_start', $providerDbId, null, [
        'provider' => $providerId,
    ]);

    // Redirect to IdP
    header('Location: ' . $authUrl);
    exit;
} catch (Exception $e) {
    $logger->errorLogCaller('SSO authorize error: ' . $e->getMessage());

    // Redirect back to login with error
    $errorMessage = urlencode(xl('SSO authentication failed') . ': ' . $e->getMessage());
    header('Location: ' . $GLOBALS['webroot'] . '/interface/login/login.php?error=' . $errorMessage);
    exit;
}
