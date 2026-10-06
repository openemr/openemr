<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Traits;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Exercises FhirServiceBaseEmptyTrait through FhirProvenanceService, which
 * takes every stub from the trait.
 */
class FhirServiceBaseEmptyTraitTest extends TestCase
{
    /**
     * @return array<string, array{callable(FhirProvenanceService): mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function writeMethodProvider(): array
    {
        return [
            'parseFhirResource' => [fn(FhirProvenanceService $service) => $service->parseFhirResource(new FHIRProvenance())],
            'insertOpenEMRRecord' => [fn(FhirProvenanceService $service) => $service->insertOpenEMRRecord([])],
            'updateOpenEMRRecord' => [fn(FhirProvenanceService $service) => $service->updateOpenEMRRecord('id', [])],
        ];
    }

    /**
     * @param callable(FhirProvenanceService): mixed $call
     */
    #[DataProvider('writeMethodProvider')]
    public function testWriteMethodsThrow(callable $call): void
    {
        $this->expectException(\BadMethodCallException::class);
        $call(new FhirProvenanceService());
    }
}
