<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR AllergyIntolerance Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirAllergyIntoleranceServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRAllergyIntolerance $fhirAllergyIntoleranceFixture;
    private FhirAllergyIntoleranceService $fhirAllergyIntoleranceService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid puuid for the allergy
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

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirAllergyIntoleranceFixture();
        $fixture['patient'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirAllergyIntoleranceFixture = new FHIRAllergyIntolerance($fixture);

        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService();
        $this->fhirAllergyIntoleranceService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        // Clean up any allergy fixtures we created
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'allergy' AND title LIKE 'test-fixture%'");
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'allergy' AND comments LIKE 'test-fixture%'");
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirAllergyIntoleranceFixture->setId(new FHIRId());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference to trigger validation error
        $this->fhirAllergyIntoleranceFixture->setPatient(new FHIRReference());
        // Also clear the code text so title is empty
        $this->fhirAllergyIntoleranceFixture->setCode(new FHIRCodeableConcept());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirAllergyIntoleranceFixture->setId(new FHIRId());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the allergy - change the verification status. The fixture is 'confirmed', so
        // asserting 'unconfirmed' comes back proves the body was actually applied; changing only
        // the id (as this did) passes just as well when update() ignores every mutable field.
        $payload = $this->fhirAllergyIntoleranceFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['verificationStatus'] = [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                    'code' => 'unconfirmed',
                    'display' => 'Unconfirmed',
                ],
            ],
        ];
        $updated = new FHIRAllergyIntolerance($payload);

        $actualResult = $this->fhirAllergyIntoleranceService->update($fhirId, $updated);
        $this->assertTrue($actualResult->isValid(), "Update should succeed: " . json_encode($actualResult->getValidationMessages()));
        $this->assertNotEmpty($actualResult->getData());

        $verification = self::digString(
            $this->readBack($actualResult),
            ['verificationStatus', 'coding', 0, 'code']
        );
        $this->assertSame('unconfirmed', $verification, 'Update should return the new verification status');
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
    public function testRecorderGoesThroughTheAttributionPolicy(): void
    {
        // recorder is an attribution claim -- it says which clinician recorded the allergy --
        // so it is resolved and authorized through PractitionerAttributionPolicy rather than
        // looked up straight to a username. Before that, a recorder naming a practitioner who
        // did not exist was silently dropped and the write reported success.
        //
        // An unresolvable reference is the cheap way to prove the policy is in the path at all:
        // the direct lookup it replaced ignored one, the policy rejects it by name.
        $payload = $this->fhirAllergyIntoleranceFixture->jsonSerialize();
        $payload['recorder'] = ['reference' => 'Practitioner/00000000-0000-4000-8000-000000000000'];
        $resource = new FHIRAllergyIntolerance($payload);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AllergyIntolerance.recorder');
        $this->fhirAllergyIntoleranceService->insert($resource);
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirAllergyIntoleranceService->update('bad-uuid', $this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertNotSame([], $actualResult->getValidationMessages());
        $this->assertSame([], $actualResult->getData());
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
