<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDevice;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirDeviceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Device Service CRUD Tests
 *
 * Device writes target the `lists` table scoped to type='medical_device'. UDI carrier,
 * lot, expiration, manufacturer, etc. are stored in udi_data JSON under standard_elements.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirDeviceServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRDevice $fhirDeviceFixture;
    private FhirDeviceService $fhirDeviceService;
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

        $fixture = (array) $this->fixtureManager->getSingleFhirDeviceFixture();
        $fixture['patient'] = ['reference' => 'Patient/' . $this->patientUuid];
        $this->fhirDeviceFixture = new FHIRDevice($fixture);

        $this->fhirDeviceService = new FhirDeviceService();
        $this->fhirDeviceService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeDeviceFixtures();
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirDeviceFixture->setId(new FHIRId());
        $processingResult = $this->fhirDeviceService->insert($this->fhirDeviceFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $data = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
        $this->assertGreaterThan(0, $data['id']);
    }

    #[Test]
    public function testInsertWithUnresolvablePatient(): void
    {
        $bogusUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        $this->fhirDeviceFixture->setId(new FHIRId());
        $payload = $this->fhirDeviceFixture->jsonSerialize();
        $payload['patient'] = ['reference' => 'Patient/' . $bogusUuid];
        $fixture = new FHIRDevice($payload);

        $processingResult = $this->fhirDeviceService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirDeviceFixture->setId(new FHIRId());
        $insertResult = $this->fhirDeviceService->insert($this->fhirDeviceFixture);
        $this->assertTrue(
            $insertResult->isValid(),
            "Insert should succeed: " . json_encode($insertResult->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($insertResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirDeviceFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['lotNumber'] = 'test-fixture-lot02-updated';
        $updated = new FHIRDevice($payload);

        $actualResult = $this->fhirDeviceService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        $result = $this->fhirDeviceService->update('bad-uuid', $this->fhirDeviceFixture);
        $this->assertFalse($result->isValid());
        $this->assertSame([], $result->getData());
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
