<?php

/**
 * Data Preparation Service for Sales by Item report
 *
 * Orchestrates all services to prepare complete report data.
 * Converts raw repository data into final report structure.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Repository\SalesByItemsRepository;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class DataPreparationService
{
    private SalesByItemsRepository $repository;
    private BillingItemService $billingService;
    private DrugSalesService $drugService;
    private GroupingService $groupingService;
    private TotalsService $totalsService;

    public function __construct(
        SalesByItemsRepository $repository = null,
        BillingItemService $billingService = null,
        DrugSalesService $drugService = null,
        GroupingService $groupingService = null,
        TotalsService $totalsService = null
    ) {
        $this->repository = $repository ?? new SalesByItemsRepository();
        $this->billingService = $billingService ?? new BillingItemService();
        $this->drugService = $drugService ?? new DrugSalesService();
        $this->groupingService = $groupingService ?? new GroupingService();
        $this->totalsService = $totalsService ?? new TotalsService();
    }

    /**
     * Prepare complete report data (detail view)
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array Report data structure
     */
    public function prepareDetailedReport(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        // Fetch raw data
        $allItems = $this->repository->getAllSalesItems($fromDate, $toDate, $facilityId, $providerId);

        // Process items
        $processedItems = $this->processItems($allItems);

        // Group and format
        $grouped = $this->groupingService->groupItems($processedItems);
        $formattedRows = $this->groupingService->formatGroupedData($grouped);

        // Calculate totals
        $totals = $this->totalsService->calculateTotals($processedItems);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'rows' => $formattedRows,
            'item_count' => count($allItems),
            'total_quantity' => $totals['quantity'],
            'total_amount' => $totals['total'],
        ];
    }

    /**
     * Prepare summary report data (no detail items)
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array Report data structure
     */
    public function prepareSummaryReport(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        // Fetch raw data
        $allItems = $this->repository->getAllSalesItems($fromDate, $toDate, $facilityId, $providerId);

        // Process items
        $processedItems = $this->processItems($allItems);

        // Group and format (summary version)
        $grouped = $this->groupingService->groupItems($processedItems);
        $formattedRows = $this->groupingService->formatGroupedDataSummary($grouped);

        // Calculate totals
        $totals = $this->totalsService->calculateTotals($processedItems);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'rows' => $formattedRows,
            'item_count' => count($allItems),
            'total_quantity' => $totals['quantity'],
            'total_amount' => $totals['total'],
        ];
    }

    /**
     * Prepare export data (for CSV/other formats)
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param bool $detailed Whether to include detail rows
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array Export-ready data
     */
    public function prepareExportData(
        string $fromDate,
        string $toDate,
        bool $detailed = false,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        if ($detailed) {
            return $this->prepareDetailedReport($fromDate, $toDate, $facilityId, $providerId);
        } else {
            return $this->prepareSummaryReport($fromDate, $toDate, $facilityId, $providerId);
        }
    }

    /**
     * Get category totals only
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array Totals by category
     */
    public function getCategoryTotals(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $allItems = $this->repository->getAllSalesItems($fromDate, $toDate, $facilityId, $providerId);
        $processedItems = $this->processItems($allItems);
        return $this->totalsService->calculateTotalsByCategory($processedItems);
    }

    /**
     * Get grand totals
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array ['quantity' => int, 'total' => float]
     */
    public function getGrandTotals(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $allItems = $this->repository->getAllSalesItems($fromDate, $toDate, $facilityId, $providerId);
        return $this->totalsService->calculateTotals($allItems);
    }

    /**
     * Process items through appropriate services
     *
     * @param SalesItem[] $items Items to process
     * @return SalesItem[] Processed items
     */
    private function processItems(array $items): array
    {
        $processed = [];

        foreach ($items as $item) {
            if ($item->getCategory() === 'Products') {
                // Drug sales
                $item = $this->drugService->processItem($item);
            } else {
                // Billing
                $item = $this->billingService->processItem($item);
            }

            $processed[] = $item;
        }

        return $processed;
    }

    /**
     * Get summary statistics
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return array Summary statistics
     */
    public function getSummaryStatistics(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $allItems = $this->repository->getAllSalesItems($fromDate, $toDate, $facilityId, $providerId);
        $processedItems = $this->processItems($allItems);
        return $this->totalsService->getSummaryStatistics($processedItems);
    }
}
