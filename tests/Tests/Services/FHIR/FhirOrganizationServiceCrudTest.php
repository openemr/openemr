<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\Services\FHIR\Serialization\FhirOrganizationSerializer;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use OpenEMR\Services\FHIR\FhirOrganizationService;

/**
 * FHIR Organization Service Crud Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirOrganizationService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirOrganizationServiceCrudTest extends TestCase
{
    /**
     * @var FacilityFixtureManager
     */
    private $fixtureManager;

    /**
     * @var FHIROrganization
     */
    private $fhirOrganizationFixture;
    private $fhirOrganizationService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FacilityFixtureManager();
        $fixture = (array) $this->fixtureManager->getSingleFhirFacilityFixture();
        $this->fhirOrganizationFixture = FhirOrganizationSerializer::deserialize($fixture);
        $this->fhirOrganizationService = new FhirOrganizationService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * Tests a successful insert operation
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsert()
    {
        $this->fhirOrganizationFixture->setId(null);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
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
        $this->fhirOrganizationFixture->setName(null);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
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
        $this->fhirOrganizationFixture->setId(null);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        $this->fhirOrganizationFixture->setName('test-fixture-Glenmark Clinic');
        $this->fhirOrganizationFixture->setId($fhirId);
        $actualResult = $this->fhirOrganizationService->update($fhirId, $this->fhirOrganizationFixture);
        $this->assertTrue($actualResult->isValid());

        $actualFhirRecord = $actualResult->getData()[0];
        $actualName = $actualFhirRecord->getName();
        $this->assertEquals('test-fixture-Glenmark Clinic', $actualName);

        $this->assertEquals($fhirId, $actualFhirRecord->getId());
    }

    /**
     * Tests an update operation where an error occurs
     * @covers ::update
     * @covers ::updateOpenEMRRecord
     */
    public function testUpdateWithErrors()
    {
        $actualResult = $this->fhirOrganizationService->update('bad-uuid', $this->fhirOrganizationFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
