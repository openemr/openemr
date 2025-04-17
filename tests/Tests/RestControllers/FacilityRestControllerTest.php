<?php

namespace OpenEMR\Tests\RestControllers;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;

/**
 * @coversDefaultClass OpenEMR\RestControllers\FacilityRestControllerTest
 *
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
        $this->facilityData = array(
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
        );

        $this->fixtureManager = new FacilityFixtureManager();
        $this->facilityController = new FacilityRestController();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * @cover ::post with invalid data
     */
    public function testPostInvalidData()
    {
        unset($this->facilityData["name"]);
        $actualResult = $this->facilityController->post($this->facilityData);
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
        $actualResult = $this->facilityController->post($this->facilityData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(2, count($actualResult["data"]));

        $facilityPid = $actualResult["data"]["id"];
        $this->assertIsInt($facilityPid);
        $this->assertGreaterThan(0, $facilityPid);
    }

    /**
     * @cover ::patch with invalid data
     */
    public function testPatchInvalidData()
    {
        $actualResult = $this->facilityController->post($this->facilityData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(2, count($actualResult["data"]));

        $actualUuid = $actualResult["data"]["uuid"];
        $this->facilityData["uuid"] = $actualUuid;

        $actualResult = $this->facilityController->patch("not-a-id", $this->facilityData);
        $this->assertEquals(400, http_response_code());
        $this->assertEquals(1, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertEquals(0, count($actualResult["data"]));
    }

    /**
     * @cover ::patch with valid data
     */
    public function testPatch()
    {
        $actualResult = $this->facilityController->post($this->facilityData);
        $this->assertEquals(201, http_response_code());
        $this->assertEquals(2, count($actualResult["data"]));

        $facilityUuid = $actualResult["data"]["uuid"];
        $this->facilityData["email"] = "info@pennfirm.com";
        $actualResult = $this->facilityController->patch($facilityUuid, $this->facilityData);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($actualResult["validationErrors"]));
        $this->assertEquals(0, count($actualResult["internalErrors"]));
        $this->assertNotNull($actualResult["data"]);

        $updatedFacility = $actualResult["data"];
        $this->assertIsArray($updatedFacility);
        $this->assertEquals($this->facilityData["email"], $updatedFacility["email"]);
    }

    /**
     * @cover ::getOne with an invalid uuid
     */
    public function testGetOneInvalidUuid()
    {
        $actualResult = $this->facilityController->getOne("not-a-uuid");
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
        $postResult = $this->facilityController->post($this->facilityData);
        $postedUuid = $postResult["data"]["uuid"];

        // confirm the id matches what was requested
        $actualResult = $this->facilityController->getOne($postedUuid);
        $this->assertEquals($postedUuid, $actualResult["data"]["uuid"]);
    }

    /**
     * @cover ::getAll
     */
    public function testGetAll()
    {
        $this->fixtureManager->installFacilityFixtures();
        $searchResult = $this->facilityController->getAll(array("facility_npi" => "0123456789"));

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($searchResult["validationErrors"]));
        $this->assertEquals(0, count($searchResult["internalErrors"]));
        $this->assertGreaterThan(1, count($searchResult["data"]));

        foreach ($searchResult["data"] as $index => $searchResult) {
            $this->assertEquals("0123456789", $searchResult["facility_npi"]);
        }
    }
}
