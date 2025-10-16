<?php

/*
 * FhirObservationHistorySdohServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    AI Assistant (GitHub Claude.Ai)
 * @copyright Elements marked with AI GENERATED CODE - are in the public domain
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI GENERATED CODE - START
 * This entire file was generated with assistance from AI (GitHub Copilot/ChatGPT)
 * to create comprehensive integration tests for the FhirObservationHistorySdohService.
 * The code has been reviewed and tested for compliance with OpenEMR standards.
 * AI GENERATED CODE - END
 */

namespace OpenEMR\Tests\Services\FHIR\Observation;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\Observation\FhirObservationHistorySdohService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for FhirObservationHistorySdohService
 *
 * Tests the complete flow of:
 * 1. Inserting SDOH records using QueryUtil
 * 2. Retrieving and converting them via FhirObservationHistorySdohService
 * 3. Validating against US Core profiles for pregnancy intent, pregnancy status, and screening assessments
 */
class FhirObservationHistorySdohServiceTest extends TestCase
{
    private FhirObservationHistorySdohService $fhirService;
    private PatientService $patientService;
    private UserService $userService;
    private array $createdRecords = [];
    private array $testPatientData = [];
    private array $testUserData = [];

    protected function setUp(): void
    {
        parent::setUp();

        // AI GENERATED CODE - START: Service initialization
        $this->fhirService = new FhirObservationHistorySdohService();
        $this->patientService = new PatientService();
        $this->userService = new UserService();
        // AI GENERATED CODE - END

        // Create test patient and user data for our tests
        $this->createTestPatientAndUser();
    }

    protected function tearDown(): void
    {
        // AI GENERATED CODE - START: Cleanup test data
        // Clean up any records we created during testing
        foreach ($this->createdRecords as $recordId) {
            try {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM form_history_sdoh WHERE id = ?",
                    [$recordId]
                );
            } catch (\Exception $e) {
                // Log but don't fail cleanup
                error_log("Failed to cleanup SDOH record {$recordId}: " . $e->getMessage());
            }
        }

        // Clean up test patient and user
        $this->cleanupTestPatientAndUser();
        // AI GENERATED CODE - END

        parent::tearDown();
    }

    /**
     * AI GENERATED CODE - START
     * Create test patient and user data for the integration tests
     * AI GENERATED CODE - END
     */
    private function createTestPatientAndUser(): void
    {
        // Create test patient
        $this->testPatientData = [
            'fname' => 'Test',
            'lname' => 'SDOH Patient',
            'DOB' => '1990-01-01',
            'sex' => 'Female',
            'race' => 'unk',
            'ethnicity' => 'unk'
        ];

        $patientResult = $this->patientService->insert($this->testPatientData);
        if (!$patientResult->isValid()) {
            $this->fail("Failed to create test patient: " . implode(", ", $patientResult->getInternalErrors()));
        }
        $patientResult = $patientResult->getFirstDataResult();
        $this->testPatientData['pid'] = $patientResult['pid'];
        $this->testPatientData['uuid'] = $patientResult['uuid'];

        // Create test user
        $this->testUserData = [
            'uuid' => UuidRegistry::getRegistryForTable('users')->createUuid(),
            'username' => 'test_sdoh_user_' . uniqid(),
            'fname' => 'Test',
            'lname' => 'SDOH User',
            'authorized' => 1,
            'active' => 1,
            'npi' => '1234567890' // Required for FHIR Practitioner
        ];

        $insertId = QueryUtils::sqlInsert("INSERT INTO users (username, fname, lname, authorized, active, npi) VALUES (?, ?, ?, ?, ?, ?)", [
            $this->testUserData['username'],
            $this->testUserData['fname'],
            $this->testUserData['lname'],
            $this->testUserData['authorized'],
            $this->testUserData['active'],
            $this->testUserData['npi']
        ]);
        $this->testUserData['id'] = $insertId;
    }

    /**
     * AI GENERATED CODE - START
     * Clean up test patient and user data
     * AI GENERATED CODE - END
     */
    private function cleanupTestPatientAndUser(): void
    {
        if (!empty($this->testPatientData['pid'])) {
            try {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM patient_data WHERE pid = ?",
                    [$this->testPatientData['pid']]
                );
            } catch (\Exception $e) {
                error_log("Failed to cleanup test patient: " . $e->getMessage());
            }
        }

        if (!empty($this->testUserData['id'])) {
            try {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM users WHERE id = ?",
                    [$this->testUserData['id']]
                );
            } catch (\Exception $e) {
                error_log("Failed to cleanup test user: " . $e->getMessage());
            }
        }
    }

    /**
     * AI GENERATED CODE - START
     * Insert a test SDOH record with specified column values
     * @param array $columnValues - Column values to insert
     * @return int Record ID of inserted record
     * AI GENERATED CODE - END
     */
    private function insertSdohRecord(array $columnValues): int
    {
        $uuid = UuidRegistry::getRegistryForTable('form_history_sdoh')->createUuid();
        $baseData = [
            'uuid' => $uuid,
            'status' => FhirObservationHistorySdohService::OBSERVATION_VALID_STATII[array_rand(FhirObservationHistorySdohService::OBSERVATION_VALID_STATII, 1)],
            'pid' => $this->testPatientData['pid'],
            'encounter' => null, // No encounter for this test
            'created_by' => $this->testUserData['id'],
            'updated_by' => $this->testUserData['id'],
            'assessment_date' => date('Y-m-d'),
            'screening_tool' => 'PHQ-9' // AI GENERATED: Standard screening tool
        ];

        $insertData = array_merge($baseData, $columnValues);
        $sdohHistoryService = new HistorySdohService();
        $insertResult = $sdohHistoryService->insert($insertData);
        if (!$insertResult->isValid()) {
            $this->fail("Failed to insert SDOH record: " . implode(", ", $insertResult->getInternalErrors()));
        }
        $savedRecord = $insertResult->getFirstDataResult();
        $this->createdRecords[] = $savedRecord['id'];
        return $savedRecord['id'];
    }

    /**
     * Test pregnancy status observation generation and validation
     * Validates against: https://hl7.org/fhir/us/core/STU8/StructureDefinition-us-core-observation-pregnancystatus.html
     */
    public function testPregnancyStatusObservationValidation(): void
    {
        // AI GENERATED CODE - START: Test pregnancy status observation
        // Insert SDOH record with pregnancy status
        $recordId = $this->insertSdohRecord([
            'pregnancy_status' => 'pregnant', // Maps to SNOMED-CT:77386006 from list_options
            'pregnancy_edd' => '2025-09-01',
            'pregnancy_gravida' => 2,
            'pregnancy_para' => 1
        ]);

        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned pregnancy status observations");

        // Find the pregnancy status observation
        $pregnancyStatusObs = null;
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding) && $coding[0]->getCode() === '82810-3') {
                    $pregnancyStatusObs = $obs;
                    break;
                }
            }
        }

        $this->assertNotNull($pregnancyStatusObs, "Should have found pregnancy status observation");

        // Validate US Core Pregnancy Status profile compliance
        $profiles = $pregnancyStatusObs->getMeta()->getProfile();
        $hasPregnancyStatusProfile = false;
        foreach ($profiles as $profile) {
            if (str_contains((string) $profile, 'us-core-observation-pregnancystatus')) {
                $hasPregnancyStatusProfile = true;
                break;
            }
        }
        $this->assertTrue($hasPregnancyStatusProfile, "Observation should have US Core pregnancy status profile");

        // Validate required elements for pregnancy status profile
        $this->assertNotEmpty($pregnancyStatusObs->getStatus(), "Status is required");
        $this->assertNotEmpty($pregnancyStatusObs->getCode(), "Code is required");
        $this->assertNotEmpty($pregnancyStatusObs->getSubject(), "Subject is required");
        $this->assertNotEmpty($pregnancyStatusObs->getEffectiveDateTime(), "Effective date is required");

        // Validate code is correct LOINC for pregnancy status
        $coding = $pregnancyStatusObs->getCode()->getCoding()[0];
        $this->assertEquals('82810-3', $coding->getCode(), "Should use LOINC 82810-3 for pregnancy status");
        $this->assertEquals('http://loinc.org', $coding->getSystem(), "Should use LOINC system");

        // Validate category is social-history
        $categories = $pregnancyStatusObs->getCategory();
        $this->assertNotEmpty($categories, "Category should be present");
        $socialHistoryFound = false;
        foreach ($categories as $category) {
            $categoryCoding = $category->getCoding();
            foreach ($categoryCoding as $coding) {
                if ($coding->getCode()->getValue() === 'social-history') {
                    $socialHistoryFound = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($socialHistoryFound, "Should have social-history category");
        // AI GENERATED CODE - END
    }

    /**
     * Test pregnancy intent observation generation and validation
     * Validates against: https://hl7.org/fhir/us/core/STU8/StructureDefinition-us-core-observation-pregnancyintent.html
     */
    public function testPregnancyIntentObservationValidation(): void
    {
        // AI GENERATED CODE - START: Test pregnancy intent observation
        // Insert SDOH record with pregnancy intent
        $recordId = $this->insertSdohRecord([
            'pregnancy_intent' => 'wants_pregnancy' // This would map to appropriate SNOMED code
        ]);

        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned pregnancy intent observations");

        // Find the pregnancy intent observation
        $pregnancyIntentObs = null;
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding) && $coding[0]->getCode() === '86645-9') {
                    $pregnancyIntentObs = $obs;
                    break;
                }
            }
        }

        $this->assertNotNull($pregnancyIntentObs, "Should have found pregnancy intent observation");

        // Validate US Core Pregnancy Intent profile compliance
        $profiles = $pregnancyIntentObs->getMeta()->getProfile();
        $hasPregnancyIntentProfile = false;
        foreach ($profiles as $profile) {
            if (str_contains((string) $profile, 'us-core-observation-pregnancyintent')) {
                $hasPregnancyIntentProfile = true;
                break;
            }
        }
        $this->assertTrue($hasPregnancyIntentProfile, "Observation should have US Core pregnancy intent profile");

        // Validate required elements for pregnancy intent profile
        $this->assertNotEmpty($pregnancyIntentObs->getStatus(), "Status is required");
        $this->assertNotEmpty($pregnancyIntentObs->getCode(), "Code is required");
        $this->assertNotEmpty($pregnancyIntentObs->getSubject(), "Subject is required");
        $this->assertNotEmpty($pregnancyIntentObs->getEffectiveDateTime(), "Effective date is required");

        // Validate code is correct LOINC for pregnancy intent
        $coding = $pregnancyIntentObs->getCode()->getCoding()[0];
        $this->assertEquals('86645-9', $coding->getCode(), "Should use LOINC 86645-9 for pregnancy intent");
        $this->assertEquals('http://loinc.org', $coding->getSystem(), "Should use LOINC system");

        // Validate category is social-history
        $categories = $pregnancyIntentObs->getCategory();
        $this->assertNotEmpty($categories, "Category should be present");
        $socialHistoryFound = false;
        foreach ($categories as $category) {
            $categoryCoding = $category->getCoding();
            foreach ($categoryCoding as $coding) {
                if ($coding->getCode()->getValue() === 'social-history') {
                    $socialHistoryFound = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($socialHistoryFound, "Should have social-history category");
        // AI GENERATED CODE - END
    }

    /**
     * Test SDOH screening assessment observations validation
     * Validates against: https://hl7.org/fhir/us/core/STU8/StructureDefinition-us-core-observation-screening-assessment.html
     */
    public function testSdohScreeningAssessmentObservationValidation(): void
    {
        // AI GENERATED CODE - START: Test SDOH screening assessment observations
        // Insert SDOH record with various screening assessment data
        $recordId = $this->insertSdohRecord([
            'food_insecurity' => 'at_risk',
            'food_insecurity_notes' => 'Patient reports difficulty affording food',
            'housing_instability' => 'worried',
            'housing_instability_notes' => 'Concerns about rent payment',
            'transportation_insecurity' => 'yes',
            'transportation_insecurity_notes' => 'No reliable transportation to appointments',
            'utilities_insecurity' => 'at_risk',
            'interpersonal_safety' => 'safe',
            'financial_strain' => 'somewhat_hard',
            'social_isolation' => 'sometimes',
            'childcare_needs' => 'yes',
            'digital_access' => 'no'
        ]);

        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned SDOH screening observations");

        // Filter to get only screening assessment observations (non-pregnancy related)
        $screeningObservations = [];
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding)) {
                    $code = $coding[0]->getCode();
                    // Exclude pregnancy-specific codes
                    if (!in_array($code, ['82810-3', '86645-9'])) {
                        $screeningObservations[] = $obs;
                    }
                }
            }
        }

        $this->assertNotEmpty($screeningObservations, "Should have screening assessment observations");

        // Validate each screening observation
        foreach ($screeningObservations as $obs) {
            // Validate US Core Screening Assessment profile compliance
            $profiles = $obs->getMeta()->getProfile();
            $hasScreeningProfile = false;
            foreach ($profiles as $profile) {
                if (str_contains($profile, 'us-core-observation-screening-assessment')) {
                    $hasScreeningProfile = true;
                    break;
                }
            }
            $this->assertTrue($hasScreeningProfile, "Observation should have US Core screening assessment profile");

            // Validate required elements for screening assessment profile
            $this->assertNotEmpty($obs->getStatus(), "Status is required");
            $this->assertNotEmpty($obs->getCode(), "Code is required");
            $this->assertNotEmpty($obs->getSubject(), "Subject is required");
            $this->assertNotEmpty($obs->getEffectiveDateTime(), "Effective date is required");

            // Validate category includes 'survey'
            $categories = $obs->getCategory();
            $this->assertNotEmpty($categories, "Category should be present");
            $surveyFound = false;
            foreach ($categories as $category) {
                $categoryCoding = $category->getCoding();
                foreach ($categoryCoding as $coding) {
                    if ($coding->getCode()->getValue() === 'survey') {
                        $surveyFound = true;
                        break 2;
                    }
                }
            }
            $this->assertTrue($surveyFound, "Should have survey category for screening assessments");

            // Validate subject reference points to our test patient
            $subjectRef = $obs->getSubject()->getReference()->getValue();
            $this->assertStringContainsString($this->testPatientData['uuid'], $subjectRef, "Subject should reference test patient");

            // Validate observation has a value (string, quantity, or codeableconcept)
            $hasValue = !empty($obs->getValueString()) ||
                !empty($obs->getValueQuantity()) ||
                !empty($obs->getValueCodeableConcept()) ||
                !empty($obs->getDataAbsentReason());
            $this->assertTrue($hasValue, "Observation should have a value or data absent reason");
        }
        // AI GENERATED CODE - END
    }

    /**
     * Test that multiple column values from a single SDOH record generate multiple observations
     */
    public function testMultipleColumnsGenerateMultipleObservations(): void
    {
        // AI GENERATED CODE - START: Test multiple observations from single record
        // Insert SDOH record with multiple assessment values
        $recordId = $this->insertSdohRecord([
            'pregnancy_status' => 'pregnant',
            'pregnancy_intent' => 'wants_pregnancy',
            'food_insecurity' => 'at_risk',
            'housing_instability' => 'worried',
            'transportation_insecurity' => 'yes',
            'financial_strain' => 'somewhat_hard'
        ]);

        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned multiple observations");

        // We should have observations for each non-null column we provided
        $expectedMinObservations = 6; // pregnancy_status, pregnancy_intent, food_insecurity, housing_instability, transportation_insecurity, financial_strain
        $this->assertGreaterThanOrEqual(
            $expectedMinObservations,
            count($observations),
            "Should have at least {$expectedMinObservations} observations for the populated columns"
        );

        // Verify we have the specific observations we expect
        $observationCodes = [];
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding)) {
                    $observationCodes[] = $coding[0]->getCode();
                }
            }
        }

        // Should include pregnancy status and intent codes
        $this->assertContains('82810-3', $observationCodes, "Should have pregnancy status observation");
        $this->assertContains('86645-9', $observationCodes, "Should have pregnancy intent observation");

        // Should have codes for SDOH assessments (these will be mapped from the service's column mappings)
        $this->assertGreaterThan(2, count($observationCodes), "Should have additional SDOH screening observations beyond pregnancy codes");
        // AI GENERATED CODE - END
    }

    /**
     * Test observation metadata and references
     */
    public function testObservationMetadataAndReferences(): void
    {
        // AI GENERATED CODE - START: Test observation metadata
        $recordId = $this->insertSdohRecord([
            'pregnancy_status' => 'pregnant',
            'food_insecurity' => 'at_risk'
        ]);

        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have observations");

        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                // Validate metadata
                $meta = $obs->getMeta();
                $this->assertNotEmpty($meta, "Meta should be present");
                $this->assertNotEmpty($meta->getProfile(), "Profile should be specified");

                // Validate patient reference
                $subject = $obs->getSubject();
                $this->assertNotEmpty($subject, "Subject should be present");
                $this->assertNotEmpty($subject->getReference(), "Subject reference should be present");
                $this->assertStringContainsString('Patient/', $subject->getReference()->getValue(), "Subject should reference Patient resource");

                // Validate effective date
                $effectiveDateTime = $obs->getEffectiveDateTime();
                $this->assertNotEmpty($effectiveDateTime, "Effective date should be present");
                $this->assertNotEmpty($effectiveDateTime->getValue(), "Effective date value should be present");

                // Validate status
                $status = $obs->getStatus();
                $this->assertNotEmpty($status, "Status should be present");
                $this->assertContains($status->getValue(), FhirObservationHistorySdohService::OBSERVATION_VALID_STATII, "Status should be valid observation status");
            }
        }
        // AI GENERATED CODE - END
    }

    /**
     * Test empty/null values handling
     */
    public function testEmptyValuesHandling(): void
    {
        // AI GENERATED CODE - START: Test empty values handling
        // Insert SDOH record with mostly null values (only required fields populated)
        $recordId = $this->insertSdohRecord([
            'pregnancy_status' => null,
            'pregnancy_intent' => null,
            'food_insecurity' => null,
            'housing_instability' => null
        ]);

        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);

        // Should still return valid result even with null values
        $this->assertTrue($searchResult->isValid(), "Search result should be valid even with null values");

        // May or may not have observations depending on service implementation
        // The service should gracefully handle null values without throwing exceptions
        $this->assertIsArray($searchResult->getData(), "Should return array of observations");
        // AI GENERATED CODE - END
    }

    /**
     * Test patient context filtering
     */
    public function testPatientContextFiltering(): void
    {
        // AI GENERATED CODE - START: Test patient context filtering
        // Create a second test patient
        $secondPatientData = [
            'fname' => 'Second',
            'lname' => 'Test Patient',
            'DOB' => '1985-01-01',
            'sex' => 'Male',
            'race' => 'unk',
            'ethnicity' => 'unk'
        ];
        $secondPatientResult = $this->patientService->insert($secondPatientData);
        if (!$secondPatientResult->isValid()) {
            $this->fail("Failed to create second test patient: " . implode(", ", $secondPatientResult->getInternalErrors()));
        }
        $secondPatientResult = $secondPatientResult->getFirstDataResult();

        try {
            // Insert SDOH records for both patients
            $firstPatientRecordId = $this->insertSdohRecord([
                'pregnancy_status' => 'pregnant'
            ]);

            // Insert record for second patient
            $uuid = UuidRegistry::getRegistryForTable('form_history_sdoh')->createUuid();
            $secondPatientRecordId = QueryUtils::sqlInsert(
                "INSERT INTO form_history_sdoh (uuid, pid, encounter, created_by, updated_by, assessment_date, pregnancy_status) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $uuid,
                    $secondPatientResult['pid'],
                    1,
                    $this->testUserData['id'],
                    $this->testUserData['id'],
                    date('Y-m-d'),
                    'not_pregnant'
                ]
            );
            $this->createdRecords[] = $secondPatientRecordId;

            // Search for first patient's observations only
            $firstPatientResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
            $firstPatientObservations = $firstPatientResult->getData();

            // Search for second patient's observations only
            $secondPatientSearchResult = $this->fhirService->getAll(['patient' => $secondPatientResult['uuid']]);
            $secondPatientObservations = $secondPatientSearchResult->getData();

            // Verify patient context filtering works
            foreach ($firstPatientObservations as $obs) {
                if ($obs instanceof FHIRObservation) {
                    $this->assertStringContainsString(
                        $this->testPatientData['uuid'],
                        $obs->getSubject()->getReference()->getValue(),
                        "First patient observations should only reference first patient"
                    );
                }
            }

            foreach ($secondPatientObservations as $obs) {
                if ($obs instanceof FHIRObservation) {
                    $this->assertStringContainsString(
                        $secondPatientResult['uuid'],
                        $obs->getSubject()->getReference()->getValue(),
                        "Second patient observations should only reference second patient"
                    );
                }
            }
        } finally {
            // Clean up second patient
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$secondPatientResult['pid']]);
        }
        // AI GENERATED CODE - END
    }
}
