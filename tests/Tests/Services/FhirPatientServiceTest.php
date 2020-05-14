<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Services\FHIR\FhirPatientService;

/**
 * FHIR Patient Service Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPatientServiceTest extends TestCase
{
    private $fixtureManager;
    private $patientFixture;
    private $fhirPatientService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = (array) $this->fixtureManager->getSinglePatientFixture();
        $this->fhirPatientService = new FhirPatientService();
    }

    /**
     * Asserts an expected identifier value against an array of patient identifiers
     *
     * @param $expectedSystem The identifier system to match
     * @param $expectedValue The expected identifer value
     * @param $actualIdentifiers FHIR Patient identifier entries
     */
    private function assertFhirPatientIdentifier($expectedSystem, $expectedValue, $actualIdentifiers)
    {
        $matchFound = false;
        foreach ($actualIdentifiers as $index => $actualIdentifier) {
            if (
                $expectedSystem == $actualIdentifier['system'] &&
                $expectedValue == $actualIdentifier['value']
            ) {
                $matchFound = true;
                break;
            }
        }

        $this->assertTrue($matchFound);
    }

    /**
     * Asserts an expected telecom/contact point entry against an array of telecoms/contact points
     * @param $expectedSystem The telecom/contact point system to match
     * @param $expectedUse The telecom/contact point use to match
     * @param $expectedValue The expected telecom/contact point value
     * @param $actualTelecoms FHIR Patient Resource telecom entries
     */
    private function assertFhirPatientTelecom($expectedSystem, $expectedUse, $expectedValue, $actualTelecoms)
    {
        $matchFound = false;

        foreach ($actualTelecoms as $index => $actualTelecom) {
            if (
                $expectedSystem == $actualTelecom['system'] &&
                $expectedUse == $actualTelecom['use'] &&
                $expectedValue == $actualTelecom['value']
            ) {
                $matchFound = true;
                break;
            }
        }

        $this->assertTrue($matchFound);
    }

    /**
     * Asserts that a FHIR Patient Resource aligns with it's source OpenEMR Patient record
     * @param $fhirPatientResource A FHIR Patient Resource
     * @param $sourcePatientRecord The OpenEMR Patient Record
     */
    private function assertFhirPatientResource($fhirPatientResource, $sourcePatientRecord)
    {
        $this->assertEquals(1, $fhirPatientResource->getMeta()['versionId']);
        $this->assertNotEmpty($fhirPatientResource->getMeta()['lastUpdated']);
        
        $this->assertTrue($fhirPatientResource->getActive());

        $this->assertNotEmpty($fhirPatientResource->getId());

        $this->assertEquals(1, count($fhirPatientResource->getName()));
        $actualName = $fhirPatientResource->getName()[0];
        $this->assertEquals('official', $actualName->getUse());

        $this->assertEquals(1, count($actualName->getPrefix()));
        $this->assertEquals($sourcePatientRecord['title'], $actualName->getPrefix()[0]);
        
        $this->assertEquals($sourcePatientRecord['lname'], $actualName->getFamily());
        $this->assertEquals(array(
            $sourcePatientRecord['fname'],
            $sourcePatientRecord['mname']), $actualName->getGiven());

        $this->assertEquals(1, count($fhirPatientResource->getAddress()));
        $actualAddress = $fhirPatientResource->getAddress()[0];
        $this->assertEquals(1, count($actualAddress->getLine()));
        $this->assertEquals($sourcePatientRecord['street'], $actualAddress->getLine()[0]);
        $this->assertEquals($sourcePatientRecord['city'], $actualAddress->getCity());
        $this->assertEquals($sourcePatientRecord['state'], $actualAddress->getState());
        $this->assertEquals($sourcePatientRecord['postal_code'], $actualAddress->getPostalCode());

        $actualTelecoms = $fhirPatientResource->getTelecom();
        $this->assertFhirPatientTelecom('phone', 'home', $sourcePatientRecord['phone_home'], $actualTelecoms);
        $this->assertFhirPatientTelecom('phone', 'work', $sourcePatientRecord['phone_biz'], $actualTelecoms);
        $this->assertFhirPatientTelecom('phone', 'mobile', $sourcePatientRecord['phone_cell'], $actualTelecoms);
        $this->assertFhirPatientTelecom('email', 'home', $sourcePatientRecord['email'], $actualTelecoms);

        $actualIdentifiers = $fhirPatientResource->getIdentifier();
        $this->assertFhirPatientIdentifier('http://hl7.org/fhir/sid/us-ssn', $sourcePatientRecord['ss'], $actualIdentifiers);
    }

    /**
     * @covers ::parseOpenEMRRecord
     */
    public function testParseOpenEMRRecord()
    {
        $actualResult = $this->fhirPatientService->parseOpenEMRRecord($this->patientFixture, false);
        $this->assertFhirPatientResource($actualResult, $this->patientFixture);
    }
}
