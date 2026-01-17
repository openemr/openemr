<?php

/**
 * Session Bridge - Integrates SSO authentication with OpenEMR sessions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Services;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionTracker;
use OpenEMR\Common\Session\SessionWrapperFactory;

class SessionBridge
{
    private SystemLogger $logger;

    public function __construct()
    {
        $this->logger = new SystemLogger();
    }

    /**
     * Create an OpenEMR session for an SSO-authenticated user
     *
     * @param array $user OpenEMR user data from users table
     * @param string $providerId SSO provider identifier
     * @param string $siteId Site identifier
     * @return bool Success
     */
    public function createSession(array $user, string $providerId, string $siteId = 'default'): bool
    {
        // Get the user's password hash from users_secure (required for session validation)
        $userSecure = sqlQuery(
            "SELECT password FROM users_secure WHERE id = ?",
            [$user['id']]
        );
        $passwordHash = $userSecure['password'] ?? '';

        // Get user's ACL group (using username, matching UserService::getAuthGroupForUser)
        $authGroup = $this->getUserGroup($user['username']);

        // Use OpenEMR's session wrapper
        $session = SessionWrapperFactory::getInstance()->getWrapper();

        // Set site FIRST - this is required by OpenEMR
        $session->set('site_id', $siteId);

        // Set core session variables (matches AuthUtils::setUserSessionVariables)
        $session->set('authUser', $user['username']);
        $session->set('authPass', $passwordHash);  // Required for authCheckSession()
        $session->set('authUserID', $user['id']);
        $session->set('authProvider', $authGroup);
        $session->set('userauthorized', $user['authorized'] ?? 0);

        // Some users may be able to authorize without being providers
        if (($user['see_auth'] ?? 0) > 2) {
            $session->set('userauthorized', '1');
        }

        // Mark this as an SSO session (used by main_screen.php per PR #10213)
        $session->set('auth_method', $providerId);

        // Additional session variables used throughout OpenEMR
        $session->set('language_choice', $user['language'] ?? 1);

        // Set facility if available
        if (!empty($user['facility_id'])) {
            $session->set('pc_facility', $user['facility_id']);
        }

        // Set up session database tracker (required for session expiration checks)
        SessionTracker::setupSessionDatabaseTracker();

        // Log successful SSO login
        $this->logger->debug('SSO session created', [
            'username' => $user['username'],
            'user_id' => $user['id'],
            'provider' => $providerId,
            'site_id' => $siteId,
        ]);

        return true;
    }

    /**
     * Get user's ACL group from the groups table
     * (matches UserService::getAuthGroupForUser)
     */
    private function getUserGroup(string $username): string
    {
        $result = sqlQuery(
            "SELECT `name` FROM `groups` WHERE BINARY `user` = ?",
            [$username]
        );

        return $result['name'] ?? 'Default';
    }

    /**
     * Store auth state for PKCE flow
     */
    public function storeAuthState(
        int $providerId,
        string $state,
        string $nonce,
        string $codeVerifier,
        string $siteId = 'default',
        int $ttlSeconds = 600
    ): bool {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);

        sqlStatement(
            "INSERT INTO sso_auth_states (state, nonce, code_verifier, provider_id, site_id, expires_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$state, $nonce, $codeVerifier, $providerId, $siteId, $expiresAt]
        );

        // Clean up expired states
        sqlStatement("DELETE FROM sso_auth_states WHERE expires_at < NOW()");

        return true;
    }

    /**
     * Retrieve and validate auth state
     */
    public function retrieveAuthState(string $state): ?array
    {
        $result = sqlQuery(
            "SELECT * FROM sso_auth_states WHERE state = ? AND expires_at > NOW()",
            [$state]
        );

        if (!$result) {
            return null;
        }

        // Delete the state (one-time use)
        sqlStatement("DELETE FROM sso_auth_states WHERE state = ?", [$state]);

        return [
            'nonce' => $result['nonce'],
            'code_verifier' => $result['code_verifier'],
            'provider_id' => (int)$result['provider_id'],
            'site_id' => $result['site_id'] ?? 'default',
        ];
    }

    /**
     * Destroy the current session (logout)
     */
    public function destroySession(): void
    {
        // Clear all session variables
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
    }

    /**
     * Check if current session is an SSO session
     */
    public function isSsoSession(): bool
    {
        return isset($_SESSION['auth_method']) && $_SESSION['auth_method'] !== 'local';
    }

    /**
     * Get the SSO provider for current session
     */
    public function getSessionProvider(): ?string
    {
        if (!$this->isSsoSession()) {
            return null;
        }
        return $_SESSION['auth_method'];
    }

    /**
     * Log SSO audit event
     */
    public function logAuditEvent(
        string $eventType,
        ?int $providerId = null,
        ?int $userId = null,
        array $eventData = []
    ): void {
        sqlStatement(
            "INSERT INTO sso_audit_log (provider_id, user_id, event_type, event_data, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $providerId,
                $userId,
                $eventType,
                json_encode($eventData),
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            ]
        );
    }
}
