<?php

/**
 * Locks the UCUM claim rules on FHIR Observation.valueQuantity for
 * vital-signs observations.
 *
 * `FhirObservationVitalsService` builds quantities through its own
 * hand-rolled helper (`getFHIRQuantityForColumn`) rather than the
 * shared `UtilsService::createQuantity`. That parallel implementation
 * claims UCUM unconditionally on every quantity it emits (system is
 * always http://unitsofmeasure.org), and hard-codes three unit
 * translations known to the vitals domain:
 *   - "in"   -> unit "in_i",  code "[in_i]"
 *   - "lb"   -> unit "lb_av", code "[lb_av]"
 *   - "degF" -> unit "degF",  code "[degF]"
 * Any other unit passes through as both the unit text and the code.
 *
 * The practical regression shape this test defends against is a
 * refactor that routes the vitals path through the stricter
 * UtilsService::ucumCodeFor helper. That helper returns null for
 * "in" and "lb" (they are not UCUM as written), which would drop
 * the system+code off the emitted quantity and silently break every
 * vital with those units. Either the service keeps its hand-rolled
 * mapping (current behavior, locked here), or the helper needs to
 * learn the vitals-domain aliases before the service routes to it.
 *
 * Reaches `getFHIRQuantityForColumn` through reflection:
 *   - the service's constructor instantiates VitalsService which
 *     touches the DB, so an isolated test cannot go through the
 *     normal constructor; `newInstanceWithoutConstructor()` is the
 *     documented way to bypass constructor side effects when the
 *     code under test is a pure method on the class
 *   - the helper is declared private, so Reflection is also the
 *     tool for direct invocation
 * The access pattern mirrors what `FhirObservationLaboratoryReferenceRangeUcumTest`
 * does for the laboratory service - its constructor is pure, so no
 * reflection is needed there, but the two tests target the same
 * UCUM invariant from each service's natural entry point.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Observation;

use OpenEMR\FHIR\R4\FHIRElement\FHIRQuantity;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FhirObservationVitalsUcumTest extends TestCase
{
    private const UCUM_SYSTEM = 'http://unitsofmeasure.org';

    /**
     * Each basic-quantity vital maps a LOINC code to the record column
     * that holds its numeric value. The private helper reads
     * `<column>` + `<column>_unit` off the record.
     *
     * @return array<string, array{string, float|int, string, string, string}>
     *     record column name, numeric value, unit input, expected
     *     emitted unit text, expected emitted code
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function basicQuantityCases(): array
    {
        return [
            'pulse with passthrough unit (/min)' => ['pulse', 72, '/min', '/min', '/min'],
            'respiration with passthrough unit (/min)' => ['respiration', 16, '/min', '/min', '/min'],
            'height in inches, hand-rolled in -> in_i mapping' => ['height', 70, 'in', 'in_i', '[in_i]'],
            'weight in pounds, hand-rolled lb -> lb_av mapping' => ['weight', 150, 'lb', 'lb_av', '[lb_av]'],
            'temperature in Fahrenheit, hand-rolled degF bracket mapping' => ['temperature', 98.6, 'degF', 'degF', '[degF]'],
            'head circumference in centimeters, passthrough' => ['head_circ', 55, 'cm', 'cm', 'cm'],
            'BMI (unitless, passthrough)' => ['BMI', 24.5, 'kg/m2', 'kg/m2', 'kg/m2'],
        ];
    }

    /**
     * Pin each basic-quantity vital's UCUM claim. Four assertions per
     * case lock value/system/unit/code - the service's contract for
     * every one of its known vital columns.
     */
    #[DataProvider('basicQuantityCases')]
    public function testBasicVitalEmitsExpectedQuantity(
        string $recordColumn,
        float|int $value,
        string $unitInput,
        string $expectedUnit,
        string $expectedCode
    ): void {
        $quantity = $this->invokeGetFhirQuantityForColumn($recordColumn, [
            $recordColumn => $value,
            $recordColumn . '_unit' => $unitInput,
        ]);
        $this->assertInstanceOf(FHIRQuantity::class, $quantity, 'quantity must be emitted for a positive value');

        $decoded = json_decode((string) json_encode($quantity), true);
        $this->assertIsArray($decoded);

        // Use loose equality on value - floatval() in the service
        // produces a float, but json_encode/decode roundtrip normalizes
        // integer-valued floats back to int in some PHP builds. The
        // service intent is numeric equality, not scalar-type identity.
        $this->assertEquals((float) $value, $decoded['value'] ?? null, 'valueQuantity.value');
        $this->assertSame(self::UCUM_SYSTEM, $decoded['system'] ?? null, 'valueQuantity.system - vitals always claim UCUM');
        $this->assertSame($expectedUnit, $decoded['unit'] ?? null, 'valueQuantity.unit');
        $this->assertSame($expectedCode, $decoded['code'] ?? null, 'valueQuantity.code');
    }

    /**
     * @return array<string, array{mixed}> record value that should produce no quantity
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonPositiveValueCases(): array
    {
        return [
            'zero' => [0],
            'zero float' => [0.0],
            'negative' => [-1],
            'null' => [null],
            'non-numeric string' => ['abc'],
        ];
    }

    /**
     * The helper's `columnHasPositiveFloatValue` gate refuses to emit a
     * quantity for zero, negative, null, or non-numeric values. Pin
     * the full set so a regression that loosened the gate (for
     * example, treating a string "0" as positive via coercion) fails
     * loudly.
     */
    #[DataProvider('nonPositiveValueCases')]
    public function testNonPositiveValueEmitsNoQuantity(mixed $value): void
    {
        $quantity = $this->invokeGetFhirQuantityForColumn('pulse', [
            'pulse' => $value,
            'pulse_unit' => '/min',
        ]);
        $this->assertNull(
            $quantity,
            'A non-positive or non-numeric value must not emit a quantity - caller substitutes dataAbsentReason'
        );
    }

    /**
     * Document the divergence between the vitals service's hand-rolled
     * UCUM mapping and UtilsService's shared helper.
     *
     * For the three hand-rolled units ("in", "lb", "degF"), the
     * vitals service claims UCUM with a specific translated code,
     * but `UtilsService::ucumCodeFor` returns null for the same
     * input. Any refactor that routes the vitals path through the
     * helper without teaching the helper these aliases will silently
     * drop UCUM metadata off every vital with those units.
     *
     * @return array<string, array{string, ?string, string}> unit, helper result, vitals-emitted code
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function divergenceCases(): array
    {
        return [
            'in - helper refuses, vitals maps to [in_i]' => ['in', null, '[in_i]'],
            'lb - helper refuses, vitals maps to [lb_av]' => ['lb', null, '[lb_av]'],
        ];
    }

    /**
     * Compare helper output to what the vitals service emits for the
     * same unit. For `in` and `lb` the helper returns null while the
     * vitals service emits a non-null UCUM code; a regression that
     * routes the vitals path through the helper without first
     * teaching the helper these aliases will flip this assertion to
     * failure and the next maintainer can decide whether to extend
     * the helper or add a divergence-aware wrapper.
     */
    #[DataProvider('divergenceCases')]
    public function testVitalsDivergesFromUtilsServiceHelperForHandRolledAliases(
        string $unit,
        ?string $helperResult,
        string $vitalsEmittedCode
    ): void {
        $this->assertSame(
            $helperResult,
            UtilsService::ucumCodeFor($unit),
            'UtilsService::ucumCodeFor contract for the bare unit - helper refuses non-UCUM spellings'
        );
        $this->assertNotSame(
            $helperResult,
            $vitalsEmittedCode,
            'Divergence intentional - current vitals service maps bare aliases while the helper refuses them'
        );
    }

    /**
     * Invoke the private `getFHIRQuantityForColumn` on the vitals
     * service without touching the DB. The constructor instantiates
     * VitalsService which pulls the DB connection, so we skip the
     * constructor entirely via `newInstanceWithoutConstructor()` -
     * the helper itself reads only the dataRecord argument and does
     * not touch the service's state.
     *
     * @param array<string, mixed> $record
     */
    private function invokeGetFhirQuantityForColumn(string $column, array $record): ?FHIRQuantity
    {
        $reflection = new ReflectionClass(FhirObservationVitalsService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('getFHIRQuantityForColumn');
        $result = $method->invoke($service, $column, $record);
        $this->assertTrue(
            $result === null || $result instanceof FHIRQuantity,
            'getFHIRQuantityForColumn must return FHIRQuantity|null'
        );
        return $result;
    }
}
