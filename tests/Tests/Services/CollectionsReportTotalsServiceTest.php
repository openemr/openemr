<?php

/**
 * Collections Report Totals Service Test
 *
 * Tests grand totals calculation for the Collections Report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\Collections\Services\TotalsService;

class CollectionsReportTotalsServiceTest extends TestCase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TotalsService();
    }

    /**
     * Test calculates grand totals from patient groups
     */
    public function testCalculatesGrandTotalsFromPatientGroups(): void
    {
        $patientGroups = [
            [
                'totals' => [
                    'charges' => 1500.00,
                    'adjustments' => -100.00,
                    'paid' => 800.00,
                    'balance' => 600.00,
                ],
            ],
            [
                'totals' => [
                    'charges' => 2500.00,
                    'adjustments' => -200.00,
                    'paid' => 1500.00,
                    'balance' => 800.00,
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($patientGroups);

        $this->assertEquals(4000.00, $totals['charges']);
        $this->assertEquals(-300.00, $totals['adjustments']);
        $this->assertEquals(2300.00, $totals['paid']);
        $this->assertEquals(1400.00, $totals['balance']);
    }

    /**
     * Test calculates aging bucket totals
     */
    public function testCalculatesAgingBucketTotals(): void
    {
        $patientGroups = [
            [
                'totals' => [
                    'charges' => 1500.00,
                    'adjustments' => 0.00,
                    'paid' => 0.00,
                    'balance' => 1500.00,
                    'aging_buckets' => [500.00, 700.00, 300.00],
                ],
            ],
            [
                'totals' => [
                    'charges' => 2000.00,
                    'adjustments' => 0.00,
                    'paid' => 0.00,
                    'balance' => 2000.00,
                    'aging_buckets' => [800.00, 900.00, 300.00],
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($patientGroups);

        $this->assertEquals([1300.00, 1600.00, 600.00], $totals['aging_buckets']);
    }

    /**
     * Test handles empty patient groups
     */
    public function testHandlesEmptyPatientGroups(): void
    {
        $totals = $this->service->calculateGrandTotals([]);

        $this->assertEquals(0.00, $totals['charges']);
        $this->assertEquals(0.00, $totals['adjustments']);
        $this->assertEquals(0.00, $totals['paid']);
        $this->assertEquals(0.00, $totals['balance']);
        $this->assertEquals([], $totals['aging_buckets']);
    }

    /**
     * Test handles patient groups without aging buckets
     */
    public function testHandlesPatientGroupsWithoutAgingBuckets(): void
    {
        $patientGroups = [
            [
                'totals' => [
                    'charges' => 1000.00,
                    'adjustments' => -50.00,
                    'paid' => 500.00,
                    'balance' => 450.00,
                    'aging_buckets' => [],
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($patientGroups);

        $this->assertEquals(1000.00, $totals['charges']);
        $this->assertEquals(450.00, $totals['balance']);
        $this->assertEquals([], $totals['aging_buckets']);
    }

    /**
     * Test calculates balance from charges, adjustments, and paid
     */
    public function testCalculatesBalanceFromFinancials(): void
    {
        $patientGroups = [
            [
                'totals' => [
                    'charges' => 5000.00,
                    'adjustments' => -500.00,
                    'paid' => 3000.00,
                    'balance' => 1500.00, // Should be recalculated
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($patientGroups);

        // Balance = charges + adjustments - paid
        $this->assertEquals(1500.00, $totals['balance']);
    }

    /**
     * Test formats totals for display
     */
    public function testFormatsTotalsForDisplay(): void
    {
        $totals = [
            'charges' => 12345.67,
            'adjustments' => -543.21,
            'paid' => 10000.50,
            'balance' => 1801.96,
            'aging_buckets' => [500.00, 800.00, 501.96],
        ];

        $formatted = $this->service->formatTotals($totals);

        $this->assertEquals('12,345.67', $formatted['charges']);
        $this->assertEquals('-543.21', $formatted['adjustments']);
        $this->assertEquals('10,000.50', $formatted['paid']);
        $this->assertEquals('1,801.96', $formatted['balance']);
        $this->assertEquals(['500.00', '800.00', '501.96'], $formatted['aging_buckets']);
    }

    /**
     * Test aggregates from insurance groups
     */
    public function testAggregatesFromInsuranceGroups(): void
    {
        $insuranceGroups = [
            [
                'totals' => [
                    'charges' => 3000.00,
                    'adjustments' => -150.00,
                    'paid' => 2000.00,
                    'balance' => 850.00,
                ],
            ],
            [
                'totals' => [
                    'charges' => 2000.00,
                    'adjustments' => -100.00,
                    'paid' => 1500.00,
                    'balance' => 400.00,
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($insuranceGroups);

        $this->assertEquals(5000.00, $totals['charges']);
        $this->assertEquals(-250.00, $totals['adjustments']);
        $this->assertEquals(3500.00, $totals['paid']);
        $this->assertEquals(1250.00, $totals['balance']);
    }

    /**
     * Test handles negative balances correctly
     */
    public function testHandlesNegativeBalancesCorrectly(): void
    {
        $patientGroups = [
            [
                'totals' => [
                    'charges' => 1000.00,
                    'adjustments' => -200.00,
                    'paid' => 1500.00, // Overpaid
                    'balance' => -700.00,
                ],
            ],
        ];

        $totals = $this->service->calculateGrandTotals($patientGroups);

        $this->assertEquals(-700.00, $totals['balance']);
    }
}
