<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirCoverageService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Coverage Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirCoverageServiceCrudTest extends TestCase
{
    private FixtureManager $fixtureManager;
    private FHIRCoverage $fhirCoverageFixture;
    private FhirCoverageService $fhirCoverageService;
    private string $patientUuid;
    private string $insurerUuid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();

        $this->fixtureManager->installPatientFixtures();
        $patients = $this->fixtureManager->getPatientFixtures();
        $patientFixture = $patients[0];
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        $this->insurerUuid = $this->fixtureManager->installInsuranceCompanyFixture();

        $fixture = (array) $this->fixtureManager->getSingleFhirCoverageFixture();
        $fixture['beneficiary'] = ['reference' => 'Patient/' . $this->patientUuid];
        $fixture['payor'] = [['reference' => 'Organization/' . $this->insurerUuid]];
        $this->fhirCoverageFixture = new FHIRCoverage($fixture);

        $this->fhirCoverageService = new FhirCoverageService();
        $this->fhirCoverageService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeCoverageFixtures();
        $this->fixtureManager->removePatientFixtures();
        $this->fixtureManager->removeInsuranceCompanyFixtures();
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithUnresolvableBeneficiary(): void
    {
        $bogusPatientUuid = UuidRegistry::uuidToString(
            (new UuidRegistry(['table_name' => 'patient_data']))->createUuid()
        );
        // The UuidRegistry::createUuid call above reserved a registry row but did not insert a
        // patient_data row, so the beneficiary reference cannot be resolved.
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['beneficiary'] = ['reference' => 'Patient/' . $bogusPatientUuid];
        $fixture = new FHIRCoverage($payload);

        $processingResult = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $processingResult = $this->fhirCoverageService->insert($this->fhirCoverageFixture);
        $this->assertTrue(
            $processingResult->isValid(),
            "Insert should succeed: " . json_encode($processingResult->getValidationMessages())
        );

        $fhirId = $this->firstDataRow($processingResult)['uuid'];
        $this->assertIsString($fhirId);

        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['id'] = $fhirId;
        $payload['subscriberId'] = 'test-fixture-policy-002-updated';
        $updated = new FHIRCoverage($payload);

        $actualResult = $this->fhirCoverageService->update($fhirId, $updated);
        $this->assertTrue(
            $actualResult->isValid(),
            "Update should succeed: " . json_encode($actualResult->getValidationMessages())
        );
        $this->assertNotEmpty($actualResult->getData());
        // FhirServiceBase::update re-parses the updated row through parseOpenEMRRecord,
        // so getData()[0] is the FHIRCoverage object. subscriberId carries policy_number.
        $updatedData = $actualResult->getData();
        $this->assertIsArray($updatedData);
        $this->assertArrayHasKey(0, $updatedData);
        $updatedResource = $updatedData[0];
        $this->assertInstanceOf(FHIRCoverage::class, $updatedResource);
        $this->assertSame('test-fixture-policy-002-updated', $updatedResource->getSubscriberId()->getValue());
    }

    #[Test]
    public function testUpdateWithBadUuid(): void
    {
        // Use a syntactically valid uuid that does not exist in insurance_data.
        // ('bad-uuid' would throw InvalidUuidStringException before validation runs.)
        $nonExistentUuid = '00000000-0000-0000-0000-000000000000';
        $actualResult = $this->fhirCoverageService->update($nonExistentUuid, $this->fhirCoverageFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertSame([], $actualResult->getData());
    }

    #[Test]
    public function testInsertWithStatusInconsistentWithDatesReturnsValidationError(): void
    {
        // Fixture period is 2024-01-01 -> 2099-12-31 (active by derivation). Claiming
        // status=cancelled should produce a 422-style validation error rather than
        // silently dropping the modifier element.
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['status'] = 'cancelled';
        $fixture = new \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage($payload);

        $result = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('status', $messages);
    }

    #[Test]
    public function testInsertWithStatusEnteredInErrorReturnsValidationError(): void
    {
        $this->fhirCoverageFixture->setId(new FHIRId());
        $payload = $this->fhirCoverageFixture->jsonSerialize();
        $payload['status'] = 'entered-in-error';
        $fixture = new \OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage($payload);

        $result = $this->fhirCoverageService->insert($fixture);
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        $this->assertArrayHasKey('status', $messages);
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
