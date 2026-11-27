<?php

/**
 * Isolated tests for NoChargeLine: which fees count as no charge.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\NoChargeLine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NoChargeLineTest extends TestCase
{
    #[DataProvider('feeProvider')]
    public function testAZeroFeeIsNoChargeHoweverItIsWritten(mixed $fee, bool $expected): void
    {
        $this->assertSame($expected, NoChargeLine::hasNoCharge($fee));
    }

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function feeProvider(): array
    {
        return [
            // What the billing table's decimal column gives back for a line with no charge:
            // the old check, a negated string, took this one for a charge.
            'zero as the database returns it' => ['0.00', true],
            'zero' => ['0', true],
            'zero with spaces' => [' 0.00 ', true],
            'empty' => ['', true],
            'null' => [null, true],
            'integer zero' => [0, true],
            'float zero' => [0.0, true],
            'not a number' => ['N/A', true],
            'a charge' => ['100.00', false],
            'a cent' => ['0.01', false],
            'a credit' => ['-25.00', false],
            'a float charge' => [12.5, false],
        ];
    }
}
