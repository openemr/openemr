<?php

/**
 * uuid_registry-aware test fixture cleanup helpers.
 *
 * OpenEMR's uuid_registry table holds a row per registered uuid, keyed
 * by `table_name` + `uuid`. Many fixture + test teardowns delete the
 * owning row from (e.g.) `form_encounter` by uuid but miss the matching
 * uuid_registry entry, leaking a registry row per test run. Over time
 * the registry grows and future UUID allocations tighten.
 *
 * Every helper here deletes the uuid_registry entry BEFORE the owning
 * row. The registry subselect reads the owning row's uuid, so if we
 * delete the owning row first the registry entry orphans silently.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Support\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

final class UuidRegistryCleanup
{
    /**
     * Delete a single row from `$tableName` by uuid along with its
     * matching uuid_registry entry.
     *
     * $tableName must be a static identifier under test-code control
     * (not user input): it is interpolated into the DELETE statement
     * because MySQL cannot bind table names as prepared parameters.
     * A whitelist regex rejects anything not matching the MySQL
     * identifier shape so a future refactor accidentally passing a
     * dynamic value fails loudly rather than opening a SQLi hole.
     */
    public static function deleteRowAndUuidEntry(string $tableName, string $uuid): void
    {
        self::assertSafeIdentifier($tableName);
        $bytes = UuidRegistry::uuidToBytes($uuid);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?',
            [$tableName, $bytes]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `{$tableName}` WHERE uuid = ?",
            [$bytes]
        );
    }

    /**
     * Sweep every row in `$tableName` matching the given WHERE clause
     * along with the uuid_registry entries that reference those rows.
     *
     * $tableName + $whereClause are both interpolated — both must be
     * static identifiers / test-controlled SQL fragments. Bound params
     * for the WHERE condition's values go through `$whereBindings`.
     *
     * Typical use: the project's `test-fixture%` sweeps that delete
     * every row created by a given test file at once rather than by
     * tracked uuid.
     *
     * @param list<mixed> $whereBindings
     */
    public static function sweepRowsAndUuidEntries(
        string $tableName,
        string $whereClause,
        array $whereBindings = []
    ): void {
        self::assertSafeIdentifier($tableName);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE table_name = ? AND uuid IN ("
                . "SELECT uuid FROM `{$tableName}` WHERE {$whereClause}"
                . ")",
            array_merge([$tableName], $whereBindings)
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `{$tableName}` WHERE {$whereClause}",
            $whereBindings
        );
    }

    /**
     * Delete a form_encounter row + its companion `forms.newpatient`
     * row + the uuid_registry entry, in the registry/forms/encounter
     * order that keeps the registry consistent.
     *
     * EncounterService::insertEncounter addForm()s a `forms` row with
     * formdir='newpatient' whenever an encounter is created through the
     * FHIR write path. The baseline EncounterFixtureManager sweep only
     * matches form_encounter.reason LIKE 'test-fixture-%' and leaves
     * the companion forms row behind — this helper picks up both.
     */
    public static function deleteEncounterAndCompanions(string $encounterUuid): void
    {
        $bytes = UuidRegistry::uuidToBytes($encounterUuid);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM uuid_registry WHERE table_name = 'form_encounter' AND uuid = ?",
            [$bytes]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE f FROM forms f'
                . ' JOIN form_encounter fe ON fe.encounter = f.encounter'
                . " WHERE fe.uuid = ? AND f.formdir = 'newpatient'",
            [$bytes]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM form_encounter WHERE uuid = ?',
            [$bytes]
        );
    }

    /**
     * Reject anything that is not a plain MySQL identifier. Belt-and-
     * suspenders: callers already pass static strings, but a future
     * refactor that routes user input through here must fail rather
     * than open a SQLi vector.
     */
    private static function assertSafeIdentifier(string $identifier): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException(
                "UuidRegistryCleanup: refusing identifier \"{$identifier}\"; "
                    . 'must match /^[a-zA-Z_][a-zA-Z0-9_]*$/'
            );
        }
    }
}
