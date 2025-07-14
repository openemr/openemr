<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * @coversDefaultClass \OpenEMR\RestControllers\FHIR\FhirPatientRestController
 */

class FhirPatientRestControllerTest extends TestCase
{
    private $fhirPatientController;
    private $fixtureManager;
    private $fhirFixture;


    protected function setUp(): void
    {
        $this->fhirPatientController = new FhirPatientRestController();
        $this->fixtureManager = new FixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * @cover ::post with valid data
     */
    public function testPost(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(201, http_response_code());
        $this->assertNotEmpty($actualResult['uuid']);
    }

    /**
     * @cover ::post with invalid data
     */
    public function testInvalidPost(): void
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    /**
     * @cover ::put with valid data
     */
    public function testPut(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPatientController->put($fhirId, $this->fhirFixture);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    /**
     * @cover ::put with valid data
     */
    public function testInvalidPut(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPatientController->put('bad-uuid', $this->fhirFixture);

        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $actualResult = $this->fhirPatientController->getOne($fhirId);
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    public function testGetOneNoMatch(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $actualResult = $this->fhirPatientController->getOne("not-a-matching-uuid");
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetAll(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'CA'];
        $actualResults = $this->fhirPatientController->getAll($searchParams);
        $this->assertNotEmpty($actualResults);

        foreach ($actualResults->getEntry() as $bundleEntry) {
            $this->assertObjectHasProperty('fullUrl', $bundleEntry);
            $this->assertObjectHasProperty('resource', $bundleEntry);
        }
    }
}
