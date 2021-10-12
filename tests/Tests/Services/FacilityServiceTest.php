<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FacilityService;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;

/**
 * Facility Service Tests
 * @coversDefaultClass OpenEMR\Services\FacilityService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityServiceTest extends TestCase
{
    /**
     * @var FacilityService
     */
    private $facilityService;

    /**
     * @var FacilityFixtureManager
     */
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->facilityService = new FacilityService();
        $this->fixtureManager = new FacilityFixtureManager();
        $this->facilityFixture = (array) $this->fixtureManager->getSingleFacilityFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * @covers ::insert when the data is invalid
     */
    public function testInsertFailure()
    {
        $this->facilityFixture["name"] = "A";
        $this->facilityFixture["facility_npi"] = "12345";
        unset($this->facilityFixture["name"]);

        $actualResult = $this->facilityService->insert($this->facilityFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("name", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("facility_npi", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::insert when the data is valid
     */
    public function testInsertSuccess()
    {
        $actualResult = $this->facilityService->insert($this->facilityFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("id", $dataResult);
        $this->assertGreaterThan(0, $dataResult["id"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $this->assertEquals(0, count($actualResult->getValidationMessages()));
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertFalse($actualResult->hasInternalErrors());
    }

    /**
     * @covers ::update when the data is not valid
     */
    public function testUpdateFailure()
    {
        $this->facilityService->insert($this->facilityFixture);

        $this->facilityFixture["name"] = "A";

        $actualResult = $this->facilityService->update("not-a-uuid", $this->facilityFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("name", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("uuid", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::update when the data is valid
     */
    public function testUpdateSuccess()
    {
        $actualResult = $this->facilityService->insert($this->facilityFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("id", $dataResult);
        $this->assertGreaterThan(0, $dataResult["id"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $actualUuid = $dataResult["uuid"];

        $this->facilityFixture["email"] = "help@pennfirm.com";
        $this->facilityService->update($actualUuid, $this->facilityFixture);

        $sql = "SELECT `uuid`, `email` FROM `facility` WHERE `uuid` = ?";
        $result = sqlQuery($sql, [UuidRegistry::uuidToBytes($actualUuid)]);
        $this->assertEquals($actualUuid, UuidRegistry::uuidToString($result["uuid"]));
        $this->assertEquals("help@pennfirm.com", $result["email"]);
    }

    /**
     * @cover ::getOne
     * @cover ::getAll
     */
    public function testFacilityQueries()
    {
        $this->fixtureManager->installFacilityFixtures();

        $result = sqlQuery("SELECT `uuid` FROM `facility`");
        $existingUuid = UuidRegistry::uuidToString($result['uuid']);
        // getOne
        $actualResult = $this->facilityService->getOne($existingUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingUuid, $resultData["uuid"]);
        $this->assertArrayHasKey("name", $resultData);
        $this->assertArrayHasKey("uuid", $resultData);

        // getOne - validate uuid
        $expectedUuid = $resultData["uuid"];
        $actualResult = $this->facilityService->getOne($expectedUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($expectedUuid, $resultData["uuid"]);

        // getOne - with an invalid uuid
        $actualResult = $this->facilityService->getOne("not-a-uuid");
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getAll
        $actualResult = $this->facilityService->getAll(array("facility_npi" => "0123456789"));
        $this->assertNotNull($actualResult);
        $this->assertEquals(2, count($actualResult->getData()));

        foreach ($actualResult->getData() as $index => $facilityRecord) {
            $this->assertArrayHasKey("name", $resultData);
            $this->assertArrayHasKey("uuid", $resultData);
            $this->assertEquals("0123456789", $facilityRecord["facility_npi"]);
        }
    }

    public function testGetFacilityForUser()
    {
        $this->fixtureManager->installFacilityFixtures();

        $actualResult = $this->facilityService->getFacilityForUser(1);
        $this->assertNotNull($actualResult);
    }
}
