<?php

namespace OpenEMR\Tests\RestControllers\FHIR;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\FHIR\FhirPractitionerRestController;
use OpenEMR\Tests\Fixtures\PractitionerFixtureManager;

/**
 * @coversDefaultClass OpenEMR\RestControllers\FHIR\FhirPractitionerRestController
 *
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

    /**
     * @cover ::post with valid data
     */
    public function testPost()
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(201, http_response_code());
        $this->assertNotEmpty($actualResult['uuid']);
    }

    /**
     * @cover ::post with invalid data
     */
    public function testInvalidPost()
    {
        unset($this->fhirFixture['name']);

        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    /**
     * @cover ::patch with valid data
     */
    public function testPatch()
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch($fhirId, $this->fhirFixture);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    /**
     * @cover ::patch with valid data
     */
    public function testInvalidPatch()
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $this->fhirFixture['name'][0]['family'] = 'Smithers';
        $actualResult = $this->fhirPractitionerController->patch('bad-uuid', $this->fhirFixture);

        $this->assertEquals(400, http_response_code());
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetOne()
    {
        $actualResult = $this->fhirPractitionerController->post($this->fhirFixture);
        $fhirId = $actualResult['uuid'];

        $actualResult = $this->fhirPractitionerController->getOne($fhirId);
        $this->assertEquals($fhirId, $actualResult->getId());
    }

    public function testGetOneNoMatch()
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $actualResult = $this->fhirPractitionerController->getOne("not-a-matching-uuid");
        $this->assertGreaterThan(0, count($actualResult['validationErrors']));
    }

    public function testGetAll()
    {
        $this->fhirPractitionerController->post($this->fhirFixture);

        $searchParams = ['address-state' => 'CA'];
        $actualResults = $this->fhirPractitionerController->getAll($searchParams);
        $this->assertNotEmpty($actualResults);

        foreach ($actualResults->getEntry() as $index => $bundleEntry) {
            $this->assertObjectHasAttribute('fullUrl', $bundleEntry);
            $this->assertObjectHasAttribute('resource', $bundleEntry);
        }
    }
}
