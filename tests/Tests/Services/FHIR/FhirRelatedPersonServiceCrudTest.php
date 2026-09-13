<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRRelatedPerson;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirRelatedPersonService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR RelatedPerson Service CRUD Tests
 *
 * RelatedPerson writes touch multiple tables atomically: person, contact (for the
 * related person AND the patient owner if missing), contact_telecom, contact_address,
 * addresses, and contact_relation. Cleanup in tearDown walks the same set.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirRelatedPersonServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRRelatedPerson $fhirRelatedPersonFixture;
    private FhirRelatedPersonService $fhirRelatedPersonService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patientFixture = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $fixture = (array) $this->fixtureManager->getSingleFhirRelatedPersonFixture();
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirRelatedPersonFixture = new FHIRRelatedPerson($fixture);

        $this->fhirRelatedPersonService = new FhirRelatedPersonService();
        $this->fhirRelatedPersonService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeRelatedPersonFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirRelatedPersonFixture->setId(new FHIRId());
        $result = $this->fhirRelatedPersonService->insert($this->fhirRelatedPersonFixture);
        $this->assertTrue(
            $result->isValid(),
            'Insert should succeed: ' . json_encode($result->getValidationMessages())
        );

        $data = $this->firstDataRow($result);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvablePatient(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirRelatedPersonFixture->setId(new FHIRId());
        $payload = $this->fhirRelatedPersonFixture->jsonSerialize();
        $payload['patient'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRRelatedPerson($payload);

        $result = $this->fhirRelatedPersonService->insert($fixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirRelatedPersonFixture->setId(new FHIRId());
        $insertResult = $this->fhirRelatedPersonService->insert($this->fhirRelatedPersonFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            'Insert should succeed: ' . json_encode($insertResult->getValidationMessages())
        );
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirRelatedPersonFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['name'] = [[
            'use' => 'official',
            'family' => 'test-fixture-RelatedLastUpdated',
            'given' => ['test-fixture-RelatedFirstUpdated'],
        ]];
        $updated = new FHIRRelatedPerson($payload);

        $result = $this->fhirRelatedPersonService->update($fhirId, $updated);
        $this->assertTrue(
            $result->isValid(),
            'Update should succeed: ' . json_encode($result->getValidationMessages())
        );
        $this->assertNotEmpty($result->getData());

        // The payload above renames the person, so assert the rename is what comes back.
        $this->assertSame(
            'test-fixture-RelatedLastUpdated',
            self::digString($this->readBack($result), ['name', 0, 'family']),
            'Update should return the new family name'
        );
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirRelatedPersonService->update('bad-uuid', $this->fhirRelatedPersonFixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
    }

    #[Test]
    public function testUpdateWithoutPatientReturnsValidationError(): void
    {
        $this->fhirRelatedPersonFixture->setId(new FHIRId());
        $insertResult = $this->fhirRelatedPersonService->insert($this->fhirRelatedPersonFixture);
        $this->assertTrue($insertResult->isValid());
        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        // Strip the patient reference — the new scoping enforcement requires it
        // so we know which patient's relationship row to update.
        $payload = $this->fhirRelatedPersonFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        unset($payload['patient']);
        $updated = new FHIRRelatedPerson($payload);

        $result = $this->fhirRelatedPersonService->update($fhirId, $updated);
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('patient', $messages);
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
