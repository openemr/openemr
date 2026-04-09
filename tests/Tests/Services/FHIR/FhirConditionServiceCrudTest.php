<?php

namespace OpenEMR\Tests\Services\FHIR;

use Monolog\Level;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * FHIR Condition Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirConditionServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRCondition $fhirConditionFixture;
    private FhirConditionService $fhirConditionService;
    private string $patientUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        // Install a patient fixture so we have a valid puuid
        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixtureData = json_decode(
            file_get_contents(__DIR__ . '/../../Fixtures/FHIR/condition.json'),
            true
        );
        $fixture = $fixtureData[0];
        $fixture['subject'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirConditionFixture = new FHIRCondition($fixture);

        $this->fhirConditionService = new FhirConditionService();
        $this->fhirConditionService->setSystemLogger(new SystemLogger(Level::Critical));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'medical_problem' AND comments LIKE 'test-fixture%'");
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirConditionFixture->setId(null);
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $processingResult->getData()[0];
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference and code to trigger validation error
        $this->fhirConditionFixture->setSubject(null);
        $this->fhirConditionFixture->setCode(null);
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertEquals(0, count($processingResult->getData()));
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirConditionFixture->setId(null);
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $processingResult->getData()[0];
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the condition
        $this->fhirConditionFixture->setId($fhirId);
        $actualResult = $this->fhirConditionService->update($fhirId, $this->fhirConditionFixture);
        $this->assertTrue($actualResult->isValid(), "Update should succeed: " . json_encode($actualResult->getValidationMessages()));

        $actualFhirRecord = $actualResult->getData()[0];
        $this->assertEquals($fhirId, $actualFhirRecord->getId());
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirConditionService->update('bad-uuid', $this->fhirConditionFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertGreaterThan(0, count($actualResult->getValidationMessages()));
        $this->assertEquals(0, count($actualResult->getData()));
    }
}
