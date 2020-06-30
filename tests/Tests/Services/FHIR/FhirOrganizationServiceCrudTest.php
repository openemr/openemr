<?php

namespace OpenEMR\Tests\Services\FHIR;

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
    private $fixtureManager;
    private $fhirOrganizationFixture;
    private $fhirOrganizationService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FacilityFixtureManager();
        $this->fhirOrganizationFixture = (array) $this->fixtureManager->getSingleFhirFacilityFixture();
        $this->fhirOrganizationService = new FhirOrganizationService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFacilityFixtures();
    }

    /**
     * Tests a successful insert operation
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsert()
    {
        unset($this->fhirOrganizationFixture['id']);
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
        unset($this->fhirOrganizationFixture['name']);
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
        unset($this->fhirOrganizationFixture['id']);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        $this->fhirOrganizationFixture['name'] = 'test-fixture-Glenmark Clinic';
        $this->fhirOrganizationFixture['id'] = $fhirId;
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
