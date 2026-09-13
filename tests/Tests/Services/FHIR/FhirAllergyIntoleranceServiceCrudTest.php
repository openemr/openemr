<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRAllergyIntolerance;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirAllergyIntoleranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR AllergyIntolerance Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
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
        $this->assertIsArray($patientFixture);
        // look up the installed patient to get the uuid
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $fixture = (array) $this->fixtureManager->getSingleFhirAllergyIntoleranceFixture();
        $fixture['patient'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirAllergyIntoleranceFixture = new FHIRAllergyIntolerance($fixture);

        $this->fhirAllergyIntoleranceService = new FhirAllergyIntoleranceService();
        $this->fhirAllergyIntoleranceService->setLogger($this->createMock(LoggerInterface::class));
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
        $this->fhirAllergyIntoleranceFixture->setId(new FHIRId());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference to trigger validation error
        $this->fhirAllergyIntoleranceFixture->setPatient(new FHIRReference());
        // Also clear the code text so title is empty
        $this->fhirAllergyIntoleranceFixture->setCode(new FHIRCodeableConcept());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirAllergyIntoleranceFixture->setId(new FHIRId());
        $processingResult = $this->fhirAllergyIntoleranceService->insert($this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the allergy - change the verification status
        $this->fhirAllergyIntoleranceFixture->setId(self::fhirId($fhirId));
        $actualResult = $this->fhirAllergyIntoleranceService->update($fhirId, $this->fhirAllergyIntoleranceFixture);
        $this->assertTrue($actualResult->isValid(), "Update should succeed: " . json_encode($actualResult->getValidationMessages()));
        $this->assertNotEmpty($actualResult->getData());
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirAllergyIntoleranceService->update('bad-uuid', $this->fhirAllergyIntoleranceFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertNotSame([], $actualResult->getValidationMessages());
        $this->assertSame([], $actualResult->getData());
    }

    private static function fhirId(string $value): FHIRId
    {
        $id = new FHIRId();
        $id->setValue($value);

        return $id;
    }

    /**
     * Reads the first row of a ProcessingResult, asserting the shape as it goes so a
     * failed insert surfaces as a test failure rather than a type error downstream.
     *
     * @return array<mixed>
     */
    private function firstDataRow(ProcessingResult $result): array
    {
        $data = $result->getData();
        $this->assertIsArray($data);
        $this->assertArrayHasKey(0, $data);
        $row = $data[0];
        $this->assertIsArray($row);

        return $row;
    }
}
