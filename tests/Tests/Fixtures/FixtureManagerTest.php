<?php

namespace OpenEMR\Tests\Fixture;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * @coversDefaultClass OpenEMR\Tests\Fixture\FixtureManager.
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
     * Executes assertions on Patient Fixture Fields
     * @param $patientFixture - The patient fixture to validate
     */
    private function assertPatientFields($patientFixture)
    {
        $this->assertNotNull($patientFixture);

        $message = "Patient is missing";
        $this->assertObjectHasAttribute("pubpid", $patientFixture, $message . " pubpid");
        $this->assertObjectHasAttribute("fname", $patientFixture, $message . " fname");
        $this->assertObjectHasAttribute("lname", $patientFixture, $message . " lname");
        $this->assertObjectHasAttribute("DOB", $patientFixture, $message . " DOB");
        $this->assertObjectHasAttribute("sex", $patientFixture, $message . " sex");

        $message = "Patient does not have a test pubpid ";
        $this->assertStringStartsWith(FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX, $patientFixture->pubpid, $message . $patientFixture->pubpid);
    }

    /**
     * @covers ::getPatientFixtures
     */
    public function testGetPatientFixtures()
    {
        $patientFixtures = $this->fixtureManager->getPatientFixtures();
        $this->assertIsArray($patientFixtures);
        $this->assertGreaterThan(0, count($patientFixtures));
        
        foreach ($patientFixtures as $index => $patientFixture) {
            $this->assertPatientFields($patientFixture);
        }
    }

    /**
     * @covers ::getPatientFixture
     */
    public function testGetPatientFixture()
    {
        $patientFixture = $this->fixtureManager->getSinglePatientFixture();
        $this->assertPatientFields($patientFixture);
    }

    /**
     * @covers ::installPatientFixtures
     * @covers ::removePatientFixtures
     */
    public function testInstallAndRemovePatientFixtures()
    {
        $actualCount = $this->fixtureManager->installPatientFixtures();
        $this->assertGreaterThan(0, $actualCount);

        $this->fixtureManager->removePatientFixtures();

        $recordCountSql = "SELECT COUNT(*) FROM patient_data WHERE pubpid LIKE ?";
        $bindVariable = FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $recordCountResult = sqlQueryNoLog($recordCountSql, array($bindVariable));
        $recordCount = array_values($recordCountResult)[0];

        $this->assertEquals(0, $recordCount);
    }
}
