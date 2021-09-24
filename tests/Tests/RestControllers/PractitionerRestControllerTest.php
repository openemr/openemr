<?php

namespace OpenEMR\Tests\RestControllers;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\PractitionerRestController;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * @coversDefaultClass OpenEMR\RestControllers\PractitionerRestController
 *
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
        $this->practitionerData = array(
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
        );

        $this->fixtureManager = new PractitionerFixtureManager();
        $this->practitionerController = new PractitionerRestController();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    /**
     * @cover ::post with invalid data
     */
    public function testPostInvalidData()
    {
        unset($this->practitionerData["fname"]);
        $actualResult = $this->practitionerController->post($this->practitionerData);
        $this->assertEquals(400, http_response_code());
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    /**
     * @cover ::post with valid data
     */
    public function testPost()
    {
        $actualResult = $this->practitionerController->post($this->practitionerData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(2, count($actualResult["data"]));

        $practitionerPid = $actualResult["data"]["id"];
        $this->assertIsInt($practitionerPid);
        $this->assertGreaterThan(0, $practitionerPid);
    }

    /**
     * @cover ::put with invalid data
     */
    public function testPutInvalidData()
    {
        $actualResult = $this->practitionerController->post($this->practitionerData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(2, count($actualResult["data"]));

        $actualUuid = $actualResult["data"]["uuid"];
        $this->practitionerData["uuid"] = $actualUuid;

        $actualResult = $this->practitionerController->patch("not-a-id", $this->practitionerData);
        $this->assertEquals(400, http_response_code());
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    /**
     * @cover ::put with valid data
     */
    public function testPut()
    {
        $actualResult = $this->practitionerController->post($this->practitionerData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(2, count($actualResult["data"]));

        $practitionerUuid = $actualResult["data"]["uuid"];
        $this->practitionerData["email"] = "help@pennfirm.com";
        $actualResult = $this->practitionerController->patch($practitionerUuid, $this->practitionerData);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertNotNull($actualResult["data"]);

        $updatedPractitioner = $actualResult["data"];
        $this->assertIsArray($updatedPractitioner);
        $this->assertEquals($this->practitionerData["email"], $updatedPractitioner["email"]);
    }

    /**
     * @cover ::getOne with an invalid uuid
     */
    public function testGetOneInvalidUuid()
    {
        $actualResult = $this->practitionerController->getOne("not-a-uuid");
        $this->assertEquals(400, http_response_code());
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals([], $actualResult["data"]);
    }

    /**
     * @cover ::getOne with a valid uuid
     */
    public function testGetOne()
    {
        // create a record
        $postResult = $this->practitionerController->post($this->practitionerData);
        $postedUuid = $postResult["data"]["uuid"];

        // confirm the id matches what was requested
        $actualResult = $this->practitionerController->getOne($postedUuid);
        $this->assertEquals($postedUuid, $actualResult["data"]["uuid"]);
    }

    /**
     * @cover ::getAll
     */
    public function testGetAll()
    {
        $this->fixtureManager->installPractitionerFixtures();
        $searchResult = $this->practitionerController->getAll(array("npi" => "0123456789"));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($searchResult["validationErrors"]));
        $this->assertEquals(0, count($searchResult["internalErrors"]));
        $this->assertGreaterThan(1, count($searchResult["data"]));

        foreach ($searchResult["data"] as $index => $searchResult) {
            $this->assertEquals("0123456789", $searchResult["npi"]);
        }
    }
}
