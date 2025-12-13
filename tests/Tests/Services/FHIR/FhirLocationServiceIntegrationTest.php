<?php

/**
 * FhirLocationServiceIntegrationTest - Integration tests for FhirLocationService export operation
 *
 * These tests use real database interactions to verify that the export operation
 * correctly filters Location resources for Group exports, ensuring only patient
 * locations for patients in the group are included while excluding others.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    [Your Name]
 * @copyright Copyright (c) 2024 [Your Name]
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Integration\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportMemoryStreamWriter;
use OpenEMR\Services\FHIR\FhirLocationService;
use OpenEMR\Services\LocationService;
use OpenEMR\Services\PatientService;
use PHPUnit\Framework\TestCase;

class FhirLocationServiceIntegrationTest extends TestCase
{
    /**
     * @var FhirLocationService
     */
    private $fhirLocationService;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var array Test data UUIDs for cleanup
     */
    private $testDataUuids = [];

    /**
     * @var array Test facility IDs for cleanup
     */
    private $testFacilityIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->fhirLocationService = new FhirLocationService();
        $this->fhirLocationService->setSystemLogger(new SystemLogger(Level::Critical));
        $this->locationService = new LocationService();
    }

    protected function tearDown(): void
    {
        // AI-generated cleanup code - START
        // Clean up test data
        $this->cleanupTestData();
        parent::tearDown();
        // AI-generated cleanup code - END
    }

    /**
     * Integration test: Group export should only include patient locations for patients in the group
     * and always include facility locations, excluding patient locations for patients not in the group
     */
    public function testGroupExportIncludesOnlyGroupPatientLocationsIntegration(): void
    {
        $this->markTestSkipped("Skipping for now until we can ensure test isolation and cleanup.");
        // Create test patients
        // TODO: should we use the openemr FixtureManager to do this?
        $groupPatient1Uuid = $this->createTestPatient('John', 'GroupMember1');
        $groupPatient2Uuid = $this->createTestPatient('Jane', 'GroupMember2');
        $nonGroupPatientUuid = $this->createTestPatient('Bob', 'NonGroupMember');

        // Create test facility
        $facilityId = $this->createTestFacility('Test Clinic', 'Main testing facility');

        // Create patient locations
        $groupPatient1LocationUuid = $this->createTestPatientLocation($groupPatient1Uuid, 'Group Patient 1 Home');
        $groupPatient2LocationUuid = $this->createTestPatientLocation($groupPatient2Uuid, 'Group Patient 2 Home');
        $nonGroupPatientLocationUuid = $this->createTestPatientLocation($nonGroupPatientUuid, 'Non-Group Patient Home');

        // AI-generated export job setup - START
        // Create export job for group operation
        $exportJob = new ExportJob();
        $exportJob->setExportType(ExportJob::EXPORT_OPERATION_GROUP);
        $exportJob->setPatientUuidsToExport([$groupPatient1Uuid, $groupPatient2Uuid]);
        $exportJob->setResourceIncludeTime(new \DateTime('2023-01-01')); // Include all test data

        // Create export stream writer with sufficient shutdown time
        $shutdownTime = new \DateTime();
        $shutdownTime->add(new \DateInterval('PT5M')); // 5 minutes should be plenty
        $streamWriter = new ExportMemoryStreamWriter($shutdownTime);
        // AI-generated export job setup - END

        // Execute the export
        $this->fhirLocationService->export($streamWriter, $exportJob);

        // Get the exported data
        $exportedContent = $streamWriter->getContents();
        $exportedLines = array_filter(explode("\n", $exportedContent));

        // Verify we have the expected number of resources
        $this->assertGreaterThanOrEqual(
            3,
            count($exportedLines),
            'Should export at least 3 locations: 1 facility + 2 group patient locations'
        );

        // Parse each exported resource and collect UUIDs
        $exportedUuids = [];
        $exportedTypes = [];
        foreach ($exportedLines as $line) {
            $resourceData = json_decode($line, true);
            $this->assertNotNull($resourceData, 'Each exported line should be valid JSON');

            $exportedUuids[] = $resourceData['id'];

            // Determine type based on name or other identifying characteristics
            if (str_contains((string) $resourceData['name'], 'Test Clinic')) {
                $exportedTypes[] = 'facility';
            } elseif (str_contains((string) $resourceData['name'], 'Group Patient')) {
                $exportedTypes[] = 'patient';
            }
        }

        // Verify facility location is included
        $facilityLocationUuid = $this->getFacilityLocationUuid($facilityId);
        if ($facilityLocationUuid) {
            $this->assertContains(
                $facilityLocationUuid,
                $exportedUuids,
                'Facility location should be included in group export'
            );
        }

        // Verify group patient locations are included
        $this->assertContains(
            $groupPatient1LocationUuid,
            $exportedUuids,
            'Group patient 1 location should be included in export'
        );
        $this->assertContains(
            $groupPatient2LocationUuid,
            $exportedUuids,
            'Group patient 2 location should be included in export'
        );

        // Most important: Verify non-group patient location is NOT included
        $this->assertNotContains(
            $nonGroupPatientLocationUuid,
            $exportedUuids,
            'Non-group patient location should NOT be included in group export'
        );

        // Verify we have at least one facility type exported
        $this->assertContains(
            'facility',
            $exportedTypes,
            'At least one facility location should be exported'
        );

        // Verify that patient locations are only for group members
        $this->assertContains(
            'patient',
            $exportedTypes,
            'At least one patient location should be exported'
        );
    }

    /**
     * Integration test: System export should include all locations regardless of patient membership
     */
    public function testSystemExportIncludesAllLocationsIntegration(): void
    {
        $this->markTestSkipped("Skipping for now until we can ensure test isolation and cleanup.");
        // Create test patients
        $patient1Uuid = $this->createTestPatient('Alice', 'SystemTest1');
        $patient2Uuid = $this->createTestPatient('Charlie', 'SystemTest2');

        // Create test facility
        $facilityId = $this->createTestFacility('System Test Clinic', 'System testing facility');

        // Create patient locations
        $patient1LocationUuid = $this->createTestPatientLocation($patient1Uuid, 'Patient 1 Home System');
        $patient2LocationUuid = $this->createTestPatientLocation($patient2Uuid, 'Patient 2 Home System');

        // Create export job for system operation
        $exportJob = new ExportJob();
        $exportJob->setExportType(ExportJob::EXPORT_OPERATION_SYSTEM);
        $exportJob->setResourceIncludeTime(new \DateTime('2023-01-01'));

        $shutdownTime = new \DateTime();
        $shutdownTime->add(new \DateInterval('PT5M'));
        $streamWriter = new ExportMemoryStreamWriter($shutdownTime);

        // Execute the export
        $this->fhirLocationService->export($streamWriter, $exportJob);

        // Get the exported data
        $exportedContent = $streamWriter->getContents();
        $exportedLines = array_filter(explode("\n", $exportedContent));

        // For system export, should include more locations (all in the system)
        $this->assertGreaterThanOrEqual(
            2,
            count($exportedLines),
            'System export should include multiple locations'
        );

        // Parse exported resources
        $exportedUuids = [];
        foreach ($exportedLines as $line) {
            $resourceData = json_decode($line, true);
            $this->assertNotNull($resourceData, 'Each exported line should be valid JSON');
            $exportedUuids[] = $resourceData['id'];
        }

        // For system export, both patient locations should be included
        $this->assertContains(
            $patient1LocationUuid,
            $exportedUuids,
            'Patient 1 location should be included in system export'
        );
        $this->assertContains(
            $patient2LocationUuid,
            $exportedUuids,
            'Patient 2 location should be included in system export'
        );
    }

    /**
     * Integration test: Group export with no patients should export only facility locations
     */
    public function testGroupExportWithNoPatientsShouldReturnEarlyIntegration(): void
    {
        $this->markTestSkipped("Skipping for now until we can ensure test isolation and cleanup.");
        // Create a facility to ensure something exists
        $facilityId = $this->createTestFacility('Empty Group Test Clinic', 'Testing empty group');

        // Create export job with no patients
        $exportJob = new ExportJob();
        $exportJob->setExportType(ExportJob::EXPORT_OPERATION_GROUP);
        $exportJob->setPatientUuidsToExport([]); // Empty patient list
        $exportJob->setResourceIncludeTime(new \DateTime('2023-01-01'));

        $shutdownTime = new \DateTime();
        $shutdownTime->add(new \DateInterval('PT5M'));
        $streamWriter = new ExportMemoryStreamWriter($shutdownTime);

        // Execute the export
        $this->fhirLocationService->export($streamWriter, $exportJob);

        // Should return early with no records written
        $exportedContent = $streamWriter->getContents();
        $this->assertEmpty(
            $exportedContent,
            'Group export with no patients should export no resources (returns early)'
        );

        $recordsWritten = $streamWriter->getRecordsWritten();
        $this->assertEquals(
            0,
            $recordsWritten,
            'Should have written 0 records for empty patient group'
        );
    }

    /**
     * Create a test patient and return UUID
     */
    private function createTestPatient(string $firstName, string $lastName): string
    {
        // AI-generated patient creation - START
        $patientData = [
            'fname' => $firstName,
            'lname' => $lastName,
            'DOB' => '1990-01-01',
            'sex' => 'Male',
            'street' => '123 Test St',
            'city' => 'TestCity',
            'state' => 'TS',
            'postal_code' => '12345',
            'phone_home' => '555-0123'
        ];
        $patientService = new PatientService();
        $result = $patientService->insert($patientData);
        if (!$result->isValid()) {
            throw new \RuntimeException("Failed to create test patient: " . json_encode($result->getValidationMessages()));
        } else {
            $this->testDataUuids[] = ['table' => 'patient_data', 'uuid' => UuidRegistry::uuidToBytes($result->getData()['uuid'])];
        }

        // Insert patient
        $sql = "INSERT INTO patient_data (fname, lname, DOB, sex, street, city, state, postal_code, phone_home, uuid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $uuid = UuidRegistry::getRegistryForTable('patient_data')->createUuid();
        $params = array_merge(array_values($patientData), [$uuid]);

        QueryUtils::sqlStatementThrowException($sql, $params);

        $this->testDataUuids[] = ['table' => 'patient_data', 'uuid' => $uuid];

        return UuidRegistry::uuidToString($uuid);
        // AI-generated patient creation - END
    }

    /**
     * Create a test facility and return facility ID
     */
    private function createTestFacility(string $name, string $description): int
    {
        $facilityData = [
            'name' => $name,
            'phone' => '555-0100',
            'street' => '456 Facility St',
            'city' => 'TestCity',
            'state' => 'TS',
            'postal_code' => '12345',
            'country_code' => 'US',
            'facility_npi' => '1234567890',
            'website' => 'https://test.facility.com',
            'email' => 'test@facility.com'
        ];

        $sql = "INSERT INTO facility (name, phone, street, city, state, postal_code, country_code, facility_npi, website, email, uuid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $uuid = UuidRegistry::getRegistryForTable('facility')->createUuid();
        $params = array_merge(array_values($facilityData), [$uuid]);

        $facilityId = QueryUtils::sqlInsert($sql, $params);

        $this->testFacilityIds[] = $facilityId;
        $this->testDataUuids[] = ['table' => 'facility', 'uuid' => $uuid];

        return $facilityId;
    }

    /**
     * Create a test patient location and return UUID
     */
    private function createTestPatientLocation(string $patientUuid, string $locationName): string
    {
        // Convert string UUID back to binary for database storage
        $patientUuidBinary = UuidRegistry::uuidToBytes($patientUuid);

        // AI-generated location creation - START
        $locationData = [
            'name' => $locationName,
            'description' => 'Test patient location',
            'type' => LocationService::TYPE_PATIENT,
            'table_uuid' => $patientUuidBinary, // Links to patient
            'street' => '789 Patient St',
            'city' => 'TestCity',
            'state' => 'TS',
            'postal_code' => '12345',
            'phone' => '555-0200',
            'email' => 'patient@test.com',
            'pid' => null,
        ];

        $sql = "INSERT INTO locations (name, description, type, table_uuid, street, city, state, postal_code, phone, email, uuid, pid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $uuid = UuidRegistry::getRegistryForTable('locations')->createUuid();
        $params = array_merge(array_values($locationData), [$uuid]);

        QueryUtils::sqlStatementThrowException($sql, $params);

        $this->testDataUuids[] = ['table' => 'locations', 'uuid' => $uuid];

        return UuidRegistry::uuidToString($uuid);
        // AI-generated location creation - END
    }

    /**
     * Get facility location UUID by facility ID
     */
    private function getFacilityLocationUuid(int $facilityId): ?string
    {
        $sql = "SELECT uuid FROM facility WHERE id = ?";
        $result = sqlQuery($sql, [$facilityId]);

        if ($result && !empty($result['uuid'])) {
            return UuidRegistry::uuidToString($result['uuid']);
        }

        return null;
    }

    /**
     * Clean up test data created during tests
     */
    private function cleanupTestData(): void
    {
        // AI-generated cleanup - START
        foreach ($this->testDataUuids as $data) {
            $table = $data['table'];
            $uuid = $data['uuid'];

            try {
                $sql = "DELETE FROM `{$table}` WHERE uuid = ?";
                QueryUtils::sqlStatementThrowException($sql, [$uuid]);
            } catch (\Exception $e) {
                // Log error but don't fail test cleanup
                error_log("Failed to cleanup test data from {$table}: " . $e->getMessage());
            }
        }

        // Clean up any additional facility references
        foreach ($this->testFacilityIds as $facilityId) {
            try {
                $sql = "DELETE FROM facility WHERE id = ?";
                QueryUtils::sqlStatementThrowException($sql, [$facilityId]);
            } catch (\Exception $e) {
                error_log("Failed to cleanup facility {$facilityId}: " . $e->getMessage());
            }
        }
        // AI-generated cleanup - END
    }

    /**
     * Helper method to verify FHIR Location resource structure
     */
    private function assertValidFhirLocationResource(array $resourceData): void
    {
        $this->assertArrayHasKey('resourceType', $resourceData);
        $this->assertEquals('Location', $resourceData['resourceType']);

        $this->assertArrayHasKey('id', $resourceData);
        $this->assertNotEmpty($resourceData['id']);

        $this->assertArrayHasKey('status', $resourceData);
        $this->assertEquals('active', $resourceData['status']);

        if (isset($resourceData['name'])) {
            $this->assertNotEmpty($resourceData['name']);
        }
    }

    /**
     * Helper method to parse and validate all exported FHIR resources
     */
    private function parseAndValidateExportedResources(string $exportedContent): array
    {
        $exportedLines = array_filter(explode("\n", $exportedContent));
        $resources = [];

        foreach ($exportedLines as $line) {
            $resourceData = json_decode($line, true);
            $this->assertNotNull($resourceData, 'Each exported line should be valid JSON');
            $this->assertValidFhirLocationResource($resourceData);
            $resources[] = $resourceData;
        }

        return $resources;
    }
}
