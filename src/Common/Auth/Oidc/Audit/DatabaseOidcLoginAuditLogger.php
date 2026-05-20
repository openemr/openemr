<?php

/**
 * {@see OidcLoginAuditLoggerInterface} implementation that writes to the
 * OpenEMR `log` table via the core {@see EventAuditLogger} singleton.
 *
 * Centralizes the `'login'` event type, empty user/group defaults, and the
 * success/failure flags, so handlers can call intention-revealing methods
 * instead of repeating the 5-argument `newEvent()` scaffolding.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Audit;

use OpenEMR\Common\Logging\EventAuditLogger;

final class DatabaseOidcLoginAuditLogger implements OidcLoginAuditLoggerInterface
{
    private const EVENT_TYPE = 'login';
    private const FAIL = 0;
    private const SUCCESS = 1;

    public function moduleNotConfigured(): void
    {
        $this->failure('', '', 'GCIP module not configured (missing issuer or client ID)');
    }

    public function discoveryFailed(): void
    {
        $this->failure('', '', 'GCIP OIDC discovery failed');
    }

    public function tokenValidationFailed(): void
    {
        $this->failure('', '', 'GCIP OIDC token validation failed');
    }

    public function accountNotProvisioned(string $issuer, string $externalId): void
    {
        // Round-5 #4 (CWE-532).
        $this->failure('', '', self::formatAccountNotProvisionedComment($issuer, $externalId));
    }

    /**
     * Build the redacted log comment for {@see accountNotProvisioned}.
     * Public so tests can pin the redaction contract without going
     * through the EventAuditLogger singleton.
     */
    public static function formatAccountNotProvisionedComment(string $issuer, string $externalId): string
    {
        $issuerHost = parse_url($issuer, PHP_URL_HOST);
        if (!is_string($issuerHost) || $issuerHost === '') {
            $issuerHost = '(invalid_iss)';
        }
        $subFingerprint = substr(hash('sha256', $externalId), 0, 12);
        return 'GCIP OIDC account not provisioned for iss_host=' . $issuerHost . ' sub_fp=' . $subFingerprint;
    }

    public function mappedUserMissing(): void
    {
        $this->failure('', '', 'GCIP OIDC mapped user not found in users table');
    }

    public function userAccountDisabled(string $username): void
    {
        $this->failure($username, '', 'GCIP OIDC user account is disabled');
    }

    public function userHasNoAuthGroup(string $username): void
    {
        $this->failure($username, '', 'GCIP OIDC user has no ACL group');
    }

    public function tenantMismatch(array $allowedTenantIds, ?string $tokenTenantId): void
    {
        $actual = $tokenTenantId ?? '(none)';
        $expected = implode(',', $allowedTenantIds);
        $this->failure('', '', 'GCIP OIDC tenant mismatch — expected one of [' . $expected . '], got ' . $actual);
    }

    public function loginSucceeded(string $username, string $authGroup): void
    {
        EventAuditLogger::getInstance()->newEvent(
            self::EVENT_TYPE,
            $username,
            $authGroup,
            self::SUCCESS,
            'success via GCIP OIDC',
        );
    }

    private function failure(string $user, string $group, string $comment): void
    {
        EventAuditLogger::getInstance()->newEvent(
            self::EVENT_TYPE,
            $user,
            $group,
            self::FAIL,
            $comment,
        );
    }
}
