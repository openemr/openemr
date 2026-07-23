<?php

declare(strict_types=1);

/*
 * FhirConditionProvenanceResourceTest.php
 *
 * Regression tests for https://github.com/openemr/openemr/issues/13054
 * createProvenanceResource() fataled with a TypeError when
 * FhirProvenanceService::createProvenanceForDomainResource() returned null
 * (e.g. no resolvable author/organization reference), which shunted Condition
 * resources into the bulk $export manifest error file.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR\Condition;

use BadMethodCallException;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\Services\FHIR\Condition\FhirConditionEncounterDiagnosisService;
use OpenEMR\Services\FHIR\Condition\FhirConditionHealthConcernService;
use OpenEMR\Services\FHIR\Condition\FhirConditionProblemListItemService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type ConditionServiceClass class-string<FhirConditionEncounterDiagnosisService|FhirConditionHealthConcernService|FhirConditionProblemListItemService>
 */
class FhirConditionProvenanceResourceTest extends TestCase
{
    /**
     * @return array<string, array{ConditionServiceClass}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function conditionServiceClassProvider(): array
    {
        return [
            'encounter-diagnosis' => [FhirConditionEncounterDiagnosisService::class],
            'health-concern' => [FhirConditionHealthConcernService::class],
            'problem-list-item' => [FhirConditionProblemListItemService::class],
        ];
    }

    /**
     * Builds a partial mock of the given Condition service where the
     * FhirProvenanceService factory seam returns a stubbed provenance service.
     *
     * @param ConditionServiceClass $serviceClass
     * @return FhirServiceBase&MockObject
     */
    private function getServiceWithProvenanceResult(string $serviceClass, ?FHIRProvenance $provenanceResult): FhirServiceBase
    {
        $provenanceService = $this->createMock(FhirProvenanceService::class);
        $provenanceService->method('createProvenanceForDomainResource')
            ->willReturn($provenanceResult);

        // Constructors are disabled because FhirConditionHealthConcernService and
        // FhirConditionProblemListItemService instantiate ConditionService (database)
        // in their constructors; createProvenanceResource() does not depend on that state.
        $service = $this->getMockBuilder($serviceClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFhirProvenanceService'])
            ->getMock();
        $service->method('getFhirProvenanceService')
            ->willReturn($provenanceService);

        return $service;
    }

    /**
     * @param ConditionServiceClass $serviceClass
     */
    #[DataProvider('conditionServiceClassProvider')]
    public function testReturnsFalseWhenProvenanceIsUnavailable(string $serviceClass): void
    {
        $service = $this->getServiceWithProvenanceResult($serviceClass, null);
        $result = $service->createProvenanceResource(new FHIRCondition());
        $this->assertFalse($result, "Expected false when no Provenance can be constructed so FhirServiceBase::getAll() can skip it.");
    }

    /**
     * @param ConditionServiceClass $serviceClass
     */
    #[DataProvider('conditionServiceClassProvider')]
    public function testReturnsFalseWhenProvenanceIsUnavailableAndEncodeRequested(string $serviceClass): void
    {
        $service = $this->getServiceWithProvenanceResult($serviceClass, null);
        $result = $service->createProvenanceResource(new FHIRCondition(), true);
        $this->assertFalse($result, "Expected false (not the JSON string 'null') when no Provenance can be constructed and encoding was requested.");
    }

    /**
     * @param ConditionServiceClass $serviceClass
     */
    #[DataProvider('conditionServiceClassProvider')]
    public function testReturnsProvenanceResourceWhenAvailable(string $serviceClass): void
    {
        $provenance = new FHIRProvenance();
        $service = $this->getServiceWithProvenanceResult($serviceClass, $provenance);
        $result = $service->createProvenanceResource(new FHIRCondition());
        $this->assertSame($provenance, $result, "Expected the FHIRProvenance instance to be returned unmodified.");
    }

    /**
     * @param ConditionServiceClass $serviceClass
     */
    #[DataProvider('conditionServiceClassProvider')]
    public function testReturnsEncodedProvenanceWhenEncodeRequested(string $serviceClass): void
    {
        $provenance = new FHIRProvenance();
        $service = $this->getServiceWithProvenanceResult($serviceClass, $provenance);
        $result = $service->createProvenanceResource(new FHIRCondition(), true);
        $this->assertIsString($result, "Expected a JSON string when encoding was requested.");
        $this->assertJson($result, "Expected the encoded Provenance to be valid JSON.");
    }

    /**
     * @param ConditionServiceClass $serviceClass
     */
    #[DataProvider('conditionServiceClassProvider')]
    public function testThrowsOnInvalidDataRecord(string $serviceClass): void
    {
        $service = $this->getServiceWithProvenanceResult($serviceClass, null);
        $this->expectException(BadMethodCallException::class);
        $service->createProvenanceResource([]);
    }
}
