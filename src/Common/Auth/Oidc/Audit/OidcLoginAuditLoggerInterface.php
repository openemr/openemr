<?php

/**
 * Contract for recording OIDC login-flow audit events.
 *
 * One method per distinct outcome of the login pipeline. Implementations
 * translate these calls into whatever audit-sink format the deployment
 * uses; the domain code stays free of sink-specific details and
 * string-wrangling.
 *
 * Intended to replace direct `EventAuditLogger::getInstance()->newEvent(…)`
 * calls in OIDC handlers so that:
 *   1. audit message wording lives in one place,
 *   2. handler branches become intention-revealing one-liners,
 *   3. tests can verify the right branch fired by spying on this interface
 *      instead of string-matching on free-form comments.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Audit;

interface OidcLoginAuditLoggerInterface
{
    /** Module is enabled but misconfigured (empty issuer or client ID). */
    public function moduleNotConfigured(): void;

    /** `.well-known/openid-configuration` retrieval failed for the configured issuer. */
    public function discoveryFailed(): void;

    /** The POSTed ID token failed cryptographic, iss/aud, or replay checks. */
    public function tokenValidationFailed(): void;

    /**
     * Token is valid but no local account is provisioned for the external identity.
     *
     * Note: the external identifier is included so administrators can
     * provision the account. For future providers where `sub` could be PII
     * (e.g. email or employee ID), revisit what gets logged.
     */
    public function accountNotProvisioned(string $issuer, string $externalId): void;

    /** External-identity mapping exists but the referenced `users.id` has no row. */
    public function mappedUserMissing(): void;

    /** The mapped local user has `active = 0`. */
    public function userAccountDisabled(string $username): void;

    /** The mapped local user has no gACL group assignment. */
    public function userHasNoAuthGroup(string $username): void;

    /**
     * Token is valid but its tenant does not match the configured allowlist,
     * or no tenant claim is present while an allowlist is configured.
     *
     * @param list<string> $allowedTenantIds non-empty list of permitted tenant IDs
     */
    public function tenantMismatch(array $allowedTenantIds, ?string $tokenTenantId): void;

    /** Login succeeded end-to-end; user is about to be set on the session. */
    public function loginSucceeded(string $username, string $authGroup): void;
}
