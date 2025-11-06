<?php

/**
 * FhirConditionService3_1_1Test.php
 * Unit tests for US Core 3.1.1 Condition Profile compliance
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain for most of this file marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 *            Minor additions were made by Stephen Nielson
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemListItemService;
use OpenEMR\Services\FHIR\FhirConditionService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\ConditionFixtureManager;

/**
 * Tests for US Core 3.1.1 Condition Profile compliance
 *
 * This test class validates that the FhirConditionService correctly implements
 * the US Core Condition Profile version 3.1.1 requirements as defined in:
 * http://hl7.org/fhir/us/core/STU3.1.1/StructureDefinition-us-core-condition.html
 * This class was generated with the assistance of Claude.AI and Microsoft Copilot
 */
class FhirConditionService3_1_1Test extends TestCase
{
    /**
     * @var FhirConditionService
     */
    private $fhirConditionService;

    /**
     * @var ConditionFixtureManager
     */
    private $fixtureManager;

    protected function setUp(): void
    {
        parent::setUp();
        // the 3_1_1 profile was based on problem-list-item, so we use that service
        $this->fhirConditionService = new FhirConditionProblemListItemService();
        $this->fixtureManager = new ConditionFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        parent::tearDown();
    }

    /**
     * Test that the service correctly populates all Must Support elements
     * Required by US Core 3.1.1: clinicalStatus, verificationStatus, category, code, subject
     */
    public function testMustSupportElementsArePopulated(): void
    {
        // Arrange
        $conditionData = $this->createValidConditionRecord();

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert - Verify all Must Support elements are present
        $this->assertInstanceOf(FHIRCondition::class, $fhirCondition);

        // clinicalStatus - Must Support
        $this->assertNotNull($fhirCondition->getClinicalStatus(), 'clinicalStatus must be present');
        $this->assertTrue($fhirCondition->getClinicalStatus()->getCoding()[0]->getCode() !== null);

        // verificationStatus - Must Support
        $this->assertNotNull($fhirCondition->getVerificationStatus(), 'verificationStatus must be present');
        $this->assertTrue($fhirCondition->getVerificationStatus()->getCoding()[0]->getCode() !== null);

        // category - Must Support (1..*)
        $this->assertNotNull($fhirCondition->getCategory(), 'category must be present');
        $this->assertGreaterThan(0, count($fhirCondition->getCategory()), 'category must have at least one value');

        // code - Must Support (1..1)
        $this->assertNotNull($fhirCondition->getCode(), 'code must be present');

        // subject - Must Support (1..1)
        $this->assertNotNull($fhirCondition->getSubject(), 'subject must be present');
        $this->assertStringStartsWith('Patient/', $fhirCondition->getSubject()->getReference());
    }

    /**
     * Test clinical status values conform to required value set
     * http://hl7.org/fhir/ValueSet/condition-clinical (required binding)
     */
    public function testClinicalStatusUsesRequiredValueSet(): void
    {
        $validClinicalStatuses = ['active', 'recurrence', 'relapse', 'inactive', 'remission', 'resolved'];

        foreach ($validClinicalStatuses as $status) {
            // Arrange
            $conditionData = $this->createConditionWithClinicalStatus($status);

            // Act
            $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

            // Assert
            $clinicalStatus = $fhirCondition->getClinicalStatus();
            $this->assertNotNull($clinicalStatus);

            $coding = $clinicalStatus->getCoding()[0];
            $this->assertEquals(
                'http://terminology.hl7.org/CodeSystem/condition-clinical',
                $coding->getSystem()
            );
            $this->assertContains($coding->getCode(), $validClinicalStatuses);
        }
    }

    /**
     * Test verification status values conform to required value set
     * http://hl7.org/fhir/ValueSet/condition-ver-status (required binding)
     */
    public function testVerificationStatusUsesRequiredValueSet(): void
    {
        $validVerificationStatuses = ['unconfirmed', 'provisional', 'differential', 'confirmed', 'refuted', 'entered-in-error'];

        foreach ($validVerificationStatuses as $status) {
            // Arrange
            $conditionData = $this->createConditionWithVerificationStatus($status);

            // Act
            $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

            // Assert
            $verificationStatus = $fhirCondition->getVerificationStatus();
            $this->assertNotNull($verificationStatus);

            $coding = $verificationStatus->getCoding()[0];
            $this->assertEquals(
                'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                $coding->getSystem()
            );
            $this->assertContains($coding->getCode(), $validVerificationStatuses);
        }
    }

    /**
     * Test category values conform to extensible US Core value set
     * US Core 3.1.1 categories: problem-list-item, encounter-diagnosis, health-concern
     */
    public function testCategoryUsesUSCoreValueSet(): void
    {
        $usCoreCategories = ['problem-list-item', 'encounter-diagnosis', 'health-concern'];

        foreach ($usCoreCategories as $category) {
            // Arrange
            $conditionData = $this->createConditionWithCategory($category);

            // Act
            $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

            // Assert
            $categories = $fhirCondition->getCategory();
            $this->assertNotNull($categories);
            $this->assertGreaterThan(0, count($categories));

            $coding = $categories[0]->getCoding()[0];
            $this->assertEquals(
                'http://terminology.hl7.org/CodeSystem/condition-category',
                $coding->getSystem()
            );
            $this->assertContains($coding->getCode(), $usCoreCategories);
        }
    }

    /**
     * Test that condition code uses extensible US Core condition code value set
     * http://hl7.org/fhir/us/core/ValueSet/us-core-condition-code
     */
    public function testConditionCodeUsesUSCoreValueSet(): void
    {
        // Arrange - Create condition with ICD-10 code (part of US Core value set)
        $conditionData = $this->createConditionWithCode('I25.10', 'Atherosclerotic heart disease', 'http://hl7.org/fhir/sid/icd-10-cm');

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert
        $code = $fhirCondition->getCode();
        $this->assertNotNull($code);

        $coding = $code->getCoding()[0];
        $this->assertEquals('I25.10', $coding->getCode());
        $this->assertEquals('Atherosclerotic heart disease', $coding->getDisplay());
        $this->assertEquals('http://hl7.org/fhir/sid/icd-10-cm', $coding->getSystem());
    }

    /**
     * Test subject references US Core Patient Profile
     */
    public function testSubjectReferencesUSCorePatient(): void
    {
        // Arrange
        $conditionData = $this->createValidConditionRecord();

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert
        $subject = $fhirCondition->getSubject();
        $this->assertNotNull($subject);

        $reference = $subject->getReference();
        $this->assertStringStartsWith('Patient/', $reference);

        // Verify UUID format
        $patientUuid = substr((string) $reference, 8); // Remove 'Patient/' prefix
        $this->assertTrue(UuidRegistry::isValidStringUUID($patientUuid));
    }

    /**
     * Test clinical status mapping from OpenEMR data
     */
    public function testClinicalStatusMapping(): void
    {
        $testCases = [
            // [OpenEMR data, expected FHIR clinical status]
            ['occurrence' => 0, 'outcome' => 0, 'enddate' => null, 'expected' => 'active'],
            ['occurrence' => 1, 'outcome' => 0, 'enddate' => null, 'expected' => 'resolved'],
            ['occurrence' => 0, 'outcome' => 1, 'enddate' => null, 'expected' => 'resolved'],
            ['occurrence' => 2, 'outcome' => 0, 'enddate' => null, 'expected' => 'recurrence'],
            ['occurrence' => 0, 'outcome' => 0, 'enddate' => date('Y-m-d', strtotime('-1 year')), 'expected' => 'inactive'],
        ];

        foreach ($testCases as $case) {
            // Arrange
            $conditionData = $this->createValidConditionRecord();
            $conditionData['occurrence'] = $case['occurrence'];
            $conditionData['outcome'] = $case['outcome'];
            $conditionData['enddate'] = $case['enddate'];

            // Act
            $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

            // Assert
            $clinicalStatus = $fhirCondition->getClinicalStatus();
            $this->assertEquals(
                $case['expected'],
                $clinicalStatus->getCoding()[0]->getCode(),
                "Clinical status mapping failed for case: " . json_encode($case)
            );
        }
    }

    /**
     * Test profile conformance in meta.profile
     */
    public function testProfileConformance(): void
    {
        // Arrange
        $conditionData = $this->createValidConditionRecord();

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert
        $meta = $fhirCondition->getMeta();
        $this->assertNotNull($meta);

        $profiles = $meta->getProfile();
        $this->assertNotNull($profiles);
        $this->assertGreaterThan(0, count($profiles));

        // Should include US Core 3.1.1 profile URL

        $this->assertContains('http://hl7.org/fhir/us/core/StructureDefinition/us-core-condition', $profiles);
    }

    /**
     * Test resource ID is properly set
     */
    public function testResourceIdIsSet(): void
    {
        // Arrange
        $conditionData = $this->createValidConditionRecord();

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert
        $id = $fhirCondition->getId();
        $this->assertNotNull($id);
        $this->assertTrue(UuidRegistry::isValidStringUUID($id->getValue()));
        $this->assertEquals($conditionData['uuid'], $id->getValue());
    }

    /**
     * Test meta.lastUpdated is populated
     */
    public function testLastUpdatedIsPopulated(): void
    {
        // Arrange
        $conditionData = $this->createValidConditionRecord();
        $conditionData['last_updated_time'] = '2023-01-15 10:30:00';

        // Act
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);

        // Assert
        $meta = $fhirCondition->getMeta();
        $this->assertNotNull($meta);

        $lastUpdated = $meta->getLastUpdated();
        $this->assertNotNull($lastUpdated);
        $this->assertNotEmpty($lastUpdated);
    }

    /**
     * Test search parameters are properly defined
     */
    public function testSearchParametersAreValid(): void
    {
        // Act
        $searchParams = $this->fhirConditionService->getSearchParams();

        // Assert required search parameters for US Core 3.1.1
        $expectedParams = ['patient', '_id', '_lastUpdated'];

        foreach ($expectedParams as $param) {
            $this->assertArrayHasKey($param, $searchParams, "Search parameter '$param' must be supported");
        }

        // Verify patient parameter configuration
        $patientParam = $searchParams['patient'];
        $this->assertEquals('reference', $patientParam->getType());
    }

    /**
     * Test constraint us-core-1: Category should be from US Core value set
     */
    public function testUSCore1Constraint(): void
    {
        // original implementation only supported problem-list-item even though the value set had encounter-diagnosis and health-concern
        // Act & Assert - Should not throw exception
        $conditionData = $this->createConditionWithCategory('problem-list-item');
        $fhirCondition = $this->fhirConditionService->parseOpenEMRRecord($conditionData);
        $this->assertNotNull($fhirCondition);

        $categories = $fhirCondition->getCategory();
        $categoryCode = $categories[0]->getCoding()[0]->getCode();
        $this->assertEquals('problem-list-item', $categoryCode);
    }

    /**
     * Helper method to create valid condition record
     */
    private function createValidConditionRecord(): array
    {
        return [
            'uuid' => UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('lists')->createUuid()),
            'pid' => 1,
            'puuid' => UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('patient_data')->createUuid()),
            'date' => '2023-01-01 10:00:00',
            'type' => 'medical_problem',
            'title' => 'Hypertension',
            'begdate' => '2023-01-01',
            'enddate' => null,
            'occurrence' => 0,
            'outcome' => 0,
            'verification' => 'confirmed',
            'diagnosis' => [
                'I10' => [
                    'description' => 'Essential hypertension',
                    'system' => 'http://hl7.org/fhir/sid/icd-10-cm'
                ]
            ],
            'activity' => 1,
            'comments' => 'Patient diagnosed with essential hypertension',
            'last_updated_time' => '2023-01-01 10:00:00'
        ];
    }

    /**
     * Helper to create condition with specific clinical status
     */
    private function createConditionWithClinicalStatus(string $status): array
    {
        $conditionData = $this->createValidConditionRecord();

        switch ($status) {
            case 'resolved':
                $conditionData['occurrence'] = 1;
                break;
            case 'recurrence':
                $conditionData['occurrence'] = 2;
                break;
            case 'inactive':
                $conditionData['enddate'] = date('Y-m-d', strtotime('-1 year'));
                break;
            case 'active':
            default:
                // Default is active
                break;
        }

        return $conditionData;
    }

    /**
     * Helper to create condition with specific verification status
     */
    private function createConditionWithVerificationStatus(string $status): array
    {
        $conditionData = $this->createValidConditionRecord();
        $conditionData['verification'] = $status;
        return $conditionData;
    }

    /**
     * Helper to create condition with specific category
     */
    private function createConditionWithCategory(string $category): array
    {
        $conditionData = $this->createValidConditionRecord();
        $conditionData['category'] = $category;
        return $conditionData;
    }

    /**
     * Helper to create condition with specific code
     */
    private function createConditionWithCode(string $code, string $display, string $system): array
    {
        $conditionData = $this->createValidConditionRecord();
        $conditionData['diagnosis'] = [
            $code => [
                'description' => $display,
                'system' => $system
            ]
        ];
        return $conditionData;
    }
}
