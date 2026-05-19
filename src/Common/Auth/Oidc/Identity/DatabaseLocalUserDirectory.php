<?php

/**
 * Database-backed implementation of {@see LocalUserDirectoryInterface}.
 *
 * Reads from core OpenEMR tables (`users`, `users_secure`, `gacl_*`) via
 * {@see QueryUtils}. Swap in an alternative implementation for testing or
 * for deployments that source user identity from elsewhere (LDAP,
 * directory service, etc.).
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Database\QueryUtils;

final class DatabaseLocalUserDirectory implements LocalUserDirectoryInterface
{
    public function findUserById(int $userId): ?array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT * FROM `users` WHERE `id` = ?',
            [$userId],
        );

        if ($rows === []) {
            return null;
        }

        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return $row;
    }

    public function findAuthGroupFor(string $username): string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `gacl_aro_groups`.`value` FROM `gacl_aro` '
            . 'INNER JOIN `gacl_groups_aro_map` ON `gacl_groups_aro_map`.`aro_id` = `gacl_aro`.`id` '
            . 'INNER JOIN `gacl_aro_groups` ON `gacl_aro_groups`.`id` = `gacl_groups_aro_map`.`group_id` '
            . 'WHERE `gacl_aro`.`section_value` = ? AND `gacl_aro`.`value` = ?',
            ['users', $username],
        );

        if ($rows === []) {
            return '';
        }

        $value = $rows[0]['value'] ?? '';
        return is_string($value) ? $value : '';
    }

    public function findPasswordHashFor(string $username): string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `password` FROM `users_secure` WHERE `username` = ?',
            [$username],
        );

        if ($rows === []) {
            return '';
        }

        $password = $rows[0]['password'] ?? '';
        return is_string($password) ? $password : '';
    }
}
