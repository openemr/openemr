<?php

/**
 * Business-logic handler for OIDC session refresh.
 *
 * Accepts the pre-extracted request data (ID token, session-stored issuer /
 * audience / subject, current username) and runs the validation pipeline:
 * discovery → token validation → issuer pinning → subject pinning → audit.
 *
 * Returns an {@see OidcSessionRefreshResult} that the HTTP endpoint
 * translates into a JSON response. The handler knows nothing about HTTP,
 * sessions, or CSRF — those are the endpoint's responsibility.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Session;

use OpenEMR\Common\Auth\Oidc\Audit\OidcRefreshAuditLoggerInterface;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidationException;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\Oidc\Token\OidcValidationParameters;

readonly class OidcSessionRefreshHandler
{
    public function __construct(
        private OidcTokenValidator $tokenValidator,
        private OidcDiscoveryClient $discoveryClient,
        private OidcRefreshAuditLoggerInterface $auditLogger,
        private int $clockSkewSeconds = 30,
    ) {
    }

    /**
     * @param non-empty-string $idToken         The fresh ID token from the client.
     * @param non-empty-string $sessionIssuer   The issuer stored in the session at login.
     * @param non-empty-string $sessionAudience The audience stored in the session at login.
     * @param string           $sessionSubject  The subject (external ID) stored in the session at login. Must be non-empty; an empty value is rejected by the runtime check below as defense-in-depth against a caller that forgets to pre-validate.
     * @param string           $username        The local OpenEMR username for audit logging.
     */
    public function handle(
        string $idToken,
        string $sessionIssuer,
        string $sessionAudience,
        string $sessionSubject,
        string $username,
    ): OidcSessionRefreshResult {
        // Subject pins the session to an external identity. Refusing refresh
        // when it's missing prevents a session in an inconsistent state from
        // being kept alive by any token from the same issuer/audience.
        if ($sessionSubject === '') {
            $this->auditLogger->subjectMismatch($username);
            return OidcSessionRefreshResult::error(401, 'subject_missing');
        }

        // 1. Discover provider metadata
        try {
            $metadata = $this->discoveryClient->getMetadata($sessionIssuer);
        } catch (OidcDiscoveryException) {
            $this->auditLogger->discoveryFailed($username);
            return OidcSessionRefreshResult::error(401, 'token_invalid', 'Discovery failed');
        }

        // 2. Validate the token
        $parameters = new OidcValidationParameters(
            expectedIssuer: $sessionIssuer,
            expectedAudience: $sessionAudience,
            clockSkewSeconds: $this->clockSkewSeconds > 0 ? $this->clockSkewSeconds : 30,
        );

        try {
            $validatedToken = $this->tokenValidator->validate($idToken, $metadata->jwksUri, $parameters);
        } catch (OidcTokenValidationException) {
            $this->auditLogger->tokenValidationFailed($username);
            return OidcSessionRefreshResult::error(401, 'token_invalid', 'Token validation failed');
        }

        // 3. Issuer pinning — must match session
        if ($validatedToken->identity->issuer !== $sessionIssuer) {
            $this->auditLogger->issuerMismatch($username);
            return OidcSessionRefreshResult::error(401, 'issuer_mismatch');
        }

        // 4. Subject pinning — must match session
        if ($validatedToken->identity->externalId !== $sessionSubject) {
            $this->auditLogger->subjectMismatch($username);
            return OidcSessionRefreshResult::error(401, 'subject_mismatch');
        }

        // 5. Success
        $this->auditLogger->refreshSucceeded($username);

        return OidcSessionRefreshResult::ok($validatedToken);
    }
}
