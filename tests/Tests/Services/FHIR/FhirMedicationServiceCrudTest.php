<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\Services\FHIR\FhirMedicationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Medication Service CRUD Tests
 *
 * Medication is master data; not patient-bound. Writes target the `drugs` table only —
 * batch (lot/expiration/manufacturer) lives on drug_inventory and is intentionally
 * not round-trip-writable through this endpoint.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
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
        $this->fhirMedicationService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeMedicationFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirMedicationFixture->setId(null);
        $processingResult = $this->fhirMedicationService->insert($this->fhirMedicationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
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
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirMedicationFixture->setId(null);
        $insertResult = $this->fhirMedicationService->insert($this->fhirMedicationFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $fhirId = $insertResult->getData()[0]['uuid'];
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
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
