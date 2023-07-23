<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\Services\FHIR\Serialization\FhirPatientSerializer;
use OpenEMR\Services\PatientService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;

/**
 * FHIR Patient Service Mapping Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPatientServiceMappingTest extends TestCase
{
    private $fixtureManager;
    private $patientFixture;

    /**
     * @var FHIRPatient
     */
    private $fhirPatientFixture;

    /**
     * @var FhirPatientService
     */
    private $fhirPatientService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = (array) $this->fixtureManager->getSinglePatientFixtureWithAddressInformation();
        $fixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
//        var_dump($fixture);
        $this->fhirPatientFixture = FhirPatientSerializer::deserialize($fixture);
//        var_dump($this->fhirPatientFixture);
//        die();
        $this->fhirPatientService = new FhirPatientService();
    }

    /**
     * Asserts that a FHIR Patient Resource aligns with it's source OpenEMR Patient record
     * @param $fhirPatientResource A FHIR Patient Resource
     * @param $sourcePatientRecord The OpenEMR Patient Record
     */
    private function assertFhirPatientResource(FHIRPatient $fhirPatientResource, $sourcePatientRecord)
    {
        $this->assertEquals($sourcePatientRecord['uuid'], $fhirPatientResource->getId());
        $this->assertEquals(1, $fhirPatientResource->getMeta()->getVersionId());
        $this->assertNotEmpty($fhirPatientResource->getMeta()->getLastUpdated());

        $this->assertEquals('generated', $fhirPatientResource->getText()['status']);
        $this->assertNotEmpty($fhirPatientResource->getText()['div']);

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
        $patientAddress = $sourcePatientRecord['addresses'][0];
        // TODO: we should add period validation here...
        $this->assertEquals($patientAddress['use'], $actualAddress->getUse());
        $this->assertEquals($patientAddress['type'], $actualAddress->getType());
        $this->assertEquals($patientAddress['line1'], $actualAddress->getLine()[0]);
        $this->assertEquals($patientAddress['city'], $actualAddress->getCity());
        $this->assertEquals($patientAddress['state'], $actualAddress->getState());
        $this->assertEquals($patientAddress['postal_code'], $actualAddress->getPostalCode());

        $actualTelecoms = $fhirPatientResource->getTelecom();
        $this->assertFhirPatientTelecom('phone', 'home', $sourcePatientRecord['phone_home'], $actualTelecoms);
        $this->assertFhirPatientTelecom('phone', 'work', $sourcePatientRecord['phone_biz'], $actualTelecoms);
        $this->assertFhirPatientTelecom('phone', 'mobile', $sourcePatientRecord['phone_cell'], $actualTelecoms);
        $this->assertFhirPatientTelecom('email', 'home', $sourcePatientRecord['email'], $actualTelecoms);

        $actualIdentifiers = $fhirPatientResource->getIdentifier();
        $this->assertFhirPatientIdentifier('SS', $sourcePatientRecord['ss'], $actualIdentifiers);
        $this->assertFhirPatientIdentifier('PT', $sourcePatientRecord['pubpid'], $actualIdentifiers);
    }

    /**
     * Asserts an expected telecom/contact point entry against an array of telecoms/contact points
     * @param $expectedSystem The telecom/contact point system to match
     * @param $expectedUse The telecom/contact point use to match
     * @param $expectedValue The expected telecom/contact point value
     * @param $actualTelecoms FHIRContactPoint[] FHIR Patient Resource telecom entries
     */
    private function assertFhirPatientTelecom($expectedSystem, $expectedUse, $expectedValue, $actualTelecoms)
    {
        $matchFound = false;

        foreach ($actualTelecoms as $index => $actualTelecom) {
            if (
                $expectedSystem == $actualTelecom->getSystem()->getValue() &&
                $expectedUse == $actualTelecom->getUse()->getValue() &&
                $expectedValue == $actualTelecom->getValue()->getValue()
            ) {
                $matchFound = true;
                break;
            }
        }

        $this->assertTrue($matchFound);
    }

        /**
     * Asserts an expected identifier value against an array of patient identifiers
     *
     * @param $expectedCode The identifier code type code
     * @param $expectedValue The expected identifer value (value set)
     * @param $actualIdentifiers FHIRIdentifier[] FHIR Patient identifier entries
     */
    private function assertFhirPatientIdentifier($expectedCode, $expectedValue, $actualIdentifiers)
    {
        $matchFound = false;
        foreach ($actualIdentifiers as $index => $actualIdentifier) {
            $type = $actualIdentifier->getType();
            if (isset($type) && empty($type->getCoding())) {
                continue;
            }
            $coding = $type->getCoding();

            $codeTypeCode = $coding[0]->getCode();
            $value = $actualIdentifier->getValue()->getValue();

            if (
                $expectedCode == $codeTypeCode &&
                $expectedValue == $value
            ) {
                $matchFound = true;
                break;
            }
        }
        $this->assertTrue($matchFound);
    }

    /**
     * @covers ::parseOpenEMRRecord
     */
    public function testParseOpenEMRRecord()
    {
        $this->patientFixture['uuid'] = $this->fixtureManager->getUnregisteredUuid();
        $actualResult = $this->fhirPatientService->parseOpenEMRRecord($this->patientFixture);
        $this->assertFhirPatientResource($actualResult, $this->patientFixture);

        $actualResult = $this->fhirPatientService->parseOpenEMRRecord($this->patientFixture, true);
        $this->assertIsString($actualResult);
    }
    /**
     * Finds matching FHIR Patient telecom entries by system and use.
     * @param $fhirPatientResource - The FHIR Patient Resource to search
     * @param $telecomSystem - The telecom system to match
     * @param $telecomUse - The telecom use to match
     * @return matching entries (array)
     */
    private function findTelecomEntry(FHIRPatient $fhirPatientResource, $telecomSystem, $telecomUse)
    {
        $matchingEntries = array();

        if (empty($fhirPatientResource->getTelecom())) {
            return $matchingEntries;
        }

        foreach ($fhirPatientResource->getTelecom() as $index => $telecomEntry) {
            if ($telecomEntry->getSystem() == $telecomSystem && $telecomEntry->getUse() == $telecomUse) {
                array_push($matchingEntries, $telecomEntry);
            }
        }
        return $matchingEntries;
    }

    /**
     * Searches a FHIR R4 Patient resource for an identifier code value.
     * @param $fhirPatientResource The FHIR Patient resource to search
     * @param $fhirCodeType The code to lookup
     * @return the code value if found, otherwise null
     */
    private function findIdentiferCodeValue(FHIRPatient $fhirPatientResource, $fhirCodeType): ?string
    {
        $codeValue = null;

        foreach ($fhirPatientResource->getIdentifier() as $index => $identifier) {
            if (empty($identifier->getType()->getCoding())) {
                continue;
            }
            $identifierCodeType = $identifier->getType()->getCoding()[0]->getCode();
            if ($identifierCodeType === $fhirCodeType) {
                $codeValue = $identifier->getValue();
                break;
            }
        }
        return (string)$codeValue;
    }
    /**
     * @covers ::parseFhirResource
     */
    public function testParseFhirResource()
    {
        $actualResult = $this->fhirPatientService->parseFhirResource($this->fhirPatientFixture);

        $id = $this->fhirPatientFixture->getId();
        $this->assertEquals($id, $actualResult['uuid']);

        $title = $this->fhirPatientFixture->getName()[0]->getPrefix()[0];
        $this->assertEquals($title, $actualResult['title']);

        $fname = $this->fhirPatientFixture->getName()[0]->getGiven()[0];
        $this->assertEquals($fname, $actualResult['fname']);

        $mname = $this->fhirPatientFixture->getName()[0]->getGiven()[1];
        $this->assertEquals($mname, $actualResult['mname']);

        $lname = $this->fhirPatientFixture->getName()[0]->getFamily();
        $this->assertEquals($lname, $actualResult['lname']);

        $dob = $this->fhirPatientFixture->getBirthDate();
        $this->assertEquals($dob, $actualResult['DOB']);

        $sex = $this->fhirPatientFixture->getGender();
        $this->assertEquals($sex, $actualResult['sex']);

        $ss = $this->findIdentiferCodeValue($this->fhirPatientFixture, 'SS');
        $this->assertEquals($ss, $actualResult['ss']);

        $pubpid = $this->findIdentiferCodeValue($this->fhirPatientFixture, 'PT');
        $this->assertEquals($pubpid, $actualResult['pubpid']);

        $address = $this->fhirPatientFixture->getAddress()[0];

        $street = $address->getLine()[0];
        $this->assertEquals($street, $actualResult['street']);

        $city = $address->getCity();
        $this->assertEquals($city, $actualResult['city']);

        $state = $address->getState();
        $this->assertEquals($state, $actualResult['state']);

        $postalCode = $address->getPostalCode();
        $this->assertEquals($postalCode, $actualResult['postal_code']);

        $phoneCell = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'mobile');
        $this->assertEquals(1, count($phoneCell));
        $this->assertEquals($phoneCell[0]->getValue(), $actualResult['phone_cell']);

        $phoneHome = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'home');
        $this->assertEquals(1, count($phoneHome));
        $this->assertEquals($phoneHome[0]->getValue(), $actualResult['phone_home']);

        $phoneBiz = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'work');
        $this->assertEquals(1, count($phoneBiz));
        $this->assertEquals($phoneBiz[0]->getValue(), $actualResult['phone_biz']);

        $email = $this->findTelecomEntry($this->fhirPatientFixture, 'email', 'home');
        $this->assertEquals(1, count($email));
        $this->assertEquals($email[0]->getValue(), $actualResult['email']);
    }
}
