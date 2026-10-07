<?php

/**
 * Isolated tests for building fee sheet NDC and units defaults from a drugs row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\HcpcsDrugDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HcpcsDrugDefaultsTest extends TestCase
{
    /**
     * @param array<string, mixed> $row
     */
    #[DataProvider('drugRowProvider')]
    public function testFromDrugRow(array $row, string $ndcInfo, ?int $units): void
    {
        $defaults = HcpcsDrugDefaults::fromDrugRow($row);
        $this->assertSame($ndcInfo, $defaults->ndcInfo);
        $this->assertSame($units, $defaults->units);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function drugRowProvider(): array
    {
        return [
            'full NDC details and units' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => '40', 'ndc_uom' => 'ML', 'ndc_quantity' => '1.000'],
                'N40009-3073-01   ML1',
                40,
            ],
            'fractional NDC quantity' => [
                ['ndc_number' => '00009307301', 'billing_units' => '20', 'ndc_uom' => 'ML', 'ndc_quantity' => '0.500'],
                'N400009307301   ML0.5',
                20,
            ],
            'NDC number only' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => null, 'ndc_uom' => '', 'ndc_quantity' => null],
                '0009-3073-01',
                null,
            ],
            'unknown NDC unit falls back to the bare NDC' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => '40', 'ndc_uom' => 'XX', 'ndc_quantity' => '1.000'],
                '0009-3073-01',
                40,
            ],
            'zero NDC quantity falls back to the bare NDC' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => '40', 'ndc_uom' => 'ML', 'ndc_quantity' => '0.000'],
                '0009-3073-01',
                40,
            ],
            'no NDC number' => [
                ['ndc_number' => '', 'billing_units' => '40', 'ndc_uom' => 'ML', 'ndc_quantity' => '1.000'],
                '',
                40,
            ],
            'zero units means no default' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => '0', 'ndc_uom' => 'ML', 'ndc_quantity' => '1.000'],
                'N40009-3073-01   ML1',
                null,
            ],
            'native int and float values' => [
                ['ndc_number' => '0009-3073-01', 'billing_units' => 80, 'ndc_uom' => 'ME', 'ndc_quantity' => 80.0],
                'N40009-3073-01   ME80',
                80,
            ],
        ];
    }
}
