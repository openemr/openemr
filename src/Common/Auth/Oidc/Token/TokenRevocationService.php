<?php

/**
 * Manages a revocation list for OIDC token JTI (JWT ID) values.
 *
 * Entries auto-expire when the token would have naturally expired,
 * keeping the table small. Combined with the 1-hour GCIP token lifetime,
 * this enables immediate lockout: revoke the jti locally, and the token
 * is rejected on the next request even though it hasn't expired yet.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Token;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;

final class TokenRevocationService implements TokenRevocationCheckerInterface
{
    public const TABLE_NAME = 'oidc_token_revocation';

    /**
     * Revoke a token by its JTI.
     *
     * @param string             $jti         The JWT ID claim value.
     * @param \DateTimeImmutable $tokenExpiry When the token would naturally expire.
     */
    public function revoke(string $jti, \DateTimeImmutable $tokenExpiry): void
    {
        if ($jti === '') {
            return;
        }

        // Aisle round-5 #10 (CWE-362). The pre-fix SELECT-then-INSERT
        // had a TOCTOU race window: two concurrent revoke calls for
        // the same jti could both pass the SELECT (no row), then one
        // INSERT would fail on the `uq_jti_hash` UNIQUE constraint and
        // sqlStatementThrowException() would propagate as a 500 —
        // realistic logout/revocation DoS. Switch to INSERT IGNORE so
        // the unique-key violation is absorbed at the DB layer; a
        // duplicate revoke is a no-op rather than an error.
        QueryUtils::sqlStatementThrowException(
            'INSERT IGNORE INTO `' . self::TABLE_NAME . '` (`jti`, `token_expiry`) VALUES (?, ?)',
            [$jti, $tokenExpiry->format('Y-m-d H:i:s')],
        );

        // Only audit on a *new* revocation. INSERT IGNORE returns
        // affected_rows = 0 when the unique-key constraint blocked
        // the row (the jti was already revoked); skip the audit
        // event in that case so re-revocation doesn't double-log.
        if (QueryUtils::affectedRows() !== 1) {
            return;
        }

        EventAuditLogger::getInstance()->newEvent(
            'security',
            '',
            '',
            1,
            'OIDC token revoked: jti=' . $jti,
        );
    }

    /**
     * Check whether a JTI has been revoked.
     */
    public function isRevoked(string $jti): bool
    {
        if ($jti === '') {
            return false;
        }

        $rows = QueryUtils::fetchRecords(
            'SELECT 1 FROM `' . self::TABLE_NAME . '` WHERE `jti` = ?',
            [$jti],
        );

        return $rows !== [];
    }

    /**
     * Remove expired entries. Intended to be called periodically (cron or on-demand).
     */
    public function purgeExpired(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '` WHERE `token_expiry` < NOW()',
        );
    }
}
