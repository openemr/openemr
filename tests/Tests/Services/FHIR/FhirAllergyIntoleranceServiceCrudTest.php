<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR AllergyIntolerance Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirAllergyIntoleranceServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRAllergyIntolerance $fhirAllergyIntoleranceFixture;
    private FhirAllergyIntoleranceService $fhirAllergyIntoleranceService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid puuid for the allergy
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        // look up the installed patient to get the uuid
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirAllergyIntoleranceFixture();
        $fixture['patient'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirAllergyIntoleranceFixture = new FHIRAllergyIntolerance($fixture);

        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService();
        $this->fhirAllergyIntoleranceService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        // Clean up any allergy fixtures we created
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'allergy' AND title LIKE 'test-fixture%'");
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'allergy' AND comments LIKE 'test-fixture%'");
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirAllergyIntoleranceFixture->setId(null);
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference to trigger validation error
        $this->fhirAllergyIntoleranceFixture->setPatient(null);
        // Also clear the code text so title is empty
        $this->fhirAllergyIntoleranceFixture->setCode(null);
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirAllergyIntoleranceFixture->setId(null);
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the allergy - change the verification status
        $this->fhirAllergyIntoleranceFixture->setId($fhirId);
        $actualResult = $this->fhirAllergyIntoleranceService->update($fhirId, $this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($actualResult->isValid(), "Update should succeed: " . json_encode($actualResult->getValidationMessages()));
        $this->assertNotEmpty($actualResult->getData());
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirAllergyIntoleranceService->update('bad-uuid', $this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
