<?php

/**
 * Isolated tests for Frequency trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Qrda\Helpers;

use Mustache_Context;
use OpenEMR\Services\Qrda\Helpers\Frequency;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Concrete class to host the Frequency trait for testing.
 */
class FrequencyHost
{
    use Frequency;
}

#[Group('isolated')]
class FrequencyTest extends TestCase
{
    private FrequencyHost $host;

    protected function setUp(): void
    {
        $this->host = new FrequencyHost();
    }

    /**
     * @return array{low: int, high: ?int, unit: string, institution_specified: bool, code_system: string, code_system_name: string, display_name: string}
     */
    private function freqEntry(int $low, ?int $high, string $unit): array
    {
        return [
            'low' => $low,
            'high' => $high,
            'unit' => $unit,
            'institution_specified' => false,
            'code_system' => '',
            'code_system_name' => '',
            'display_name' => '',
        ];
    }

    private function makeContext(string $code): Mustache_Context
    {
        $context = $this->createMock(Mustache_Context::class);
        $context->method('find')
            ->with('code')
            ->willReturn($code);
        return $context;
    }

    /** @param array{low: int, high: int|null, unit: string} $entry */
    private function assertPointFrequency(string $result, array $entry, bool $institutionSpecified): void
    {
        $this->assertStringContainsString("xsi:type='PIVL_TS'", $result);
        $this->assertStringContainsString("value='" . $entry['low'] . "'", $result);
        $this->assertStringContainsString("unit='" . $entry['unit'] . "'", $result);
        if ($institutionSpecified) {
            $this->assertStringContainsString("institutionSpecified='true'", $result);
        } else {
            $this->assertStringNotContainsString('institutionSpecified', $result);
        }
    }

    /** @param array{low: int, high: int|null, unit: string} $entry */
    private function assertRangeFrequency(string $result, array $entry, bool $institutionSpecified): void
    {
        $this->assertStringContainsString("xsi:type='PIVL_TS'", $result);
        $this->assertStringContainsString("xsi:type='IVL_PQ'", $result);
        $this->assertStringContainsString("<low value='" . $entry['low'] . "'", $result);
        $this->assertStringContainsString("<high value='" . $entry['high'] . "'", $result);
        if ($institutionSpecified) {
            $this->assertStringContainsString("institutionSpecified='true'", $result);
        } else {
            $this->assertStringNotContainsString('institutionSpecified', $result);
        }
    }

    public function testInstitutionNotSpecifiedPointFrequency(): void
    {
        $entry = $this->freqEntry(low: 4, high: null, unit: 'h');
        $result = $this->host->institution_not_specified_point_frequency($entry);
        $this->assertPointFrequency($result, $entry, false);
    }

    public function testInstitutionNotSpecifiedRangeFrequency(): void
    {
        $entry = $this->freqEntry(low: 2, high: 4, unit: 'h');
        $result = $this->host->institution_not_specified_range_frequency($entry);
        $this->assertRangeFrequency($result, $entry, false);
    }

    public function testInstitutionSpecifiedPointFrequency(): void
    {
        $entry = $this->freqEntry(low: 24, high: null, unit: 'h');
        $result = $this->host->institution_specified_point_frequency($entry);
        $this->assertPointFrequency($result, $entry, true);
    }

    public function testInstitutionSpecifiedRangeFrequency(): void
    {
        $entry = $this->freqEntry(low: 12, high: 24, unit: 'h');
        $result = $this->host->institution_specified_range_frequency($entry);
        $this->assertRangeFrequency($result, $entry, true);
    }

    public function testMedicationFrequencyDispatchesNotSpecifiedPoint(): void
    {
        // 225756002: every 4 hours, institution_specified=false, no high
        $context = $this->makeContext('225756002');
        $result = $this->host->medication_frequency($context);

        $this->assertStringContainsString("value='4'", $result);
        $this->assertStringNotContainsString('institutionSpecified', $result);
        $this->assertStringNotContainsString('IVL_PQ', $result);
    }

    public function testMedicationFrequencyDispatchesNotSpecifiedRange(): void
    {
        // 225752000: every 2-4 hours, institution_specified=false, has high
        $context = $this->makeContext('225752000');
        $result = $this->host->medication_frequency($context);

        $this->assertStringContainsString("<low value='2'", $result);
        $this->assertStringContainsString("<high value='4'", $result);
        $this->assertStringNotContainsString('institutionSpecified', $result);
    }

    public function testMedicationFrequencyDispatchesSpecifiedPoint(): void
    {
        // 229797004: once daily, institution_specified=true, no high
        $context = $this->makeContext('229797004');
        $result = $this->host->medication_frequency($context);

        $this->assertStringContainsString("institutionSpecified='true'", $result);
        $this->assertStringContainsString("value='24'", $result);
        $this->assertStringNotContainsString('IVL_PQ', $result);
    }

    public function testMedicationFrequencyDispatchesSpecifiedRange(): void
    {
        // 396107007: 1-2 times/day, institution_specified=true, has high
        $context = $this->makeContext('396107007');
        $result = $this->host->medication_frequency($context);

        $this->assertStringContainsString("institutionSpecified='true'", $result);
        $this->assertStringContainsString("<low value='12'", $result);
        $this->assertStringContainsString("<high value='24'", $result);
    }

    public function testMedicationFrequencyDefaultsToEvery24Hours(): void
    {
        // Unknown code falls back to 396125000 (every 24h, not institution_specified)
        $context = $this->makeContext('unknown-code');
        $result = $this->host->medication_frequency($context);

        $this->assertStringContainsString("value='24'", $result);
        $this->assertStringNotContainsString('institutionSpecified', $result);
    }

    public function testMedicationFrequencyHandlesNonStringCode(): void
    {
        $context = $this->createMock(Mustache_Context::class);
        $context->method('find')
            ->with('code')
            ->willReturn(null);
        $result = $this->host->medication_frequency($context);

        // Falls back to default (every 24h)
        $this->assertStringContainsString("value='24'", $result);
        $this->assertStringNotContainsString('institutionSpecified', $result);
    }
}
