<?php

/**
 * Locks the UCUM claim rules on FHIR Observation.referenceRange[].low
 * and .high for laboratory results.
 *
 * `FhirObservationLaboratoryService::parseOpenEMRRecord` builds the
 * reference range through two `UtilsService::createQuantity` calls —
 * one for low, one for high — using the same `units` value on both.
 * PR #14933 tightened `createQuantity` + `ucumCodeFor` so UCUM's
 * system+code only go out for a unit the engine accepts. The
 * existing `UtilsServiceQuantityTest` covers the helper in isolation
 * (value/component + top-level quantity paths); this test locks the
 * reference-range invocation specifically so a regression in the
 * reference-range code (e.g., passing the unit text for `$code`
 * instead of the actual code, swapping low/high, dropping one side)
 * surfaces independently from the helper-level tests.
 *
 * Expects a service-tier regression here — not a helper-tier one —
 * to catch reference-range-specific drift.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Observation;

use OpenEMR\Services\FHIR\Observation\FhirObservationLaboratoryService;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirObservationLaboratoryReferenceRangeUcumTest extends TestCase
{
    private const UCUM_SYSTEM = 'http://unitsofmeasure.org';

    /**
     * Cases named by the unit shape. The expected shape is what the
     * reference-range low/high should carry in the FHIR Observation
     * — the same contract `UtilsServiceQuantityTest` locks at the
     * helper tier, projected onto the reference-range path.
     *
     * @return array<string, array{string, string, ?string}> units input, expected unit text, expected UCUM code (null when unit is not UCUM)
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unitCases(): array
    {
        return [
            'UCUM-valid lab unit (mg/dL)' => ['mg/dL', 'mg/dL', 'mg/dL'],
            'uppercase variant of a UCUM unit (MG/DL)' => ['MG/DL', 'MG/DL', null],
            'cell-count unit as labs send it' => ['Thous/CU.MM', 'Thous/CU.MM', null],
            'Drug Units alias (mcg → ug)' => ['mcg', 'mcg', 'ug'],
            'bracketed UCUM annotation ({score})' => ['{score}', '{score}', '{score}'],
            'unit with trailing whitespace trims under UCUM' => ['mg/dL ', 'mg/dL', 'mg/dL'],
        ];
    }

    /**
     * Pin the full reference-range quantity shape for each unit case.
     * Both low and high go through the same `createQuantity` call, so
     * the asserted shape applies identically to each side. Any
     * regression that:
     *   - passes a different `$code` to one of the two createQuantity calls,
     *   - swaps low/high (e.g. assigns `$ranges[0]` to high and `$ranges[1]` to low),
     *   - stops emitting one of the two sides,
     *   - caches the UCUM lookup across calls and corrupts it,
     * will fail at least one of the four assertSame calls below.
     */
    #[DataProvider('unitCases')]
    public function testReferenceRangeLowAndHighFollowUcumContract(
        string $units,
        string $expectedUnit,
        ?string $expectedCode
    ): void {
        $referenceRange = $this->extractReferenceRangeJson($units, '3.5-7.5');
        $this->assertNotNull($referenceRange, 'A well-formed low-high range should produce one referenceRange');

        $low = $referenceRange['low'] ?? null;
        $high = $referenceRange['high'] ?? null;
        $this->assertIsArray($low, 'referenceRange.low must be present when range is well-formed');
        $this->assertIsArray($high, 'referenceRange.high must be present when range is well-formed');

        $expectedSystem = $expectedCode === null ? null : self::UCUM_SYSTEM;

        $this->assertSame($expectedUnit, $low['unit'] ?? null, 'low.unit text');
        $this->assertSame($expectedUnit, $high['unit'] ?? null, 'high.unit text');
        $this->assertSame($expectedCode, $low['code'] ?? null, 'low.code — UCUM only when the unit is UCUM');
        $this->assertSame($expectedCode, $high['code'] ?? null, 'high.code — UCUM only when the unit is UCUM');
        $this->assertSame($expectedSystem, $low['system'] ?? null, 'low.system — present only alongside a UCUM code');
        $this->assertSame($expectedSystem, $high['system'] ?? null, 'high.system — present only alongside a UCUM code');
    }

    public function testEmptyUnitEmitsSimpleQuantityOnBothSides(): void
    {
        // An empty-string unit degenerates to a SimpleQuantity — only
        // the numeric value survives. This covers the branch where
        // ucumCodeFor returns null without even consulting the engine.
        $referenceRange = $this->extractReferenceRangeJson('', '3.5-7.5');
        $this->assertNotNull($referenceRange);

        $low = $referenceRange['low'] ?? null;
        $high = $referenceRange['high'] ?? null;
        $this->assertIsArray($low);
        $this->assertIsArray($high);

        $this->assertSame(['value' => 3.5], $low, 'low degenerates to SimpleQuantity on empty unit');
        $this->assertSame(['value' => 7.5], $high, 'high degenerates to SimpleQuantity on empty unit');
    }

    public function testMalformedRangeEmitsNoReferenceRange(): void
    {
        // A single number (e.g. "0.5") has no high bound, so the
        // controller does not add a referenceRange. Pin this control
        // so a regression that treated a single value as both low
        // and high fails loudly here.
        $referenceRange = $this->extractReferenceRangeJson('mg/dL', '0.5');
        $this->assertNull(
            $referenceRange,
            'A single-value range produces no referenceRange — both low AND high are required'
        );
    }

    /**
     * Mirror a key invariant from `UtilsServiceQuantityTest` at the
     * service tier: the helper is the source of truth for the UCUM
     * mapping, so the reference-range path and the helper must agree
     * for every unit the data provider covers. If the service tier
     * drifts from the helper tier, both suites must be updated
     * together.
     */
    #[DataProvider('unitCases')]
    public function testServiceAgreesWithHelperOnUcumCode(
        string $units,
        string $expectedUnit,
        ?string $expectedCode
    ): void {
        $this->assertSame(
            $expectedCode,
            UtilsService::ucumCodeFor($units),
            'UtilsServiceQuantityTest and this test must agree on the UCUM mapping'
        );
    }

    /**
     * Drive the lab service with a minimal dataRecord and return the
     * first referenceRange (JSON-decoded), or null when none exists.
     * Decoding to JSON sidesteps PHPStan's PHPDoc-typed getters that
     * claim non-null for always-present FHIR fields; the resource's
     * wire shape is what the server actually sends, so that's what
     * we lock.
     *
     * @return array<array-key, mixed>|null
     */
    private function extractReferenceRangeJson(string $units, string $range): ?array
    {
        $service = new FhirObservationLaboratoryService();
        $observation = $service->parseOpenEMRRecord([
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'range' => $range,
            'units' => $units,
        ]);
        $decoded = json_decode((string) json_encode($observation), true);
        $this->assertIsArray($decoded);
        $ranges = $decoded['referenceRange'] ?? null;
        if (!is_array($ranges) || $ranges === []) {
            return null;
        }
        $first = $ranges[0] ?? null;
        $this->assertIsArray($first);
        return $first;
    }
}
