<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\Tests\RestControllers\FHIR\Trait\FhirResponseAssertionTrait;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirOrganizationRestController;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirOrganizationRestControllerTest extends TestCase
{
    use JsonResponseHandlerTrait;
    use FhirResponseAssertionTrait;

    const LOG_LEVEL = Level::Emergency; // Set the log level to Emergency for testing so we skip most logging that is used for testing
    /**
     * @var FhirOrganizationRestController
     */
    private FhirOrganizationRestController $fhirOrganizationController;

    /*
     * FacilityFixtureManager
     */
    private FacilityFixtureManager $fixtureManager;

    private array $fhirFixture;

    protected function setUp(): void
    {
        $this->fhirOrganizationController = new FhirOrganizationRestController();
        // disable regular error logging
        $this->fhirOrganizationController->setSystemLogger(new SystemLogger(self::LOG_LEVEL));
        $this->fixtureManager = new FacilityFixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirFacilityFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    public function testPost(): void
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertArrayHasKey('uuid', $contents);
        $this->assertNotEmpty($contents['uuid']);
    }

    public function testInvalidPost(): void
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testPatch(): void
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $this->fhirFixture['name'] = 'test-fixture-Glenmark Clinic';
        $actualResult = $this->fhirOrganizationController->patch($fhirId, $this->fhirFixture);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testInvalidPatch(): void
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $this->fhirFixture['name'] = 'Smithers Clinic';
        $actualResult = $this->fhirOrganizationController->patch('bad-uuid', $this->fhirFixture);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->fhirOrganizationController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $actualResult = $this->fhirOrganizationController->getOne($fhirId);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertNotEmpty($contents, "getOne() should have returned a result");
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testGetOneNoMatch(): void
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $actualResult = $this->fhirOrganizationController->getOne("not-a-matching-uuid");
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals(1, count($contents['validationErrors']));
    }

    public function testGetAll(): void
    {
        $this->fhirOrganizationController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'MA'];
        $actualResults = $this->fhirOrganizationController->getAll($searchParams);
        $fhirOrganization = new FHIROrganization();
        $this->assertFhirBundleResponse($actualResults, Response::HTTP_OK, $fhirOrganization->get_fhirElementName());
    }
}
