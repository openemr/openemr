<?php

/**
 * Stores one-time OIDC state for the External IdP module.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Service;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

final class OidcStateService
{
    private const SESSION_KEY = 'external_idp_oidc_pending';
    private const TTL_SECONDS = 300;
    private const ALLOWED_LOGIN_OPTIONS = ['languageChoice', 'facility', 'appChoice'];

    public static function generateToken(int $bytes = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }

    public static function createPkceChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    public static function normalizeSiteId(mixed $siteId): string
    {
        if (!is_scalar($siteId)) {
            return 'default';
        }

        $siteId = trim((string) $siteId);
        if ($siteId === '' || !preg_match('/^[A-Za-z0-9_.-]{1,63}$/', $siteId)) {
            return 'default';
        }
        return $siteId;
    }

    public static function buildReturnTarget(string $siteId): string
    {
        return OEGlobalsBag::getInstance()->getWebRoot() . '/interface/main/main_screen.php?auth=external&site=' . rawurlencode(self::normalizeSiteId($siteId));
    }

    /**
     * @param array<string, scalar|null> $loginOptions
     * @return array<string, mixed>
     */
    public function store(string $providerId, string $siteId, array $loginOptions): array
    {
        $providerId = $this->normalizeProviderId($providerId);
        $siteId = self::normalizeSiteId($siteId);
        $state = [
            'provider_id' => $providerId,
            'site_id' => $siteId,
            'state' => self::generateToken(32),
            'nonce' => self::generateToken(32),
            'pkce_verifier' => self::generateToken(64),
            'created_at' => time(),
            'return_target' => self::buildReturnTarget($siteId),
            'login_options' => $this->filterLoginOptions($loginOptions),
        ];
        $state['code_challenge'] = self::createPkceChallenge($state['pkce_verifier']);

        SessionWrapperFactory::getInstance()->getActiveSession()->set(self::SESSION_KEY, $state);
        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    public function consume(string $state): array
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $pending = $session->get(self::SESSION_KEY);

        if (
            !is_array($pending)
            || !is_string($pending['provider_id'] ?? null)
            || !is_string($pending['site_id'] ?? null)
            || !is_string($pending['state'] ?? null)
            || !is_string($pending['nonce'] ?? null)
            || !is_string($pending['pkce_verifier'] ?? null)
            || !is_string($pending['code_challenge'] ?? null)
            || !is_string($pending['return_target'] ?? null)
            || !is_array($pending['login_options'] ?? null)
            || !is_int($pending['created_at'] ?? null)
        ) {
            $session->remove(self::SESSION_KEY);
            throw new \RuntimeException('OIDC authentication state is missing or invalid.');
        }

        if (!hash_equals($pending['state'], $state)) {
            $session->remove(self::SESSION_KEY);
            throw new \RuntimeException('OIDC authentication state did not match.');
        }

        if ($pending['created_at'] < (time() - self::TTL_SECONDS)) {
            $session->remove(self::SESSION_KEY);
            throw new \RuntimeException('OIDC authentication state expired.');
        }

        $session->remove(self::SESSION_KEY);
        return $pending;
    }

    private function normalizeProviderId(string $providerId): string
    {
        $providerId = trim($providerId);
        if ($providerId === '' || !preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,63}$/', $providerId)) {
            throw new \InvalidArgumentException('OIDC provider ID is invalid.');
        }

        return $providerId;
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
