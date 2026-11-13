<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\Serialization\FhirPatientSerializer;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Patient Service Crud Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class FhirPatientServiceCrudTest extends TestCase
{
    private $fixtureManager;
    private $patientFixture;

    /**
     * @var FHIRPatient
     */
    private $fhirPatientFixture;

    /**
     * @var FhirPatientService
     */
    private $fhirPatientService;

    private PractitionerFixtureManager $practitionerFixtureManager;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->practitionerFixtureManager = new PractitionerFixtureManager();
        $this->patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $fixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        $this->fhirPatientFixture = FhirPatientSerializer::deserialize($fixture);
        $this->fhirPatientService = new FhirPatientService();
        $this->fhirPatientService->setSystemLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirPatientFixture->setId(null);
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $this->assertGreaterThan(0, $dataResult['pid']);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        $this->fhirPatientFixture->name = [];
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirPatientFixture->setId(null);
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        $this->fhirPatientFixture->getName()[0]->setFamily('Smith');
        $this->fhirPatientFixture->setId($fhirId);
        $actualResult = $this->fhirPatientService->update($fhirId, $this->fhirPatientFixture);
        $this->assertTrue($actualResult->isValid());

        $actualFhirRecord = $actualResult->getData()[0];
        $actualName = $actualFhirRecord->getName();
        $this->assertEquals('Smith', $actualName[0]->getFamily());

        $this->assertEquals($fhirId, $actualFhirRecord->getId());
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirPatientService->update('bad-uuid', $this->fhirPatientFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }

    #[Test]
    public function testInsertWithGeneralPractitionerSetsTheProvider(): void
    {
        $practitionerUuid = $this->installPractitionerFixture();
        $this->fhirPatientFixture->setId(new FHIRId());
        $this->fhirPatientFixture->addGeneralPractitioner(UtilsService::createRelativeReference('Practitioner', $practitionerUuid));

        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            'Insert should succeed: ' . json_encode($processingResult->getValidationMessages())
        );

        $fhirId = $this->insertedUuid($processingResult);
        $this->assertSame('Practitioner/' . $practitionerUuid, $this->readGeneralPractitionerReference($fhirId));
    }

    #[Test]
    public function testUpdateWithGeneralPractitionerSetsTheProvider(): void
    {
        $practitionerUuid = $this->installPractitionerFixture();
        $this->fhirPatientFixture->setId(new FHIRId());
        $fhirId = $this->insertedUuid($this->fhirPatientService->insert($this->fhirPatientFixture));
        $this->assertNull($this->readGeneralPractitionerReference($fhirId));

        $this->fhirPatientFixture->setId(new FHIRId($fhirId));
        $this->fhirPatientFixture->addGeneralPractitioner(UtilsService::createRelativeReference('Practitioner', $practitionerUuid));
        $updateResult = $this->fhirPatientService->update($fhirId, $this->fhirPatientFixture);
        $this->assertTrue(
            $updateResult->isValid(),
            'Update should succeed: ' . json_encode($updateResult->getValidationMessages())
        );

        $this->assertSame('Practitioner/' . $practitionerUuid, $this->readGeneralPractitionerReference($fhirId));
    }

    #[Test]
    public function testInsertWithUnresolvableGeneralPractitioner(): void
    {
        $this->fhirPatientFixture->setId(new FHIRId());
        $this->fhirPatientFixture->addGeneralPractitioner(
            UtilsService::createRelativeReference('Practitioner', $this->practitionerFixtureManager->getUnregisteredUuid())
        );

        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    /**
     * Installs the practitioner fixtures and returns the uuid of one of them.
     */
    private function installPractitionerFixture(): string
    {
        $this->practitionerFixtureManager->installPractitionerFixtures();
        $practitionerRow = QueryUtils::querySingleRow(
            "SELECT uuid FROM users WHERE fname LIKE 'test-fixture-%' ORDER BY id DESC LIMIT 1",
            []
        );
        $this->assertIsArray($practitionerRow);
        if (($practitionerRow['uuid'] ?? '') === '') {
            $this->markTestSkipped('Practitioner fixture did not produce a queryable row');
        }

        return UuidRegistry::uuidToString($practitionerRow['uuid']);
    }

    private function insertedUuid(ProcessingResult $result): string
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);
        $uuid = $row['uuid'] ?? null;
        $this->assertIsString($uuid);

        return $uuid;
    }

    private function readGeneralPractitionerReference(string $fhirId): ?string
    {
        $readBack = $this->fhirPatientService->getOne($fhirId);
        $this->assertTrue($readBack->isValid(), 'Read-back should succeed');
        $data = $readBack->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $patient = $data[0];
        $this->assertInstanceOf(FHIRPatient::class, $patient);
        $generalPractitioner = $patient->getGeneralPractitioner()[0] ?? null;

        return $generalPractitioner === null ? null : (string) $generalPractitioner->getReference();
    }
}
