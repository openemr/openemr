<?php

/**
 * CarePlanServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\CarePlanService;
use OpenEMR\Tests\Fixtures\CarePlanFixtureManager;
use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * Practitioner Service Tests
 * @coversDefaultClass OpenEMR\Services\PractitionerService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CarePlanServiceTest extends TestCase
{
    /**
     * @var CarePlanService
     */
    private $service;

    /**
     * @var CarePlanFixtureManager
     */
    private $fixtureManager;

    protected function setUp(): void
    {
        $this->service = new CarePlanService();
        $this->fixtureManager = new CarePlanFixtureManager();
        $this->fixture = (array) $this->fixtureManager->getSingleFixture();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
    }

    /**
     * @cover ::getOne
     */
    public function testGetOne()
    {
        $this->fixtureManager->installFixtures();

        // attempt to verify the uuid surrogate key is working correctly
        $expectedResult = sqlQuery("SELECT `fcp`.`id` AS `form_id`,`fe`.`uuid` AS `euuid`, `fcp`.`encounter` FROM `form_care_plan` fcp "
        . " JOIN `form_encounter` fe ON `fcp`.`encounter` = `fe`.`encounter` LIMIT 1");
        $expectedResult['euuid'] = UuidRegistry::uuidToString($expectedResult['euuid']);
        $uuid = $this->service->getSurrogateKeyForRecord($expectedResult);

        // getOne
        $actualResult = $this->service->getOne($uuid);
        $resultData = $actualResult->getData()[0];
        $this->assertNotNull($resultData);
        foreach (['euuid','form_id'] as $check) {
            $this->assertEquals($expectedResult[$check], $resultData[$check]);
        }
    }
}
