<?php

/**
 * Tests for TotalsService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\CashReceipts;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\CashReceipts\Services\TotalsService;
use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;
use OpenEMR\Reports\CashReceipts\Model\Receipt;

/**
 * @coversDefaultClass \OpenEMR\Reports\CashReceipts\Services\TotalsService
 */
class TotalsServiceTest extends TestCase
{
    private TotalsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TotalsService();
    }

    /**
     * Create mock provider summaries for testing
     */
    private function createMockProviderSummaries(): array
    {
        $summaries = [];

        // Provider 1: 2 professional receipts
        $provider1 = new ProviderSummary(1, 'Dr. Smith');
        $receipt1 = new Receipt([
            'pid' => 1,
            'encounter' => 1,
            'provider_id' => 1,
            'trans_date' => '2024-01-15',
            'amount' => 50.00,
            'type' => 'copay',
            'is_clinic' => false,
        ]);
        $receipt2 = new Receipt([
            'pid' => 2,
            'encounter' => 2,
            'provider_id' => 1,
            'trans_date' => '2024-01-20',
            'amount' => 75.00,
            'type' => 'ar_activity',
            'is_clinic' => false,
        ]);
        $provider1->addReceipt($receipt1);
        $provider1->addReceipt($receipt2);
        $summaries[] = $provider1;

        // Provider 2: 1 professional + 1 clinic receipt
        $provider2 = new ProviderSummary(2, 'Dr. Jones');
        $receipt3 = new Receipt([
            'pid' => 3,
            'encounter' => 3,
            'provider_id' => 2,
            'trans_date' => '2024-01-10',
            'amount' => 100.00,
            'type' => 'copay',
            'is_clinic' => false,
        ]);
        $receipt4 = new Receipt([
            'pid' => 4,
            'encounter' => 4,
            'provider_id' => 2,
            'trans_date' => '2024-01-12',
            'amount' => 25.00,
            'type' => 'ar_activity',
            'is_clinic' => true,
        ]);
        $provider2->addReceipt($receipt3);
        $provider2->addReceipt($receipt4);
        $summaries[] = $provider2;

        return $summaries;
    }

    /**
     * @covers ::calculateGrandTotals
     */
    public function testCalculateGrandTotalsReturnsCorrectStructure(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $totals = $this->service->calculateGrandTotals($summaries);

        $this->assertIsArray($totals);
        $this->assertArrayHasKey('professional', $totals);
        $this->assertArrayHasKey('clinic', $totals);
        $this->assertArrayHasKey('grand', $totals);
    }

    /**
     * @covers ::calculateGrandTotals
     */
    public function testCalculateGrandTotalsCalculatesProfessionalCorrectly(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $totals = $this->service->calculateGrandTotals($summaries);

        // Provider 1: 50 + 75 = 125
        // Provider 2: 100 (clinic 25 excluded)
        // Total professional: 225
        $this->assertEquals(225.00, $totals['professional'], '', 0.01);
    }

    /**
     * @covers ::calculateGrandTotals
     */
    public function testCalculateGrandTotalsCalculatesClinicCorrectly(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $totals = $this->service->calculateGrandTotals($summaries);

        // Only Provider 2 has clinic receipt: 25
        $this->assertEquals(25.00, $totals['clinic'], '', 0.01);
    }

    /**
     * @covers ::calculateGrandTotals
     */
    public function testCalculateGrandTotalsCalculatesGrandTotal(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $totals = $this->service->calculateGrandTotals($summaries);

        // Grand total: 225 + 25 = 250
        $this->assertEquals(250.00, $totals['grand'], '', 0.01);
    }

    /**
     * @covers ::calculateGrandTotals
     */
    public function testCalculateGrandTotalsHandlesEmptyArray(): void
    {
        $totals = $this->service->calculateGrandTotals([]);

        $this->assertEquals(0.0, $totals['professional']);
        $this->assertEquals(0.0, $totals['clinic']);
        $this->assertEquals(0.0, $totals['grand']);
    }

    /**
     * @covers ::getTotalReceiptCount
     */
    public function testGetTotalReceiptCountReturnsCorrectCount(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $count = $this->service->getTotalReceiptCount($summaries);

        // Provider 1: 2 receipts, Provider 2: 2 receipts = 4 total
        $this->assertEquals(4, $count);
    }

    /**
     * @covers ::getTotalReceiptCount
     */
    public function testGetTotalReceiptCountHandlesEmptyArray(): void
    {
        $count = $this->service->getTotalReceiptCount([]);

        $this->assertEquals(0, $count);
    }

    /**
     * @covers ::getTotalEncounterCount
     */
    public function testGetTotalEncounterCountReturnsCorrectCount(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $count = $this->service->getTotalEncounterCount($summaries);

        // Each receipt is unique encounter, so 4 encounters
        $this->assertEquals(4, $count);
    }

    /**
     * @covers ::getTotalEncounterCount
     */
    public function testGetTotalEncounterCountHandlesEmptyArray(): void
    {
        $count = $this->service->getTotalEncounterCount([]);

        $this->assertEquals(0, $count);
    }

    /**
     * @covers ::getAverageReceiptAmount
     */
    public function testGetAverageReceiptAmountCalculatesCorrectly(): void
    {
        $summaries = $this->createMockProviderSummaries();
        $average = $this->service->getAverageReceiptAmount($summaries);

        // Total: 250, Count: 4, Average: 62.50
        $this->assertEquals(62.50, $average, '', 0.01);
    }

    /**
     * @covers ::getAverageReceiptAmount
     */
    public function testGetAverageReceiptAmountReturnsZeroForEmptyArray(): void
    {
        $average = $this->service->getAverageReceiptAmount([]);

        $this->assertEquals(0.0, $average);
    }

    /**
     * @covers ::getAverageReceiptAmount
     */
    public function testGetAverageReceiptAmountHandlesSingleReceipt(): void
    {
        $summaries = [];
        $provider = new ProviderSummary(1, 'Dr. Test');
        $receipt = new Receipt([
            'pid' => 1,
            'encounter' => 1,
            'provider_id' => 1,
            'trans_date' => '2024-01-15',
            'amount' => 100.00,
            'type' => 'copay',
        ]);
        $provider->addReceipt($receipt);
        $summaries[] = $provider;

        $average = $this->service->getAverageReceiptAmount($summaries);

        $this->assertEquals(100.00, $average, '', 0.01);
    }
}
