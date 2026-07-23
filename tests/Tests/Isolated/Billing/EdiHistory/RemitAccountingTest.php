<?php

/**
 * Isolated tests for edih_835_accounting_balanced().
 *
 * Regression coverage for two historical bugs in the 835 payment html
 * trailer's Balanced/Not Balanced determination:
 * - an (int) cast truncated fractional dollars before rounding, so a
 *   balanced remit of fee 100.00 vs pmt 99.50 + adj 0.50 compared as
 *   100 vs 99 and rendered Not Balanced
 * - float equality on sums of rounded floats failed on binary
 *   representation drift (0.10 + 0.20 != 0.30), so balanced remits
 *   could render Not Balanced by ~1e-17
 *
 * The balance check now compares fee and accounted totals in integer
 * cents, which these tests pin down with synthetic amounts only.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\RemitAccounting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RemitAccountingTest extends TestCase
{
    /**
     * Shape the totals array the way edih_835_payment_html() builds it.
     *
     * @return array<string, float>
     */
    private static function acctng(
        float $fee,
        float $pmt = 0.0,
        float $clmadj = 0.0,
        float $svcadj = 0.0,
        float $svcptrsp = 0.0,
        float $plbadj = 0.0
    ): array {
        return [
            'fee' => $fee,
            'pmt' => $pmt,
            'clmadj' => $clmadj,
            'svcadj' => $svcadj,
            'svcptrsp' => $svcptrsp,
            'plbadj' => $plbadj,
        ];
    }

    /**
     * @return array<string, array{array<string, float>, bool}>
     */
    public static function remitProvider(): array
    {
        return [
            'fully paid, no adjustments' => [
                self::acctng(fee: 125.00, pmt: 125.00),
                true,
            ],
            'fractional dollars balance (old int cast truncated these)' => [
                self::acctng(fee: 100.00, pmt: 99.50, clmadj: 0.50),
                true,
            ],
            'float drift 0.10 + 0.20 vs 0.30 (old float == failed here)' => [
                self::acctng(fee: 0.30, pmt: 0.10, clmadj: 0.20),
                true,
            ],
            'accumulated drift across all components' => [
                self::acctng(fee: 1.00, pmt: 0.10, clmadj: 0.20, svcadj: 0.30, svcptrsp: 0.15, plbadj: 0.25),
                true,
            ],
            'negative plb adjustment balances' => [
                self::acctng(fee: 50.00, pmt: 60.00, plbadj: -10.00),
                true,
            ],
            'medicare sequestration style split' => [
                self::acctng(fee: 87.62, pmt: 68.51, clmadj: 1.40, svcadj: 0.19, svcptrsp: 17.52),
                true,
            ],
            'zero-dollar remit' => [
                self::acctng(fee: 0.00),
                true,
            ],
            'off by one cent' => [
                self::acctng(fee: 100.00, pmt: 99.99),
                false,
            ],
            'off by one cent the other way' => [
                self::acctng(fee: 100.00, pmt: 100.01),
                false,
            ],
            'unpaid remit' => [
                self::acctng(fee: 250.00),
                false,
            ],
            'adjustment overshoots' => [
                self::acctng(fee: 40.00, pmt: 40.00, clmadj: 5.00),
                false,
            ],
        ];
    }

    /**
     * @param array<string, float> $acctng
     */
    #[DataProvider('remitProvider')]
    public function testBalanceDetermination(array $acctng, bool $expected): void
    {
        $this->assertSame($expected, RemitAccounting::isBalanced($acctng));
    }

    /**
     * The production caller rounds every component to 2 decimals with
     * round((float)$v, 2) before the balance check; verify the pair
     * behaves for values carrying accumulation noise, as sums of many
     * SVC/CAS amounts do.
     */
    public function testBalancedAfterProductionStyleRounding(): void
    {
        $acctng = self::acctng(
            fee: 0.1 + 0.2 + 99.7,       // 99.999999999999986 in binary
            pmt: 100.00
        );
        $acctng = array_map(static fn($v): float => round((float)$v, 2), $acctng);

        $this->assertTrue(RemitAccounting::isBalanced($acctng));
    }
}
