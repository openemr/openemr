<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR MedicationRequest Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
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
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirMedicationRequestFixture();
        $fixture['subject'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirMedicationRequestFixture = new FHIRMedicationRequest($fixture);

        $this->fhirMedicationRequestService = new FhirMedicationRequestService();
        $this->fhirMedicationRequestService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeMedicationRequestFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirMedicationRequestFixture->setId(new FHIRId());
        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvableSubject(): void
    {
        $bogusPatientUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirMedicationRequestFixture->setId(new FHIRId());
        $payload = $this->fhirMedicationRequestFixture->jsonSerialize();
        $payload['subject'] = ['reference' => 'Patient/' . $bogusPatientUuid];
        $fixture = new FHIRMedicationRequest($payload);

        $processingResult = $this->fhirMedicationRequestService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testInsertMissingDrugReturnsValidationError(): void
    {
        $this->fhirMedicationRequestFixture->setId(new FHIRId());
        $this->fhirMedicationRequestFixture->setMedicationCodeableConcept(new FHIRCodeableConcept());

        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertFalse($processingResult->isValid());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirMedicationRequestFixture->setId(new FHIRId());
        $processingResult = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($processingResult)['uuid'];
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
        $updatedData = $actualResult->getData();
        $this->assertIsArray($updatedData);
        $this->assertArrayHasKey(0, $updatedData);
        $updatedResource = $updatedData[0];
        $this->assertInstanceOf(FHIRMedicationRequest::class, $updatedResource);
        $notes = $updatedResource->getNote();
        $this->assertCount(1, $notes);
        $this->assertSame('test-fixture updated note', (string) $notes[0]->getText());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $actualResult = $this->fhirMedicationRequestService->update('bad-uuid', $this->fhirMedicationRequestFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertNotSame([], $actualResult->getValidationMessages());
        $this->assertSame([], $actualResult->getData());
    }

    /**
     * `prescriptions` names its owner column `patient_id`, and BaseService::buildUpdateColumns()
     * only skips `pid` -- so without an ownership check a PUT body's subject would be written
     * straight into the row. A subject naming a different patient has to be rejected rather than
     * silently moving the prescription into that patient's chart.
     */
    #[Test]
    public function testUpdateCannotRebindPrescriptionToAnotherPatient(): void
    {
        $this->fhirMedicationRequestFixture->setId(new FHIRId());
        $insert = $this->fhirMedicationRequestService->insert($this->fhirMedicationRequestFixture);
        $this->assertTrue(
            $insert->isValid(),
            "Insert should succeed: " . json_encode($insert->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($insert)['uuid'];
        $this->assertIsString($fhirId);
        $uuidBytes = UuidRegistry::uuidToBytes($fhirId);

        $ownerPid = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM prescriptions WHERE uuid = ?",
            'patient_id',
            [$uuidBytes]
        );
        $this->assertIsNumeric($ownerPid);

        $payload = $this->fhirMedicationRequestFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['subject'] = ['reference' => 'Patient/' . $this->secondPatientUuid()];

        $result = $this->fhirMedicationRequestService->update($fhirId, new FHIRMedicationRequest($payload));
        $this->assertFalse($result->isValid(), 'A PUT whose subject is a different patient must be rejected');

        $ownerPidAfter = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM prescriptions WHERE uuid = ?",
            'patient_id',
            [$uuidBytes]
        );
        $this->assertIsNumeric($ownerPidAfter);
        $this->assertSame((int) $ownerPid, (int) $ownerPidAfter, 'The prescription must stay with its owner');
    }

    /**
     * Uuid of a second installed patient fixture, used to prove a PUT cannot move a record
     * from the patient that owns it to someone else's chart.
     */
    private function secondPatientUuid(): string
    {
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[1];
        $this->assertIsArray($patientFixture);
        $record = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($record);

        return UuidRegistry::uuidToString($record['uuid']);
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
