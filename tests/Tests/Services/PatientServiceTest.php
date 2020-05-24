<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Services\PatientService;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * Patient Service Tests
 * @coversDefaultClass OpenEMR\Services\PatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class PatientServiceTest extends TestCase
{
    private $patientService;
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->patientService = new PatientService();
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = (array) $this->fixtureManager->getSinglePatientFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * @covers ::getFreshPid
     */
    public function testGetFreshPid()
    {
        $actualValue = $this->patientService->getFreshPid();
        $this->assertGreaterThan(0, $actualValue);
    }

    /**
     * @covers ::insert when the data is invalid
     */
    public function testInsertFailure()
    {
        $this->patientFixture["fname"] = "A";
        $this->patientFixture["DOB"] = "12/27/2017";
        unset($this->patientFixture["sex"]);

        $actualResult = $this->patientService->insert($this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("sex", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("DOB", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::insert when the data is valid
     */
    public function testInsertSuccess()
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("pid", $dataResult);
        $this->assertGreaterThan(0, $dataResult["pid"]);
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
        $actualResult = $this->patientService->insert($this->patientFixture);
        $actualUuid = $actualResult->getData()[0]["uuid"];

        $this->patientFixture["fname"] = "A";
        $this->patientFixture["uuid"] = $actualUuid;

        $actualResult = $this->patientService->update("not-a-pid", $this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("pid", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    /**
     * @covers ::update when the data is valid
     */
    public function testUpdateSuccess()
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("pid", $dataResult);
        $this->assertGreaterThan(0, $dataResult["pid"]);
        $this->assertArrayHasKey("uuid", $dataResult);
        
        $actualPid = $dataResult["pid"];
        $actualUuid = $dataResult["uuid"];
        $this->patientFixture["pid"] = $actualPid;
        $this->patientFixture["uuid"] = $actualUuid;
        $this->patientFixture["phone_home"] = "555-111-4444";
        $actualResult = $this->patientService->update($actualPid, $this->patientFixture);

        $sql = "SELECT pid, phone_home FROM patient_data WHERE pid = ?";
        $result = sqlQuery($sql, array($actualPid));

        $this->assertEquals($actualPid, intval($result["pid"]));
        $this->assertEquals("555-111-4444", $result["phone_home"]);
    }

    /**
     * @cover ::getOne
     * @cover ::getAll
     */
    public function testPatientQueries()
    {
        $this->fixtureManager->installPatientFixtures();
        $existingPid = $this->patientService->getFreshPid() - 1;

        // getOne
        $actualResult = $this->patientService->getOne($existingPid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingPid, intval($resultData["pid"]));
        $this->assertArrayHasKey("fname", $resultData);
        $this->assertArrayHasKey("lname", $resultData);
        $this->assertArrayHasKey("sex", $resultData);
        $this->assertArrayHasKey("DOB", $resultData);
        $this->assertArrayHasKey("uuid", $resultData);

        // getOne - validate uuid
        $expectedUuid = $resultData["uuid"];
        $actualResult = $this->patientService->getOne($expectedUuid, true);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingPid, intval($resultData["pid"]));
        $this->assertEquals($expectedUuid, $resultData["uuid"]);

        // getOne - with an invalid pid
        $actualResult = $this->patientService->getOne("not-a-pid");
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getOne - with an invalid uuid
        $actualResult = $this->patientService->getOne("not-a-uuid", true);
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getAll
        $actualResult = $this->patientService->getAll(array("state" => "CA"));
        $this->assertNotNull($actualResult);
        $this->assertGreaterThan(1, count($actualResult->getData()));

        foreach ($actualResult->getData() as $index => $patientRecord) {
            $this->assertArrayHasKey("fname", $resultData);
            $this->assertArrayHasKey("lname", $resultData);
            $this->assertArrayHasKey("sex", $resultData);
            $this->assertArrayHasKey("DOB", $resultData);
            $this->assertArrayHasKey("uuid", $resultData);
            $this->assertEquals("CA", $patientRecord["state"]);
        }
    }
}
