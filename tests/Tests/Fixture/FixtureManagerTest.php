<?php

namespace OpenEMR\Tests\Fixture;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixture\FixtureManager;

/**
 * Tests FixtureManager
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FixtureManagerTest extends TestCase
{
    private $fixtureManager;

    public function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
    }

    /**
     * Validates that patient fixtures utilize core fields with a "test case specific" pubpid
     */
    public function testGetPatientFixtures()
    {
        $patientFixtures = $this->fixtureManager->getPatientFixtures();
        $this->assertIsArray($patientFixtures);
        $this->assertGreaterThan(0, count($patientFixtures));
        
        foreach ($patientFixtures as $index => $patientFixture) {
            $message = "Patient " . $index . " is missing";
            $this->assertObjectHasAttribute("pubpid", $patientFixture, $message . " pubpid");
            $this->assertObjectHasAttribute("fname", $patientFixture, $message . " fname");
            $this->assertObjectHasAttribute("lname", $patientFixture, $message . " lname");
            $this->assertObjectHasAttribute("DOB", $patientFixture, $message . " DOB");

            $message = "Patient " . $index . " does not have a test pubpid ";
            $this->assertStringStartsWith("test-fixture", $patientFixture->pubpid, $message . $patientFixture->pubpid);
        }
    }

    /**
     * Validates loading and deleting Patient Fixtures
     */
    public function testInstallAndRemovePatientFixtures()
    {
        $actualCount = $this->fixtureManager->installPatientFixtures();
        $this->assertGreaterThan(0, $actualCount);

        $this->fixtureManager->removePatientFixtures();

        $recordCountSql = "SELECT COUNT(*) FROM patient_data WHERE pubpid LIKE ?";
        $recordCountResult = sqlQueryNoLog($recordCountSql, array("test-fixture%"));
        $recordCount = array_values($recordCountResult)[0];

        $this->assertEquals(0, $recordCount);
    }
}
