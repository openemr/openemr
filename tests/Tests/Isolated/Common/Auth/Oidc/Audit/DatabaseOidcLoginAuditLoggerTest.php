<?php

/**
 * Aisle round-5 #4 (CWE-532) regression tests for
 * {@see DatabaseOidcLoginAuditLogger}.
 *
 * The pre-fix `accountNotProvisioned()` wrote raw OIDC iss + sub
 * values into the persistent `log` table — which surfaces in any
 * log-viewer role, gets retained in DB backups, and may carry PII
 * (Azure AD's sub is often the user's email or UPN). The fix
 * redacts: issuer to host-only, sub to a 12-char SHA-256
 * fingerprint. These tests pin the contract directly via the
 * `formatAccountNotProvisionedComment()` static, so the redaction
 * logic is testable without going through the EventAuditLogger
 * singleton.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Audit;

use OpenEMR\Common\Auth\Oidc\Audit\DatabaseOidcLoginAuditLogger;
use PHPUnit\Framework\TestCase;

final class DatabaseOidcLoginAuditLoggerTest extends TestCase
{
    public function testAccountNotProvisionedCommentExtractsIssuerHost(): void
    {
        // Realistic Google Identity issuer URL — the host alone is
        // enough operational signal ("which IdP rejected the
        // login?") without persisting the full path.
        $comment = DatabaseOidcLoginAuditLogger::formatAccountNotProvisionedComment(
            'https://accounts.example.com/oauth2/v3',
            'opaque-stable-sub',
        );

        self::assertStringContainsString('iss_host=accounts.example.com', $comment);
        self::assertStringNotContainsString('/oauth2/v3', $comment);
    }

    public function testAccountNotProvisionedCommentRedactsExternalIdToSha256Fingerprint(): void
    {
        // Subject as an email address — the realistic worst case
        // for PII leak. The redacted output must NOT contain the
        // raw email, but must contain the SHA-256 fingerprint
        // prefix so an operator can correlate against their IdP's
        // logs (where the full sub is canonical).
        $rawSub = 'user@example.com';
        $expectedFingerprint = substr(hash('sha256', $rawSub), 0, 12);

        $comment = DatabaseOidcLoginAuditLogger::formatAccountNotProvisionedComment(
            'https://idp.example.com',
            $rawSub,
        );

        self::assertStringContainsString('sub_fp=' . $expectedFingerprint, $comment);
        self::assertStringNotContainsString($rawSub, $comment);
        // Belt-and-braces: any portion of the raw sub that survives
        // would betray PII even partially. The full fingerprint
        // doesn't share characters with the local-part of the email.
        self::assertStringNotContainsString('@example.com', $comment);
    }

    public function testAccountNotProvisionedCommentHandlesMalformedIssuer(): void
    {
        // Malformed/missing issuer — `parse_url` returns null/false
        // for non-URL strings. Output should fall back to a stable
        // sentinel rather than emitting an empty `iss_host=` (which
        // would defeat operator triage) or letting the raw bad
        // value through.
        $comment = DatabaseOidcLoginAuditLogger::formatAccountNotProvisionedComment(
            'not-a-url',
            'sub-value',
        );

        self::assertStringContainsString('iss_host=(invalid_iss)', $comment);
        self::assertStringNotContainsString('not-a-url', $comment);
    }

    public function testAccountNotProvisionedCommentTruncatesFingerprintTo12Chars(): void
    {
        // Pin the truncation length so a future widening (e.g. to
        // 32 chars) is a deliberate, visible decision rather than
        // accidental. 12 hex chars = 48 bits of entropy — enough
        // for operator correlation in a per-org IdP log without
        // creating a quasi-identifier in the OpenEMR audit table.
        $comment = DatabaseOidcLoginAuditLogger::formatAccountNotProvisionedComment(
            'https://idp.example.com',
            'arbitrary-sub',
        );

        // Extract the fingerprint from the comment via regex —
        // matches `sub_fp=<hex>` and asserts on the captured length.
        self::assertMatchesRegularExpression(
            '/sub_fp=[0-9a-f]{12}(?![0-9a-f])/',
            $comment,
            'Fingerprint must be exactly 12 hex characters and not bleed into more',
        );
    }
}
