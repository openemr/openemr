<?php

/**
 * UtilsServiceDataMissingExtensionIsolatedTest
 *
 * Locks the shape of {@see UtilsService::createDataMissingExtension()}.
 * The prior two-level nested shape (outer wrapper with no url + inner
 * extension carrying the url + valueCode) triggered a Ruby-side crash
 * in Inferno's `fhir_models` validator (`undefined method 'tr' for nil`)
 * because Extension.url has cardinality 1..1 in the FHIR spec — the
 * outer wrapper had none. The current shape is a single flat extension
 * with the data-absent-reason canonical url and valueCode="unknown", per
 * the US Core general-guidance "missing data" pattern.
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
     * `createDataMissingExtension()` for PHPStan. The source can't
     * declare `: FHIRExtension` without surfacing ~30 latent errors on
     * caller sites that misuse the return value (setSubject/setDate/etc.
     * pass an Extension where a Reference/DateTime is expected). See
     * the source docblock for the follow-up plan; these tests still lock
     * the shape the fix produces at runtime.
     */
    private function extensionUnderTest(): FHIRExtension
    {
        $ext = UtilsService::createDataMissingExtension();
        $this->assertInstanceOf(FHIRExtension::class, $ext);
        return $ext;
    }

    public function testUrlIsDataAbsentReasonCanonical(): void
    {
        // Extension.url cardinality is 1..1 per FHIR. A missing url
        // triggers `undefined method 'tr' for nil` in the Inferno
        // `fhir_models` validator's `find_extension` — the specific
        // crash the outer-wrapper shape produced before this fix.
        $ext = $this->extensionUnderTest();

        $this->assertSame(
            FhirCodeSystemConstants::DATA_ABSENT_REASON_EXTENSION,
            (string) $ext->getUrl(),
            'Extension url must be the FHIR data-absent-reason canonical'
        );
    }

    public function testCarriesValueCodeUnknown(): void
    {
        $ext = $this->extensionUnderTest();

        $this->assertSame(
            'unknown',
            (string) $ext->getValueCode(),
            'US Core general-guidance "missing data" pattern uses valueCode=unknown'
        );
    }

    public function testHasNoNestedInnerExtension(): void
    {
        // The prior two-level shape (outer wrapper wrapping an inner
        // extension) is the specific defect this method's fix targets.
        // A flat single extension must not carry any inner .extension
        // entries — otherwise the outer wrapper's missing-url problem
        // would return.
        $ext = $this->extensionUnderTest();

        $this->assertSame(
            [],
            $ext->getExtension(),
            'createDataMissingExtension() must NOT wrap the payload in a nested inner extension — the flat shape is what US Core / FHIR requires'
        );
    }

    public function testEachInvocationReturnsAFreshInstance(): void
    {
        // The helper is called from ~30 sites across the FHIR services.
        // Callers assume they can freely add the returned Extension to
        // their parent element without side effects on other callers'
        // instances. A cached/shared instance would let one caller's
        // subsequent mutations (setValueCode, addExtension, etc.) leak
        // into unrelated resources.
        $a = UtilsService::createDataMissingExtension();
        $b = UtilsService::createDataMissingExtension();

        $this->assertNotSame($a, $b, 'Each invocation must return a new instance');
    }
}
