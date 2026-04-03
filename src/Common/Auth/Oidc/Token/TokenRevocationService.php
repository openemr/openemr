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

final class TokenRevocationService
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

        $existing = QueryUtils::fetchRecords(
            'SELECT 1 FROM `' . self::TABLE_NAME . '` WHERE `jti` = ?',
            [$jti],
        );

        if ($existing !== []) {
            return;
        }

        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `' . self::TABLE_NAME . '` (`jti`, `token_expiry`) VALUES (?, ?)',
            [$jti, $tokenExpiry->format('Y-m-d H:i:s')],
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
