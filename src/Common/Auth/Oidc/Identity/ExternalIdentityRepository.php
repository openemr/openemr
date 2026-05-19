<?php

/**
 * Persistence for external identity mappings (local user ↔ OIDC issuer + subject).
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Database\QueryUtils;

class ExternalIdentityRepository
{
    public const TABLE_NAME = 'oidc_external_identity';

    /**
     * Find a mapping by external identity (issuer + subject).
     */
    public function findByExternal(string $issuer, string $externalId): ?ExternalIdentityMapping
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `' . self::TABLE_NAME . '` WHERE `issuer` = ? AND `external_id` = ?',
            [$issuer, $externalId],
        );

        if ($rows === []) {
            return null;
        }

        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return ExternalIdentityMapping::fromDatabaseRow($row);
    }

    /**
     * Find a mapping by local user ID.
     */
    public function findByUserId(int $userId): ?ExternalIdentityMapping
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `' . self::TABLE_NAME . '` WHERE `user_id` = ?',
            [$userId],
        );

        if ($rows === []) {
            return null;
        }

        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return ExternalIdentityMapping::fromDatabaseRow($row);
    }

    /**
     * Create or update a mapping atomically.
     *
     * Uses INSERT ... ON DUPLICATE KEY UPDATE so a concurrent request
     * hitting the same unique key (user_id or issuer+external_id) results
     * in an update rather than a duplicate-key error or a lost-update race.
     */
    public function save(ExternalIdentityMapping $mapping): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `' . self::TABLE_NAME . '` (`user_id`, `issuer`, `external_id`, `email`) '
            . 'VALUES (?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE `issuer` = VALUES(`issuer`), `external_id` = VALUES(`external_id`), `email` = VALUES(`email`)',
            [$mapping->userId, $mapping->issuer, $mapping->externalId, $mapping->email],
        );
    }

    /**
     * Remove a mapping by local user ID.
     */
    public function remove(int $userId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . self::TABLE_NAME . '` WHERE `user_id` = ?',
            [$userId],
        );
    }
}
