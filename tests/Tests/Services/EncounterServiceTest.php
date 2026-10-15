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
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\VitalsService;
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

    #[Test]
    public function testUpdateVitalKeepsUuid(): void
    {
        $this->fixtureManager->installFixtures();
        $encounter = QueryUtils::querySingleRow("SELECT `pid`, `encounter` FROM `form_encounter` ORDER BY `id` DESC LIMIT 1");
        $this->assertIsArray($encounter);
        $pid = $encounter['pid'];
        $eid = $encounter['encounter'];

        [$vid] = $this->service->insertVital($pid, $eid, ['bps' => '120', 'bpd' => '80']);
        try {
            $uuidBefore = QueryUtils::fetchSingleValue("SELECT `uuid` FROM `form_vitals` WHERE `id` = ?", 'uuid', [$vid]);
            $this->assertNotEmpty($uuidBefore);

            // a REST PUT body doesn't carry the uuid
            $this->service->updateVital($pid, $eid, $vid, ['bps' => '130', 'bpd' => '85']);

            $row = QueryUtils::querySingleRow("SELECT `uuid`, `bps` FROM `form_vitals` WHERE `id` = ?", [$vid]);
            $this->assertIsArray($row);
            $this->assertEquals('130', $row['bps']);
            $this->assertSame($uuidBefore, $row['uuid'], "Updating a vital must not change or clear its uuid");

            // an empty uuid in the save data is treated as not sent
            (new VitalsService())->saveVitalsArray([
                'id' => $vid, 'uuid' => '', 'pid' => $pid, 'eid' => $eid, 'authorized' => '1', 'bps' => '140',
            ]);
            $row = QueryUtils::querySingleRow("SELECT `uuid`, `bps` FROM `form_vitals` WHERE `id` = ?", [$vid]);
            $this->assertIsArray($row);
            $this->assertEquals('140', $row['bps']);
            $this->assertSame($uuidBefore, $row['uuid'], "An empty uuid must not change or clear the stored one");
        } finally {
            QueryUtils::sqlStatementThrowException("DELETE FROM `forms` WHERE `formdir` = 'vitals' AND `form_id` = ?", [$vid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM `form_vitals` WHERE `id` = ?", [$vid]);
        }
    }
}
