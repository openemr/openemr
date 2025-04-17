<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;

/**
 * @coversDefaultClass OpenEMR\RestControllers\FHIR\FhirOrganizationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirOrganizationRestControllerTest extends TestCase
{
    /**
     * @var FhirOrganizationRestController
     */
    private $fhirOrganizationController;

    /*
     * FacilityFixtureManager
     */
    private $fixtureManager;

    private $fhirFixture;

    protected function setUp(): void
    {
        $this->fhirOrganizationController = new FhirOrganizationRestController();
        $this->fixtureManager = new FacilityFixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirFacilityFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * @cover ::post with valid data
     */
    public function testPost()
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $this->assertEquals(201, http_response_code());
        $this->assertNotEmpty($actualResult['uuid']);
    }

    /**
     * @cover ::post with invalid data
     */
    public function testInvalidPost()
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    /**
     * @cover ::patch with valid data
     */
    public function testPatch()
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $this->fhirFixture['name'] = 'test-fixture-Glenmark Clinic';
        $actualResult = $this->fhirOrganizationController->patch($fhirId, $this->fhirFixture);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    /**
     * @cover ::patch with valid data
     */
    public function testInvalidPatch()
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $this->fhirFixture['name'] = 'Smithers Clinic';
        $actualResult = $this->fhirOrganizationController->patch('bad-uuid', $this->fhirFixture);

        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetOne()
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $actualResult = $this->fhirOrganizationController->getOne($fhirId);
        $this->assertNotEmpty($actualResult, "getOne() should have returned a result");
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    public function testGetOneNoMatch()
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $actualResult = $this->fhirOrganizationController->getOne("not-a-matching-uuid");
        $this->assertEquals(1, count($actualResult['validationErrors']));
    }

    public function testGetAll()
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'MA'];
        $actualResults = $this->fhirOrganizationController->getAll($searchParams);
        $this->assertNotEmpty($actualResults);

        foreach ($actualResults->getEntry() as $index => $bundleEntry) {
            $this->assertObjectHasProperty('fullUrl', $bundleEntry);
            $this->assertObjectHasProperty('resource', $bundleEntry);
        }
    }
}
