<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Services\FHIR\Serialization\FhirPatientSerializer;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;

/**
 * FHIR Patient Service Crud Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = (array) $this->fixtureManager->getSinglePatientFixture();
        $fixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        $this->fhirPatientFixture = FhirPatientSerializer::deserialize($fixture);
        $this->fhirPatientService = new FhirPatientService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * Tests a successful insert operation
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsert()
    {
        $this->fhirPatientFixture->setId(null);
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $this->assertGreaterThan(0, $dataResult['pid']);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    /**
     * Tests an insert operation where an error occurs
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsertWithErrors()
    {
        $this->fhirPatientFixture->name = [];
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    /**
     * Tests a successful update operation
     * @covers ::update
     * @covers ::updateOpenEMRRecord
     */
    public function testUpdate()
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

    /**
     * Tests an update operation where an error occurs
     * @covers ::update
     * @covers ::updateOpenEMRRecord
     */
    public function testUpdateWithErrors()
    {
        $actualResult = $this->fhirPatientService->update('bad-uuid', $this->fhirPatientFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
