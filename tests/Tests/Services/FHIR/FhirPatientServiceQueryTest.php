<?php

namespace OpenEMR\Tests\Services\FHIR;

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
            ['address', 'Avenue'],
            ['address', '90210'],
            ['address', 'San Diego'],
            ['address', 'CA'],
            ['address-city', 'San Diego'],
            ['address-postalcode', '90210'],
            ['address-state', 'CA'],
            ['birthdate', '1960-01-01'],
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
     * @covers ::getOne
     */
    public function testGetOne()
    {
        $actualResult = $this->fhirPatientService->getAll(['state' => 'CA']);
        $this->assertGreaterThan(0, $actualResult->getData());

        $expectedId = $actualResult->getData()[0]->getId();

        $actualResult = $this->fhirPatientService->getOne($expectedId);
        $this->assertGreaterThan(0, $actualResult->getData());
        $actualId = $actualResult->getData()[0]->getId();

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
