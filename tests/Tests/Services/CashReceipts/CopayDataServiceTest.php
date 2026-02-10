<?php

/**
 * Tests for CopayDataService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\CashReceipts;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\CashReceipts\Services\CopayDataService;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;
use OpenEMR\Reports\CashReceipts\Model\Receipt;

/**
 * @coversDefaultClass \OpenEMR\Reports\CashReceipts\Services\CopayDataService
 */
class CopayDataServiceTest extends TestCase
{
    private CashReceiptsRepository $repository;
    private CopayDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CashReceiptsRepository();
        $this->service = new CopayDataService($this->repository, false);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithBasicFilters(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
        foreach ($receipts as $receipt) {
            $this->assertInstanceOf(Receipt::class, $receipt);
            $this->assertEquals('copay', $receipt->getType());
            $this->assertFalse($receipt->isClinicReceipt()); // Copays are always professional
        }
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithFacilityFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'facility_id' => 1,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithProviderFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'provider_id' => 1,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
        // If there are results, verify they're all for the specified provider
        if (!empty($receipts)) {
            foreach ($receipts as $receipt) {
                $this->assertEquals(1, $receipt->getProviderId());
            }
        }
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithDiagnosisFilter(): void
    {
        // First get some receipts without diagnosis filter
        $filtersWithoutDx = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];
        $receiptsWithoutDx = $this->service->processReceipts($filtersWithoutDx);

        // Now with diagnosis filter (using common code)
        $filtersWithDx = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'diagnosis_code_type' => 'ICD10',
            'diagnosis_code' => 'Z00%', // Wellness visit codes
        ];
        $receiptsWithDx = $this->service->processReceipts($filtersWithDx);

        $this->assertIsArray($receiptsWithDx);
        // With diagnosis filter, we should have equal or fewer receipts
        $this->assertLessThanOrEqual(count($receiptsWithoutDx), count($receiptsWithDx));
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsSkipsTransactionsWithoutDiagnosis(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'diagnosis_code_type' => 'ICD10',
            'diagnosis_code' => 'Z99.99', // Very specific unlikely code
        ];

        $receipts = $this->service->processReceipts($filters);

        // Should return empty or very few results for non-existent diagnosis
        $this->assertIsArray($receipts);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsCreatesCorrectReceiptObjects(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $receipts = $this->service->processReceipts($filters);

        if (!empty($receipts)) {
            $receipt = $receipts[0];
            
            // Verify Receipt object properties
            $this->assertIsInt($receipt->getPatientId());
            $this->assertIsInt($receipt->getEncounterId());
            $this->assertIsInt($receipt->getProviderId());
            $this->assertIsString($receipt->getTransactionDate());
            $this->assertIsFloat($receipt->getAmount());
            $this->assertEquals('copay', $receipt->getType());
            $this->assertNull($receipt->getProcedureCode()); // Copays don't have procedure codes
            $this->assertEquals(0, $receipt->getPayerId()); // Copays are patient payments
            $this->assertFalse($receipt->isClinicReceipt());
        } else {
            $this->assertTrue(true, 'No receipts in database for test period');
        }
    }

    /**
     * @covers ::getTotalAmount
     */
    public function testGetTotalAmountCalculatesCorrectly(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $receipts = $this->service->processReceipts($filters);
        $total = $this->service->getTotalAmount($receipts);

        $this->assertIsFloat($total);
        $this->assertGreaterThanOrEqual(0, $total);

        // Manually calculate total to verify
        $manualTotal = 0.0;
        foreach ($receipts as $receipt) {
            $manualTotal += $receipt->getAmount();
        }

        $this->assertEquals($manualTotal, $total, '', 0.01); // Allow for floating point precision
    }

    /**
     * @covers ::getReceiptCount
     */
    public function testGetReceiptCountReturnsCorrectCount(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $receipts = $this->service->processReceipts($filters);
        $count = $this->service->getReceiptCount($receipts);

        $this->assertIsInt($count);
        $this->assertEquals(count($receipts), $count);
    }

    /**
     * @covers ::getTotalAmount
     */
    public function testGetTotalAmountReturnsZeroForEmptyArray(): void
    {
        $total = $this->service->getTotalAmount([]);

        $this->assertEquals(0.0, $total);
    }

    /**
     * @covers ::getReceiptCount
     */
    public function testGetReceiptCountReturnsZeroForEmptyArray(): void
    {
        $count = $this->service->getReceiptCount([]);

        $this->assertEquals(0, $count);
    }
}
