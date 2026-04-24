<?php

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Patient Service Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

class PatientServiceTest extends TestCase
{
    /**
     * @var PatientService
     */
    private $patientService;
    private $fixtureManager;

    private array $patientFixture;

    protected function setUp(): void
    {
        $this->patientService = new PatientService();
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = $this->fixtureManager->getSinglePatientFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    #[Test]
    public function testGetFreshPid(): void
    {
        $actualValue = $this->patientService->getFreshPid();
        $this->assertGreaterThan(0, $actualValue);
    }

    #[Test]
    public function testInsertFailure(): void
    {
        $this->patientFixture["fname"] = "";
        $this->patientFixture["DOB"] = "12/27/2017";
        if (isset($this->patientFixture["sex"])) {
            unset($this->patientFixture["sex"]);
        }

        $actualResult = $this->patientService->insert($this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("sex", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("DOB", $actualResult->getValidationMessages());
        $this->assertEquals(3, count($actualResult->getValidationMessages()));
    }

    #[Test]
    public function testInsertSuccess(): void
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

    #[Test]
    public function testUpdateFailure(): void
    {
        $this->patientService->insert($this->patientFixture);

        $this->patientFixture["fname"] = "";

        $actualResult = $this->patientService->update("not-a-uuid", $this->patientFixture);

        $this->assertFalse($actualResult->isValid());

        $this->assertArrayHasKey("fname", $actualResult->getValidationMessages());
        $this->assertArrayHasKey("uuid", $actualResult->getValidationMessages());
        $this->assertEquals(2, count($actualResult->getValidationMessages()));
    }

    #[Test]
    public function testUpdateSuccess(): void
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $this->assertEquals(1, count($actualResult->getData()));

        $dataResult = $actualResult->getData()[0];
        $this->assertIsArray($dataResult);
        $this->assertArrayHasKey("pid", $dataResult);
        $this->assertGreaterThan(0, $dataResult["pid"]);
        $this->assertArrayHasKey("uuid", $dataResult);

        $actualUuid = $dataResult["uuid"];

        $this->patientFixture["phone_home"] = "555-111-4444";
        $this->patientService->update($actualUuid, $this->patientFixture);

        $sql = "SELECT `uuid`, `phone_home` FROM `patient_data` WHERE `uuid` = ?";
        $result = sqlQuery($sql, [UuidRegistry::uuidToBytes($actualUuid)]);
        $this->assertEquals($actualUuid, UuidRegistry::uuidToString($result["uuid"]));
        $this->assertEquals("555-111-4444", $result["phone_home"]);
    }

    #[Test]
    public function testUpdateBackfillsPortalLoginUsername(): void
    {
        $actualResult = $this->patientService->insert($this->patientFixture);
        $this->assertTrue($actualResult->isValid());
        $data = $actualResult->getData();
        $this->assertIsArray($data);
        $dataResult = $data[0];
        $this->assertIsArray($dataResult);
        $pid = $dataResult['pid'];

        // Create portal credentials with a username but empty login username
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_access_onsite (pid, portal_username, portal_login_username) VALUES (?, ?, '')",
            [$pid, 'testportaluser']
        );

        try {
            // Update patient with portal access enabled via databaseUpdate() —
            // this is the code path used by demographics_save.php where the fix lives
            $this->patientFixture['pid'] = $pid;
            $this->patientFixture['allow_patient_portal'] = 'YES';
            $this->patientService->databaseUpdate($this->patientFixture);

            $row = QueryUtils::querySingleRow("SELECT portal_login_username FROM patient_access_onsite WHERE pid = ?", [$pid]);
            $this->assertIsArray($row);
            $this->assertSame('testportaluser', $row['portal_login_username']);
        } finally {
            QueryUtils::sqlStatementThrowException("DELETE FROM patient_access_onsite WHERE pid = ?", [$pid]);
        }
    }

    #[Test]
    public function testPatientQueries(): void
    {
        $this->fixtureManager->installPatientFixtures();

        $result = sqlQuery("SELECT `uuid` FROM `patient_data`");
        $existingUuid = UuidRegistry::uuidToString($result['uuid']);

        // getOne
        $actualResult = $this->patientService->getOne($existingUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($existingUuid, $resultData["uuid"]);
        $this->assertArrayHasKey("fname", $resultData);
        $this->assertArrayHasKey("lname", $resultData);
        $this->assertArrayHasKey("sex", $resultData);
        $this->assertArrayHasKey("DOB", $resultData);
        $this->assertArrayHasKey("uuid", $resultData);

        // getOne - validate uuid
        $expectedUuid = $resultData["uuid"];
        $actualResult = $this->patientService->getOne($expectedUuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($expectedUuid, $resultData["uuid"]);

        // getOne - with an invalid uuid
        $actualResult = $this->patientService->getOne("not-a-uuid");
        $this->assertEquals(1, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));

        // getAll
        $actualResult = $this->patientService->getAll(["postal_code" => "90210"]);
        $this->assertNotNull($actualResult);
        $this->assertGreaterThan(1, count($actualResult->getData()));

        foreach ($actualResult->getData() as $patientRecord) {
            $this->assertArrayHasKey("fname", $resultData);
            $this->assertArrayHasKey("lname", $resultData);
            $this->assertArrayHasKey("sex", $resultData);
            $this->assertArrayHasKey("DOB", $resultData);
            $this->assertArrayHasKey("uuid", $resultData);
            $this->assertEquals("90210", $patientRecord["postal_code"]);
        }
    }
}
