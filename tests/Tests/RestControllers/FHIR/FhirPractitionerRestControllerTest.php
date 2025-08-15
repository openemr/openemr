<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirPractitionerRestControllerTest extends TestCase
{
    /**
     * @var FhirPractitionerRestController
     */
    private $fhirPractitionerController;

    private $fixtureManager;
    private $fhirFixture;

    protected function setUp(): void
    {
        $this->fhirPractitionerController = new FhirPractitionerRestController();
        $this->fixtureManager = new PractitionerFixtureManager();

        $this->fhirFixture = (array) $this->fixtureManager->getSingleFhirPractitionerFixture();
        unset($this->fhirFixture['id']);
        unset($this->fhirFixture['meta']);
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePractitionerFixtures();
    }

    public function testPost(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(201, http_response_code());
        $this->assertNotEmpty($actualResult['uuid']);
    }

    public function testInvalidPost(): void
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testPatch(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch($fhirId, $this->fhirFixture);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    public function testInvalidPatch(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch('bad-uuid', $this->fhirFixture);

        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetOne(): void
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $actualResult = $this->fhirPractitionerController->getOne($fhirId);
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    public function testGetOneNoMatch(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $actualResult = $this->fhirPractitionerController->getOne("not-a-matching-uuid");
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetAll(): void
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'CA'];
        $actualResults = $this->fhirPractitionerController->getAll($searchParams);
        $this->assertNotEmpty($actualResults);

        foreach ($actualResults->getEntry() as $bundleEntry) {
            $this->assertObjectHasProperty('fullUrl', $bundleEntry);
            $this->assertObjectHasProperty('resource', $bundleEntry);
        }
    }
}
