<?php

/**
 * Locks the expected-rejection transforms on the Synthea FHIR bundle
 * importer (#15441): the tier-2 LOINC bans, the item-less CarePlan drop,
 * and the per-bundle SpO2 duplicate drop.
 *
 * The importer's constructor only stores strings and registers transform
 * callables (no I/O), so a real instance is safe in an isolated test.
 * Private transforms are reached through ReflectionMethod, mirroring the
 * documented access pattern in FhirObservationVitalsUcumTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve-Wiig
 * @copyright Copyright (c) 2026 Steve-Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Import;

use OpenEMR\Services\FHIR\Import\FhirBundleImporter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirBundleImporterTransformTest extends TestCase
{
    /**
     * @param array<array-key, mixed> $resource
     * @param array<string, array<array-key, mixed>> $bundleIndex
     * @return array<array-key, mixed>|null
     */
    private function invokeStatic(string $method, array $resource, array $bundleIndex = []): ?array
    {
        $m = new \ReflectionMethod(FhirBundleImporter::class, $method);
        $result = $m->invoke(null, $resource, $bundleIndex);
        return is_array($result) ? $result : null;
    }

    private function newImporter(): FhirBundleImporter
    {
        return new FhirBundleImporter('https://localhost', 'default', '.', 'admin', 'pass');
    }

    /**
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>|null
     */
    private function invokeSpo2(FhirBundleImporter $importer, array $resource): ?array
    {
        $m = new \ReflectionMethod(FhirBundleImporter::class, 'transformObservationDropDuplicateSpo2');
        $result = $m->invoke($importer, $resource, []);
        return is_array($result) ? $result : null;
    }

    /** @return array<array-key, mixed> */
    private function observation(string $loinc, string $category = 'vital-signs'): array
    {
        return [
            'resourceType' => 'Observation',
            'category' => [['coding' => [['code' => $category]]]],
            'code' => ['coding' => [['code' => $loinc]]],
        ];
    }

    /** @return array<array-key, mixed> */
    private function spo2(string $loinc, string $encounter, string $when): array
    {
        $o = $this->observation($loinc);
        $o['encounter'] = ['reference' => $encounter];
        $o['effectiveDateTime'] = $when;
        return $o;
    }

    /** @return array<string, array{0: string}> */
    public static function rejectedLoincProvider(): array
    {
        return [
            'BMI' => ['39156-5'],
            'pain scale' => ['72514-3'],
            'dialysis weight difference' => ['74006-8'],
            'right eye IOP' => ['79892-6'],
            'left eye IOP' => ['79893-4'],
            'NT-proBNP' => ['71425-3'],
        ];
    }

    #[DataProvider('rejectedLoincProvider')]
    public function testRejectedLoincsAreDropped(string $loinc): void
    {
        $this->assertNull($this->invokeStatic('transformObservationDropRejectedLoincs', $this->observation($loinc)));
    }

    public function testSupportedVitalIsKept(): void
    {
        $o = $this->observation('8867-4');
        $this->assertSame($o, $this->invokeStatic('transformObservationDropRejectedLoincs', $o));
    }

    public function testNonVitalCategoryIsDropped(): void
    {
        $this->assertNull($this->invokeStatic('transformObservationDropRejectedLoincs', $this->observation('71425-3', 'laboratory')));
    }

    public function testUncategorizedObservationIsKept(): void
    {
        $o = ['resourceType' => 'Observation', 'code' => ['coding' => [['code' => '8867-4']]]];
        $this->assertSame($o, $this->invokeStatic('transformObservationDropRejectedLoincs', $o));
    }

    /** @return array<string, array{0: array<array-key, mixed>}> */
    public static function emptyActivityProvider(): array
    {
        return [
            'missing' => [[]],
            'empty array' => [['activity' => []]],
            'not an array' => [['activity' => 'x']],
        ];
    }

    /** @param array<array-key, mixed> $plan */
    #[DataProvider('emptyActivityProvider')]
    public function testCareplanWithoutActivityIsDropped(array $plan): void
    {
        $this->assertNull($this->invokeStatic('transformCareplanDropNoActivity', $plan));
    }

    public function testCareplanWithActivityIsKept(): void
    {
        $plan = ['resourceType' => 'CarePlan', 'activity' => [['detail' => ['status' => 'scheduled']]]];
        $this->assertSame($plan, $this->invokeStatic('transformCareplanDropNoActivity', $plan));
    }

    public function testForcePlanIntentStillApplies(): void
    {
        $plan = ['resourceType' => 'CarePlan', 'intent' => 'order', 'activity' => [[]]];
        $out = $this->invokeStatic('transformCareplanForcePlanIntent', $plan);
        $this->assertNotNull($out);
        $this->assertSame('plan', $out['intent']);
    }

    public function testSpo2CrossCodeDuplicateIsDropped(): void
    {
        $i = $this->newImporter();
        $first = $this->spo2('2708-6', 'urn:uuid:enc-1', '2026-01-01T00:00:00Z');
        $second = $this->spo2('59408-5', 'urn:uuid:enc-1', '2026-01-01T00:00:00Z');
        $this->assertSame($first, $this->invokeSpo2($i, $first));
        $this->assertNull($this->invokeSpo2($i, $second));
    }

    public function testSpo2SameCodeDuplicateIsDropped(): void
    {
        $i = $this->newImporter();
        $o = $this->spo2('2708-6', 'urn:uuid:enc-1', '2026-01-01T00:00:00Z');
        $this->assertSame($o, $this->invokeSpo2($i, $o));
        $this->assertNull($this->invokeSpo2($i, $o));
    }

    public function testSpo2DifferentTimeIsKept(): void
    {
        $i = $this->newImporter();
        $a = $this->spo2('2708-6', 'urn:uuid:enc-1', '2026-01-01T00:00:00Z');
        $b = $this->spo2('59408-5', 'urn:uuid:enc-1', '2026-01-01T01:00:00Z');
        $this->assertSame($a, $this->invokeSpo2($i, $a));
        $this->assertSame($b, $this->invokeSpo2($i, $b));
    }

    public function testSpo2DifferentEncounterIsKept(): void
    {
        $i = $this->newImporter();
        $a = $this->spo2('2708-6', 'urn:uuid:enc-1', '2026-01-01T00:00:00Z');
        $b = $this->spo2('59408-5', 'urn:uuid:enc-2', '2026-01-01T00:00:00Z');
        $this->assertSame($a, $this->invokeSpo2($i, $a));
        $this->assertSame($b, $this->invokeSpo2($i, $b));
    }

    public function testSpo2WithoutEncounterIsKept(): void
    {
        $i = $this->newImporter();
        $o = $this->observation('2708-6');
        $o['effectiveDateTime'] = '2026-01-01T00:00:00Z';
        $this->assertSame($o, $this->invokeSpo2($i, $o));
        $this->assertSame($o, $this->invokeSpo2($i, $o));
    }

    public function testSpo2WithoutEffectiveDateTimeIsKept(): void
    {
        $i = $this->newImporter();
        $o = $this->observation('59408-5');
        $o['encounter'] = ['reference' => 'urn:uuid:enc-1'];
        $this->assertSame($o, $this->invokeSpo2($i, $o));
    }

    public function testNonSpo2ObservationIsUntouchedByDedup(): void
    {
        $i = $this->newImporter();
        $o = $this->observation('8867-4');
        $o['encounter'] = ['reference' => 'urn:uuid:enc-1'];
        $o['effectiveDateTime'] = '2026-01-01T00:00:00Z';
        $this->assertSame($o, $this->invokeSpo2($i, $o));
        $this->assertSame($o, $this->invokeSpo2($i, $o));
    }
}
