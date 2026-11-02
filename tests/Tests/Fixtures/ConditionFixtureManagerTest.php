<?php

/**
 * ConditionFixtureManager removes the uuid_registry rows of the records it deletes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\TestCase;

class ConditionFixtureManagerTest extends TestCase
{
    private ConditionFixtureManager $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = new ConditionFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->fixtures->removeFixtures();
    }

    /**
     * The patient, encounter and condition register their uuids when they're made, and a FHIR read
     * gives the user one. removeFixtures() removes each of those uuid_registry rows with its record.
     */
    public function testRemoveFixturesRemovesTheUuidRegistryRowsOfWhatItDeletes(): void
    {
        $patient = $this->fixtures->createTestPatient();
        $pid = $patient['pid'] ?? null;
        $this->assertIsInt($pid);
        $encounter = $this->fixtures->createTestEncounter($pid);
        $condition = $this->fixtures->createTestCondition($patient);
        $userId = $this->fixtures->createTestUser()['id'] ?? null;
        $this->assertIsInt($userId);
        // As a FHIR read of the user does.
        UuidRegistry::createMissingUuidForRow('users', 'id', $userId);
        $userUuid = QueryUtils::fetchSingleValue('SELECT uuid FROM users WHERE id = ?', 'uuid', [$userId]);
        $this->assertIsString($userUuid);
        $registered = ['users' => $userUuid];
        foreach (['patient_data' => $patient['uuid'] ?? null, 'form_encounter' => $encounter['uuid'] ?? null, 'lists' => $condition['lists_uuid'] ?? null] as $table => $uuid) {
            $this->assertIsString($uuid);
            $registered[$table] = UuidRegistry::uuidToBytes($uuid);
        }
        foreach ($registered as $table => $uuid) {
            $this->assertSame(1, self::registryRows($table, $uuid), "the $table record is registered");
        }

        // One record per table that no fixture owns, registered like any other, which the
        // cleanup must leave alone, record and registry row both.
        $unrelated = [];
        try {
            foreach (array_keys($registered) as $table) {
                $unrelated[$table] = self::insertUnrelatedRecord($table);
            }
            $this->fixtures->removeFixtures();

            foreach ($registered as $table => $uuid) {
                $this->assertSame(0, self::registryRows($table, $uuid), "the $table record's uuid_registry row survived removeFixtures()");
                $this->assertSame(1, self::registryRows($table, $unrelated[$table]), "removeFixtures() removed an unrelated $table record's uuid_registry row");
                $count = QueryUtils::fetchSingleValue(self::unrelatedRecordSql($table, 'count'), 'cnt', [$unrelated[$table]]);
                $this->assertSame(1, is_numeric($count) ? (int)$count : -1, "removeFixtures() removed an unrelated $table record");
            }
        } finally {
            foreach ($unrelated as $table => $uuid) {
                QueryUtils::sqlStatementThrowException(self::unrelatedRecordSql($table, 'delete'), [$uuid]);
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid = ?', [$uuid]);
            }
        }
    }

    /**
     * A record that no fixture owns, with its uuid registered as OpenEMR registers any other.
     */
    private static function insertUnrelatedRecord(string $table): string
    {
        $uuid = (new UuidRegistry(['table_name' => $table]))->createUuid();
        $binds = match ($table) {
            'patient_data' => [$uuid, self::unusedPid()],
            'users' => [$uuid, 'unrelated-record-' . bin2hex(random_bytes(4))],
            default => [$uuid],
        };
        $inserted = false;
        try {
            QueryUtils::sqlStatementThrowException(self::unrelatedRecordSql($table, 'insert'), $binds);
            $inserted = true;
        } finally {
            // A record that couldn't be inserted leaves no registry row behind.
            if (!$inserted) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid = ?', [$uuid]);
            }
        }
        return $uuid;
    }

    private static function unusedPid(): int
    {
        $max = QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) AS pid FROM patient_data', 'pid', []);
        return (is_numeric($max) ? (int)$max : 0) + 1;
    }

    private static function unrelatedRecordSql(string $table, string $action): string
    {
        return match ([$table, $action]) {
            ['users', 'insert'] => 'INSERT INTO users (uuid, username) VALUES (?, ?)',
            ['users', 'count'] => 'SELECT COUNT(*) AS cnt FROM users WHERE uuid = ?',
            ['users', 'delete'] => 'DELETE FROM users WHERE uuid = ?',
            ['patient_data', 'insert'] => "INSERT INTO patient_data (uuid, pid, fname, lname) VALUES (?, ?, 'Unrelated', 'Record')",
            ['patient_data', 'count'] => 'SELECT COUNT(*) AS cnt FROM patient_data WHERE uuid = ?',
            ['patient_data', 'delete'] => 'DELETE FROM patient_data WHERE uuid = ?',
            ['form_encounter', 'insert'] => "INSERT INTO form_encounter (uuid, reason, pid) VALUES (?, 'unrelated-record', 0)",
            ['form_encounter', 'count'] => 'SELECT COUNT(*) AS cnt FROM form_encounter WHERE uuid = ?',
            ['form_encounter', 'delete'] => 'DELETE FROM form_encounter WHERE uuid = ?',
            ['lists', 'insert'] => "INSERT INTO lists (uuid, pid, type, title) VALUES (?, 0, 'medical_problem', 'unrelated-record')",
            ['lists', 'count'] => 'SELECT COUNT(*) AS cnt FROM lists WHERE uuid = ?',
            ['lists', 'delete'] => 'DELETE FROM lists WHERE uuid = ?',
            default => throw new \LogicException("No unrelated-record SQL for $table ($action)"),
        };
    }

    private static function registryRows(string $table, string $uuid): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS cnt FROM uuid_registry WHERE table_name = ? AND uuid = ?', 'cnt', [$table, $uuid]);
        return is_numeric($count) ? (int)$count : -1;
    }
}
