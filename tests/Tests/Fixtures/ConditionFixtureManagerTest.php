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

        // One unrelated row per table, which the cleanup must leave alone.
        $unrelated = [];
        foreach (array_keys($registered) as $table) {
            $unrelated[$table] = (new UuidRegistry(['table_name' => $table]))->createUuid();
        }
        try {
            $this->fixtures->removeFixtures();

            foreach ($registered as $table => $uuid) {
                $this->assertSame(0, self::registryRows($table, $uuid), "the $table record's uuid_registry row survived removeFixtures()");
                $this->assertSame(1, self::registryRows($table, $unrelated[$table]), "removeFixtures() removed an unrelated $table uuid_registry row");
            }
        } finally {
            foreach ($unrelated as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE uuid = ?', [$uuid]);
            }
        }
    }

    private static function registryRows(string $table, string $uuid): int
    {
        $count = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS cnt FROM uuid_registry WHERE table_name = ? AND uuid = ?', 'cnt', [$table, $uuid]);
        return is_numeric($count) ? (int)$count : -1;
    }
}
