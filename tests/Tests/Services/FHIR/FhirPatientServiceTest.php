<?php

namespace OpenEMR\Tests\Services\FHIR;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;

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
    private $fhirPatientFixture;
    private $fhirPatientService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->patientFixture = (array) $this->fixtureManager->getSinglePatientFixture();
        $this->fhirPatientFixture = (array) $this->fixtureManager->getSingleFhirPatientFixture();
        $this->fhirPatientService = new FhirPatientService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * Asserts an expected identifier value against an array of patient identifiers
     *
     * @param $expectedCode The identifier code type code
     * @param $expectedValue The expected identifer value (value set)
     * @param $actualIdentifiers FHIR Patient identifier entries
     */
    private function assertFhirPatientIdentifier($expectedCode, $expectedValue, $actualIdentifiers)
    {
        $matchFound = false;
        foreach ($actualIdentifiers as $index => $actualIdentifier) {
            if (!isset($actualIdentifier['type']['coding'][0])) {
                continue;
            }
            $codeTypeCode = $actualIdentifier['type']['coding'][0]['code'];
            $value = $actualIdentifier['value'];

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
        $this->assertFhirPatientIdentifier('SS', $sourcePatientRecord['ss'], $actualIdentifiers);
        $this->assertFhirPatientIdentifier('PT', $sourcePatientRecord['pubpid'], $actualIdentifiers);
    }

    /**
     * @covers ::parseOpenEMRRecord
     */
    public function testParseOpenEMRRecord()
    {
        $actualResult = $this->fhirPatientService->parseOpenEMRRecord($this->patientFixture, false);
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
    private function findTelecomEntry($fhirPatientResource, $telecomSystem, $telecomUse)
    {
        $matchingEntries = array();

        if (!isset($fhirPatientResource['telecom'])) {
            return $matchingEntries;
        }

        foreach ($fhirPatientResource['telecom'] as $index => $telecomEntry) {
            if ($telecomEntry['system'] == $telecomSystem && $telecomEntry['use'] == $telecomUse) {
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
    private function findIdentiferCodeValue($fhirPatientResource, $fhirCodeType)
    {
        $codeValue = null;

        foreach ($fhirPatientResource['identifier'] as $index => $identifier) {
            if (!isset($identifier['type']['coding'][0])) {
                continue;
            }

            $identifierCodeType = $identifier['type']['coding'][0]['code'];
            if ($identifierCodeType === $fhirCodeType) {
                $codeValue = $identifier['value'];
                break;
            }
        }
        return $codeValue;
    }
    /**
     * @covers ::parseFhirResource
     */
    public function testParseFhirResource()
    {
        $actualResult = $this->fhirPatientService->parseFhirResource($this->fhirPatientFixture);

        $title = $this->fhirPatientFixture['name'][0]['prefix'][0];
        $this->assertEquals($title, $actualResult['title']);

        $fname = $this->fhirPatientFixture['name'][0]['given'][0];
        $this->assertEquals($fname, $actualResult['fname']);

        $mname = $this->fhirPatientFixture['name'][0]['given'][1];
        $this->assertEquals($mname, $actualResult['mname']);

        $lname = $this->fhirPatientFixture['name'][0]['family'];
        $this->assertEquals($lname, $actualResult['lname']);

        $dob = $this->fhirPatientFixture['birthDate'];
        $this->assertEquals($dob, $actualResult['DOB']);

        $sex = $this->fhirPatientFixture['gender'];
        $this->assertEquals($sex, $actualResult['sex']);

        $ss = $this->findIdentiferCodeValue($this->fhirPatientFixture, 'SS');
        $this->assertEquals($ss, $actualResult['ss']);

        $pubpid = $this->findIdentiferCodeValue($this->fhirPatientFixture, 'PT');
        $this->assertEquals($pubpid, $actualResult['pubpid']);

        $address = $this->fhirPatientFixture['address'][0];

        $street = $address['line'][0];
        $this->assertEquals($street, $actualResult['street']);

        $city = $address['city'];
        $this->assertEquals($city, $actualResult['city']);

        $state = $address['state'];
        $this->assertEquals($state, $actualResult['state']);

        $postalCode = $address['postalCode'];
        $this->assertEquals($postalCode, $actualResult['postal_code']);

        $phoneCell = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'mobile');
        $this->assertEquals(1, count($phoneCell));
        $this->assertEquals($phoneCell[0]['value'], $actualResult['phone_cell']);

        $phoneHome = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'home');
        $this->assertEquals(1, count($phoneHome));
        $this->assertEquals($phoneHome[0]['value'], $actualResult['phone_home']);

        $phoneBiz = $this->findTelecomEntry($this->fhirPatientFixture, 'phone', 'work');
        $this->assertEquals(1, count($phoneBiz));
        $this->assertEquals($phoneBiz[0]['value'], $actualResult['phone_biz']);

        $email = $this->findTelecomEntry($this->fhirPatientFixture, 'email', 'home');
        $this->assertEquals(1, count($email));
        $this->assertEquals($email[0]['value'], $actualResult['email']);
    }

    /**
     * Tests a successful insert operation
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsert()
    {
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertTrue($processingResult->isValid());
        $this->assertGreaterThan(0, $processingResult->getData()[0]['pid']);
    }

    /**
     * Tests an insert operation where an error occurs
     * @covers ::insert
     * @covers ::insertOpenEMRRecord
     */
    public function testInsertWithErrors()
    {
        unset($this->fhirPatientFixture['name']);
        $processingResult = $this->fhirPatientService->insert($this->fhirPatientFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    /**
     * Tests a successful update operation
     * @covers ::update
     * @covers ::updateOpenEMRRecord
     */
    public function testUpdate()
    {
        $this->expectException(\Exception::class);
        $this->fhirPatientService->update('a-fhir-resource-id', $this->fhirPatientFixture);
    }

    /**
     * Tests an update operation where an error occurs
     * @covers ::update
     * @covers ::updateOpenEMRRecord
     */
    public function testUpdateWithErrors()
    {
        $this->expectException(\Exception::class);
        $this->fhirPatientService->update('a-fhir-resource-id', $this->fhirPatientFixture);
    }

    /**
     * @covers ::getOne
     */
    public function testGetOne()
    {
        $this->expectException(\Exception::class);
        $this->fhirPatientService->getOne('a-fhir-resource-id');
    }

    /**
     * Executes assertions against a 'GetAll' Patient Search processing result
     * @param $processingResult The OpenEMR Processing Result
     * @param $isExpectedToHaveAResult Indicates if the result is expected to have at least one search result
     */
    private function assertGetAllSearchResults($processingResult, $isExpectedToHaveAResult = true)
    {
        $this->assertTrue($processingResult->isValid());

        if ($isExpectedToHaveAResult) {
            $this->assertGreaterThan(0, count($processingResult->getData()));
        } else {
            $this->assertEquals(0, count($processingResult->getData()));
        }
    }

    /**
     * Tests getAll queries with the address parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllAddress()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('address' => 'Avenue');

        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertStringContainsString('Avenue', $fhirResource->getAddress()[0]->getLine()[0]);
        }

        $fhirSearchParameters = array('address' => '90210');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('90210', $fhirResource->getAddress()[0]->getPostalCode());
        }

        $fhirSearchParameters = array('address' => 'San Diego');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('San Diego', $fhirResource->getAddress()[0]->getCity());
        }

        $fhirSearchParameters = array('address' => 'CA');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('CA', $fhirResource->getAddress()[0]->getState());
        }
    }

    /**
     * Tests getAll queries with the address-city parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllAddressCity()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('address-city' => 'San Diego');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('San Diego', $fhirResource->getAddress()[0]->getCity());
        }
    }

    /**
     * Tests getAll queries with the address-postalcode parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllAddressPostalCode()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('address-postalcode' => '90210');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('90210', $fhirResource->getAddress()[0]->getPostalCode());
        }
    }

    /**
     * Tests getAll queries with the address-state parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllAddressState()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('address-state' => 'CA');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('CA', $fhirResource->getAddress()[0]->getState());
        }
    }

    /**
     * Tests getAll queries with the birthdate parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllBirthDate()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('birthdate' => '1960-01-01');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('1960-01-01', $fhirResource->getBirthDate());
        }
    }

    /**
     * Tests getAll queries with the email parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllEmail()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('email' => 'info@pennfirm.com');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        $isMatched = false;
        foreach ($processingResult->getData() as $index => $fhirResource) {
            foreach ($fhirResource->getTelecom() as $index => $telecom) {
                if ($telecom['system'] == 'email' && $telecom['value'] == 'info@pennfirm.com') {
                    $isMatched = true;
                    break;
                }
            }
            $this->assertTrue($isMatched);
            $isMatched = false;
        }
    }

    /**
     * Tests getAll queries with the family name parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllFamily()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('family' => 'Moses');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('Moses', $fhirResource->getName()[0]->getFamily());
        }
    }

    /**
     * Tests getAll queries with the gender parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllGender()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('gender' => 'male');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        foreach ($processingResult->getData() as $index => $fhirResource) {
            $this->assertEquals('male', $fhirResource->getGender());
        }
    }

    /**
     * Tests getAll queries with the given name parameter
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllGivenName()
    {
        $this->fixtureManager->installPatientFixtures();

        $fhirSearchParameters = array('given' => 'Eduardo');
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);

        $this->assertGetAllSearchResults($processingResult);

        $isMatched = false;
        foreach ($processingResult->getData() as $index => $fhirResource) {
            foreach ($fhirResource->getName()[0]->getGiven() as $index => $givenName) {
                if ($givenName == 'Eduardo') {
                    $isMatched = true;
                    break;
                }
            }
            $this->assertTrue($isMatched);
        }
    }

    /**
     * Utility function used to generate resources used as 'FHIR Patient Fixtures'
     */
    public function generateFhirPatientFixtures()
    {
        $patientFixtures = $this->fixtureManager->getPatientFixtures();
        
        foreach ($patientFixtures as $index => $patientFixture) {
            var_dump('-----------');
            $actualResult = $this->fhirPatientService->parseOpenEMRRecord($patientFixture, true);
            var_dump($actualResult);
            var_dump('-----------');
        }
    }
}
