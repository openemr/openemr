<?php

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Entity\PatientRelationship;
use OpenEMR\Services\PatientRelationshipService;
use OpenEMR\Services\PatientService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * Patient Relationship Service Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PatientRelationshipServiceTest extends TestCase
{
    private PatientRelationshipService $relationshipService;
    private FixtureManager $fixtureManager;
    private array $patientFixtures;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->relationshipService = new PatientRelationshipService(new PatientService());

        // Install patient fixtures for testing
        $this->patientFixtures = $this->fixtureManager->installPatientFixtures();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->fixtureManager->removePatientFixtures();
        $this->removeRelationshipFixtures();
    }

    private function removeRelationshipFixtures(): void
    {
        // Remove any relationships created during testing
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_relationships WHERE notes LIKE 'TEST:%'");
    }

    public function testCreateValidRelationship(): void
    {
        $patientIds = array_keys($this->patientFixtures);
        if (count($patientIds) < 2) {
            $this->markTestSkipped('Need at least 2 patient fixtures for relationship testing');
        }

        $relationship = new PatientRelationship(
            (int)$patientIds[0],
            (int)$patientIds[1],
            'lives_with',
            1,
            'TEST: Valid relationship'
        );

        $result = $this->relationshipService->createRelationship($relationship);

        $this->assertFalse($result->hasErrors());
        $this->assertNotEmpty($result->getData());

        $data = $result->getData();
        $this->assertArrayHasKey(0, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('uuid', $data[0]);
        $this->assertArrayHasKey('relationship', $data[0]);
        $this->assertGreaterThan(0, $data[0]['id']);
        $this->assertNotEmpty($data[0]['uuid']);
    }

    public function testCreateRelationshipWithInvalidPatient(): void
    {
        $relationship = new PatientRelationship(
            999999, // Non-existent patient
            1,
            'lives_with',
            1,
            'TEST: Invalid patient'
        );

        $result = $this->relationshipService->createRelationship($relationship);

        $this->assertTrue($result->hasErrors());
        $this->assertContains('One or both patients do not exist', $result->getValidationMessages());
    }

    public function testCreateRelationshipWithInvalidData(): void
    {
        $relationship = new PatientRelationship(
            1,
            1, // Same patient ID
            'lives_with',
            1,
            'TEST: Self relationship'
        );

        $result = $this->relationshipService->createRelationship($relationship);

        $this->assertTrue($result->hasErrors());
        $this->assertContains('Cannot create relationship with self', $result->getValidationMessages());
    }

    public function testGetPatientRelationships(): void
    {
        // First create a test relationship
        $patientIds = array_keys($this->patientFixtures);
        if (count($patientIds) < 2) {
            $this->markTestSkipped('Need at least 2 patient fixtures for relationship testing');
        }

        $relationship = new PatientRelationship(
            (int)$patientIds[0],
            (int)$patientIds[1],
            'family_member',
            1,
            'TEST: Get relationships test'
        );

        $createResult = $this->relationshipService->createRelationship($relationship);
        $this->assertFalse($createResult->hasErrors());

        // Now test getting relationships
        $result = $this->relationshipService->getPatientRelationships((int)$patientIds[0]);

        $this->assertFalse($result->hasErrors());
        $relationships = $result->getData();
        $this->assertIsArray($relationships);
        $this->assertNotEmpty($relationships);

        // Check structure of returned data
        $firstRelationship = $relationships[0];
        $this->assertArrayHasKey('entity', $firstRelationship);
        $this->assertArrayHasKey('patient_name', $firstRelationship);
        $this->assertArrayHasKey('related_name', $firstRelationship);
        $this->assertArrayHasKey('relationship_title', $firstRelationship);
        $this->assertInstanceOf(PatientRelationship::class, $firstRelationship['entity']);
    }

    public function testDeleteRelationship(): void
    {
        // First create a test relationship
        $patientIds = array_keys($this->patientFixtures);
        if (count($patientIds) < 2) {
            $this->markTestSkipped('Need at least 2 patient fixtures for relationship testing');
        }

        $relationship = new PatientRelationship(
            (int)$patientIds[0],
            (int)$patientIds[1],
            'close_contact',
            1,
            'TEST: Delete test relationship'
        );

        $createResult = $this->relationshipService->createRelationship($relationship);
        $this->assertFalse($createResult->hasErrors());

        $createdData = $createResult->getData();
        $relationshipId = $createdData[0]['id'];

        // Now test deletion
        $deleteResult = $this->relationshipService->deleteRelationship($relationshipId);

        $this->assertFalse($deleteResult->hasErrors());
        $this->assertArrayHasKey('deleted', $deleteResult->getData()[0]);
        $this->assertTrue($deleteResult->getData()[0]['deleted']);

        // Verify relationship is marked as inactive
        $checkResult = $this->relationshipService->getPatientRelationships((int)$patientIds[0]);
        $relationships = $checkResult->getData();

        // Should be empty since inactive relationships are filtered out
        $this->assertEmpty($relationships);
    }

    public function testGetRelationshipTypes(): void
    {
        $types = $this->relationshipService->getRelationshipTypes();

        $this->assertIsArray($types);
        $this->assertNotEmpty($types);

        // Check structure
        foreach ($types as $type) {
            $this->assertArrayHasKey('option_id', $type);
            $this->assertArrayHasKey('title', $type);
            $this->assertNotEmpty($type['option_id']);
            $this->assertNotEmpty($type['title']);
        }

        // Should include our default types
        $optionIds = array_column($types, 'option_id');
        $this->assertContains('lives_with', $optionIds);
        $this->assertContains('family_member', $optionIds);
    }

    public function testBidirectionalRelationshipQuery(): void
    {
        // Create relationships in both directions
        $patientIds = array_keys($this->patientFixtures);
        if (count($patientIds) < 2) {
            $this->markTestSkipped('Need at least 2 patient fixtures for relationship testing');
        }

        $patient1Id = (int)$patientIds[0];
        $patient2Id = (int)$patientIds[1];

        // Patient 1 -> Patient 2
        $relationship1 = new PatientRelationship(
            $patient1Id,
            $patient2Id,
            'household_member',
            1,
            'TEST: Bidirectional test 1->2'
        );

        $this->relationshipService->createRelationship($relationship1);

        // Patient 2 -> Patient 1 (reverse relationship)
        $relationship2 = new PatientRelationship(
            $patient2Id,
            $patient1Id,
            'caregiver',
            1,
            'TEST: Bidirectional test 2->1'
        );

        $this->relationshipService->createRelationship($relationship2);

        // Patient 1 should see both relationships (one where they're the patient, one where they're the related patient)
        $result1 = $this->relationshipService->getPatientRelationships($patient1Id);
        $relationships1 = $result1->getData();

        $this->assertCount(2, $relationships1);

        // Patient 2 should also see both relationships
        $result2 = $this->relationshipService->getPatientRelationships($patient2Id);
        $relationships2 = $result2->getData();

        $this->assertCount(2, $relationships2);
    }
}
