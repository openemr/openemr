<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\DaySheet;

use OpenEMR\Billing\DaySheet\BillRow;
use OpenEMR\Billing\DaySheet\DaySheetAggregator;
use PHPUnit\Framework\TestCase;

final class DaySheetAggregatorTest extends TestCase
{
    private DaySheetAggregator $aggregator;

    protected function setUp(): void
    {
        $this->aggregator = new DaySheetAggregator();
    }

    public function testEmptyInputProducesEmptyTotals(): void
    {
        $totals = $this->aggregator->aggregate([]);

        $this->assertSame([], $totals->userTotals);
        $this->assertSame([], $totals->providerTotals);
        $this->assertTrue($totals->grandTotals->isAllZero());
    }

    public function testFeeRowAccumulatesIntoUserAndProviderAndGrand(): void
    {
        $totals = $this->aggregator->aggregate([
            $this->billingRow(user: 'alice', providerId: '7', fee: 100.0),
            $this->billingRow(user: 'alice', providerId: '7', fee: 50.0),
        ]);

        $this->assertCount(1, $totals->userTotals);
        $this->assertSame('alice', $totals->userTotals[0]->key);
        $this->assertSame(150.0, $totals->userTotals[0]->fee);

        $this->assertCount(1, $totals->providerTotals);
        $this->assertSame('7', $totals->providerTotals[0]->key);
        $this->assertSame(150.0, $totals->providerTotals[0]->fee);

        $this->assertSame(150.0, $totals->grandTotals->fee);
    }

    public function testInsurancePaymentAndRefundSplitBySign(): void
    {
        $totals = $this->aggregator->aggregate([
            $this->insurancePayment(user: 'alice', providerId: '7', insCode: 200.0, adjust: 5.0),
            $this->insurancePayment(user: 'alice', providerId: '7', insCode: -75.0, adjust: 0.0),
        ]);

        $this->assertSame(200.0, $totals->userTotals[0]->insPay);
        $this->assertSame(-75.0, $totals->userTotals[0]->insRef);
        $this->assertSame(5.0, $totals->userTotals[0]->insAdj);
    }

    public function testPatientPaymentAndRefundSplitBySignWithPcpExclusion(): void
    {
        $totals = $this->aggregator->aggregate([
            $this->patientPayment(user: 'alice', providerId: '7', patCode: 50.0, payType: 'CASH', adjust: 2.0),
            $this->patientPayment(user: 'alice', providerId: '7', patCode: -10.0, payType: 'CASH', adjust: 0.0),
            $this->patientPayment(user: 'alice', providerId: '7', patCode: -25.0, payType: 'PCP', adjust: 0.0),
        ]);

        $this->assertSame(50.0, $totals->userTotals[0]->patPay);
        $this->assertSame(-10.0, $totals->userTotals[0]->patRef);
        $this->assertSame(2.0, $totals->userTotals[0]->patAdj);
    }

    public function testRowsBeyondTwentyUsersAreNotDropped(): void
    {
        $rows = [];
        for ($i = 0; $i < 25; $i++) {
            $rows[] = $this->billingRow(user: "user{$i}", providerId: '1', fee: 1.0);
        }

        $totals = $this->aggregator->aggregate($rows);

        $this->assertCount(25, $totals->userTotals);
        $this->assertSame(25.0, $totals->grandTotals->fee);
    }

    public function testZeroOnlySlotsAreFilteredFromUserAndProviderLists(): void
    {
        $totals = $this->aggregator->aggregate([
            $this->billingRow(user: 'alice', providerId: '7', fee: 100.0),
            // bob has only adjustment-zero rows; should be filtered out:
            new BillRow('bob', '8', 'Insurance Payment', '', 0, 0, 0, 0, 0),
        ]);

        $this->assertCount(1, $totals->userTotals);
        $this->assertSame('alice', $totals->userTotals[0]->key);
    }

    public function testAcceptsArrayRowsAndCoercesNullsToZero(): void
    {
        $totals = $this->aggregator->aggregate([
            ['user' => 'alice', 'provider_id' => '7', 'code_type' => '', 'fee' => 100],
            ['user' => 'alice', 'provider_id' => '7', 'code_type' => 'Insurance Payment', 'ins_code' => '50.5'],
        ]);

        $this->assertSame(100.0, $totals->userTotals[0]->fee);
        $this->assertSame(50.5, $totals->userTotals[0]->insPay);
    }

    private function billingRow(string $user, string $providerId, float $fee): BillRow
    {
        return new BillRow($user, $providerId, '', '', $fee, 0, 0, 0, 0);
    }

    private function insurancePayment(string $user, string $providerId, float $insCode, float $adjust): BillRow
    {
        return new BillRow($user, $providerId, 'Insurance Payment', '', 0, $insCode, 0, $adjust, 0);
    }

    private function patientPayment(string $user, string $providerId, float $patCode, string $payType, float $adjust): BillRow
    {
        return new BillRow($user, $providerId, 'Patient Payment', $payType, 0, 0, $patCode, 0, $adjust);
    }
}
