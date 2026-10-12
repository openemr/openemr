<?php

/*
 * FhirServiceBaseFailClosedIsolatedTest.php
 *
 * Tests for patient-compartment enforcement in
 * {@see \OpenEMR\Services\FHIR\FhirServiceBase}. Previously,
 * {@see FhirServiceBase::getOne()} gated the patient-context search-field
 * bind on
 *   isset($puuidBind) && $this instanceof IPatientCompartmentResourceService
 * so any service that did not implement the marker interface silently
 * dropped the puuid bind and returned data unfiltered. Now, the absence of
 * the marker denies the request.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

class FhirServiceBaseFailClosedIsolatedTest extends TestCase
{
    private const FAKE_RESOURCE_UUID = '11111111-2222-3333-4444-555555555555';
    private const FAKE_PATIENT_UUID  = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

    public function testGetOneDeniesPatientScopeOnUnmarkedService(): void
    {
        $service = new FailClosedTestNonCompartmentService();
        $result = $service->getOne(self::FAKE_RESOURCE_UUID, self::FAKE_PATIENT_UUID);

        $this->assertFalse(
            $result->isValid(),
            'A patient-scoped call against a service that neither implements IPatientCompartmentResourceService nor INonPatientCompartmentResourceService must be denied.'
        );
        $this->assertNotEmpty(
            $result->getInternalErrors(),
            'The denied ProcessingResult should carry an internal error explaining the denial.'
        );
        $this->assertFalse(
            $service->searchWasReached,
            'When the compartment check denies, searchForOpenEMRRecords() must never be reached.'
        );
    }

    public function testGetOneAppliesPatientBindOnCompartmentImplementingService(): void
    {
        $service = new FailClosedTestCompartmentService();
        $service->getOne(self::FAKE_RESOURCE_UUID, self::FAKE_PATIENT_UUID);

        $this->assertTrue(
            $service->searchWasReached,
            'A compartment-implementing service must reach the search step.'
        );
        $this->assertArrayHasKey('puuid', $service->lastSearchParams);
    }

    public function testGetOneAllowsNonPatientCompartmentServiceWithoutBinding(): void
    {
        $service = new FailClosedTestNonPatientCompartmentService();
        $service->getOne(self::FAKE_RESOURCE_UUID, self::FAKE_PATIENT_UUID);

        $this->assertTrue(
            $service->searchWasReached,
            'A non-patient-compartment-marked service must reach the search step even under a patient-scoped call.'
        );
        $this->assertArrayNotHasKey(
            'puuid',
            $service->lastSearchParams,
            'Non-patient-compartment services do not accept patient binding, so puuid must not appear in search params.'
        );
    }

    public function testGetOneAllowsUnscopedRequestOnAnyService(): void
    {
        $service = new FailClosedTestNonCompartmentService();
        $service->getOne(self::FAKE_RESOURCE_UUID, null);

        $this->assertTrue(
            $service->searchWasReached,
            'A non-patient-scoped call must be allowed even when the service does not implement the compartment interface.'
        );
    }

    public function testCreateOpenEMRSearchParametersThrowsOnNonCompartmentService(): void
    {
        $service = new FailClosedTestNonCompartmentService();
        $result = $service->getAll(['_id' => self::FAKE_RESOURCE_UUID], self::FAKE_PATIENT_UUID);

        $this->assertNotEmpty(
            $result->getValidationMessages(),
            'A patient-scoped getAll() must surface a validation message when the service does not implement the compartment interface.'
        );
        $this->assertFalse(
            $service->searchWasReached,
            'When createOpenEMRSearchParameters() throws, searchForOpenEMRRecords() must not be reached.'
        );
    }

    /**
     * Regression gate for the generic trait's compartment binding
     * behavior at ResourceServiceSearchTrait::createOpenEMRSearchParameters:96-110.
     * When a caller supplies `patient=<X>` AND the token carries a bind,
     * the trait emits the bound-patient value under its FHIR field name
     * ('patient') while the caller's value remains under the mapped
     * service field name ('puuid'). The two coexist in the resulting
     * parameter map; a service's search layer AND-combines them, which
     * on any cross-patient attempt yields an empty result set because
     * no record satisfies both WHERE conditions simultaneously.
     *
     * (Hot services such as FhirObservationService / FhirConditionService
     * also pre-overwrite the caller's value in fhirSearchParameters
     * before this trait method is invoked — see FhirObservationService.php
     * line 113. This test exercises only the generic trait path.)
     *
     * The property pinned here: the bound value is present under 'patient'
     * so the AND-combination enforces the compartment. A refactor that
     * dropped the trait-level emission would need to be caught before it
     * eliminated this fence.
     */
    public function testCompartmentBindEmitsBoundPatientFieldAlongsideCallerSuppliedField(): void
    {
        $service = new FailClosedTestCompartmentService();
        $attackerPuuid = 'cafecafe-cafe-cafe-cafe-cafecafecafe';
        $service->getAll(
            ['patient' => $attackerPuuid],
            self::FAKE_PATIENT_UUID
        );

        $this->assertTrue($service->searchWasReached);
        $this->assertArrayHasKey(
            'patient',
            $service->lastSearchParams,
            "The trait must emit the bound patient under its FHIR field name ('patient'). "
            . "A refactor that stopped emitting this would eliminate the AND-combine fence "
            . "and let the caller-supplied value flow through alone."
        );
        $boundField = $service->lastSearchParams['patient'];
        $this->assertInstanceOf(\OpenEMR\Services\Search\ISearchField::class, $boundField);
        $values = $boundField->getValues();
        $this->assertCount(1, $values);
        $value = $values[0];
        $this->assertInstanceOf(\OpenEMR\Services\Search\ReferenceSearchValue::class, $value);
        $this->assertSame(
            self::FAKE_PATIENT_UUID,
            (string) $value,
            "The 'patient' key must carry the token-bound patient UUID."
        );
    }
}

/**
 * Shared skeleton for the three FhirServiceBase test fixtures — implements the
 * abstract methods with strict types so the parent's contract stays satisfied
 * without pulling in FhirServiceBaseEmptyTrait (whose loose parameter typing
 * would spray missingType.iterableValue / parameter.defaultValue errors across
 * each concrete fixture).
 *
 * @internal Test fixture — not production code.
 *
 * @codeCoverageIgnore Test fixture — not production code.
 */
abstract class FailClosedTestServiceBase extends FhirServiceBase
{
    public bool $searchWasReached = false;

    /** @var array<string, mixed> */
    public array $lastSearchParams = [];

    /**
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        return [];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRDomainResource
    {
        // Never invoked in these tests — return an empty resource to satisfy
        // the parent's non-nullable return type.
        return new FHIRDomainResource();
    }

    /**
     * @phpstan-ignore missingType.parameter
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        return new ProcessingResult();
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        return new ProcessingResult();
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $this->searchWasReached = true;
        // @phpstan-ignore assign.propertyType
        $this->lastSearchParams = $openEMRSearchParameters;
        return new ProcessingResult();
    }

    public function createProvenanceResource($dataRecord, $encode = false): null
    {
        return null;
    }
}

/**
 * @internal Test fixture — extends FhirServiceBase without either marker.
 *
 * @codeCoverageIgnore Test fixture — not production code.
 */
final class FailClosedTestNonCompartmentService extends FailClosedTestServiceBase
{
}

/**
 * @internal Test fixture — extends FhirServiceBase and implements the compartment marker.
 *
 * @codeCoverageIgnore Test fixture — not production code.
 */
final class FailClosedTestCompartmentService extends FailClosedTestServiceBase implements IPatientCompartmentResourceService
{
    /**
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'patient' => $this->getPatientContextSearchField(),
        ];
    }

    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }
}

/**
 * @internal Test fixture — extends FhirServiceBase and opts out via the non-patient-compartment marker.
 *
 * @codeCoverageIgnore Test fixture — not production code.
 */
final class FailClosedTestNonPatientCompartmentService extends FailClosedTestServiceBase implements INonPatientCompartmentResourceService
{
}
