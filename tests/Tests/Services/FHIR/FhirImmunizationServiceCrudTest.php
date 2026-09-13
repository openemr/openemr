<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Immunization Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirImmunizationServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRImmunization $fhirImmunizationFixture;
    private FhirImmunizationService $fhirImmunizationService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid patient_id for the immunization
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
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirImmunizationFixture();
        $fixture['patient'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirImmunizationFixture = new FHIRImmunization($fixture);

        $this->fhirImmunizationService = new FhirImmunizationService();
        $this->fhirImmunizationService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        // Clean up any immunization fixtures we created
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM immunizations WHERE note LIKE 'test-fixture%'"
        );
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirImmunizationFixture->setId(new FHIRId());
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference to trigger validation error
        $this->fhirImmunizationFixture->setPatient(new FHIRReference());
        // Also clear the vaccine code so cvx_code is empty
        $this->fhirImmunizationFixture->setVaccineCode(new FHIRCodeableConcept());
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirImmunizationFixture->setId(new FHIRId());
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the immunization - change the status, then change it back. Both directions
        // matter: status maps onto two columns (completion_status and added_erroneously) and
        // parseOpenEMRRecord() reads added_erroneously first, so a mapping that sets only the
        // column its branch is named for leaves the other stale. That made the second leg here
        // -- entered-in-error back to completed -- return 200 and read back unchanged.
        $this->fhirImmunizationFixture->setId(self::fhirId($fhirId));

        $erroneous = $this->withStatus($this->fhirImmunizationFixture, 'entered-in-error');
        $actualResult = $this->fhirImmunizationService->update($fhirId, $erroneous);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
        $this->assertSame(
            'entered-in-error',
            self::digString($this->readBack($actualResult), ['status']),
            'Update should return the new status'
        );

        $completed = $this->withStatus($this->fhirImmunizationFixture, 'completed');
        $revertResult = $this->fhirImmunizationService->update($fhirId, $completed);
        $this->assertTrue(
            $revertResult->isValid(),
            "Revert should succeed: " . json_encode($revertResult->getValidationMessages())
        );
        $this->assertSame(
            'completed',
            self::digString($this->readBack($revertResult), ['status']),
            'Reverting entered-in-error to completed should be visible on read-back'
        );
    }

    /**
     * Copies the fixture with a different Immunization.status.
     */
    private function withStatus(FHIRImmunization $source, string $status): FHIRImmunization
    {
        $payload = $source->jsonSerialize();
        $payload['status'] = $status;

        return new FHIRImmunization($payload);
    }

    /**
     * The FHIR resource update() returns, as a plain array.
     *
     * Asserting on the serialized form rather than the getters keeps these checks independent
     * of whether a field comes back as a scalar or a wrapped FHIR primitive.
     *
     * @return array<mixed>
     */
    private function readBack(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $decoded = json_decode((string) json_encode($data[0]), true);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    /**
     * Reads a nested string out of a decoded FHIR resource, or null when the path is absent.
     *
     * Chained offset reads on a json_decode() result are all `mixed` at level 10; walking the
     * path with a narrowing check keeps the assertions readable without casting.
     *
     * @param list<string|int> $path
     */
    private static function digString(mixed $source, array $path): ?string
    {
        $cursor = $source;
        foreach ($path as $key) {
            if (!is_array($cursor) || !array_key_exists($key, $cursor)) {
                return null;
            }
            $cursor = $cursor[$key];
        }

        return is_string($cursor) ? $cursor : null;
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirImmunizationService->update(
            'bad-uuid',
            $this->fhirImmunizationFixture
        );
        $this->assertFalse($actualResult->isValid());
        $this->assertNotSame([], $actualResult->getValidationMessages());
        $this->assertSame([], $actualResult->getData());
    }

    /**
     * `immunizations` names its owner column `patient_id`, and BaseService::buildUpdateColumns()
     * only skips `pid` -- so without an ownership check a PUT body's patient reference would be
     * written straight into the row, moving the vaccination into another patient's chart.
     */
    #[Test]
    public function testUpdateCannotRebindImmunizationToAnotherPatient(): void
    {
        $this->fhirImmunizationFixture->setId(new FHIRId());
        $insert = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertTrue(
            $insert->isValid(),
            "Insert should succeed: " . json_encode($insert->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($insert)['uuid'];
        $this->assertIsString($fhirId);
        $uuidBytes = UuidRegistry::uuidToBytes($fhirId);

        $ownerPid = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM immunizations WHERE uuid = ?",
            'patient_id',
            [$uuidBytes]
        );
        $this->assertIsNumeric($ownerPid);

        $payload = $this->fhirImmunizationFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['patient'] = ['reference' => 'Patient/' . $this->secondPatientUuid()];

        $result = $this->fhirImmunizationService->update($fhirId, new FHIRImmunization($payload));
        $this->assertFalse($result->isValid(), 'A PUT whose patient is a different patient must be rejected');

        $ownerPidAfter = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM immunizations WHERE uuid = ?",
            'patient_id',
            [$uuidBytes]
        );
        $this->assertIsNumeric($ownerPidAfter);
        $this->assertSame((int) $ownerPid, (int) $ownerPidAfter, 'The immunization must stay with its owner');
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

    private static function fhirId(string $value): FHIRId
    {
        $id = new FHIRId();
        $id->setValue($value);

        return $id;
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
