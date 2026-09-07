<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirMedicationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Medication Service CRUD Tests
 *
 * Medication is master data; not patient-bound. Writes target the `drugs` table only —
 * batch (lot/expiration/manufacturer) lives on drug_inventory and is intentionally
 * not round-trip-writable through this endpoint.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirMedicationServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRMedication $fhirMedicationFixture;
    private FhirMedicationService $fhirMedicationService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $fixture = (array) $this->fixtureManager->getSingleFhirMedicationFixture();
        $this->fhirMedicationFixture = new FHIRMedication($fixture);

        $this->fhirMedicationService = new FhirMedicationService();
        $this->fhirMedicationService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeMedicationFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirMedicationFixture->setId(new FHIRId());
        $processingResult = $this->fhirMedicationService->insert($this->fhirMedicationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
        $this->assertArrayHasKey('drug_id', $dataResult);
        $this->assertGreaterThan(0, $dataResult['drug_id']);
    }

    #[Test]
    public function testInsertMissingCodeReturnsValidationError(): void
    {
        // No code = no name/drug_code derivable, and `name` is NOT NULL with no useful default.
        $payload = $this->fhirMedicationFixture->jsonSerialize();
        unset($payload['id'], $payload['code']);
        $fixture = new FHIRMedication($payload);

        $processingResult = $this->fhirMedicationService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirMedicationFixture->setId(new FHIRId());
        $insertResult = $this->fhirMedicationService->insert($this->fhirMedicationFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirMedicationFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['code']['coding'][0]['display'] = 'test-fixture-medication-001-updated';
        $payload['code']['text'] = 'test-fixture-medication-001-updated';
        $updated = new FHIRMedication($payload);

        $actualResult = $this->fhirMedicationService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $actualResult = $this->fhirMedicationService->update('bad-uuid', $this->fhirMedicationFixture);
        $this->assertFalse($actualResult->isValid());
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
