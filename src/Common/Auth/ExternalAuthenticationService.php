<?php

/**
 * Safely transitions a validated external identity into the normal OpenEMR
 * staff-user login flow.
 *
 * External protocol validation belongs in a module. This service deliberately
 * accepts only an already-mapped local user ID and never receives tokens or
 * claims.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\UserService;

class ExternalAuthenticationService
{
    public const PENDING_SESSION_KEY = 'external_authentication_pending';
    public const LOGIN_OPTIONS_SESSION_KEY = 'external_authentication_login_options';
    private const PENDING_TTL_SECONDS = 300;
    private const ALLOWED_LOGIN_OPTIONS = ['languageChoice', 'facility', 'appChoice'];

    /**
     * Revalidate the local user and prepare a one-time continuation through the
     * standard OpenEMR MFA and session-creation flow.
     *
     * @param array<string, scalar|null> $loginOptions
     */
    public function complete(ExternalAuthenticationResult $result, array $loginOptions = []): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $userInfo = privQuery(
            'SELECT `id`, `username`, `authorized`, `see_auth`, `active` FROM `users` WHERE `id` = ?',
            [$result->userId]
        );
        if (empty($userInfo['id']) || empty($userInfo['username']) || (int) $userInfo['active'] !== 1) {
            EventAuditLogger::getInstance()->newEvent('external_login', '', '', 0, 'local user is missing or inactive');
            return false;
        }

        $username = (string) $userInfo['username'];
        $authGroup = (new UserService())->getAuthGroupForUser($username);
        if (empty($authGroup) || AclExtended::aclGetGroupTitles($username) == 0) {
            EventAuditLogger::getInstance()->newEvent('external_login', $username, (string) $authGroup, 0, 'local user is not authorized');
            return false;
        }

        $userSecure = privQuery(
            'SELECT `password` FROM `users_secure` WHERE `id` = ? AND BINARY `username` = ?',
            [$userInfo['id'], $username]
        );
        if (empty($userSecure['password'])) {
            EventAuditLogger::getInstance()->newEvent('external_login', $username, (string) $authGroup, 0, 'local user credentials are missing');
            return false;
        }

        AuthUtils::setUserSessionVariables($username, (string) $userSecure['password'], $userInfo, $authGroup);
        $session->set(self::PENDING_SESSION_KEY, [
            'provider_id' => $result->providerId,
            'created_at' => time(),
        ]);
        $session->set(self::LOGIN_OPTIONS_SESSION_KEY, $this->filterLoginOptions($loginOptions));
        EventAuditLogger::getInstance()->newEvent('external_login', $username, (string) $authGroup, 1, 'external provider ' . $result->providerId . ' authentication succeeded');
        return true;
    }

    public static function hasPendingAuthentication(): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $pending = $session->get(self::PENDING_SESSION_KEY);
        if ($pending === null) {
            return false;
        }
        if (
            !is_array($pending)
            || !is_string($pending['provider_id'] ?? null)
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,63}$/', $pending['provider_id'])
            || !is_int($pending['created_at'] ?? null)
        ) {
            self::clearPendingAuthentication();
            return false;
        }

        if ($pending['created_at'] < (time() - self::PENDING_TTL_SECONDS)) {
            self::clearPendingAuthentication();
            return false;
        }
        return true;
    }

    public static function getLoginOption(string $name): string|int|null
    {
        if (!in_array($name, self::ALLOWED_LOGIN_OPTIONS, true)) {
            throw new \InvalidArgumentException('Unsupported external login option.');
        }

        $options = SessionWrapperFactory::getInstance()->getActiveSession()->get(self::LOGIN_OPTIONS_SESSION_KEY);
        $value = is_array($options) ? ($options[$name] ?? null) : null;
        return is_string($value) || is_int($value) ? $value : null;
    }

    public static function clearPendingAuthentication(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove(self::PENDING_SESSION_KEY);
        $session->remove(self::LOGIN_OPTIONS_SESSION_KEY);
    }

    /**
     * @param array<string, scalar|null> $loginOptions
     * @return array<string, string|int>
     */
    private function filterLoginOptions(array $loginOptions): array
    {
        $filtered = [];
        foreach (self::ALLOWED_LOGIN_OPTIONS as $name) {
            $value = $loginOptions[$name] ?? null;
            if (is_string($value) || is_int($value)) {
                $filtered[$name] = $value;
            }
        }
        return $filtered;
    }
}
