<?php

/**
 * UtilsServiceDataMissingExtensionIsolatedTest
 *
 * Locks the two-level nested shape of
 * {@see UtilsService::createDataMissingExtension()}: an outer FHIRExtension
 * wrapping an inner FHIRExtension that carries the data-absent-reason
 * canonical URL + valueCode="unknown".
 *
 * The wrapper is designed for composition into
 * {@see OpenEMRFHIRDateTime} (and other trait-enabled primitive
 * domain-model subclasses). When the parent primitive's `value` is
 * empty and the wrapper is attached via `addExtension()`, the trait
 * serializes it into the FHIR primitive-extension companion slot
 * (e.g. `_effectiveDateTime`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Utils;

use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class UtilsServiceDataMissingExtensionIsolatedTest extends TestCase
{
    /**
     * Narrows the intentionally-untyped return of
     * `createDataMissingExtension()` for PHPStan.
     */
    private function outerWrapperUnderTest(): FHIRExtension
    {
        $ext = UtilsService::createDataMissingExtension();
        $this->assertInstanceOf(FHIRExtension::class, $ext);
        return $ext;
    }

    public function testOuterWrapperCarriesExactlyOneInnerExtension(): void
    {
        $outer = $this->outerWrapperUnderTest();
        $inner = $outer->getExtension();

        $this->assertCount(
            1,
            $inner,
            'Outer wrapper must carry exactly one inner extension carrying the data-absent-reason payload'
        );
    }

    public function testInnerExtensionUrlIsDataAbsentReasonCanonical(): void
    {
        $outer = $this->outerWrapperUnderTest();
        $inner = $outer->getExtension()[0] ?? null;

        $this->assertInstanceOf(FHIRExtension::class, $inner);
        $this->assertSame(
            FhirCodeSystemConstants::DATA_ABSENT_REASON_EXTENSION,
            (string) $inner->getUrl(),
            'Inner extension url must be the FHIR data-absent-reason canonical'
        );
    }

    public function testInnerExtensionCarriesValueCodeUnknown(): void
    {
        $outer = $this->outerWrapperUnderTest();
        $inner = $outer->getExtension()[0] ?? null;

        $this->assertInstanceOf(FHIRExtension::class, $inner);
        $this->assertSame(
            'unknown',
            (string) $inner->getValueCode(),
            'US Core general-guidance "missing data" pattern uses valueCode=unknown'
        );
    }

    public function testEachInvocationReturnsAFreshInstance(): void
    {
        // The helper is called from many sites across the FHIR services.
        // Callers assume they can attach the returned wrapper to their
        // parent element without side effects on other callers'
        // instances. A cached/shared instance would let one caller's
        // subsequent mutations leak into unrelated resources.
        $a = UtilsService::createDataMissingExtension();
        $b = UtilsService::createDataMissingExtension();

        $this->assertNotSame($a, $b, 'Each invocation must return a new instance');
    }
}
