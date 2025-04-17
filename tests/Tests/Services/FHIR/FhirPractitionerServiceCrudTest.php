<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Services\FHIR\Serialization\FhirPractitionerSerializer;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;

/**
 * FHIR Practitioner Service Crud Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPractitionerService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPractitionerServiceCrudTest extends TestCase
{
    private $fixtureManager;
    private $practitionerFixture;
    /**
     * @var FHIRPractitioner
     */
    private $fhirPractitionerFixture;

    /**
     * @var FhirPractitionerService
     */
    private $fhirPractitionerService;

    protected function setUp(): void
    {
        $this->fixtureManager = new PractitionerFixtureManager();
        $this->practitionerFixture = (array) $this->fixtureManager->getSinglePractitionerFixture();
        $fixture = (array) $this->fixtureManager->getSingleFhirPractitionerFixture();
        $this->fhirPractitionerFixture = FhirPractitionerSerializer::deserialize($fixture);
        $this->fhirPractitionerService = new FhirPractitionerService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    /**
     * Tests a successful insert operation
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsert()
    {
        $this->fhirPractitionerFixture->setId(null);
        $processingResult = $this->fhirPractitionerService->insert($this->fhirPractitionerFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $this->assertGreaterThan(0, $dataResult['id']);
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
        $this->fhirPractitionerFixture->name = []; // clear the names TODO: I don't like this public accessor, can we fix it?s
        $processingResult = $this->fhirPractitionerService->insert($this->fhirPractitionerFixture);
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
        $this->fhirPractitionerFixture->setId(null);
        $processingResult = $this->fhirPractitionerService->insert($this->fhirPractitionerFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        $this->fhirPractitionerFixture->getName()[0]->setFamily('Smith');
        $this->fhirPractitionerFixture->setId($fhirId);
        $actualResult = $this->fhirPractitionerService->update($fhirId, $this->fhirPractitionerFixture);
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
        $actualResult = $this->fhirPractitionerService->update('bad-uuid', $this->fhirPractitionerFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
