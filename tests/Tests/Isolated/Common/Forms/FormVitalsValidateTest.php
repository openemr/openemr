<?php

/**
 * Isolated FormVitals Validation Test
 *
 * Tests the validate() method on FormVitals against VitalsFieldRanges.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Davit Mnatsakanyan
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms;

use OpenEMR\Common\Forms\FormVitals;
use OpenEMR\Common\Forms\VitalsFieldRanges;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormVitalsValidateTest extends TestCase
{
    protected function setUp(): void
    {
        // FormVitals → ORDataObject constructor reads globals. Provide required values.
        $GLOBALS['pid'] ??= 1;
        $GLOBALS['adodb'] ??= ['db' => null];
        // xl() needs translation disabled to work without database.
        $GLOBALS['disable_translation'] = true;

        // Load translation helpers so xl() is defined as a passthrough.
        $translationFile = realpath(__DIR__ . '/../../../../../library/translation.inc.php');
        if ($translationFile !== false && !function_exists('xl')) {
            require_once $translationFile;
        }
    }

    private function createVitals(): FormVitals
    {
        return new FormVitals();
    }

    /**
     * @return array<string, array{string, float|int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function validValueProvider(): array
    {
        return [
            'weight mid-range' => ['weight', 150],
            'height mid-range' => ['height', 66],
            'bps mid-range' => ['bps', 120],
            'bpd mid-range' => ['bpd', 80],
            'pulse mid-range' => ['pulse', 72],
            'respiration mid-range' => ['respiration', 16],
            'temperature mid-range' => ['temperature', 98.6],
            'oxygen_saturation mid-range' => ['oxygen_saturation', 97],
            'oxygen_flow_rate mid-range' => ['oxygen_flow_rate', 2],
            'inhaled_oxygen_concentration mid-range' => ['inhaled_oxygen_concentration', 40],
            'head_circ mid-range' => ['head_circ', 14],
            'waist_circ mid-range' => ['waist_circ', 32],
            'ped_weight_height mid-range' => ['ped_weight_height', 50],
            'ped_bmi mid-range' => ['ped_bmi', 50],
            'ped_head_circ mid-range' => ['ped_head_circ', 50],
        ];
    }

    #[DataProvider('validValueProvider')]
    public function testValidValueProducesNoErrorsOrWarnings(string $field, float|int $value): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = $value;
        $result = $vitals->validate();
        $this->assertSame([], $result['errors'], "Expected no errors for {$field}={$value}");
        $this->assertSame([], $result['warnings'], "Expected no warnings for {$field}={$value}");
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function allFieldsProvider(): array
    {
        return [
            'weight' => ['weight'],
            'height' => ['height'],
            'bps' => ['bps'],
            'bpd' => ['bpd'],
            'pulse' => ['pulse'],
            'respiration' => ['respiration'],
            'temperature' => ['temperature'],
            'oxygen_saturation' => ['oxygen_saturation'],
            'oxygen_flow_rate' => ['oxygen_flow_rate'],
            'inhaled_oxygen_concentration' => ['inhaled_oxygen_concentration'],
            'head_circ' => ['head_circ'],
            'waist_circ' => ['waist_circ'],
            'ped_weight_height' => ['ped_weight_height'],
            'ped_bmi' => ['ped_bmi'],
            'ped_head_circ' => ['ped_head_circ'],
        ];
    }

    #[DataProvider('allFieldsProvider')]
    public function testNonNumericValueProducesError(string $field): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = 'abc';
        $result = $vitals->validate();
        $this->assertNotEmpty($result['errors'], "Expected error for non-numeric {$field}");
    }

    #[DataProvider('allFieldsProvider')]
    public function testNegativeValueProducesError(string $field): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = -1;
        $result = $vitals->validate();
        $this->assertNotEmpty($result['errors'], "Expected error for negative {$field}");
    }

    #[DataProvider('allFieldsProvider')]
    public function testValueAboveMaxProducesError(string $field): void
    {
        $range = VitalsFieldRanges::getRangeForField($field);
        $this->assertNotNull($range);

        $vitals = $this->createVitals();
        $vitals->$field = $range['max'] + 1;
        $result = $vitals->validate();
        $this->assertNotEmpty($result['errors'], "Expected error for {$field} above max");
    }

    #[DataProvider('allFieldsProvider')]
    public function testNullValueIsSkipped(string $field): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = null;
        $result = $vitals->validate();
        $this->assertSame([], $result['errors'], "Expected no errors for null {$field}");
        $this->assertSame([], $result['warnings'], "Expected no warnings for null {$field}");
    }

    #[DataProvider('allFieldsProvider')]
    public function testEmptyStringIsSkipped(string $field): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = '';
        $result = $vitals->validate();
        $this->assertSame([], $result['errors'], "Expected no errors for empty string {$field}");
        $this->assertSame([], $result['warnings'], "Expected no warnings for empty string {$field}");
    }

    #[DataProvider('allFieldsProvider')]
    public function testZeroValueIsValidated(string $field): void
    {
        $vitals = $this->createVitals();
        $vitals->$field = 0;
        $result = $vitals->validate();
        // Zero should NOT be skipped — it should be validated.
        // For all fields, zero is within hard bounds (min=0), so no error.
        // But for fields with warningMin > 0, it should produce a warning.
        $range = VitalsFieldRanges::getRangeForField($field);
        $this->assertNotNull($range);
        $this->assertSame([], $result['errors'], "Zero should not produce errors for {$field}");

        if ($range['warningMin'] > 0) {
            $this->assertNotEmpty($result['warnings'], "Zero should produce a warning for {$field} (warningMin={$range['warningMin']})");
        }
    }

    public function testWarningBandTriggersWarningNotError(): void
    {
        // oxygen_saturation has warningMin=70, min=0
        // A value of 50 is within hard bounds but outside warning range
        $vitals = $this->createVitals();
        $vitals->oxygen_saturation = 50;
        $result = $vitals->validate();
        $this->assertSame([], $result['errors'], 'Value within hard bounds should not error');
        $this->assertNotEmpty($result['warnings'], 'Value outside warning range should warn');
    }

    public function testMultipleFieldsValidatedIndependently(): void
    {
        $vitals = $this->createVitals();
        $vitals->bps = 120;
        $vitals->bpd = 999; // above max of 300
        $result = $vitals->validate();
        $this->assertCount(1, $result['errors'], 'Only the out-of-range field should error');
    }
}
