<?php

/*
 * FhirObservationPatientServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR\Observation;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use OpenEMR\Services\ListService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for FhirObservationPatientService
 *
 * Tests the complete flow of:
 * 1. Inserting patient data with occupation and sexual orientation
 * 2. Retrieving and converting them via FhirObservationPatientService
 * 3. Validating against US Core profiles for occupation and sexual orientation observations
 */
class FhirObservationPatientServiceTest extends TestCase
{
    use SystemLoggerAwareTrait;

    private FhirObservationPatientService $fhirService;
    private PatientService $patientService;
    private UserService $userService;
    private ListService $listService;
    private array $createdRecords = [];
    private array $testPatientData = [];
    private array $testUserData = [];

    protected function setUp(): void
    {
        parent::setUp();

        // disable debug logs so we don't see them on unit tests
        $systemLogger = new SystemLogger(Level::Warning);
        // AI GENERATED CODE - START: Service initialization
        $this->fhirService = new FhirObservationPatientService();
        $this->fhirService->setSystemLogger($systemLogger);
        $this->patientService = new PatientService();
        $this->patientService->setLogger($systemLogger);
        $this->userService = new UserService();
        $this->listService = new ListService();
        // AI GENERATED CODE - END

        // Create test patient and user data for our tests
        $this->createTestPatientAndUser();
        $this->ensureRequiredListOptions();
    }

    protected function tearDown(): void
    {
        // AI GENERATED CODE - START: Cleanup test data
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
            'lname' => 'Patient Observation',
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
            'username' => 'test_patient_obs_user_' . uniqid(),
            'fname' => 'Test',
            'lname' => 'Patient Observer',
            'authorized' => 1,
            'active' => 1,
            'npi' => '9876543210' // Required for FHIR Practitioner
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
     * Ensure required list options exist for testing
     * AI GENERATED CODE - END
     */
    private function ensureRequiredListOptions(): void
    {
        // TODO: @adunsulag not sure I like creating list options in tests, but we need them to exist for the coded values
        // Ensure OccupationODH list options exist
        $this->ensureListOption('OccupationODH', 'test_occupation', 'Test Software Developer', 'SNOMED-CT:223366009');

        // Ensure IndustryODH list options exist
        $this->ensureListOption('IndustryODH', 'test_industry', 'Test Software Development Industry', 'NAICS:541511');

        // Ensure sexual_orientation list options exist
        $this->ensureListOption('sexual_orientation', 'test_heterosexual', 'Test Heterosexual', 'SNOMED-CT:20430005');
    }

    /**
     * AI GENERATED CODE - START
     * Ensure a specific list option exists
     * AI GENERATED CODE - END
     */
    private function ensureListOption(string $listId, string $optionId, string $title, string $codes): void
    {
        // Check if option already exists
        $existing = QueryUtils::fetchRecords(
            "SELECT * FROM list_options WHERE list_id = ? AND option_id = ?",
            [$listId, $optionId]
        );

        if (!$existing) {
            QueryUtils::sqlInsert(
                "INSERT INTO list_options (list_id, option_id, title, codes, activity) VALUES (?, ?, ?, ?, 1)",
                [$listId, $optionId, $title, $codes]
            );
        }
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
                $this->getSystemLogger()->errorLogCaller("Failed to cleanup test patient: " . $e->getMessage());
            }
        }

        if (!empty($this->testUserData['id'])) {
            try {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM users WHERE id = ?",
                    [$this->testUserData['id']]
                );
            } catch (\Exception $e) {
                $this->getSystemLogger()->errorLogCaller("Failed to cleanup test user: " . $e->getMessage());
            }
        }
    }

    /**
     * AI GENERATED CODE - START
     * Update patient data with specific field values
     * @param array $fieldValues - Field values to update
     * @return bool Success of update operation
     * AI GENERATED CODE - END
     */
    private function updatePatientData(array $fieldValues): bool
    {
        $updateData = array_merge(['uuid' => $this->testPatientData['uuid']], $fieldValues);
        $updateResult = $this->patientService->update($this->testPatientData['uuid'], $updateData);

        if (!$updateResult->isValid()) {
            $this->fail("Failed to update patient data: " . implode(", ", $updateResult->getInternalErrors()));
            return false;
        }
        return true;
    }

    /**
     * Test sexual orientation observation generation and validation
     * Validates against: https://hl7.org/fhir/us/core/STU8/StructureDefinition-us-core-observation-sexual-orientation.html
     */
    public function testSexualOrientationObservationValidation(): void
    {
        // AI GENERATED CODE - START: Test sexual orientation observation
        // Update patient with sexual orientation data
        $this->updatePatientData([
            'sexual_orientation' => 'test_heterosexual'
        ]);

        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned sexual orientation observations");

        // Find the sexual orientation observation
        $sexualOrientationObs = null;
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding) && $coding[0]->getCode() === '76690-7') {
                    $sexualOrientationObs = $obs;
                    break;
                }
            }
        }

        $this->assertNotNull($sexualOrientationObs, "Should have found sexual orientation observation");

        // Validate US Core Sexual Orientation profile compliance
        $profiles = $sexualOrientationObs->getMeta()->getProfile();
        $hasSexualOrientationProfile = false;
        foreach ($profiles as $profile) {
            if (str_contains((string) $profile->getValue(), 'us-core-observation-sexual-orientation')) {
                $hasSexualOrientationProfile = true;
                break;
            }
        }
        $this->assertTrue($hasSexualOrientationProfile, "Observation should have US Core sexual orientation profile");

        // Validate required elements for sexual orientation profile
        $this->assertNotEmpty($sexualOrientationObs->getStatus(), "Status is required");
        $this->assertNotEmpty($sexualOrientationObs->getCode(), "Code is required");
        $this->assertNotEmpty($sexualOrientationObs->getSubject(), "Subject is required");
        $this->assertNotEmpty($sexualOrientationObs->getEffectiveDateTime(), "Effective date is required");

        // Validate code is correct LOINC for sexual orientation
        $coding = $sexualOrientationObs->getCode()->getCoding()[0];
        $this->assertEquals('76690-7', $coding->getCode(), "Should use LOINC 76690-7 for sexual orientation");
        $this->assertEquals('http://loinc.org', $coding->getSystem(), "Should use LOINC system");

        // Validate category is social-history
        $categories = $sexualOrientationObs->getCategory();
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

        // Validate value is present as CodeableConcept
        $valueCC = $sexualOrientationObs->getValueCodeableConcept();
        $this->assertNotEmpty($valueCC, "Should have valueCodeableConcept for sexual orientation");
        $this->assertNotEmpty($valueCC->getCoding(), "ValueCodeableConcept should have coding");
        // AI GENERATED CODE - END
    }

    private function getMappedUuidRecord(string $resource, string $category, string $code): ?string
    {
        $mappings = UuidMapping::getMappingForUUID($this->testPatientData['uuid']);
        foreach ($mappings as $mapping) {
            if ($mapping['resource'] === $resource) {
                $variables = parse_url((string) $mapping['resource_path'], PHP_URL_QUERY);
                if (($variables['category'] ?? '') === $category && ($variables['code'] ?? '') === $code) {
                    return $mapping['uuid'];
                }
            }
        }
        return null;
    }

    /**
     * Test parseOpenEMRRecord method directly
     */
    public function testParseOpenEMRRecordMethod(): void
    {
        // AI GENERATED CODE - START: Test parseOpenEMRRecord method directly
        // Create test data record for sexual orientation
        $sexualOrientationRecord = [
            'uuid' => $this->getMappedUuidRecord('Observation', 'social-history', '76690-7'),
            'code' => 'LOINC:76690-7',
            'description' => 'Sexual Orientation',
            'ob_type' => 'social-history',
            'ob_status' => 'final',
            'puuid' => $this->testPatientData['uuid'],
            'user_uuid' => $this->testUserData['uuid'] ?? 'test-user-uuid',
            'date' => date('Y-m-d H:i:s'),
            'last_updated_time' => date('Y-m-d H:i:s'),
            'profiles' => [FhirObservationPatientService::USCDI_PROFILE_SEXUAL_ORIENTATION_URI],
            'value' => 'SNOMED-CT:20430005',
            'value_code_description' => 'Test Heterosexual'
        ];

        // Test parseOpenEMRRecord method
        $observation = $this->fhirService->parseOpenEMRRecord($sexualOrientationRecord);
        $this->assertInstanceOf(FHIRObservation::class, $observation, "Should return FHIRObservation instance");

        // Validate key elements
        $this->assertEquals($sexualOrientationRecord['uuid'], $observation->getId(), "ID should match");
        $this->assertEquals('final', $observation->getStatus()->getValue(), "Status should be final");
        $this->assertEquals('76690-7', $observation->getCode()->getCoding()[0]->getCode(), "Code should be correct");
        $this->assertStringContainsString($this->testPatientData['uuid'], $observation->getSubject()->getReference()->getValue(), "Subject should reference test patient");
        // AI GENERATED CODE - END
    }

    /**
     * Test empty/null values handling
     */
    public function testEmptyValuesHandling(): void
    {
        // AI GENERATED CODE - START: Test empty values handling
        // Update patient with empty/null values
        $this->updatePatientData([
            'occupation' => null,
            'industry' => null,
            'sexual_orientation' => null
        ]);

        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);

        // Should still return valid result even with null values
        $this->assertTrue($searchResult->isValid(), "Search result should be valid even with null values");

        // Should return empty array since no valid coded values are present
        $observations = $searchResult->getData();
        $this->assertIsArray($observations, "Should return array of observations");
        $this->assertEmpty($observations, "Should return empty array when no coded values are present");
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
            // Update first patient with occupation data
            $this->updatePatientData(['occupation' => 'test_occupation']);

            // Update second patient with sexual orientation data
            $updateData = ['uuid' => $secondPatientResult['uuid'], 'sexual_orientation' => 'test_heterosexual'];
            $updateResult = $this->patientService->update($secondPatientResult['uuid'], $updateData);
            $this->assertTrue($updateResult->isValid(), "Should update second patient successfully");

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

            // Verify different observation types per patient
            if (!empty($firstPatientObservations)) {
                $firstPatientCodes = [];
                foreach ($firstPatientObservations as $obs) {
                    if ($obs instanceof FHIRObservation) {
                        $coding = $obs->getCode()->getCoding();
                        if (!empty($coding)) {
                            $firstPatientCodes[] = $coding[0]->getCode();
                        }
                    }
                }
                $this->assertContains('11341-5', $firstPatientCodes, "First patient should have occupation observation");
            }

            if (!empty($secondPatientObservations)) {
                $secondPatientCodes = [];
                foreach ($secondPatientObservations as $obs) {
                    if ($obs instanceof FHIRObservation) {
                        $coding = $obs->getCode()->getCoding();
                        if (!empty($coding)) {
                            $secondPatientCodes[] = $coding[0]->getCode();
                        }
                    }
                }
                $this->assertContains('76690-7', $secondPatientCodes, "Second patient should have sexual orientation observation");
            }
        } finally {
            // Clean up second patient
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$secondPatientResult['pid']]);
        }
        // AI GENERATED CODE - END
    }

    /**
     * Test category filtering
     */
    public function testCategoryFiltering(): void
    {
        // AI GENERATED CODE - START: Test category filtering
        // Update patient with data
        $this->updatePatientData([
            'occupation' => 'test_occupation',
            'sexual_orientation' => 'test_heterosexual'
        ]);

        // Search with social-history category filter
        $searchResult = $this->fhirService->getAll([
            'patient' => $this->testPatientData['uuid'],
            'category' => 'social-history'
        ]);
        $this->assertTrue($searchResult->isValid(), "Search with category filter should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should return observations for social-history category");

        // Verify all returned observations have social-history category
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $categories = $obs->getCategory();
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
                $this->assertTrue($socialHistoryFound, "All observations should have social-history category");
            }
        }
        // AI GENERATED CODE - END
    }

    /**
     * Test code filtering
     */
    public function testCodeFiltering(): void
    {
        // AI GENERATED CODE - START: Test code filtering
        // Update patient with both types of data
        $this->updatePatientData([
            'sexual_orientation' => 'test_heterosexual'
        ]);

        // Search for only sexual orientation observations
        $searchResult = $this->fhirService->getAll([
            'patient' => $this->testPatientData['uuid'],
            'code' => '76690-7'
        ]);
        $this->assertTrue($searchResult->isValid(), "Search with code filter should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should return observations for sexual orientation code");

        // Verify all returned observations have the specified code
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                $this->assertNotEmpty($coding, "Observation should have coding");
                $this->assertEquals('76690-7', $coding[0]->getCode(), "Should only return sexual orientation observations");
            }
        }

        // Search for only occupation observations
        $searchResult = $this->fhirService->getAll([
            'patient' => $this->testPatientData['uuid'],
            'code' => '11341-5'
        ]);
        $this->assertTrue($searchResult->isValid(), "Search with occupation code filter should be valid");

        $observations = $searchResult->getData();
        // AI GENERATED CODE - END
        $this->assertEmpty($observations, "Should NOT return observations for occupation code");
    }
}
