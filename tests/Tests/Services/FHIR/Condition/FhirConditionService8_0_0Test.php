<?php

/**
 * FhirConditionService8_0_0Test.php
 * Unit tests for US Core 8.0.0 Condition Profile compliance
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Public Domain for most of this file marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 *            Minor additions were made by Stephen Nielson
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemsHealthConcernService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;

/**
 * Tests for US Core 8.0.0 Condition Profile compliance
 *
 * This test class validates the new mapped service architecture with separate
 * profiles for encounter diagnoses and problems/health concerns as defined in:
 * - US Core Encounter Diagnosis Profile
 * - US Core Problems and Health Concerns Profile
 */
class FhirConditionService8_0_0Test extends TestCase
{
    /**
     * @var FhirConditionService
     */
    private $fhirConditionService;

    /**
     * @var FhirConditionEncounterDiagnosisService
     */
    private $encounterDiagnosisService;

    /**
     * @var FhirConditionProblemsHealthConcernService
     */
    private $problemsHealthConcernService;

    /**
     * @var ConditionFixtureManager
     */
    private $fixtureManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fhirConditionService = new FhirConditionService();
        $this->encounterDiagnosisService = new FhirConditionEncounterDiagnosisService();
        $this->problemsHealthConcernService = new FhirConditionProblemsHealthConcernService();
        $this->fixtureManager = new ConditionFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        parent::tearDown();
    }

    /**
     * Test that encounter diagnosis service correctly identifies encounter-linked conditions
     */
    public function testEncounterDiagnosisServiceHandlesEncounterLinkedConditions()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData);

        // Act
        $supportsCategory = $this->encounterDiagnosisService->supportsCategory('encounter-diagnosis');
        $fhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);

        // Assert
        $this->assertTrue($supportsCategory);
        $this->assertInstanceOf(FHIRCondition::class, $fhirCondition);

        // Verify encounter reference is present (required for encounter diagnosis)
        $encounter = $fhirCondition->getEncounter();
        $this->assertNotNull($encounter, 'Encounter reference is required for encounter diagnosis');
        $this->assertEquals('Encounter/' . $conditionData['encounter_uuid'], $encounter->getReference());

        // Verify category is encounter-diagnosis
        $categories = $fhirCondition->getCategory();
        $this->assertNotNull($categories);
        $categoryCode = $categories[0]->getCoding()[0]->getCode();
        $this->assertEquals('encounter-diagnosis', $categoryCode);
    }

    /**
     * Test that problems/health concerns service handles non-encounter conditions
     */
    public function testProblemsHealthConcernServiceHandlesNonEncounterConditions()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $conditionData = $this->fixtureManager->createTestCondition($patientData, [
            'title' => 'Chronic Hypertension',
            'type' => 'medical_problem'
        ]);

        // Act
        $supportsCategory = $this->problemsHealthConcernService->supportsCategory('problem-list-item');
        $fhirCondition = $this->problemsHealthConcernService->parseOpenEMRRecord($conditionData);

        // Assert
        $this->assertTrue($supportsCategory);
        $this->assertInstanceOf(FHIRCondition::class, $fhirCondition);

        // Verify encounter reference is NOT present (problems are not encounter-specific)
        $encounter = $fhirCondition->getEncounter();
        $this->assertNull($encounter, 'Problems/health concerns should not have encounter reference');

        // Verify category is problem-list-item
        $categories = $fhirCondition->getCategory();
        $this->assertNotNull($categories);
        $categoryCode = $categories[0]->getCoding()[0]->getCode();
        $this->assertEquals('problem-list-item', $categoryCode);
    }

    /**
     * Test synthetic UUID generation for encounter diagnoses (post-cutover)
     */
    public function testSyntheticUUIDGenerationForNewEncounterDiagnoses()
    {
        // Arrange - Create condition after cutover date
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData, [
            'date' => date('Y-m-d H:i:s'), // Current date (after cutover)
        ]);

        // Act
        $fhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);

        // Assert
        $resourceId = $fhirCondition->getId()->getValue();

        // Should NOT be the original condition UUID for post-cutover conditions
        $this->assertNotEquals($conditionData['lists_uuid'], $resourceId);

        // Should be a valid UUID
        $this->assertTrue(UuidRegistry::isValidStringUUID($resourceId));
        $this->assertEquals($conditionData['uuid'], $resourceId, "UUID should be issue_encounter uuid and not lists_uuid");

        // Should be deterministic - same condition + encounter should generate same UUID
        $fhirCondition2 = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);
        $resourceId2 = $fhirCondition2->getId()->getValue();
        $this->assertEquals($resourceId, $resourceId2, 'Synthetic UUID should be deterministic');
    }

    /**
     * Test original UUID preservation for historical data (pre-cutover)
     */
    public function testOriginalUUIDPreservationForHistoricalData()
    {
        // Arrange - Create condition before cutover date
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData, [
            'date' => '2024-01-01 10:00:00', // Before cutover date
        ]);

        $fhirConditionResult = $this->encounterDiagnosisService->getAll([
            'encounter' => $encounterData['uuid']
        ]);
        $this->assertTrue($fhirConditionResult->isValid(), "FHIR Condition should have been created successfully");
        $fhirCondition = $fhirConditionResult->getFirstDataResult();
        $this->assertNotNull($fhirCondition, "FHIR Condition should not be null for encounter " . $encounterData['uuid']);
        $this->assertInstanceOf(FHIRCondition::class, $fhirCondition);

        // Assert
        $resourceId = $fhirCondition->getId()->getValue();

        // Should be the original condition UUID for pre-cutover conditions which is the lists_uuid
        $this->assertEquals($conditionData['lists_uuid'], $resourceId);
    }

    /**
     * Test clinical status mapping from resolved flag in issue_encounter
     */
    public function testClinicalStatusMappingFromResolvedFlag()
    {
        $testCases = [
            ['resolved' => 0, 'expected' => 'active'],
            ['resolved' => 1, 'expected' => 'resolved'],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $patientData = $this->fixtureManager->createTestPatient();
            $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
            $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData);
            $conditionData['resolved'] = $case['resolved'];

            // Act
            $fhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);

            // Assert
            $clinicalStatus = $fhirCondition->getClinicalStatus();
            $this->assertEquals(
                $case['expected'],
                $clinicalStatus->getCoding()[0]->getCode(),
                "Clinical status mapping failed for resolved={$case['resolved']}"
            );
        }
    }

    /**
     * Test that both services are properly registered in main FhirConditionService
     */
    public function testMappedServicesAreRegistered()
    {
        // This test would need to be adjusted based on the actual implementation
        // of how mapped services are accessed

        // Act
        $profileUris = $this->fhirConditionService->getProfileURIs();

        // Assert
        $this->assertContains(
            FhirConditionEncounterDiagnosisService::USCGI_PROFILE_ENCOUNTER_DIAGNOSIS_URI,
            $profileUris,
            'Encounter diagnosis profile should be supported'
        );
        $this->assertContains(
            FhirConditionProblemsHealthConcernService::USCGI_PROFILE_PROBLEMS_HEALTH_CONCERNS_URI,
            $profileUris,
            'Problems and health concerns profile should be supported'
        );
    }

    /**
     * Test encounter diagnosis profile conformance
     */
    public function testEncounterDiagnosisProfileConformance()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData);

        // Act
        $fhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);

        // Assert
        $meta = $fhirCondition->getMeta();
        $this->assertNotNull($meta);

        $profiles = $meta->getProfile();
        $this->assertNotNull($profiles);

        $this->assertContains(
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-encounter-diagnosis',
            $profiles
        );
    }

    /**
     * Test problems and health concerns profile conformance
     */
    public function testProblemsHealthConcernProfileConformance()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $conditionData = $this->fixtureManager->createTestCondition($patientData);

        // Act
        $fhirCondition = $this->problemsHealthConcernService->parseOpenEMRRecord($conditionData);

        // Assert
        $meta = $fhirCondition->getMeta();
        $this->assertNotNull($meta);

        $profiles = $meta->getProfile();
        $this->assertNotNull($profiles);
        $this->assertContains(
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition-problems-health-concerns',
            $profiles
        );
    }

    /**
     * Test recorded date population for encounter diagnoses
     */
    public function testRecordedDatePopulationForEncounterDiagnoses()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $conditionData = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData);

        // Act
        $fhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);

        // Assert
        $recordedDate = $fhirCondition->getRecordedDate();
        $this->assertNotNull($recordedDate, 'recordedDate is Must Support for encounter diagnosis');
        $this->assertNotEmpty($recordedDate);
    }

    /**
     * Test that multiple encounters for same condition generate unique resources
     */
    public function testMultipleEncountersGenerateUniqueResources()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $encounter1 = $this->fixtureManager->createTestEncounter($patientData['pid']);
        $encounter2 = $this->fixtureManager->createTestEncounter($patientData['pid']);

        // Create same condition linked to different encounters
        $condition1 = $this->fixtureManager->createTestEncounterCondition($patientData, $encounter1, [
            'title' => 'Hypertension',
            'date' => date('Y-m-d H:i:s') // Post-cutover
        ]);

        $condition2 = $this->fixtureManager->createTestEncounterCondition($patientData, $encounter2, [
            'title' => 'Hypertension',
            'date' => date('Y-m-d H:i:s') // Post-cutover
        ]);

        // Act
        $fhirCondition1 = $this->encounterDiagnosisService->parseOpenEMRRecord($condition1);
        $fhirCondition2 = $this->encounterDiagnosisService->parseOpenEMRRecord($condition2);

        // Assert
        $resourceId1 = $fhirCondition1->getId()->getValue();
        $resourceId2 = $fhirCondition2->getId()->getValue();

        // Should generate different FHIR resource IDs
        $this->assertNotEquals(
            $resourceId1,
            $resourceId2,
            'Same condition in different encounters should generate different FHIR resource IDs'
        );

        // Both should reference their respective encounters
        $this->assertEquals(
            'Encounter/' . $encounter1['uuid'],
            $fhirCondition1->getEncounter()->getReference()
        );
        $this->assertEquals(
            'Encounter/' . $encounter2['uuid'],
            $fhirCondition2->getEncounter()->getReference()
        );
    }

    /**
     * Test verification status defaults for encounter diagnoses vs problems
     */
    public function testVerificationStatusDefaults()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $encounterData = $this->fixtureManager->createTestEncounter($patientData['pid']);

        $encounterCondition = $this->fixtureManager->createTestEncounterCondition($patientData, $encounterData);
        unset($encounterCondition['verification']); // Remove explicit verification

        $problemCondition = $this->fixtureManager->createTestCondition($patientData);
        unset($problemCondition['verification']); // Remove explicit verification

        // Act
        $encounterFhirCondition = $this->encounterDiagnosisService->parseOpenEMRRecord($encounterCondition);
        $problemFhirCondition = $this->problemsHealthConcernService->parseOpenEMRRecord($problemCondition);

        // Assert
        $encounterVerification = $encounterFhirCondition->getVerificationStatus()->getCoding()[0]->getCode();
        $problemVerification = $problemFhirCondition->getVerificationStatus()->getCoding()[0]->getCode();

        // Encounter diagnoses should default to 'confirmed'
        $this->assertEquals('confirmed', $encounterVerification);

        // Problems should default to 'unconfirmed'
        $this->assertEquals('unconfirmed', $problemVerification);
    }

    /**
     * Test search parameters for encounter filtering
     */
    public function testEncounterSearchParameter()
    {
        // Act
        $searchParams = $this->encounterDiagnosisService->getSearchParams();

        // Assert
        $this->assertArrayHasKey(
            'encounter',
            $searchParams,
            'Encounter diagnosis service must support encounter search parameter'
        );

        $encounterParam = $searchParams['encounter'];
        $this->assertEquals('reference', $encounterParam->getType());
    }

    /**
     * Test backwards compatibility - 3.1.1 profile still supported
     */
    public function testBackwardsCompatibilityWith3_1_1Profile()
    {
        // Act
        $profileUris = $this->fhirConditionService->getProfileURIs();

        // Assert - Should still support legacy 3.1.1 profile for backwards compatibility
        $this->assertContains(
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition',
            $profileUris,
            'Should maintain backwards compatibility with US Core 3.1.1'
        );
    }

    /**
     * Test that service routing works correctly based on category
     */
    public function testServiceRoutingBasedOnCategory()
    {
        // This would test the mapped service architecture's routing logic
        // The specific implementation would depend on how the main service delegates to sub-services

        $this->assertTrue($this->encounterDiagnosisService->supportsCategory('encounter-diagnosis'));
        $this->assertFalse($this->encounterDiagnosisService->supportsCategory('problem-list-item'));

        $this->assertTrue($this->problemsHealthConcernService->supportsCategory('problem-list-item'));
        $this->assertTrue($this->problemsHealthConcernService->supportsCategory('health-concern'));
        $this->assertFalse($this->problemsHealthConcernService->supportsCategory('encounter-diagnosis'));
    }

    /**
     * Test error handling for encounter diagnosis without encounter reference
     */
    public function testEncounterDiagnosisRequiresEncounterReference()
    {
        // Arrange
        $patientData = $this->fixtureManager->createTestPatient();
        $conditionData = $this->fixtureManager->createTestCondition($patientData);
        // Don't link to encounter

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('EncounterDiagnosis must have valid encounter reference');

        $this->encounterDiagnosisService->parseOpenEMRRecord($conditionData);
    }
}
