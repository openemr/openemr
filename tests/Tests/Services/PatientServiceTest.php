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
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
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
        $this->patientFixture = (array) $this->fixtureManager->getPatientFixture();
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
        $this->assertGreaterThan(0, $actualResult->getData());
    }

    /**
     * @covers ::update when the data is not valid
     */
    public function testUpdateFailure()
    {
        $this->patientService->insert($this->patientFixture);
        $this->patientFixture["fname"] = "A";

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
        
        $fixturePid = $actualResult->getData();
        $this->patientFixture["phone_home"] = "555-111-4444";

        $actualResult = $this->patientService->update($fixturePid, $this->patientFixture);

        $sql = "SELECT pid, phone_home FROM patient_data WHERE pid = ?";
        $result = sqlQuery($sql, array($fixturePid));

        $this->assertEquals($fixturePid, intval($result["pid"]));
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
        $this->patientService->setPid($existingPid);

        // getOne
        $actualResult = $this->patientService->getOne();
        $resultData = $actualResult->getData();
        $this->assertNotNull($resultData);
        $this->assertEquals($existingPid, intval($resultData["pid"]));
        $this->assertArrayHasKey("fname", $resultData);
        $this->assertArrayHasKey("lname", $resultData);
        $this->assertArrayHasKey("sex", $resultData);
        $this->assertArrayHasKey("DOB", $resultData);

        // getAll
        $actualResult = $this->patientService->getAll(array("state" => "CA"));
        $resultData = $actualResult->getData();
        $this->assertNotNull($resultData);
        $this->assertGreaterThan(0, count($resultData));

        foreach ($resultData as $key => $value) {
            $this->assertEquals("CA", $value["state"]);
        }
    }
}
