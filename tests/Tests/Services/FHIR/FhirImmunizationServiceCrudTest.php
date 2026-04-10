<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRImmunization;
use OpenEMR\Services\FHIR\FhirImmunizationService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * FHIR Immunization Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirImmunizationServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRImmunization $fhirImmunizationFixture;
    private FhirImmunizationService $fhirImmunizationService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid patient_id for the immunization
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        // look up the installed patient to get the uuid
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirImmunizationFixture();
        $fixture['patient'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirImmunizationFixture = new FHIRImmunization($fixture);

        $this->fhirImmunizationService = new FhirImmunizationService();
        $this->fhirImmunizationService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        // Clean up any immunization fixtures we created
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM immunizations WHERE note LIKE 'test-fixture%'"
        );
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirImmunizationFixture->setId(null);
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference to trigger validation error
        $this->fhirImmunizationFixture->setPatient(null);
        // Also clear the vaccine code so cvx_code is empty
        $this->fhirImmunizationFixture->setVaccineCode(null);
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirImmunizationFixture->setId(null);
        $processingResult = $this->fhirImmunizationService->insert($this->fhirImmunizationFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the immunization - change the status
        $this->fhirImmunizationFixture->setId($fhirId);
        $actualResult = $this->fhirImmunizationService->update(
            $fhirId,
            $this->fhirImmunizationFixture
        );
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirImmunizationService->update(
            'bad-uuid',
            $this->fhirImmunizationFixture
        );
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
