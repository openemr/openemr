<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * FHIR Condition Service CRUD Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
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
        $this->assertIsArray($patientFixture);
        $patientRecord = QueryUtils::querySingleRow(
            "SELECT uuid FROM patient_data WHERE pubpid = ?",
            [$patientFixture['pubpid']]
        );
        $this->assertIsArray($patientRecord);
        $this->patientUuid = \OpenEMR\Common\Uuid\UuidRegistry::uuidToString($patientRecord['uuid']);

        // Load FHIR fixture and set patient reference
        $raw = file_get_contents(__DIR__ . '/../../Fixtures/FHIR/condition.json');
        $this->assertIsString($raw);
        $fixtureData = json_decode($raw, true);
        $this->assertIsArray($fixtureData);
        $fixture = $fixtureData[0];
        $this->assertIsArray($fixture);
        $fixture['subject'] = [
            'reference' => 'Patient/' . $this->patientUuid
        ];
        $this->fhirConditionFixture = new FHIRCondition($fixture);

        $this->fhirConditionService = new FhirConditionService();
        $this->fhirConditionService->setLogger($this->createMock(LoggerInterface::class));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
        QueryUtils::sqlStatementThrowException("DELETE FROM lists WHERE type = 'medical_problem' AND comments LIKE 'test-fixture%'");
    }

    #[Test]
    public function testInsert(): void
    {
        $this->fhirConditionFixture->setId(new FHIRId());
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $this->assertArrayHasKey('uuid', $dataResult);
        $this->assertIsString($dataResult['uuid']);
    }

    #[Test]
    public function testInsertWithErrors(): void
    {
        // Remove the patient reference and code to trigger validation error
        $this->fhirConditionFixture->setSubject(new FHIRReference());
        $this->fhirConditionFixture->setCode(new FHIRCodeableConcept());
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertFalse($processingResult->isValid());
        $this->assertSame([], $processingResult->getData());
    }

    #[Test]
    public function testUpdate(): void
    {
        $this->fhirConditionFixture->setId(new FHIRId());
        $processingResult = $this->fhirConditionService->insert($this->fhirConditionFixture);
        $this->assertTrue($processingResult->isValid(), "Insert should succeed: " . json_encode($processingResult->getValidationMessages()));

        $dataResult = $this->firstDataRow($processingResult);
        $fhirId = $dataResult['uuid'];
        $this->assertIsString($fhirId);

        // Update the condition
        $this->fhirConditionFixture->setId(self::fhirId($fhirId));
        $actualResult = $this->fhirConditionService->update($fhirId, $this->fhirConditionFixture);
        $this->assertTrue($actualResult->isValid(), "Update should succeed: " . json_encode($actualResult->getValidationMessages()));
    }

    #[Test]
    public function testUpdateWithErrors(): void
    {
        $actualResult = $this->fhirConditionService->update('bad-uuid', $this->fhirConditionFixture);
        $this->assertFalse($actualResult->isValid());
        $this->assertNotSame([], $actualResult->getValidationMessages());
        $this->assertSame([], $actualResult->getData());
    }

    /**
     * ICD-10-CM and ICD-9-CM are the US Core diagnosis code systems. They must
     * map to OpenEMR's ICD10 / ICD9 code-type prefixes via CodeTypesService, not
     * be passed through as the raw system URL.
     */
    #[Test]
    public function testParseMapsIcdSystemsToOpenEmrCodeTypes(): void
    {
        $fixture = $this->fhirConditionFixture->jsonSerialize();
        $fixture['code'] = [
            'coding' => [
                ['system' => 'http://hl7.org/fhir/sid/icd-10-cm', 'code' => 'E11.9'],
                ['system' => 'http://hl7.org/fhir/sid/icd-9-cm', 'code' => '250.00'],
            ],
        ];
        $parsed = $this->fhirConditionService->parseFhirResource(new FHIRCondition($fixture));

        $this->assertSame('ICD10:E11.9;ICD9:250.00', $parsed['diagnosis']);
    }

    /**
     * FHIR R4 dateTime permits partial values (YYYY, YYYY-MM). lists.begdate is a DATE
     * column, so a year-only onset cannot be stored faithfully. FhirDateTimeParser
     * throws rather than dropping the element: parseFhirResource() runs inside the
     * try/catch in FhirGenericRestController, so the caller gets a 400 OperationOutcome
     * instead of a silently discarded onset date.
     */
    #[Test]
    #[DataProvider('partialOnsetDateTimeProvider')]
    public function testParseRejectsPartialOnsetDateTime(string $onsetDateTime): void
    {
        $fixture = $this->fhirConditionFixture->jsonSerialize();
        $fixture['onsetDateTime'] = $onsetDateTime;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/partial-precision/');

        $this->fhirConditionService->parseFhirResource(new FHIRCondition($fixture));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function partialOnsetDateTimeProvider(): array
    {
        return [
            'year only' => ['2020'],
            'year and month' => ['2020-03'],
        ];
    }

    /**
     * A full FHIR date (YYYY-MM-DD) and full date-time both resolve to the
     * OpenEMR Y-m-d begdate.
     */
    #[Test]
    public function testParseAcceptsFullOnsetDateTime(): void
    {
        $fixture = $this->fhirConditionFixture->jsonSerialize();
        $fixture['onsetDateTime'] = '2020-03-15';
        $parsed = $this->fhirConditionService->parseFhirResource(new FHIRCondition($fixture));
        $this->assertSame('2020-03-15', $parsed['begdate']);

        $fixture['onsetDateTime'] = '2020-03-15T09:30:00+00:00';
        $parsed = $this->fhirConditionService->parseFhirResource(new FHIRCondition($fixture));
        $this->assertSame('2020-03-15', $parsed['begdate']);
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
