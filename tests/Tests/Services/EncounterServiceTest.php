<?php

/**
 * EncounterServiceTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\EncounterService;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EncounterServiceTest extends TestCase
{
    /**
     * @var EncounterService
     */
    private $service;

    /**
     * @var EncounterFixtureManager
     */
    private $fixtureManager;

    /** @var array<string, mixed> */
    private $fixture;

    protected function setUp(): void
    {
        $this->service = new EncounterService();
        $this->fixtureManager = new EncounterFixtureManager();
        $this->fixture = (array) $this->fixtureManager->getSingleFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    #[Test]
    public function testGetOne(): void
    {
        $this->fixtureManager->installFixtures();

        // attempt to verify the uuid surrogate key is working correctly
        $uuid = QueryUtils::fetchSingleValue("SELECT `uuid`,`encounter` FROM `form_encounter`", "uuid");
        $uuidString = UuidRegistry::uuidToString($uuid);
        // getOne
        $actualResult = $this->service->getEncounter($uuidString);
        $this->assertNotNull($actualResult, "Processing result should be returned");
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
    }

    #[Test]
    public function testSearchWithBoundPatientUUID(): void
    {
        $this->fixtureManager->installFixtures();

        // attempt to verify the uuid surrogate key is working correctly
        $uuid = QueryUtils::fetchSingleValue("SELECT `pd`.`uuid` FROM `form_encounter` fe "
        . " JOIN `patient_data` `pd` ON `fe`.pid = `pd`.`pid`", "uuid");
        $uuidString = UuidRegistry::uuidToString($uuid);
        // getOne
        $actualResult = $this->service->search([], true, $uuidString);
        $this->assertNotNull($actualResult, "Processing result should be returned");
        $this->assertNotEmpty($actualResult->getData(), "Search result should have returned a result");
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        $this->assertEquals($uuidString, $resultData['puuid'], "Patient uuid should match bound patient");
    }
    private const FIXTURE_DATE_OF_SERVICE = '2024-03-05 10:15:00';

    /**
     * Reads the test-fixture encounter with typed fields, so the assertions below narrow instead of
     * casting. The shared fixture stores no date, so a known one is written first: the date
     * assertions compare against a real value, not empty against empty.
     *
     * @return array{encounter: int|string, date: string, pc_catid: int}
     */
    private function fixtureEncounter(): array
    {
        $row = QueryUtils::querySingleRow("SELECT `encounter`, `pc_catid` FROM `form_encounter` WHERE `reason` LIKE 'test-fixture-%' LIMIT 1");
        $this->assertIsArray($row);
        $encounter = $row['encounter'] ?? null;
        if (!is_int($encounter) && !is_string($encounter)) {
            self::fail('fixture encounter id must be int or string');
        }
        $categoryId = $row['pc_catid'] ?? null;
        $this->assertIsNumeric($categoryId);
        QueryUtils::sqlStatementThrowException(
            "UPDATE `form_encounter` SET `date` = ? WHERE `encounter` = ?",
            [self::FIXTURE_DATE_OF_SERVICE, $encounter]
        );

        return ['encounter' => $encounter, 'date' => self::FIXTURE_DATE_OF_SERVICE, 'pc_catid' => (int) $categoryId];
    }

    /**
     * Characterization of library/encounter.inc.php: fetchDateService() returns the calendar
     * date of the encounter without the time part.
     */
    #[Test]
    public function testFetchDateServiceReturnsTheEncounterDate(): void
    {
        $this->fixtureManager->installFixtures();
        $row = $this->fixtureEncounter();

        $this->assertSame(substr($row['date'], 0, 10), fetchDateService($row['encounter']));
    }

    /**
     * Characterization of library/encounter.inc.php: fetchCategoryIdByEncounter() returns the
     * calendar category of the encounter for the default (patient) attendant type.
     */
    #[Test]
    public function testFetchCategoryIdByEncounterReturnsTheCategory(): void
    {
        $this->fixtureManager->installFixtures();
        $row = $this->fixtureEncounter();

        // Pins the current contract: the category comes back as an int.
        $this->assertSame($row['pc_catid'], fetchCategoryIdByEncounter($row['encounter']));
    }
    /**
     * The service methods the shims delegate to return the same values as the shims, and behave
     * predictably for an unknown encounter.
     */
    #[Test]
    public function testStaticEncounterLookupsMatchTheLegacyFunctions(): void
    {
        $this->fixtureManager->installFixtures();
        $row = $this->fixtureEncounter();

        $this->assertSame(substr($row['date'], 0, 10), EncounterService::fetchDateService($row['encounter']));
        $this->assertSame($row['pc_catid'], EncounterService::fetchCategoryIdByEncounter($row['encounter']));
        $this->assertSame('', EncounterService::fetchDateService(-1), 'unknown encounter yields an empty date');
        $this->assertNull(EncounterService::fetchCategoryIdByEncounter(-1), 'unknown encounter yields no category');
    }
    /**
     * With a therapy-group attendant the category comes from form_groups_encounter, through both
     * the legacy shim and the service method; the same encounter number is unknown on the patient
     * side, which proves the table switch rather than a coincidental match.
     */
    #[Test]
    public function testCategoryLookupUsesTheGroupEncounterTableForTherapyGroups(): void
    {
        $bag = OEGlobalsBag::getInstance();
        $previousAttendantType = $bag->getString('attendant_type');
        $encounter = 987654321;
        $groupCategory = 9;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `form_groups_encounter` (`id`, `date`, `reason`, `group_id`, `encounter`, `pc_catid`) VALUES (?, ?, ?, ?, ?, ?)",
            [987654321, self::FIXTURE_DATE_OF_SERVICE, 'test-fixture-group-encounter', 1, $encounter, $groupCategory]
        );

        try {
            $bag->set('attendant_type', 'pid');
            $patientSideCategory = fetchCategoryIdByEncounter($encounter);
            $this->assertNull($patientSideCategory, 'no patient encounter carries this number');

            $bag->set('attendant_type', 'gid');
            $groupSideCategory = fetchCategoryIdByEncounter($encounter);
            $this->assertSame($groupCategory, $groupSideCategory);
            $this->assertSame($groupCategory, EncounterService::fetchCategoryIdByEncounter($encounter));
        } finally {
            $bag->set('attendant_type', $previousAttendantType);
            QueryUtils::sqlStatementThrowException("DELETE FROM `form_groups_encounter` WHERE `reason` = ?", ['test-fixture-group-encounter']);
        }
    }
    /**
     * The legacy shims accept anything, as they always did. A non-scalar encounter must keep
     * yielding "nothing found" instead of reaching the typed service methods and raising a
     * TypeError.
     */
    #[Test]
    public function testLegacyShimsTreatNonScalarEncountersAsUnknown(): void
    {
        $this->assertNull(fetchCategoryIdByEncounter(['not', 'an', 'id']));
        $this->assertNull(fetchCategoryIdByEncounter(null));
        $this->assertSame('', fetchDateService(['not', 'an', 'id']));
        $this->assertSame('', fetchDateService(null));
    }
}
