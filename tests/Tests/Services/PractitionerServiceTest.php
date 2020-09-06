<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * Practitioner Service Tests
 * @coversDefaultClass OpenEMR\Services\PractitionerService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PractitionerServiceTest extends TestCase
{
    private $practitionerService;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->practitionerService = new PractitionerService();
        $this->fixtureManager = new PractitionerFixtureManager();
        $this->practitionerFixture = (array) $this->fixtureManager->getSinglePractitionerFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    /**
     * @covers ::insert when the data is invalid
     */
    public function testInsertFailure()
    {
        $this->practitionerFixture["fname"] = "A";
        $this->practitionerFixture["npi"] = "12345";
        unset($this->practitionerFixture["lname"]);

        $actualResult = $this->practitionerService->insert($this->practitionerFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("lname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("npi", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::insert when the data is valid
     */
    public function testInsertSuccess()
    {
        $actualResult = $this->practitionerService->insert($this->practitionerFixture);
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
        $this->practitionerService->insert($this->practitionerFixture);

        $this->practitionerFixture["fname"] = "A";

        $actualResult = $this->practitionerService->update("not-a-uuid", $this->practitionerFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("uuid", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::update when the data is valid
     */
    public function testUpdateSuccess()
    {
        $actualResult = $this->practitionerService->insert($this->practitionerFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("id", $dataResult);
        $this->assertGreaterThan(0, $dataResult["id"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $actualUuid = $dataResult["uuid"];

        $this->practitionerFixture["email"] = "help@pennfirm.com";
        $this->practitionerService->update($actualUuid, $this->practitionerFixture);

        $sql = "SELECT `uuid`, `email` FROM `users` WHERE `uuid` = ?";
        $result = sqlQuery($sql, [UuidRegistry::uuidToBytes($actualUuid)]);
        $this->assertEquals($actualUuid, UuidRegistry::uuidToString($result["uuid"]));
        $this->assertEquals("help@pennfirm.com", $result["email"]);
    }

    /**
     * @cover ::getOne
     * @cover ::getAll
     */
    public function testPractitionerQueries()
    {
        $this->fixtureManager->installPractitionerFixtures();

        $result = sqlQuery("SELECT `uuid` FROM `users` WHERE npi IS NOT NULL");
        $existingUuid = UuidRegistry::uuidToString($result['uuid']);
        // getOne
        $actualResult = $this->practitionerService->getOne($existingUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingUuid, $resultData["uuid"]);
        $this->assertArrayHasKey("fname", $resultData);
        $this->assertArrayHasKey("lname", $resultData);
        $this->assertArrayHasKey("uuid", $resultData);

        // getOne - validate uuid
        $expectedUuid = $resultData["uuid"];
        $actualResult = $this->practitionerService->getOne($expectedUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($expectedUuid, $resultData["uuid"]);

        // getOne - with an invalid uuid
        $actualResult = $this->practitionerService->getOne("not-a-uuid");
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getAll
        $actualResult = $this->practitionerService->getAll(array("npi" => "0123456789"));
        $this->assertNotNull($actualResult);
        $this->assertGreaterThan(1, count($actualResult->getData()));

        foreach ($actualResult->getData() as $index => $practitionerRecord) {
            $this->assertArrayHasKey("fname", $resultData);
            $this->assertArrayHasKey("lname", $resultData);
            $this->assertArrayHasKey("uuid", $resultData);
            $this->assertEquals("0123456789", $practitionerRecord["npi"]);
        }
    }
}
