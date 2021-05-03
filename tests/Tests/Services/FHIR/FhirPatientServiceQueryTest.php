<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;

/**
 * FHIR Patient Service Query Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPatientServiceQueryTest extends TestCase
{
    private $fixtureManager;
    private $patientFixture;
    private $fhirPatientFixture;

    /**
     * @var FhirPatientService
     */
    private $fhirPatientService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $this->fhirPatientService = new FhirPatientService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
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
     * PHPUnit Data Provider for FHIR patient searches
     */
    public function searchParameterDataProvider()
    {

        return [
            ['identifier', 'test-fixture-789456'],
            ['gender', 'male'],
            ['gender', 'female'],
            ['gender', 'unknown'], // handle unknown gender's

            // need to do a bunch of identifier tests to make sure our token searching is working.

            ['address:contains', 'Avenue'],
            ['address:prefix', '789'],
            ['address', '789'], // default is :prefix
            ['address:contains', 'Diego'],
            ['address:exact', '400 West Broadway'],

            // name searches
            ['name', 'Ilias'], // first name
            ['name', 'Ilias'],
            ['name', 'Johnny'],
            ['name', 'Jenane'],
            ['name', 'Mr.'], // title

            // if someone does a full timestamp birthdate, this tests the full timestamp parser even though
            // birthdate is just a date not a datetime
            ['birthdate', '1960-01-01T13:25:60'],

            // now combinations of birthdates
            ['birthdate', '1945'], // search by year
            ['birthdate', '1945-02'], // search by year, month
            ['birthdate', '1945-02-14'], // search by year, month, day

            // now let's do it with our equality search which should be the same
            ['birthdate', 'eq1945'], // search by year
            ['birthdate', 'eq1945-02'], // search by year, month
            ['birthdate', 'eq1945-02-14'], // search by year, month, day

            // now inequality search
            ['birthdate', 'ne1945'], // search by year
            ['birthdate', 'ne1945-02'], // search by year, month
            ['birthdate', 'ne1945-02-14'], // search by year, month, day

            // now we will do less than, only 1 patient in data set has DOB of 1933-03-22
            ['birthdate', 'lt1934'], // search by year
            ['birthdate', 'lt1933-04'], // search by year, month
            ['birthdate', 'lt1933-03-23'], // search by year, month, day

            // now we will do ends before, only 1 patient in data set has DOB of 1933-03-22
            ['birthdate', 'eb1934'], // search by year
            ['birthdate', 'eb1933-04'], // search by year, month
            ['birthdate', 'eb1933-03-23'], // search by year, month, day

            // now we will do greater than, only 1 patient in data set has DOB of 1977-05-02
            ['birthdate', 'gt1976'], // search by year
            ['birthdate', 'gt1977-04'], // search by year, month
            ['birthdate', 'gt1977-05-01'], // search by year, month, day

            // now we will do starts after, only 1 patient in data set has DOB of 1977-05-02
            ['birthdate', 'sa1976'], // search by year
            ['birthdate', 'sa1977-04'], // search by year, month
            ['birthdate', 'sa1977-05-01'], // search by year, month, day

            // now we will do less than or equal to, only 1 patient in data set has DOB of 1933-03-22
            ['birthdate', 'le1933'], // search by year
            ['birthdate', 'le1933-03'], // search by year, month
            ['birthdate', 'le1933-03-22'], // search by year, month, day

            // now we will do greater than or equal to, only 1 patient in data set has DOB of 1977-05-02
            ['birthdate', 'ge1977'], // search by year
            ['birthdate', 'ge1977-05'], // search by year, month
            ['birthdate', 'ge1977-05-02'], // search by year, month, day



            // range searches for dates.

            ['email', 'info@pennfirm.com'],
            ['family', 'Moses'],
            ['gender', 'male'],
            ['given', 'Eduardo'],
            ['name', 'Mr.'],
            ['name', 'Ilias'],
            ['name', 'Johnny'],
            ['name', 'Jenane'],
            ['phone', '(619) 555-4859'],
            ['phone', '(619) 555-7821'],
            ['phone', '(619) 555-7822'],
            ['telecom', 'info@pennfirm.com'],
            ['telecom', '(619) 555-4859'],
            ['telecom', '(619) 555-7821'],
            ['telecom', '(619) 555-7822'],
        ];
    }

    /**
     * PHPUnit Data Provider for FHIR patient searches
     */
    public function searchParameterCompoundDataProvider()
    {
        return [
            ['birthdate', 'le1960-01-01', 'name:contains', 'lias'], // check operators and comparators work combined
            ['birthdate', '1945', 'name', 'Moses'], // check defaults work
            ['birthdate', '1945', 'family', 'Moses'], // check birthdate+family works
            ['name', 'Ilias', 'birthdate', '1933-03'], // check name+birthdate work
            ['gender', 'female', 'name', 'Ilias'], // check gender+name works
            ['birthdate', '1933-03', 'gender', 'female'], // check birthdate+gender works
            ['name', 'Moses', 'gender', 'male'],
        ];
    }

    /**
     * Tests getAll queries
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     * @dataProvider searchParameterDataProvider
     */
    public function testGetAll($parameterName, $parameterValue)
    {
        $fhirSearchParameters = [$parameterName => $parameterValue];
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);
    }

    /**
     * Tests getAll queries for the _id search parameter.  Since we can't combine a dataProvider with our test fixture
     * installation, we run this test separately
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllWithUuid()
    {
        $select = "SELECT `uuid` FROM `patient_data` WHERE `pubpid`=?";
        $result = sqlStatement($select, ['test-fixture-789456']);
        $patient = sqlFetchArray($result);
        $fhirSearchParameters = ['_id' => UuidRegistry::uuidToString($patient['uuid'])];
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);
    }

    /**
     * Tests getAll compound search queries
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     * @dataProvider searchParameterCompoundDataProvider
     */
    public function testGetAllCompound($parameter1, $parameter1Value, $parameter2, $parameter2Value)
    {
        $fhirSearchParameters = [$parameter1 => $parameter1Value, $parameter2 => $parameter2Value];
        $processingResult = $this->fhirPatientService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);
    }

    /**
     * Uses the getAll method so we can't pass unless that is working.
     * @covers ::getOne
     */
    public function testGetOne()
    {
        $actualResult = $this->fhirPatientService->getAll([]);
        $this->assertNotEmpty($actualResult->getData(), "Get All should have returned a result");

        $this->assertInstanceOf(FhirPatient::class, $actualResult->getData()[0], "Instance returned should have been the correct patient class");
        $expectedId = $actualResult->getData()[0]->getId()->getValue();

        $actualResult = $this->fhirPatientService->getOne($expectedId);
        $this->assertGreaterThan(0, $actualResult->getData());
        $actualId = $actualResult->getData()[0]->getId()->getValue();

        $this->assertEquals($expectedId, $actualId);
    }

       /**
     * @covers ::getOne with an invalid uuid
     */
    public function testGetOneInvalidUuid()
    {
        $actualResult = $this->fhirPatientService->getOne('not-a-uuid');
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getInternalErrors()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
