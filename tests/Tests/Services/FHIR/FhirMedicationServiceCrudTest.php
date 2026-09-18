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
        $code = $payload['code'] ?? [];
        $this->assertIsArray($code);
        $coding = $code['coding'] ?? [];
        $this->assertIsArray($coding);
        $this->assertArrayHasKey(0, $coding);
        $this->assertIsArray($coding[0]);
        $coding[0]['display'] = 'test-fixture-medication-001-updated';
        $code['coding'] = $coding;
        $code['text'] = 'test-fixture-medication-001-updated';
        $payload['code'] = $code;
        // form is the field asserted below: tablet (C42998) -> capsule (C25158). Both sides of
        // that mapping are literal tables in FhirMedicationService, so the round trip depends on
        // nothing but the stored `drugs`.`form` value. code.coding[].display is deliberately not
        // asserted -- the read rebuilds it from the drug-code registry and only falls back to
        // `drugs`.`name` when the registry has no description for the code, so what comes back
        // depends on whether RxNorm data is loaded in the test database.
        $payload['form'] = [
            'coding' => [
                ['system' => 'http://ncimeta.nci.nih.gov', 'code' => 'C25158', 'display' => 'capsule'],
            ],
        ];
        $updated = new FHIRMedication($payload);

        $actualResult = $this->fhirMedicationService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());

        // Read back rather than trusting update()'s own answer: a service that accepted the
        // write and persisted none of it satisfies every assertion above.
        $readBack = $this->fhirMedicationService->getOne($fhirId);
        $this->assertTrue($readBack->isValid(), 'Read-back should succeed');
        $readRecords = $readBack->getData();
        $this->assertIsArray($readRecords);
        $this->assertArrayHasKey(0, $readRecords);
        $serialized = json_decode((string) json_encode($readRecords[0]), true);
        $this->assertIsArray($serialized);
        $form = $serialized['form'] ?? null;
        $this->assertIsArray($form);
        $formCodings = $form['coding'] ?? null;
        $this->assertIsArray($formCodings);
        $this->assertArrayHasKey(0, $formCodings);
        $firstFormCoding = $formCodings[0];
        $this->assertIsArray($firstFormCoding);
        $this->assertSame(
            'C25158',
            $firstFormCoding['code'] ?? null,
            'Medication.form should be updated to capsule'
        );
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
