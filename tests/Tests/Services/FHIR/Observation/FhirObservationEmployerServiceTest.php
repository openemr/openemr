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
use OpenEMR\Services\EmployerService;
use OpenEMR\Services\FHIR\Observation\FhirObservationEmployerService;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use OpenEMR\Services\ListService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Integration test for FhirObservationPatientService
 *
 * Tests the complete flow of:
 * 1. Inserting patient data with occupation and sexual orientation
 * 2. Retrieving and converting them via FhirObservationPatientService
 * 3. Validating against US Core profiles for occupation and sexual orientation observations
 */
class FhirObservationEmployerServiceTest extends TestCase
{
    use SystemLoggerAwareTrait;

    private PatientService $patientService;
    private UserService $userService;
    private EmployerService $employerService;

    private FhirObservationEmployerService $fhirService;

    private array $testPatientData;
    private array $testUserData;
    private array $testEmployerData;

    private array $backupSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patientService = new PatientService();
        $this->userService = new UserService();
        $this->employerService = new EmployerService();
        $this->fhirService = new FhirObservationEmployerService();
        $this->ensureRequiredListOptions();
        $this->createTestPatientAndUser();
        $this->createTestEmployerData();
        $this->backupSession = $_SESSION;
        $_SESSION['authUserID'] = $this->testUserData['id'];
    }

    protected function tearDown(): void
    {
        $this->cleanupEmployerData();
        $this->cleanupTestPatientAndUser();
        $_SESSION = $this->backupSession;
        parent::tearDown();
    }

    private function createTestEmployerData(): array
    {
        $employerId = $this->employerService->updateEmployerData($this->testPatientData['pid'], [
            'pid' => $this->testPatientData['pid'],
            'name' => 'Test Employer Inc.',
            'occupation' => 'test_occupation', // Assuming this code exists in list_options
            'industry' => 'test_industry',     // Assuming this code exists in list_options
            'date_start' => '2020-01-01',
            'date_end' => null,
            'last_updated_time' => date('Y-m-d H:i:s')
        ], true);
        $this->testEmployerData = $this->employerService->getMostRecentEmployerData($this->testPatientData['pid']);
        return $this->testEmployerData;
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

    private function cleanupEmployerData(): void
    {
        if (!empty($this->testEmployerData['uuid'])) {
            try {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM employer_data WHERE uuid = ?",
                    [$this->testEmployerData['uuid']]
                );
            } catch (\Exception $e) {
                $this->getSystemLogger()->errorLogCaller("Failed to cleanup test employer data: ", ['message' => $e->getMessage()]);
            }
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
     * Test occupation observation generation and validation
     * Validates against: https://hl7.org/fhir/us/core/STU8/StructureDefinition-us-core-observation-occupation.html
     */
    public function testOccupationObservationValidation(): void
    {
        // AI GENERATED CODE - START: Test occupation observation
        // Retrieve observations using the FHIR service
        $searchResult = $this->fhirService->getAll(['patient' => $this->testPatientData['uuid']]);
        $this->assertTrue($searchResult->isValid(), "Search result should be valid");

        $observations = $searchResult->getData();
        $this->assertNotEmpty($observations, "Should have returned occupation observations");

        // Find the occupation observation
        $occupationObs = null;
        foreach ($observations as $obs) {
            if ($obs instanceof FHIRObservation) {
                $coding = $obs->getCode()->getCoding();
                if (!empty($coding) && $coding[0]->getCode() === '11341-5') {
                    $occupationObs = $obs;
                    break;
                }
            }
        }

        $this->assertNotNull($occupationObs, "Should have found occupation observation");

        // Validate US Core Occupation profile compliance
        $profiles = $occupationObs->getMeta()->getProfile();
        $hasOccupationProfile = false;
        foreach ($profiles as $profile) {
            if (str_contains((string) $profile->getValue(), 'us-core-observation-occupation')) {
                $hasOccupationProfile = true;
                break;
            }
        }
        $this->assertTrue($hasOccupationProfile, "Observation should have US Core occupation profile");

        // Validate required elements for occupation profile
        $this->assertNotEmpty($occupationObs->getStatus(), "Status is required");
        $this->assertNotEmpty($occupationObs->getCode(), "Code is required");
        $this->assertNotEmpty($occupationObs->getSubject(), "Subject is required");
        $this->assertNotEmpty($occupationObs->getEffectiveDateTime(), "Effective date is required");

        // Validate code is correct LOINC for occupation history
        $coding = $occupationObs->getCode()->getCoding()[0];
        $this->assertEquals('11341-5', $coding->getCode(), "Should use LOINC 11341-5 for occupation history");
        $this->assertEquals('http://loinc.org', $coding->getSystem(), "Should use LOINC system");

        // Validate category is social-history
        $categories = $occupationObs->getCategory();
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
        $valueCC = $occupationObs->getValueCodeableConcept();
        $this->assertNotEmpty($valueCC, "Should have valueCodeableConcept for occupation");
        $this->assertNotEmpty($valueCC->getCoding(), "ValueCodeableConcept should have coding");

        // Validate components for occupation and industry
        $components = $occupationObs->getComponent();
        $this->assertNotEmpty($components, "Should have components for occupation and industry");

        // Look for industry component
        $industryComponentFound = false;
        foreach ($components as $component) {
            $componentCode = $component->getCode();
            if (!empty($componentCode->getCoding())) {
                $coding = $componentCode->getCoding()[0];
                if ($coding->getCode() === '86188-0') {
                    $industryComponentFound = true;
                    // Validate industry component has value
                    $compValueCC = $component->getValueCodeableConcept();
                    $this->assertNotEmpty($compValueCC, "Industry component should have valueCodeableConcept");
                    break;
                }
            }
        }
        $this->assertTrue($industryComponentFound, "Should have industry component with LOINC 86188-0");
        // AI GENERATED CODE - END
    }


    /**
     * Test parseOpenEMRRecord method directly
     */
    public function testParseOpenEMRRecordMethod(): void
    {
        // AI GENERATED CODE - START: Test parseOpenEMRRecord method directly

        // Create test data record for occupation with components
        $occupationRecord = [
            'uuid' => Uuid::uuid4()->toString(),
            'code' => FhirObservationEmployerService::COLUMN_MAPPINGS['11341-5']['fullcode'],
            'description' => FhirObservationEmployerService::COLUMN_MAPPINGS['11341-5']['description'],
            'ob_type' => FhirObservationEmployerService::CATEGORY_SOCIAL_HISTORY,
            'ob_status' => 'final',
            'puuid' => $this->testPatientData['uuid'],
            'user_uuid' => $this->testUserData['uuid'] ?? 'test-user-uuid',
            'date' => date('Y-m-d H:i:s'),
            'last_updated_time' => date('Y-m-d H:i:s'),
            'profiles' => [FhirObservationEmployerService::USCDI_PROFILE_OCCUPATION_URI],
            'value' => 'SNOMED-CT:223366009',
            'value_code_description' => 'Test Software Developer',
            'components' => [
                [
                    'code' => 'LOINC:86188-0',
                    'description' => 'History of Occupation industry',
                    'value' => 'NAICS:541511',
                    'value_code_description' => 'Test Software Development Industry'
                ]
            ]
        ];

        // Test parseOpenEMRRecord method for occupation
        $occupationObs = $this->fhirService->parseOpenEMRRecord($occupationRecord);
        $this->assertInstanceOf(FHIRObservation::class, $occupationObs, "Should return FHIRObservation instance for occupation");

        // Validate occupation observation
        $this->assertEquals('11341-5', $occupationObs->getCode()->getCoding()[0]->getCode(), "Occupation code should be correct");
        $this->assertNotEmpty($occupationObs->getComponent(), "Should have components");

        // Validate industry component
        $components = $occupationObs->getComponent();
        $industryComponent = $components[0];
        $this->assertEquals('86188-0', $industryComponent->getCode()->getCoding()[0]->getCode(), "Industry component code should be correct");
        $this->assertNotEmpty($industryComponent->getValueCodeableConcept(), "Industry component should have valueCodeableConcept");
        // AI GENERATED CODE - END
    }
}
