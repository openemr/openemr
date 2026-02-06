<?php

/**
 * SSO Callback Endpoint - Handles the OIDC callback from identity providers
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Load OpenEMR bootstrap (no auth required - this is the SSO callback)
$ignoreAuth = true;
$sessionAllowWrite = true;  // Required to persist session data
require_once dirname(__FILE__, 5) . '/globals.php';

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\SSO\Services\ProviderRegistry;
use OpenEMR\Modules\SSO\Services\SessionBridge;
use OpenEMR\Modules\SSO\Services\UserLinkService;

$logger = new SystemLogger();
$sessionBridge = new SessionBridge();

try {
    // Check for error from IdP
    if (!empty($_GET['error'])) {
        $errorDesc = $_GET['error_description'] ?? $_GET['error'];
        throw new RuntimeException(xl('Identity provider error') . ': ' . $errorDesc);
    }

    // Get authorization code and state
    $code = $_GET['code'] ?? '';
    $state = $_GET['state'] ?? '';

    if (empty($code) || empty($state)) {
        throw new RuntimeException(xl('Missing authorization code or state'));
    }

    // Validate state and retrieve auth context
    $authState = $sessionBridge->retrieveAuthState($state);
    if (!$authState) {
        throw new RuntimeException(xl('Invalid or expired authentication session. Please try again.'));
    }

    $providerId = $authState['provider_id'];
    $nonce = $authState['nonce'];
    $codeVerifier = $authState['code_verifier'];
    $siteId = $authState['site_id'] ?? 'default';

    // Get provider by database ID
    $providerType = sqlQuery(
        "SELECT provider_type, auto_provision, default_acl FROM sso_providers WHERE id = ?",
        [$providerId]
    );

    if (!$providerType) {
        throw new RuntimeException(xl('Provider not found'));
    }

    $registry = new ProviderRegistry();
    $provider = $registry->getProvider($providerType['provider_type']);

    if (!$provider || !$provider->isEnabled()) {
        throw new RuntimeException(xl('Provider is not available'));
    }

    // Exchange code for tokens
    $tokens = $provider->exchangeCodeForTokens($code, $codeVerifier);

    if (empty($tokens['id_token'])) {
        throw new RuntimeException(xl('No ID token in response'));
    }

    // Validate ID token
    $claims = $provider->validateIdToken($tokens['id_token'], $nonce);

    // Extract user info
    $userInfo = $provider->extractUserInfo($claims);

    // Find or link OpenEMR user
    $userLinkService = new UserLinkService();
    $autoProvision = (bool)$providerType['auto_provision'];
    $defaultAcl = $providerType['default_acl'];

    $user = $userLinkService->findOrLinkUser($providerId, $userInfo, $autoProvision, $defaultAcl);

    if (!$user) {
        $sessionBridge->logAuditEvent('login_failure', $providerId, null, [
            'reason' => 'no_matching_user',
            'email' => $userInfo['email'] ?? 'unknown',
        ]);
        throw new RuntimeException(
            xl('No matching OpenEMR user found for') . ' ' . ($userInfo['email'] ?? xl('this account')) .
            '. ' . xl('Please contact your administrator.')
        );
    }

    // Check if user is active
    if (empty($user['active'])) {
        $sessionBridge->logAuditEvent('login_failure', $providerId, (int)$user['id'], [
            'reason' => 'user_inactive',
        ]);
        throw new RuntimeException(xl('Your account is inactive. Please contact your administrator.'));
    }

    // Create OpenEMR session
    $sessionBridge->createSession($user, $providerType['provider_type'], $siteId);

    // Log successful login
    $sessionBridge->logAuditEvent('login_success', $providerId, (int)$user['id'], [
        'username' => $user['username'],
    ]);

    // Store tokens in session for potential logout
    $_SESSION['sso_tokens'] = [
        'id_token' => $tokens['id_token'],
        'access_token' => $tokens['access_token'] ?? null,
    ];

    // Redirect to main screen using POST with CSRF token
    // This maintains CSRF protection without requiring core changes
    $redirectUrl = $GLOBALS['webroot'] . '/interface/main/main_screen.php?site=' . urlencode($siteId);
    $csrfToken = CsrfUtils::collectCsrfToken();
    ?>
    <!DOCTYPE html>
    <html>
    <head><title><?php echo xlt('Redirecting'); ?>...</title></head>
    <body>
        <p><?php echo xlt('Authentication successful. Redirecting'); ?>...</p>
        <form id="sso-redirect" method="POST" action="<?php echo attr($redirectUrl); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>">
        </form>
        <script>document.getElementById('sso-redirect').submit();</script>
        <noscript>
            <p><?php echo xlt('JavaScript is required. Please click the button below.'); ?></p>
            <button type="submit" form="sso-redirect"><?php echo xlt('Continue'); ?></button>
        </noscript>
    </body>
    </html>
    <?php
    exit;
} catch (Exception $e) {
    $logger->errorLogCaller('SSO callback error: ' . $e->getMessage());

    // Log failure
    $sessionBridge->logAuditEvent('login_failure', $providerId ?? null, null, [
        'error' => $e->getMessage(),
    ]);

    // Redirect back to login with error
    $errorMessage = urlencode($e->getMessage());
    header('Location: ' . $GLOBALS['webroot'] . '/interface/login/login.php?error=' . $errorMessage);
    exit;
}
