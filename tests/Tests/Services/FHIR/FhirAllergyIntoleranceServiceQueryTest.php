<?php
/**
 * FHIR Allergy Intolerance Service Query Tests
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPatientService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Fixtures\FixtureManager;

class FhirAllergyIntoleranceServiceQueryTest extends TestCase
{
    private $fixtureManager;
    private $patientFixture;
    private $fhirPatientFixture;

    /**
     * @var FhirAllergyIntoleranceService
     */
    private $fhirService;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installAllergyIntoleranceFixtures();
        $this->fhirService = new FhirAllergyIntoleranceService();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeAllergyIntoleranceFixtures();
    }

    /**
     * Executes assertions against a 'GetAll' AllergyIntolerance Search processing result
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

    private function getReferenceURL($reference) {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $fhirUrl =  $baseUrl . "/apis/default/fhir/";
        return $fhirUrl . $reference;
    }

    /**
     * PHPUnit Data Provider for FHIR AllergyIntolerance searches
     */
    public function searchParameterDataProvider()
    {
        $pubpid = FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $select = "SELECT `uuid` FROM `lists` WHERE `type`='allergy' AND pid IN (select id FROM patient_data WHERE pubpid LIKE ?) LIMIT 2";
        $allergy_uuids = QueryUtils::fetchTableColumn($select, 'uuid', [$pubpid]);
        $uuids = array_map(function($v) { return UuidRegistry::uuidToString($v); }, $allergy_uuids);
        list($uuidPatient1, $uuidPatient2) = $uuids;


        return [
//            ['patient', "Patient/$uuidPatient1"],
//            ['patient', "$uuidPatient1"],
//            // make sure we can handle different ids
//            ['patient', "Patient/$uuidPatient2"],
//            ['patient', "$uuidPatient2"],
//
//            // full URL resolution
//            ["patient", $this->getReferenceURL("Patient/$uuidPatient1")],
//            ["patient", $this->getReferenceURL("Patient/$uuidPatient2")],

            // multiple select
//            ['patient', "Patient/$uidPatient1,Patient/$uuidPatient2"],
//            ['patient', "$uidPatient1,$uuidPatient2"],
        ];
    }

    /**
     * Tests getAll queries
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     * @dataProvider searchParameterDataProvider
     */
//    public function testGetAll($parameterName, $parameterValue)
//    {
//        $fhirSearchParameters = [$parameterName => $parameterValue];
//        $processingResult = $this->fhirService->getAll($fhirSearchParameters);
//        $this->assertGetAllSearchResults($processingResult);
//    }

    /**
     * Tests getAll queries for the _id search parameter.  Since we can't combine a dataProvider with our test fixture
     * installation, we run this test separately
     * @covers ::getAll
     * @covers ::searchForOpenEMRRecords
     */
    public function testGetAllWithUuid()
    {
        $select = "SELECT `uuid` FROM `lists` WHERE `type`='allergy' LIMIT 1";
        $allergy_uuid = QueryUtils::fetchSingleValue($select, 'uuid');
        $fhirSearchParameters = ['_id' => UuidRegistry::uuidToString($allergy_uuid)];
        $processingResult = $this->fhirService->getAll($fhirSearchParameters);
        $this->assertGetAllSearchResults($processingResult);
    }

    /**
     * Uses the getAll method so we can't pass unless that is working.
     * @covers ::getOne
     */
    public function testGetOne()
    {
        $actualResult = $this->fhirService->getAll([]);
        $this->assertNotEmpty($actualResult->getData(), "Get All should have returned a result");

        $this->assertInstanceOf(FHIRAllergyIntolerance::class, $actualResult->getData()[0], "Instance returned should have been the correct AllergyIntolerance class");
        $expectedId = $actualResult->getData()[0]->getId()->getValue();

        $actualResult = $this->fhirService->getOne($expectedId);
        $this->assertGreaterThan(0, $actualResult->getData());
        $actualId = $actualResult->getData()[0]->getId()->getValue();

        $this->assertEquals($expectedId, $actualId);
    }

//       /**
//     * @covers ::getOne with an invalid uuid
//     */
//    public function testGetOneInvalidUuid()
//    {
//        $this->expectException(\InvalidArgumentException::class);
//        $actualResult = $this->fhirService->getOne('not-a-uuid');
//        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
//        $this->assertEquals(0, count($actualResult->getInternalErrors()));
//        $this->assertEquals(0, count($actualResult->getData()));
//    }
}
