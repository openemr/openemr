<?php

/**
 * ConditionFixtureManager.php
 * Test fixture manager for Condition-related tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain for most of this file marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 *            Minor additions were made by Stephen Nielson
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientIssuesService;
use OpenEMR\Services\UserService;

/**
 * Manages test fixtures for condition-related unit tests
 */
class ConditionFixtureManager
{
    private $createdRecords = [];
    private PatientIssuesService $patientIssuesService;

    public function createTestUser(): array
    {
        $sql = "INSERT INTO users (username, password, active, npi) VALUES (?, ?, ?, ?)";
        $bind = ['testuser', 'NoLongerUsed', 1, '1234567890'];
        $userId = QueryUtils::sqlInsert($sql, $bind);
        $this->createdRecords['users'][] = [
            'id' => $userId,
            'username' => 'testuser',
        ];
        return ['id' => $userId, 'username' => 'testuser'];
    }

    /**
     * Create a test patient record
     */
    public function createTestPatient(): array
    {
        $uuid = UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('patient_data')->createUuid());
        $pid = $this->getNextPid();

        $patientData = [
            'pid' => $pid,
            'uuid' => $uuid,
            'fname' => 'Test',
            'lname' => 'Patient',
            'DOB' => '1980-01-01',
            'sex' => 'Male',
            'pubpid' => 'TEST-' . $pid
        ];

        $sql = "INSERT INTO patient_data (pid, uuid, fname, lname, DOB, sex, pubpid) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $bind = [$pid, UuidRegistry::uuidToBytes($uuid), $patientData['fname'], $patientData['lname'],
            $patientData['DOB'], $patientData['sex'], $patientData['pubpid']];

        QueryUtils::sqlStatementThrowException($sql, $bind);

        $this->createdRecords['patients'][] = $pid;

        return $patientData;
    }

    /**
     * Create a test encounter record
     */
    public function createTestEncounter(int $pid): array
    {
        $uuid = UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('form_encounter')->createUuid());
        $encounterId = $this->getNextEncounterId();

        $encounterData = [
            'id' => $encounterId,
            'uuid' => $uuid,
            'pid' => $pid,
            'encounter' => $encounterId,
            'date' => date('Y-m-d H:i:s'),
            'reason' => 'Test encounter',
            'facility_id' => 1
        ];

        $sql = "INSERT INTO form_encounter (id, uuid, pid, encounter, date, reason, facility_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $bind = [$encounterId, UuidRegistry::uuidToBytes($uuid), $pid, $encounterId,
            $encounterData['date'], $encounterData['reason'], $encounterData['facility_id']];

        QueryUtils::sqlStatementThrowException($sql, $bind);

        $this->createdRecords['encounters'][] = $encounterId;

        return $encounterData;
    }

    /**
     * Create a test condition record
     */
    public function createTestCondition(array $patientData, array $options = []): array
    {
        $uuid = UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('lists')->createUuid());
        $listId = $this->getNextListId();

        $conditionData = array_merge([
            'id' => $listId,
            'lists_uuid' => $uuid,
            'pid' => $patientData['pid'],
            'date' => date('Y-m-d H:i:s'),
            'type' => 'medical_problem',
            'title' => 'Test Condition',
            'begdate' => date('Y-m-d'),
            'enddate' => null,
            'occurrence' => 0,
            'outcome' => 0,
            'activity' => 1,
            'comments' => 'Test condition for unit testing',
            'verification' => 'confirmed'
        ], $options);

        $sql = "INSERT INTO lists (id, uuid, pid, date, type, title, begdate, enddate, occurrence, outcome, activity, comments, verification)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $bind = [
            $listId, UuidRegistry::uuidToBytes($uuid), $conditionData['pid'], $conditionData['date'],
            $conditionData['type'], $conditionData['title'], $conditionData['begdate'], $conditionData['enddate'],
            $conditionData['occurrence'], $conditionData['outcome'], $conditionData['activity'],
            $conditionData['comments'], $conditionData['verification']
        ];

        QueryUtils::sqlStatementThrowException($sql, $bind);

        $this->createdRecords['conditions'][] = $listId;

        // Add computed fields for FHIR processing
        $conditionData['puuid'] = $patientData['uuid'];
        $conditionData['last_updated_time'] = $conditionData['date'];

        return $conditionData;
    }

    public function getPatientIssuesService(): PatientIssuesService
    {
        if (!isset($this->patientIssuesService)) {
            $this->patientIssuesService = new PatientIssuesService();
        }
        return $this->patientIssuesService;
    }

    /**
     * Create a test condition linked to an encounter
     */
    public function createTestEncounterCondition(array $patientData, array $encounterData, array $options = []): array
    {
        // Create the condition first
        $testUser = $this->createTestUser();
        $conditionData = $this->createTestCondition($patientData, $options);
        $patientIssuesService = $this->getPatientIssuesService();
        // Link it to the encounter via issue_encounter table
        $uuid = $patientIssuesService->linkIssueToEncounter(
            $patientData['pid'],
            $encounterData['encounter'],
            $conditionData['id'],
            $testUser['id'],
            0
        );
        $this->createdRecords['issue_encounters'][] = [
            'pid' => $patientData['pid'],
            'list_id' => $conditionData['id'],
            'encounter' => $encounterData['encounter']
        ];

        // Add encounter information to condition data
        $conditionData['encounter_uuid'] = $encounterData['uuid'];
        $conditionData['encounter_id'] = $encounterData['encounter'];
        $conditionData['resolved'] = 0;
        $conditionData['uuid'] = UuidRegistry::uuidToString($uuid);

        return $conditionData;
    }

    /**
     * Create condition with specific diagnosis codes
     */
    public function createConditionWithDiagnosis(array $patientData, string $code, string $description, string $system): array
    {
        $conditionData = $this->createTestCondition($patientData);

        // Add diagnosis information (this would typically be in a separate codes table)
        $conditionData['diagnosis'] = [
            $code => [
                'description' => $description,
                'system' => $system
            ]
        ];

        return $conditionData;
    }

    /**
     * Remove all created test fixtures
     */
    public function removeFixtures(): void
    {
        try {
            // Remove issue_encounter records
            if (isset($this->createdRecords['issue_encounters'])) {
                foreach ($this->createdRecords['issue_encounters'] as $record) {
                    $this->getPatientIssuesService()->unlinkIssueFromEncounter($record['pid'], $record['encounter'], $record['list_id']);
                }
            }

            // Remove condition records
            if (isset($this->createdRecords['conditions'])) {
                foreach ($this->createdRecords['conditions'] as $listId) {
                    $sql = "DELETE FROM lists WHERE id = ?";
                    QueryUtils::sqlStatementThrowException($sql, [$listId]);
                }
            }

            // Remove encounter records
            if (isset($this->createdRecords['encounters'])) {
                foreach ($this->createdRecords['encounters'] as $encounterId) {
                    $sql = "DELETE FROM form_encounter WHERE id = ?";
                    QueryUtils::sqlStatementThrowException($sql, [$encounterId]);
                }
            }

            // Remove patient records
            if (isset($this->createdRecords['patients'])) {
                foreach ($this->createdRecords['patients'] as $pid) {
                    $sql = "DELETE FROM patient_data WHERE pid = ?";
                    QueryUtils::sqlStatementThrowException($sql, [$pid]);
                }
            }
            // Remove user records
            if (isset($this->createdRecords['users'])) {
                foreach ($this->createdRecords['users'] as $user) {
                    $sql = "DELETE FROM users WHERE id = ?";
                    QueryUtils::sqlStatementThrowException($sql, [$user['id']]);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't throw - cleanup should be best effort
            error_log("Error cleaning up test fixtures: " . $e->getMessage());
        }

        $this->createdRecords = [];
    }

    /**
     * Get next available PID for testing
     */
    private function getNextPid(): int
    {
        $sql = "SELECT COALESCE(MAX(pid), 0) + 1 as next_pid FROM patient_data WHERE pid >= 90000";
        $result = QueryUtils::sqlStatementThrowException($sql, []);
        $row = sqlFetchArray($result);
        return max(90000, $row['next_pid']); // Use PIDs >= 90000 for testing
    }

    /**
     * Get next available encounter ID for testing
     */
    private function getNextEncounterId(): int
    {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM form_encounter WHERE id >= 90000";
        $result = QueryUtils::sqlStatementThrowException($sql, []);
        $row = sqlFetchArray($result);
        return max(90000, $row['next_id']); // Use IDs >= 90000 for testing
    }

    /**
     * Get next available list ID for testing
     */
    private function getNextListId(): int
    {
        $sql = "SELECT COALESCE(MAX(id), 0) + 1 as next_id FROM lists WHERE id >= 90000";
        $result = QueryUtils::sqlStatementThrowException($sql, []);
        $row = sqlFetchArray($result);
        return max(90000, $row['next_id']); // Use IDs >= 90000 for testing
    }

    /**
     * Create sample diagnosis data for testing
     */
    public static function getSampleDiagnoses(): array
    {
        return [
            'hypertension' => [
                'code' => 'I10',
                'description' => 'Essential hypertension',
                'system' => 'http://hl7.org/fhir/sid/icd-10-cm'
            ],
            'diabetes' => [
                'code' => 'E11.9',
                'description' => 'Type 2 diabetes mellitus without complications',
                'system' => 'http://hl7.org/fhir/sid/icd-10-cm'
            ],
            'copd' => [
                'code' => 'J44.0',
                'description' => 'Chronic obstructive pulmonary disease with acute lower respiratory infection',
                'system' => 'http://hl7.org/fhir/sid/icd-10-cm'
            ],
            'depression' => [
                'code' => 'F32.9',
                'description' => 'Major depressive disorder, single episode, unspecified',
                'system' => 'http://hl7.org/fhir/sid/icd-10-cm'
            ]
        ];
    }

    /**
     * Create test scenarios for different condition categories
     */
    public function createCategoryTestScenarios(array $patientData): array
    {
        $scenarios = [];

        // Problem list item
        $scenarios['problem-list'] = $this->createTestCondition($patientData, [
            'title' => 'Chronic Hypertension',
            'type' => 'medical_problem',
            'begdate' => '2020-01-01',
            'enddate' => null
        ]);

        // Health concern
        $scenarios['health-concern'] = $this->createTestCondition($patientData, [
            'title' => 'Risk for Falls',
            'type' => 'medical_problem',
            'begdate' => '2023-01-01',
            'enddate' => null
        ]);

        return $scenarios;
    }
}
