<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR MedicationRequest Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirMedicationRequestServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRMedicationRequest $fhirMedicationRequestFixture;
    private FhirMedicationRequestService $fhirMedicationRequestService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirMedicationRequestFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirMedicationRequestFixture = new FHIRMedicationRequest($fixture);

        $this->fhirMedicationRequestService = new FhirMedicationRequestService();
        $this->fhirMedicationRequestService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeMedicationRequestFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirMedicationRequestFixture->setId(null);
        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusPatientUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirMedicationRequestFixture->setId(null);
        $payload = $this->fhirMedicationRequestFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusPatientUuid];
        $fixture = new FHIRMedicationRequest($payload);

        $processingResult = $this->fhirMedicationRequestService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testInsertMissingDrugReturnsValidationError(): void
    {
        $this->fhirMedicationRequestFixture->setId(null);
        $this->fhirMedicationRequestFixture->setMedicationCodeableConcept(null);

        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertFalse($processingResult->isValid());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirMedicationRequestFixture->setId(null);
        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $fhirId = $processingResult->getData()[0]['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirMedicationRequestFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['note'] = [['text' => 'test-fixture updated note']];
        $updated = new FHIRMedicationRequest($payload);

        $actualResult = $this->fhirMedicationRequestService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
        // FhirServiceBase::update re-parses the updated row through parseOpenEMRRecord,
        // so getData()[0] is the FHIRMedicationRequest object (not the column array).
        // populateNote stores the note as a raw string on FHIRAnnotation.text (it doesn't
        // wrap in FHIRMarkdown), so we compare the string directly.
        $updatedResource = $actualResult->getData()[0];
        $notes = $updatedResource->getNote();
        $this->assertCount(1, $notes);
        $this->assertSame('test-fixture updated note', $notes[0]->getText());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $actualResult = $this->fhirMedicationRequestService->update('bad-uuid', $this->fhirMedicationRequestFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
