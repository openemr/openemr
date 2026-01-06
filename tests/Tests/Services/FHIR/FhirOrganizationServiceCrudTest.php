<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\Services\FHIR\FhirOrganizationService;
use OpenEMR\Services\FHIR\Serialization\FhirOrganizationSerializer;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * FHIR Organization Service Crud Tests
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
        $this->fhirOrganizationService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirOrganizationFixture->setId(null);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
        $this->assertTrue($processingResult->isValid());

        $dataResult = $processingResult->getData()[0];
        $this->assertGreaterThan(0, $dataResult['id']);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        $this->fhirOrganizationFixture->setName(null);
        $processingResult = $this->fhirOrganizationService->insert($this->fhirOrganizationFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
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

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirOrganizationService->update('bad-uuid', $this->fhirOrganizationFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
