<?php

/**
 * Integration tests for DatabaseLocalUserDirectory.
 *
 * The class is a thin wrapper around three QueryUtils calls — there's
 * little testable PHP logic. The real risk lives in the SQL shapes:
 * the right table, the right WHERE clause (in particular the
 * `section_value = 'users'` filter on the GACL join chain that
 * prevents cross-section ACL leakage), and the right column
 * extraction. Integration-tier coverage hits a real DB to pin those.
 *
 * Mirrors the IpLoginRateLimiterTest pattern: skip when the
 * DISABLE_DATABASE env var is set, seed test rows with a marker
 * prefix, scrub on tearDown.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Auth\Oidc\Identity\DatabaseLocalUserDirectory;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

final class DatabaseLocalUserDirectoryTest extends TestCase
{
    /** Username/name prefix for every row this suite seeds — used by
     *  the LIKE-based teardown sweep so concurrent test runs don't
     *  step on each other. */
    private const MARKER = 'test-localdir-';

    /** ID floor for gacl_aro / gacl_aro_groups rows. Real OpenEMR
     *  installs reserve IDs <100000 for legacy seeds; this band is
     *  safe to claim and easy to scrub by range. */
    private const GACL_ID_FLOOR = 9999000;

    private DatabaseLocalUserDirectory $directory;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->directory = new DatabaseLocalUserDirectory();
        $this->cleanTestRecords();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTestRecords();
        }
    }

    private function cleanTestRecords(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `users` WHERE `username` LIKE ?",
            [self::MARKER . '%'],
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `users_secure` WHERE `username` LIKE ?",
            [self::MARKER . '%'],
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `gacl_aro` WHERE `id` BETWEEN ? AND ?",
            [self::GACL_ID_FLOOR, self::GACL_ID_FLOOR + 999],
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `gacl_aro_groups` WHERE `id` BETWEEN ? AND ?",
            [self::GACL_ID_FLOOR, self::GACL_ID_FLOOR + 999],
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `gacl_groups_aro_map` WHERE `aro_id` BETWEEN ? AND ?",
            [self::GACL_ID_FLOOR, self::GACL_ID_FLOOR + 999],
        );
    }

    // -----------------------------------------------------------------
    // findUserById
    // -----------------------------------------------------------------

    public function testFindUserByIdReturnsRowWhenUserExists(): void
    {
        $username = self::MARKER . 'find-by-id';
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `users` (`username`, `fname`, `lname`) VALUES (?, ?, ?)",
            [$username, 'Test', 'Local-Dir'],
        );
        $userId = (int) $this->fetchScalar(
            "SELECT `id` FROM `users` WHERE `username` = ?",
            [$username],
        );
        self::assertGreaterThan(0, $userId, 'fixture: inserted user must have an auto-increment id');

        $row = $this->directory->findUserById($userId);

        self::assertIsArray($row);
        self::assertSame($username, $row['username']);
        self::assertSame('Test', $row['fname']);
        self::assertSame('Local-Dir', $row['lname']);
    }

    public function testFindUserByIdReturnsNullWhenUserDoesNotExist(): void
    {
        // ID well outside any plausible auto-increment value.
        $row = $this->directory->findUserById(self::GACL_ID_FLOOR + 999_999);

        self::assertNull($row, 'Missing user must return null, never an empty array or false');
    }

    // -----------------------------------------------------------------
    // findAuthGroupFor
    // -----------------------------------------------------------------

    public function testFindAuthGroupForReturnsGroupValueWhenAclMappingExists(): void
    {
        // Seed the three-table chain: aro (the user) → groups_aro_map
        // (the link) → aro_groups (the role group). The directory's
        // SQL joins all three; this fixture covers each side.
        $username = self::MARKER . 'with-acl';
        $aroId = self::GACL_ID_FLOOR + 100;
        $groupId = self::GACL_ID_FLOOR + 200;
        $groupValue = 'Physicians';

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_aro` (`id`, `section_value`, `value`, `name`)"
            . " VALUES (?, 'users', ?, ?)",
            [$aroId, $username, $username],
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_aro_groups` (`id`, `value`, `name`)"
            . " VALUES (?, ?, ?)",
            [$groupId, $groupValue, 'Physicians Group'],
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_groups_aro_map` (`group_id`, `aro_id`)"
            . " VALUES (?, ?)",
            [$groupId, $aroId],
        );

        $result = $this->directory->findAuthGroupFor($username);

        self::assertSame($groupValue, $result);
    }

    public function testFindAuthGroupForReturnsEmptyStringWhenNoMapping(): void
    {
        $result = $this->directory->findAuthGroupFor(self::MARKER . 'no-acl');

        self::assertSame('', $result, 'Missing ACL mapping must return empty string, never null or false');
    }

    /**
     * Pins the security-relevant `section_value = 'users'` filter on
     * the GACL join chain. GACL has separate sections (users, groups,
     * etc.); without the filter, a username collision across sections
     * would leak the wrong group back to the auth flow. This test
     * seeds an aro entry with the same `value` but a different
     * `section_value`, and verifies the lookup returns empty (not
     * the group value the wrong-section row would chain to).
     */
    public function testFindAuthGroupForRequiresUsersSectionValue(): void
    {
        $username = self::MARKER . 'wrong-section';
        $aroId = self::GACL_ID_FLOOR + 110;
        $groupId = self::GACL_ID_FLOOR + 210;

        // Same `value` (the username), but section_value is "groups"
        // — should not be picked up by the directory.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_aro` (`id`, `section_value`, `value`, `name`)"
            . " VALUES (?, 'groups', ?, ?)",
            [$aroId, $username, $username],
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_aro_groups` (`id`, `value`, `name`)"
            . " VALUES (?, 'Administrators', 'Admin Group')",
            [$groupId],
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `gacl_groups_aro_map` (`group_id`, `aro_id`)"
            . " VALUES (?, ?)",
            [$groupId, $aroId],
        );

        $result = $this->directory->findAuthGroupFor($username);

        self::assertSame(
            '',
            $result,
            'Cross-section name collision must NOT leak the group value — '
            . 'pin the WHERE section_value = "users" filter',
        );
    }

    // -----------------------------------------------------------------
    // findPasswordHashFor
    // -----------------------------------------------------------------

    public function testFindPasswordHashForReturnsHashWhenUserExists(): void
    {
        $username = self::MARKER . 'with-secret';
        // Plausible-shaped bcrypt fixture — the directory just returns
        // the column verbatim, so the value need only be a non-empty
        // string for the assertion below.
        $hash = '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMN';

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `users_secure` (`id`, `username`, `password`)"
            . " VALUES (?, ?, ?)",
            [self::GACL_ID_FLOOR + 300, $username, $hash],
        );

        try {
            $result = $this->directory->findPasswordHashFor($username);
            self::assertSame($hash, $result);
        } finally {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `users_secure` WHERE `id` = ?",
                [self::GACL_ID_FLOOR + 300],
            );
        }
    }

    public function testFindPasswordHashForReturnsEmptyStringWhenUserDoesNotExist(): void
    {
        $result = $this->directory->findPasswordHashFor(self::MARKER . 'no-secret');

        self::assertSame(
            '',
            $result,
            'Missing user must return empty string, never null/false — '
            . 'callers feed this into AuthHash and would treat null as a falsy hash',
        );
    }

    /**
     * @param array<int, mixed> $params
     * @return scalar|null
     */
    private function fetchScalar(string $sql, array $params): mixed
    {
        $rows = QueryUtils::fetchRecords($sql, $params);
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];
        $value = is_array($row) ? array_values($row)[0] ?? null : null;
        return is_scalar($value) ? $value : null;
    }
}
