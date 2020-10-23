<?php

namespace OpenEMR\Tests\Fixtures;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;

/**
 * @coversDefaultClass OpenEMR\Tests\Fixture\FixtureManager
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
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

        $expectedFields = array("pubpid", "title", "fname", "mname",
         "lname", "ss", "street", "contact_relationship",
         "postal_code", "city", "state", "phone_contact",
         "phone_home", "phone_biz", "email", "DOB",
         "sex", "status", "drivers_license");

        $message = "Patient is missing";
        foreach ($expectedFields as $index => $expectedField) {
            $this->assertArrayHasKey($expectedField, $patientFixture, $message . " " . $expectedField);
        }

        $message = "Patient does not have a test pubpid ";
        $this->assertStringStartsWith("test-fixture", $patientFixture['pubpid'], $message . $patientFixture['pubpid']);
    }

    /**
     * Executes assertions on FHIR Patient resource fields
     * @param $fhirPatientFixture The FHIR patient fixture to validate
     */
    private function assertFhirPatientFields($fhirPatientFixture)
    {
        $this->assertNotNull($fhirPatientFixture);

        $actualMeta = $fhirPatientFixture['meta'];
        $this->assertNotNull($actualMeta['versionId']);
        $this->assertNotNull($actualMeta['lastUpdated']);

        $this->assertEquals('Patient', $fhirPatientFixture['resourceType']);

        $actualIdentifiers = $fhirPatientFixture['identifier'];
        $this->assertEquals(2, count($actualIdentifiers));

        $actualIdentifierCodes = array();
        foreach ($actualIdentifiers as $index => $actualIdentifier) {
            $actualCode = $actualIdentifier['type']['coding'][0]['code'];
            array_push($actualIdentifierCodes, $actualCode);
        }

        $this->assertContains('SS', $actualIdentifierCodes);
        $this->assertContains('PT', $actualIdentifierCodes);

        $this->assertTrue($fhirPatientFixture['active']);

        $actualNames = $fhirPatientFixture['name'];
        $this->assertEquals(1, count($actualNames));

        $actualName = $actualNames[0];
        $this->assertEquals('official', $actualName['use']);
        $this->assertNotNull($actualName['family']);
        $this->assertGreaterThanOrEqual(1, count($actualName['given']));
        $this->assertGreaterThanOrEqual(1, count($actualName['prefix']));

        $this->assertGreaterThanOrEqual(1, count($fhirPatientFixture['telecom']));
        foreach ($fhirPatientFixture['telecom'] as $index => $telecom) {
            $this->assertNotNull($telecom['system']);
            $this->assertNotNull($telecom['value']);
            $this->assertNotNull($telecom['use']);
        }

        $this->assertNotNull($fhirPatientFixture['gender']);
        $this->assertNotNull($fhirPatientFixture['birthDate']);

        $this->assertGreaterThanOrEqual(1, count($fhirPatientFixture['address']));
        foreach ($fhirPatientFixture['address'] as $index => $address) {
            $this->assertGreaterThanOrEqual(1, count($address['line']));
            $this->assertNotNull($address['city']);
            $this->assertNotNull($address['state']);
            $this->assertNotNull($address['postalCode']);
        }
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
        $recordCountResult = sqlQueryNoLog($recordCountSql, array("test-fixture%"));
        $recordCount = array_values($recordCountResult)[0];

        $this->assertEquals(0, $recordCount);
    }

    /**
     * @covers ::getFhirPatientFixtures
     */
    public function testGetFhirPatientFixtures()
    {
        $fhirPatientFixtures = $this->fixtureManager->getFhirPatientFixtures();
        $this->assertIsArray($fhirPatientFixtures);

        $actualCount = count($fhirPatientFixtures);
        $this->assertGreaterThanOrEqual(0, $actualCount);

        foreach ($fhirPatientFixtures as $index => $fhirPatientFixture) {
            $this->assertFhirPatientFields($fhirPatientFixture);
        }
    }
}
