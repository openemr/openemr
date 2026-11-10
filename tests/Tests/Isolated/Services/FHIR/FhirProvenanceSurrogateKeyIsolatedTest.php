<?php

/**
 * Isolated tests for FhirProvenanceService::getSurrogateKeyForResource(), which picks
 * the V1 or V2 key separator from a Patient's typed meta.lastUpdated instant.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCondition;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FhirProvenanceSurrogateKeyIsolatedTest extends TestCase
{
    private function surrogateKey(FHIRDomainResource $resource, string $lastUpdated): string
    {
        $meta = new FHIRMeta();
        $meta->setLastUpdated(new FHIRInstant($lastUpdated));
        $resource->setMeta($meta);
        $resource->setId(new FHIRId('abc-123'));

        // getSurrogateKeyForResource() does not touch instance state, so skip the constructor.
        $service = (new \ReflectionClass(FhirProvenanceService::class))->newInstanceWithoutConstructor();
        return $service->getSurrogateKeyForResource($resource);
    }

    public function testPatientUpdatedBeforeV2CutoffUsesV1Separator(): void
    {
        $this->assertSame(
            'Patient' . FhirProvenanceService::SURROGATE_KEY_SEPARATOR_V1 . 'abc-123',
            $this->surrogateKey(new FHIRPatient(), '2021-06-01T12:00:00+00:00')
        );
    }

    public function testPatientUpdatedAfterV2CutoffUsesV2Separator(): void
    {
        $this->assertSame(
            'Patient' . FhirProvenanceService::SURROGATE_KEY_SEPARATOR_V2 . 'abc-123',
            $this->surrogateKey(new FHIRPatient(), '2024-06-01T12:00:00+00:00')
        );
    }

    public function testNonPatientAlwaysUsesV2Separator(): void
    {
        $this->assertSame(
            'Condition' . FhirProvenanceService::SURROGATE_KEY_SEPARATOR_V2 . 'abc-123',
            $this->surrogateKey(new FHIRCondition(), '2021-06-01T12:00:00+00:00')
        );
    }
}
