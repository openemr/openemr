<?php

/**
 * Helpers for storing and retrieving OIDC token metadata in the PHP session.
 *
 * After OIDC authentication, the module stores token metadata (expiry, issuer,
 * jti) in the session. On subsequent requests, the re-validation check uses
 * this metadata to determine whether the session is still bound to a valid
 * token.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Session;

use OpenEMR\Common\Session\SessionWrapperFactory;

final class OidcSessionHelper
{
    private const KEY_TOKEN_EXPIRY = 'oidc_token_expiry';
    private const KEY_ISSUER = 'oidc_issuer';
    private const KEY_JTI = 'oidc_jti';
    private const KEY_SUBJECT = 'oidc_subject';
    private const KEY_AUDIENCE = 'oidc_audience';
    private const KEY_AUTH_METHOD = 'oidc_auth_method';

    /**
     * Store OIDC token metadata in the session after successful authentication.
     */
    public static function setTokenMetadata(
        \DateTimeImmutable $tokenExpiry,
        string $issuer,
        ?string $jti = null,
        ?string $subject = null,
        ?string $audience = null,
    ): void {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set(self::KEY_TOKEN_EXPIRY, $tokenExpiry->getTimestamp());
        $session->set(self::KEY_ISSUER, $issuer);
        $session->set(self::KEY_JTI, $jti);
        $session->set(self::KEY_SUBJECT, $subject);
        $session->set(self::KEY_AUDIENCE, $audience);
        $session->set(self::KEY_AUTH_METHOD, 'oidc');
    }

    /**
     * Check if the current session was authenticated via OIDC.
     */
    public static function isOidcSession(): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        return $session->get(self::KEY_AUTH_METHOD) === 'oidc';
    }

    /**
     * Get the token expiry timestamp, or null if not an OIDC session.
     */
    public static function getTokenExpiry(): ?int
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $expiry = $session->get(self::KEY_TOKEN_EXPIRY);
        return is_int($expiry) ? $expiry : null;
    }

    /**
     * Get the OIDC issuer for this session.
     */
    public static function getIssuer(): ?string
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $issuer = $session->get(self::KEY_ISSUER);
        return is_string($issuer) ? $issuer : null;
    }

    /**
     * Get the JTI (JWT ID) for this session.
     */
    public static function getJti(): ?string
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $jti = $session->get(self::KEY_JTI);
        return is_string($jti) ? $jti : null;
    }

    /**
     * Get the OIDC subject (sub claim) for this session.
     */
    public static function getSubject(): ?string
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $subject = $session->get(self::KEY_SUBJECT);
        return is_string($subject) ? $subject : null;
    }

    /**
     * Get the OIDC audience (client ID) for this session.
     */
    public static function getAudience(): ?string
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $audience = $session->get(self::KEY_AUDIENCE);
        return is_string($audience) ? $audience : null;
    }

    /**
     * Update the token expiry after a silent refresh.
     */
    public static function updateTokenExpiry(\DateTimeImmutable $newExpiry, ?string $newJti = null): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set(self::KEY_TOKEN_EXPIRY, $newExpiry->getTimestamp());
        if ($newJti !== null) {
            $session->set(self::KEY_JTI, $newJti);
        }
    }

    /**
     * Check if the OIDC token has expired (with optional grace period).
     *
     * @param int $now             Current Unix timestamp.
     * @param int $graceSeconds    Grace period in seconds (default 0).
     */
    public static function isTokenExpired(int $now, int $graceSeconds = 0): bool
    {
        $expiry = self::getTokenExpiry();
        if ($expiry === null) {
            return false; // Not an OIDC session
        }

        return $now > ($expiry + $graceSeconds);
    }

    /**
     * Clear OIDC session metadata (on logout or session destruction).
     */
    public static function clearTokenMetadata(): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->remove(self::KEY_TOKEN_EXPIRY);
        $session->remove(self::KEY_ISSUER);
        $session->remove(self::KEY_JTI);
        $session->remove(self::KEY_SUBJECT);
        $session->remove(self::KEY_AUDIENCE);
        $session->remove(self::KEY_AUTH_METHOD);
    }
}
