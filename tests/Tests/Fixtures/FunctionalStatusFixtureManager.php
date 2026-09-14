<?php

/**
 * Test fixture manager for functional/cognitive status data.
 *
 * Seeds the form-based data model that the C-CDA export reads: a row in
 * form_functional_cognitive_status plus the linking `forms` row
 * (formdir='functional_cognitive_status') that drives
 * EncounterccdadispatchTable::getFunctionalCognitiveStatus().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

class FunctionalStatusFixtureManager
{
    private const TEST_ID_FLOOR = 90000;
    private const FORM_DIR = 'functional_cognitive_status';

    /** activity value distinguishing the two entry kinds in form_functional_cognitive_status. */
    public const ACTIVITY_FUNCTIONAL = 0;
    public const ACTIVITY_COGNITIVE = 1;

    /** @var list<int> */
    private array $patientIds = [];
    /** @var list<int> */
    private array $encounterIds = [];
    /** @var list<int> */
    private array $statusIds = [];
    /** @var list<int> */
    private array $formIds = [];

    /**
     * @return array{pid: int, uuid: string, fname: string, lname: string, DOB: string, sex: string, pubpid: string}
     */
    public function createTestPatient(): array
    {
        $uuid = UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('patient_data')->createUuid());
        $pid = $this->nextTestId("SELECT COALESCE(MAX(pid), 0) + 1 AS next_id FROM patient_data WHERE pid >= ?");

        $patientData = [
            'pid' => $pid,
            'uuid' => $uuid,
            'fname' => 'Test',
            'lname' => 'FunctionalStatus',
            'DOB' => '1950-07-23',
            'sex' => 'Male',
            'pubpid' => 'TEST-FS-' . $pid,
        ];

        $sql = "INSERT INTO patient_data (pid, uuid, fname, lname, DOB, sex, pubpid) VALUES (?, ?, ?, ?, ?, ?, ?)";
        QueryUtils::sqlStatementThrowException($sql, [
            $pid,
            UuidRegistry::uuidToBytes($uuid),
            $patientData['fname'],
            $patientData['lname'],
            $patientData['DOB'],
            $patientData['sex'],
            $patientData['pubpid'],
        ]);

        $this->patientIds[] = $pid;

        return $patientData;
    }

    /**
     * @return array{id: int, uuid: string, pid: int, encounter: int, date: string, facility_id: int}
     */
    public function createTestEncounter(int $pid): array
    {
        $uuid = UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('form_encounter')->createUuid());
        $encounterId = $this->nextTestId("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM form_encounter WHERE id >= ?");

        $encounterData = [
            'id' => $encounterId,
            'uuid' => $uuid,
            'pid' => $pid,
            'encounter' => $encounterId,
            'date' => date('Y-m-d H:i:s'),
            'facility_id' => 1,
        ];

        $sql = "INSERT INTO form_encounter (id, uuid, pid, encounter, date, reason, facility_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        QueryUtils::sqlStatementThrowException($sql, [
            $encounterId,
            UuidRegistry::uuidToBytes($uuid),
            $pid,
            $encounterId,
            $encounterData['date'],
            'Test encounter',
            $encounterData['facility_id'],
        ]);

        $this->encounterIds[] = $encounterId;

        return $encounterData;
    }

    /**
     * Seed a single functional-status entry and its linking forms row.
     *
     * Values are the functional-status data from the ONC certification scenario
     * 170.315_b1_ToC_Amb sample1 (USCDI v3): "Dependence on Cane" (SNOMED-CT
     * 105504002), which the C-CDA export emits as the Functional Status
     * Observation value.
     *
     * @param array{pid: int, ...} $patientData
     * @param array{id: int, encounter: int, ...} $encounterData
     * @return array{id: int, form_id: int, pid: int, encounter: int, activity: int, code: string, codetext: string, description: string, date: string}
     */
    public function createTestFunctionalStatus(array $patientData, array $encounterData): array
    {
        $statusId = $this->nextTestId("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM form_functional_cognitive_status WHERE id >= ?");

        $data = [
            'id' => $statusId,
            'form_id' => $statusId,
            'pid' => $patientData['pid'],
            'encounter' => $encounterData['encounter'],
            'activity' => self::ACTIVITY_FUNCTIONAL,
            'code' => '105504002',
            'codetext' => 'Dependence on Cane',
            'description' => 'Dependence on Cane',
            'date' => '2005-05-01',
        ];

        $statusSql = "INSERT INTO form_functional_cognitive_status
            SET id = ?, date = ?, pid = ?, encounter = ?, user = ?, groupname = ?, authorized = ?, activity = ?, code = ?, codetext = ?, description = ?";
        QueryUtils::sqlStatementThrowException($statusSql, [
            $data['id'],
            $data['date'],
            $data['pid'],
            $data['encounter'],
            'admin',
            'Default',
            1,
            $data['activity'],
            $data['code'],
            $data['codetext'],
            $data['description'],
        ]);
        $this->statusIds[] = $statusId;

        $formSql = "INSERT INTO forms
            (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $formId = QueryUtils::sqlInsert($formSql, [
            $data['date'],
            $data['encounter'],
            'Functional and Cognitive Status Form',
            $data['form_id'],
            $data['pid'],
            'admin',
            'Default',
            1,
            0,
            self::FORM_DIR,
        ]);
        $this->formIds[] = $formId;

        return $data;
    }

    public function removeFixtures(): void
    {
        $this->deleteByIds("DELETE FROM forms WHERE id IN", $this->formIds);
        $this->deleteByIds("DELETE FROM form_functional_cognitive_status WHERE id IN", $this->statusIds);
        $this->deleteByIds("DELETE FROM form_encounter WHERE id IN", $this->encounterIds);
        $this->deleteByIds("DELETE FROM patient_data WHERE pid IN", $this->patientIds);

        $this->patientIds = [];
        $this->encounterIds = [];
        $this->statusIds = [];
        $this->formIds = [];
    }

    /**
     * Deletes every row whose id is in $ids using a single bound IN clause.
     * $deleteSql must be a literal ending in "... IN"; only value placeholders
     * are generated (never identifiers).
     *
     * @param literal-string $deleteSql
     * @param list<int> $ids
     */
    private function deleteByIds(string $deleteSql, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        QueryUtils::sqlStatementThrowException("$deleteSql ($placeholders)", $ids);
    }

    /**
     * @param literal-string $maxIdSql
     */
    private function nextTestId(string $maxIdSql): int
    {
        $next = QueryUtils::fetchSingleValue($maxIdSql, 'next_id', [self::TEST_ID_FLOOR]);

        if (!is_numeric($next)) {
            return self::TEST_ID_FLOOR;
        }

        return max(self::TEST_ID_FLOOR, (int) $next);
    }
}
