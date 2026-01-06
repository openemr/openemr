<?php

namespace OpenEMR\Tests\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;
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
class FacilityRestControllerTest extends TestCase
{
    const FACILITY_API_URL = "/apis/api/facility";

    private $facilityData;

    /**
     * @var FacilityRestController
     */
    private $facilityController;

    /**
     * @var FacilityFixtureManager
     */
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->facilityData = [
            'name' => 'test-fixture-Your Clinic Name Here',
            'phone' => '(619) 555-4859',
            'fax' => '(619) 555-7822',
            'street' => '789 Third Avenue',
            'city' => 'San Diego',
            'state' => 'CA',
            'postal_code' => '90210',
            'country_code' => '+1',
            'federal_ein' => '',
            'website' => null,
            'email' => 'info@pennfirm.com',
            'service_location' => '1',
            'billing_location' => '1',
            'accepts_assignment' => '0',
            'pos_code' => null,
            'x12_sender_id' => '',
            'attn' => '',
            'domain_identifier' => '',
            'facility_npi' => '0123456789',
            'facility_taxonomy' => '',
            'tax_id_type' => '',
            'color' => '#99FFFF',
            'primary_business_entity' => '0',
            'facility_code' => '',
            'extra_validation' => '1',
            'mail_street' => '',
            'mail_street2' => '',
            'mail_city' => '',
            'mail_state' => '',
            'mail_zip' => '',
            'oid' => '',
            'iban' => '',
            'info' => ''
        ];

        $this->fixtureManager = new FacilityFixtureManager();
        $this->facilityController = new FacilityRestController();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    public function testPostInvalidData(): void
    {
        unset($this->facilityData["name"]);
        $response = $this->facilityController->post($this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPost(): void
    {
        $response = $this->facilityController->post($this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(2, count($actualResult["data"]));

        $facilityPid = $actualResult["data"]["id"];
        $this->assertIsInt($facilityPid);
        $this->assertGreaterThan(0, $facilityPid);
    }

    public function testPatchInvalidData(): void
    {
        $response = $this->facilityController->post($this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $actualUuid = $actualResult["data"]["uuid"];
        $this->facilityData["uuid"] = $actualUuid;

        $response = $this->facilityController->patch("not-a-id", $this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPatch(): void
    {
        $response = $this->facilityController->post($this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $facilityUuid = $actualResult["data"]["uuid"];
        $this->facilityData["email"] = "info@pennfirm.com";
        $response = $this->facilityController->patch($facilityUuid, $this->facilityData, $this->createMock(HttpRestRequest::class));

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertNotNull($actualResult["data"]);

        $updatedFacility = $actualResult["data"];
        $this->assertIsArray($updatedFacility);
        $this->assertEquals($this->facilityData["email"], $updatedFacility["email"]);
    }

    public function testGetOneInvalidUuid(): void
    {
        $response = $this->facilityController->getOne("not-a-uuid", $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals([], $actualResult["data"]);
    }

    public function testGetOne(): void
    {
        // create a record
        $response = $this->facilityController->post($this->facilityData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $postResult = json_decode($response->getBody(), true);
        $postedUuid = $postResult["data"]["uuid"];

        // confirm the id matches what was requested
        $response = $this->facilityController->getOne($postedUuid, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals($postedUuid, $actualResult["data"]["uuid"]);
    }

    public function testGetAll(): void
    {
        $this->fixtureManager->installFacilityFixtures();
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->server = $this->createMock(ServerBag::class);
        $restRequest->server->method('get')->with('REDIRECT_URL')->willReturn('http://localhost/');
        $restRequest->query = new InputBag();

        $response = $this->facilityController->getAll($restRequest, ["facility_npi" => "0123456789"]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $searchResult = json_decode($response->getBody(), true);

        $this->assertEquals(0, count($searchResult["validationErrors"]));
        $this->assertEquals(0, count($searchResult["internalErrors"]));
        $this->assertGreaterThan(1, count($searchResult["data"]));

        foreach ($searchResult["data"] as $searchResult) {
            $this->assertEquals("0123456789", $searchResult["facility_npi"]);
        }
    }
}
