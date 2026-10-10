<?php

/**
 * SurgeryService encounter linkage tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProcedure;
use OpenEMR\Services\FHIR\Procedure\FhirProcedureSurgeryService;
use OpenEMR\Services\SurgeryService;
use PHPUnit\Framework\TestCase;

/**
 * issue_encounter.encounter holds the encounter number (form_encounter.encounter), not the
 * form_encounter row id. The fixture makes the two collide on purpose: encounter A's number is
 * encounter B's row id, and B's number is A's row id. A surgery linked to A must report A's uuid;
 * a join on the row id would report B's. A second patient has an encounter with A's number too; the
 * link belongs to the fixture patient only.
 */
class SurgeryServiceTest extends TestCase
{
    /** fname/lname/title/reason of every row this test writes. */
    private const TAG = 'test-fixture-surgery-encounter';

    private int $pid;
    private int $encounterAId;
    private int $encounterBId;
    private string $encounterAUuid;
    private int $linkedSurgeryId;
    private string $linkedSurgeryUuid;
    private int $unlinkedSurgeryId;

    /**
     * Seed a patient, the two crossed encounters and two surgeries, one linked to encounter A.
     */
    protected function setUp(): void
    {
        $this->removeFixtures();

        $nextPid = QueryUtils::fetchSingleValue("SELECT COALESCE(MAX(pid), 0) + 1 AS next_pid FROM patient_data", 'next_pid');
        $this->assertIsNumeric($nextPid);
        $this->pid = (int) $nextPid;
        QueryUtils::sqlInsert(
            "INSERT INTO patient_data (pid, uuid, fname, lname) VALUES (?, ?, ?, ?)",
            [$this->pid, self::newUuid(), self::TAG, self::TAG]
        );

        $encounterAUuid = self::newUuid();
        $this->encounterAUuid = UuidRegistry::uuidToString($encounterAUuid);
        $this->encounterAId = $this->insertEncounter($encounterAUuid);
        $this->encounterBId = $this->insertEncounter(self::newUuid());
        QueryUtils::sqlStatementThrowException("UPDATE form_encounter SET encounter = ? WHERE id = ?", [$this->encounterBId, $this->encounterAId]);
        QueryUtils::sqlStatementThrowException("UPDATE form_encounter SET encounter = ? WHERE id = ?", [$this->encounterAId, $this->encounterBId]);

        // another patient's encounter carrying the same number must not match the link
        $otherPid = $this->pid + 1;
        QueryUtils::sqlInsert(
            "INSERT INTO patient_data (pid, uuid, fname, lname) VALUES (?, ?, ?, ?)",
            [$otherPid, self::newUuid(), self::TAG, self::TAG]
        );
        $otherEncounterId = $this->insertEncounter(self::newUuid(), $otherPid);
        QueryUtils::sqlStatementThrowException("UPDATE form_encounter SET encounter = ? WHERE id = ?", [$this->encounterBId, $otherEncounterId]);

        $linkedSurgeryUuid = self::newUuid();
        $this->linkedSurgeryUuid = UuidRegistry::uuidToString($linkedSurgeryUuid);
        $this->linkedSurgeryId = $this->insertSurgery($linkedSurgeryUuid);
        $this->unlinkedSurgeryId = $this->insertSurgery(self::newUuid());

        // the link stores encounter A's number, which is encounter B's row id
        QueryUtils::sqlInsert(
            "INSERT INTO issue_encounter (pid, list_id, encounter, resolved) VALUES (?, ?, ?, 0)",
            [$this->pid, $this->linkedSurgeryId, $this->encounterBId]
        );
    }

    /**
     * Remove every row this test wrote.
     */
    protected function tearDown(): void
    {
        $this->removeFixtures();
    }

    /**
     * The linked surgery reports encounter A: its number as eid and its uuid as euuid.
     */
    public function testSearchReturnsTheLinkedEncounter(): void
    {
        $record = $this->findSurgery($this->linkedSurgeryId);

        $this->assertSame($this->encounterAUuid, $record['euuid'] ?? null);
        $this->assertSame((string) $this->encounterBId, $record['eid'] ?? null, 'eid is the encounter number');
    }

    /**
     * A surgery without an issue_encounter link has no encounter.
     */
    public function testSearchReturnsNoEncounterForUnlinkedSurgery(): void
    {
        $record = $this->findSurgery($this->unlinkedSurgeryId);

        $this->assertNull($record['euuid'] ?? null);
        $this->assertNull($record['eid'] ?? null);
    }

    /**
     * The FHIR Procedure built from the linked surgery references encounter A.
     */
    public function testFhirProcedureReferencesTheLinkedEncounter(): void
    {
        $result = (new FhirProcedureSurgeryService())->getAll(['_id' => $this->linkedSurgeryUuid]);
        $this->assertTrue($result->isValid());

        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $procedure = $data[0];
        $this->assertInstanceOf(FHIRProcedure::class, $procedure);
        $this->assertSame('Encounter/' . $this->encounterAUuid, (string) $procedure->getEncounter()->getReference());
    }

    /**
     * Row of the given surgery in an unfiltered SurgeryService::search().
     *
     * @return array<array-key, mixed>
     */
    private function findSurgery(int $surgeryId): array
    {
        $result = (new SurgeryService())->search([]);
        $this->assertTrue($result->isValid());

        $records = $result->getData();
        $this->assertIsArray($records);
        $matches = [];
        foreach ($records as $record) {
            $this->assertIsArray($record);
            if (($record['id'] ?? null) == $surgeryId) {
                $matches[] = $record;
            }
        }
        $this->assertCount(1, $matches, 'The surgery should appear exactly once');
        return $matches[0];
    }

    /**
     * Insert an encounter with a placeholder number (for the fixture patient unless another pid
     * is given); return its row id.
     */
    private function insertEncounter(string $uuid, ?int $pid = null): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (pid, encounter, uuid, date, reason) VALUES (?, 0, ?, NOW(), ?)",
            [$pid ?? $this->pid, $uuid, self::TAG]
        );
    }

    /**
     * Insert a surgery issue for the fixture patient; return its lists.id.
     */
    private function insertSurgery(string $uuid): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO lists (pid, type, title, uuid, date, begdate) VALUES (?, 'surgery', ?, ?, NOW(), NOW())",
            [$this->pid, self::TAG, $uuid]
        );
    }

    /**
     * Delete the fixture rows, found through the tag on the patient, encounters and surgeries.
     */
    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE ie FROM issue_encounter ie JOIN lists l ON l.id = ie.list_id WHERE l.title = ?",
            [self::TAG]
        );
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'surgery' AND title = ?", [self::TAG]);
        QueryUtils::sqlStatementThrowException("DELETE FROM form_encounter WHERE reason = ?", [self::TAG]);
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE fname = ? AND lname = ?", [self::TAG, self::TAG]);
    }

    /**
     * A fresh uuid in binary form, not recorded in uuid_registry.
     */
    private static function newUuid(): string
    {
        return (new UuidRegistry(['disable_tracker' => true]))->createUuid();
    }
}
