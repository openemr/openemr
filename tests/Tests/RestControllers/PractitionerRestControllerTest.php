<?php

namespace OpenEMR\Tests\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\PractitionerRestController;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PractitionerRestControllerTest extends TestCase
{
    const PRACTITIONER_API_URL = "/apis/api/practitioner";

    private $practitionerData;
    /**
     * @var PractitionerRestController
     */
    private $practitionerController;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->practitionerData = [
            "id" => "test-fixture-789456",
            "uuid" => "90cde167-7b9b-4ed1-bd55-533925cb2605",
            "title" => "Mrs.",
            "fname" => "test-fixture-Eduardo",
            "mname" => "Kathy",
            "lname" => "Perez",
            "federaltaxid" => "",
            "federaldrugid" => "",
            "upin" => "",
            "facility_id" => "3",
            "facility" => "Your Clinic Name Here",
            "npi" => "0123456789",
            "email" => "info@pennfirm.com",
            "specialty" => "",
            "billname" => null,
            "url" => null,
            "assistant" => null,
            "organization" => null,
            "valedictory" => null,
            "street" => "789 Third Avenue",
            "streetb" => "123 Cannaut Street",
            "city" => "San Diego",
            "state" => "CA",
            "zip" => "90210",
            "phone" => "(619) 555-9827",
            "fax" => null,
            "phonew1" => "(619) 555-7822",
            "phonecell" => "(619) 555-7821",
            "notes" => null,
            "state_license_number" => "123456",
            "abook_title" => "Specialist",
            "physician_title" => "Attending physician",
            "physician_code" => "SNOMED-CT =>405279007",
            "username" => "kperez"
        ];

        $this->fixtureManager = new PractitionerFixtureManager();
        $this->practitionerController = new PractitionerRestController();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    public function testPostInvalidData(): void
    {
        unset($this->practitionerData["fname"]);
        $response = $this->practitionerController->post($this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPost(): void
    {
        $response = $this->practitionerController->post($this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(2, count($actualResult["data"]));

        $practitionerPid = $actualResult["data"]["id"];
        $this->assertIsInt($practitionerPid);
        $this->assertGreaterThan(0, $practitionerPid);
    }

    public function testPutInvalidData(): void
    {
        $response = $this->practitionerController->post($this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $actualUuid = $actualResult["data"]["uuid"];
        $this->practitionerData["uuid"] = $actualUuid;

        $response = $this->practitionerController->patch("not-a-id", $this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPut(): void
    {
        $response = $this->practitionerController->post($this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $practitionerUuid = $actualResult["data"]["uuid"];
        $this->practitionerData["email"] = "help@pennfirm.com";
        $response = $this->practitionerController->patch($practitionerUuid, $this->practitionerData, $this->createMock(HttpRestRequest::class));

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertNotNull($actualResult["data"]);

        $updatedPractitioner = $actualResult["data"];
        $this->assertIsArray($updatedPractitioner);
        $this->assertEquals($this->practitionerData["email"], $updatedPractitioner["email"]);
    }

    public function testGetOneInvalidUuid(): void
    {
        $response = $this->practitionerController->getOne("not-a-uuid", $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals([], $actualResult["data"]);
    }

    public function testGetOne(): void
    {
        // create a record
        $response = $this->practitionerController->post($this->practitionerData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $postResult = json_decode((string) $response->getBody(), true);
        $postedUuid = $postResult["data"]["uuid"];

        // confirm the id matches what was requested
        $response = $this->practitionerController->getOne($postedUuid, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals($postedUuid, $actualResult["data"]["uuid"]);
    }

    public function testGetAll(): void
    {
        $this->fixtureManager->installPractitionerFixtures();
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->server = $this->createMock(ServerBag::class);
        $restRequest->server->method('get')->with('REDIRECT_URL')->willReturn('http://localhost/');
        $restRequest->query = new InputBag();
        $response = $this->practitionerController->getAll($restRequest, ["npi" => "0123456789"]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $searchResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(0, count($searchResult["validationErrors"]));
        $this->assertEquals(0, count($searchResult["internalErrors"]));
        $this->assertGreaterThan(1, count($searchResult["data"]));

        foreach ($searchResult["data"] as $searchResult) {
            $this->assertEquals("0123456789", $searchResult["npi"]);
        }
    }
}
