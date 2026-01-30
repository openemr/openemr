<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\Tests\RestControllers\FHIR\Trait\FhirResponseAssertionTrait;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use Symfony\Component\HttpFoundation\Response;

class FhirPatientRestControllerTest extends TestCase
{
    use JsonResponseHandlerTrait;
    use FhirResponseAssertionTrait;

    private FhirPatientRestController $fhirPatientController;
    private FixtureManager $fixtureManager;
    private array $fhirFixture;


    protected function setUp(): void
    {
        $this->fhirPatientController = new FhirPatientRestController();
        $this->fhirPatientController->setSystemLogger(new SystemLogger(Level::Emergency));
        $this->fixtureManager = new FixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    public function testPost(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertArrayHasKey('uuid', $contents);
        $this->assertNotEmpty($contents['uuid']);
    }

    public function testInvalidPost(): void
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testPut(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPatientController->put($fhirId, $this->fhirFixture);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());

        $contents = $this->getJsonContents($actualResult);
        $this->assertNotEmpty($contents, "put() should have returned a result");
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testInvalidPut(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPatientController->put('bad-uuid', $this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->fhirPatientController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $actualResult = $this->fhirPatientController->getOne($fhirId);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertNotEmpty($contents, "getOne() should have returned a result");
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testGetOneNoMatch(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $actualResult = $this->fhirPatientController->getOne("not-a-matching-uuid");
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals(1, count($contents['validationErrors']));
    }

    public function testGetAll(): void
    {
        $this->fhirPatientController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'CA'];
        $actualResults = $this->fhirPatientController->getAll($searchParams);
        $fhirPatient = new FHIRPatient();
        $this->assertFhirBundleResponse($actualResults, Response::HTTP_OK, $fhirPatient->get_fhirElementName());
    }
}
