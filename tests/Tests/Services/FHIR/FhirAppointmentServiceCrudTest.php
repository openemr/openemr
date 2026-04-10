<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAppointment;
use OpenEMR\Services\FHIR\FhirAppointmentService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Appointment Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirAppointmentServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRAppointment $fhirAppointmentFixture;
    private FhirAppointmentService $fhirAppointmentService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid puuid for the appointment
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        // look up the installed patient to get the uuid
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirAppointmentFixture();
        $fixture['participant'] = [
            [
                'actor' => [
                    'reference' => 'Patient/' . $this->patientUuid
                ],
                'status' => 'accepted'
            ]
        ];
        $this->fhirAppointmentFixture = new FHIRAppointment($fixture);

        $this->fhirAppointmentService = new FhirAppointmentService();
        $this->fhirAppointmentService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        // Clean up any appointment fixtures we created
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM openemr_postcalendar_events WHERE pc_hometext LIKE 'test-fixture%'"
        );
    }

    #[Test]
    public function testInsert(): void
    {
        $processingResult = $this->fhirAppointmentService->insert($this->fhirAppointmentFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
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
        $this->assertEquals(0, count($processingResult->getData()));
    }
}
