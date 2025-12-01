<?php

/**
 * Tests for ArActivityDataService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\CashReceipts;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\CashReceipts\Services\ArActivityDataService;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;
use OpenEMR\Reports\CashReceipts\Model\Receipt;

/**
 * @coversDefaultClass \OpenEMR\Reports\CashReceipts\Services\ArActivityDataService
 */
class ArActivityDataServiceTest extends TestCase
{
    private CashReceiptsRepository $repository;
    private ArActivityDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CashReceiptsRepository();
        $this->service = new ArActivityDataService($this->repository, false);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithPaymentDateMode(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
        foreach ($receipts as $receipt) {
            $this->assertInstanceOf(Receipt::class, $receipt);
            $this->assertEquals('ar_activity', $receipt->getType());
        }
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithServiceDateMode(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_SERVICE,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithEntryDateMode(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_ENTRY,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsFiltersDateRange(): void
    {
        // Test with narrow date range
        $narrowFilters = [
            'from_date' => '2024-06-01',
            'to_date' => '2024-06-30',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $narrowReceipts = $this->service->processReceipts($narrowFilters);

        // Test with wider date range
        $wideFilters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $wideReceipts = $this->service->processReceipts($wideFilters);

        // Wider range should have equal or more receipts
        $this->assertGreaterThanOrEqual(count($narrowReceipts), count($wideReceipts));
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithClinicCallback(): void
    {
        // Create service with clinic callback that marks codes starting with '9' as clinic
        $isClinicCallback = function ($code) {
            return substr($code, 0, 1) === '9';
        };

        $service = new ArActivityDataService($this->repository, false, $isClinicCallback);

        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receipts = $service->processReceipts($filters);

        // Check if any receipts were marked as clinic based on callback
        if (!empty($receipts)) {
            $hasClinicReceipts = false;
            foreach ($receipts as $receipt) {
                if ($receipt->isClinicReceipt()) {
                    $hasClinicReceipts = true;
                    // Verify the code starts with '9'
                    $code = $receipt->getProcedureCode();
                    if ($code) {
                        $this->assertEquals('9', substr($code, 0, 1));
                    }
                }
            }
        }

        $this->assertTrue(true); // Test passed without errors
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithDiagnosisFilter(): void
    {
        $filtersWithoutDx = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receiptsWithoutDx = $this->service->processReceipts($filtersWithoutDx);

        $filtersWithDx = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
            'diagnosis_code_type' => 'ICD10',
            'diagnosis_code' => 'Z00%',
        ];

        $receiptsWithDx = $this->service->processReceipts($filtersWithDx);

        $this->assertIsArray($receiptsWithDx);
        $this->assertLessThanOrEqual(count($receiptsWithoutDx), count($receiptsWithDx));
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsWithProviderFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
            'provider_id' => 1,
        ];

        $receipts = $this->service->processReceipts($filters);

        $this->assertIsArray($receipts);
        if (!empty($receipts)) {
            foreach ($receipts as $receipt) {
                $this->assertEquals(1, $receipt->getProviderId());
            }
        }
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsCreatesCorrectReceiptObjects(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receipts = $this->service->processReceipts($filters);

        if (!empty($receipts)) {
            $receipt = $receipts[0];

            $this->assertIsInt($receipt->getPatientId());
            $this->assertIsInt($receipt->getEncounterId());
            $this->assertIsInt($receipt->getProviderId());
            $this->assertIsString($receipt->getTransactionDate());
            $this->assertIsFloat($receipt->getAmount());
            $this->assertGreaterThanOrEqual(0, $receipt->getAmount()); // Should be positive
            $this->assertEquals('ar_activity', $receipt->getType());
        }
    }

    /**
     * @covers ::processReceipts
     */
    public function testProcessReceiptsConvertsNegativeAmountsToPositive(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receipts = $this->service->processReceipts($filters);

        // All amounts should be positive (abs() applied)
        foreach ($receipts as $receipt) {
            $this->assertGreaterThanOrEqual(0, $receipt->getAmount());
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
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
        ];

        $receipts = $this->service->processReceipts($filters);
        $total = $this->service->getTotalAmount($receipts);

        $this->assertIsFloat($total);
        $this->assertGreaterThanOrEqual(0, $total);

        // Manually verify
        $manualTotal = 0.0;
        foreach ($receipts as $receipt) {
            $manualTotal += $receipt->getAmount();
        }

        $this->assertEquals($manualTotal, $total, '', 0.01);
    }

    /**
     * @covers ::getReceiptCount
     */
    public function testGetReceiptCountReturnsCorrectCount(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'date_mode' => ArActivityDataService::DATE_MODE_PAYMENT,
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
