<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\Tests\RestControllers\FHIR\Trait\FhirResponseAssertionTrait;
use OpenEMR\Tests\RestControllers\FHIR\Trait\JsonResponseHandlerTrait;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPractitionerRestControllerTest extends TestCase
{
    use JsonResponseHandlerTrait;
    use FhirResponseAssertionTrait;

    /**
     * @var FhirPractitionerRestController
     */
    private FhirPractitionerRestController $fhirPractitionerController;

    private PractitionerFixtureManager $fixtureManager;
    private array $fhirFixture;

    protected function setUp(): void
    {
        $this->fhirPractitionerController = new FhirPractitionerRestController();
        $this->fhirPractitionerController->setSystemLogger(new SystemLogger(Level::Emergency));
        $this->fixtureManager = new PractitionerFixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPractitionerFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    public function testPost(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_CREATED, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertArrayHasKey('uuid', $contents);
        $this->assertNotEmpty($contents['uuid']);
    }

    public function testInvalidPost(): void
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testPatch(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch($fhirId, $this->fhirFixture);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testInvalidPatch(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch('bad-uuid', $this->fhirFixture);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertGreaterThan(0, count($contents['validationErrors']));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $contents = $this->getJsonContents($actualResult);
        $fhirId = $contents['uuid'];

        $actualResult = $this->fhirPractitionerController->getOne($fhirId);
        $this->assertEquals(Response::HTTP_OK, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertNotEmpty($contents, "getOne() should have returned a result");
        $this->assertEquals($fhirId, $contents['id']);
    }

    public function testGetOneNoMatch(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $actualResult = $this->fhirPractitionerController->getOne("not-a-matching-uuid");
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actualResult->getStatusCode());
        $contents = $this->getJsonContents($actualResult);
        $this->assertEquals(1, count($contents['validationErrors']));
    }

    public function testGetAll(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'CA'];
        $actualResults = $this->fhirPractitionerController->getAll($searchParams);
        $fhirPractitioner = new FHIRPractitioner();
        $this->assertFhirBundleResponse($actualResults, Response::HTTP_OK, $fhirPractitioner->get_fhirElementName());
    }
}
