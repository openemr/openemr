<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment;
use OpenEMR\Services\FHIR\FhirAppointmentService;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Appointment Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirAppointmentServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FacilityFixtureManager $facilityFixtureManager;
    private FHIRAppointment $fhirAppointmentFixture;
    private FhirAppointmentService $fhirAppointmentService;
    private string $patientUuid;
    private string $facilityUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->facilityFixtureManager = new FacilityFixtureManager();

        // Install a patient fixture so we have a valid puuid for the appointment
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        // look up the installed patient to get the uuid
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Install a facility — FhirAppointmentService now requires an explicit
        // Location reference rather than silently defaulting to the first row.
        $this->facilityFixtureManager->installFacilityFixtures();
        $facilityRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM facility ORDER BY id DESC LIMIT 1",
            []
        );
        $this->assertIsArray($facilityRow);
        $this->facilityUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($facilityRow['uuid']);

        // Load FHIR fixture and set patient + facility references
        $fixture = (array) $this->fixtureManager->getSingleFhirAppointmentFixture();
        $fixture['participant'] = [
            [
                'actor' => [
                    'reference' => 'Patient/' . $this->patientUuid
                ],
                'status' => 'accepted'
            ],
            [
                'actor' => [
                    'reference' => 'Location/' . $this->facilityUuid
                ],
                'status' => 'accepted'
            ]
        ];
        $this->fhirAppointmentFixture = new FHIRAppointment($fixture);

        $this->fhirAppointmentService = new FhirAppointmentService();
        $this->fhirAppointmentService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        // Clean up any appointment fixtures we created
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM openemr_postcalendar_events WHERE pc_hometext LIKE 'test-fixture%'"
        );
        $this->fixtureManager->removePatientFixtures();
        $this->facilityFixtureManager->removeInstalledFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $processingResult = $this->fhirAppointmentService->insert($this->fhirAppointmentFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertNotEmpty($dataResult);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Create a minimal appointment missing required fields (no participant, no start date)
        $badFixture = new FHIRAppointment([
            'resourceType' => 'Appointment',
            'status' => 'booked'
        ]);
        $processingResult = $this->fhirAppointmentService->insert($badFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    /**
     * Reads the first row of a ProcessingResult, asserting the shape as it goes so a
     * failed insert surfaces as a test failure rather than a type error downstream.
     *
     * @return array<mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);

        return $row;
    }
}
