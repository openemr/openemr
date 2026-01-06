<?php

namespace OpenEMR\Tests\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\Search\SearchQueryConfig;
use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\Tests\Fixtures\FixtureManager;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerBag;

class PatientRestControllerTest extends TestCase
{
    const PATIENT_API_URL = "/apis/api/patient";

    private $patientData;
    private PatientRestController $patientController;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->patientData = [
            "pubpid" => "test-fixture-doe",
            "title" => "Mr.",
            "fname" => "John",
            "mname" => "D",
            "lname" => "Doe",
            "ss" => "111-11-1111",
            "street" => "2100 Anyhoo Lane",
            "postal_code" => "92101",
            "city" => "San Diego",
            "state" => "CA",
            "phone_contact" => "(555) 555-5551",
            "phone_home" => "(555) 555-5552",
            "phone_biz" => "(555) 555-5553",
            "email" => "john.doe@email.com",
            "DOB" => "1977-05-01",
            "sex" => "Male",
            "status" => "single",
            "drivers_license" => "102245737"
        ];

        $this->fixtureManager = new FixtureManager();
        $this->patientController = new PatientRestController();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    public function testPostInvalidData(): void
    {
        unset($this->patientData["fname"]);
        $response = $this->patientController->post($this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPost(): void
    {
        $response = $this->patientController->post($this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(2, count($actualResult["data"]));

        $patientPid = $actualResult["data"]["pid"];
        $this->assertIsInt($patientPid);
        $this->assertGreaterThan(0, $patientPid);
    }

    public function testPutInvalidData(): void
    {
        $response = $this->patientController->post($this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $actualUuid = $actualResult["data"]["uuid"];
        $this->patientData["uuid"] = $actualUuid;

        $response = $this->patientController->put("not-a-pid", $this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    public function testPut(): void
    {
        $response = $this->patientController->post($this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(2, count($actualResult["data"]));

        $patientUuid = $actualResult["data"]["uuid"];
        $this->patientData["phone_home"] = "111-111-1111";
        $response = $this->patientController->put($patientUuid, $this->patientData, $this->createMock(HttpRestRequest::class));

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode($response->getBody(), true);
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertNotNull($actualResult["data"]);

        $updatedPatient = $actualResult["data"];
        $this->assertIsArray($updatedPatient);
        $this->assertEquals($this->patientData["phone_home"], $updatedPatient["phone_home"]);
    }

    public function testGetOneInvalidUuid(): void
    {
        $response = $this->patientController->getOne("not-a-uuid", $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals([], $actualResult["data"]);
    }

    public function testGetOne(): void
    {
        // create a record
        $response = $this->patientController->post($this->patientData, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $postResult = json_decode($response->getBody(), true);
        $postedUuid = $postResult["data"]["uuid"];

        // confirm the pid matches what was requested
        $response = $this->patientController->getOne($postedUuid, $this->createMock(HttpRestRequest::class));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $actualResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals($postedUuid, $actualResult["data"]["uuid"]);
    }

    public function testGetAll(): void
    {
        $this->fixtureManager->installPatientFixtures();
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->server = $this->createMock(ServerBag::class);
        $restRequest->server->method('get')->with('REDIRECT_URL')->willReturn('http://localhost/');
        $restRequest->query = new InputBag();
        $response = $this->patientController->getAll($restRequest, ["postal_code" => "90210"], new SearchQueryConfig());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $searchResult = json_decode((string) $response->getBody(), true);
        $this->assertEquals(0, count($searchResult["validationErrors"]));
        $this->assertEquals(0, count($searchResult["internalErrors"]));
        $this->assertGreaterThan(1, count($searchResult["data"]));

        foreach ($searchResult["data"] as $searchResult) {
            $this->assertEquals("90210", $searchResult["postal_code"]);
        }
    }
}
