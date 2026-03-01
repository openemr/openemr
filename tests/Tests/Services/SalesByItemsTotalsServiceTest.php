<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\SalesByItems\Services\TotalsService;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

/**
 * SalesByItemsTotalsServiceTest - TDD tests for totals calculations (Phase 2)
 *
 * Tests the service that calculates and formats totals at various levels.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SalesByItemsTotalsServiceTest extends TestCase
{
    private TotalsService $service;

    protected function setUp(): void
    {
        $this->service = new TotalsService();
    }

    /**
     * Test 1: Calculate basic totals
     */
    public function testCalculateBasicTotals(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 2, 400.00, '101.102'),
            new SalesItem(102, 103, 'Products', 'Bandage', '2025-01-03', 10, 50.00, '102.103'),
        ];

        // Act
        $totals = $this->service->calculateTotals($items);

        // Assert
        $this->assertEquals(13, $totals['quantity']);
        $this->assertEquals(600.00, $totals['total']);
    }

    /**
     * Test 2: Calculate empty totals
     */
    public function testCalculateEmptyTotals(): void
    {
        // Arrange
        $items = [];

        // Act
        $totals = $this->service->calculateTotals($items);

        // Assert
        $this->assertEquals(0, $totals['quantity']);
        $this->assertEquals(0.0, $totals['total']);
    }

    /**
     * Test 3: Calculate single item totals
     */
    public function testCalculateSingleItemTotals(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 5, 750.00, '100.101'),
        ];

        // Act
        $totals = $this->service->calculateTotals($items);

        // Assert
        $this->assertEquals(5, $totals['quantity']);
        $this->assertEquals(750.00, $totals['total']);
    }

    /**
     * Test 4: Calculate totals by category
     */
    public function testCalculateTotalsByCategory(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 2, 400.00, '101.102'),
            new SalesItem(102, 103, 'Products', 'Bandage', '2025-01-03', 10, 50.00, '102.103'),
        ];

        // Act
        $byCategory = $this->service->calculateTotalsByCategory($items);

        // Assert
        $this->assertCount(2, $byCategory);
        $this->assertEquals(3, $byCategory['Office Visit']['quantity']);
        $this->assertEquals(550.00, $byCategory['Office Visit']['total']);
        $this->assertEquals(10, $byCategory['Products']['quantity']);
        $this->assertEquals(50.00, $byCategory['Products']['total']);
    }

    /**
     * Test 5: Calculate totals by category and product
     */
    public function testCalculateTotalsByCategoryAndProduct(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99213', '2025-01-02', 1, 150.00, '101.102'),
            new SalesItem(102, 103, 'Office Visit', '99214', '2025-01-03', 1, 200.00, '102.103'),
        ];

        // Act
        $byGrouping = $this->service->calculateTotalsByCategoryAndProduct($items);

        // Assert
        $this->assertCount(1, $byGrouping);
        $this->assertCount(2, $byGrouping['Office Visit']);
        $this->assertEquals(2, $byGrouping['Office Visit']['99213']['quantity']);
        $this->assertEquals(300.00, $byGrouping['Office Visit']['99213']['total']);
        $this->assertEquals(1, $byGrouping['Office Visit']['99214']['quantity']);
        $this->assertEquals(200.00, $byGrouping['Office Visit']['99214']['total']);
    }

    /**
     * Test 6: Format currency US format
     */
    public function testFormatCurrencyUSFormat(): void
    {
        // Act
        $formatted = $this->service->formatCurrency(1234.56);

        // Assert
        $this->assertEquals('$1234.56', $formatted);
    }

    /**
     * Test 7: Format currency with zeros
     */
    public function testFormatCurrencyWithZeros(): void
    {
        // Act
        $formatted = $this->service->formatCurrency(100.00);

        // Assert
        $this->assertEquals('$100.00', $formatted);
    }

    /**
     * Test 8: Format quantity
     */
    public function testFormatQuantity(): void
    {
        // Act
        $formatted = $this->service->formatQuantity(42);

        // Assert
        $this->assertEquals('42', $formatted);
        $this->assertIsString($formatted);
    }

    /**
     * Test 9: Get summary statistics
     */
    public function testGetSummaryStatistics(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 100.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 1, 200.00, '101.102'),
            new SalesItem(102, 103, 'Products', 'Bandage', '2025-01-03', 1, 150.00, '102.103'),
        ];

        // Act
        $stats = $this->service->getSummaryStatistics($items);

        // Assert
        $this->assertEquals(3, $stats['item_count']);
        $this->assertEquals(3, $stats['total_quantity']);
        $this->assertEquals(450.00, $stats['total_amount']);
        $this->assertEquals(150.00, $stats['average_amount']);
        $this->assertEquals(100.00, $stats['min_amount']);
        $this->assertEquals(200.00, $stats['max_amount']);
    }

    /**
     * Test 10: Get summary statistics for empty items
     */
    public function testGetSummaryStatisticsEmptyItems(): void
    {
        // Arrange
        $items = [];

        // Act
        $stats = $this->service->getSummaryStatistics($items);

        // Assert
        $this->assertEquals(0, $stats['item_count']);
        $this->assertEquals(0, $stats['total_quantity']);
        $this->assertEquals(0.0, $stats['total_amount']);
        $this->assertEquals(0.0, $stats['average_amount']);
        $this->assertEquals(0.0, $stats['min_amount']);
        $this->assertEquals(0.0, $stats['max_amount']);
    }

    /**
     * Test 11: Calculate category totals with single item
     */
    public function testCalculateCategoryTotalsWithSingleItem(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
        ];

        // Act
        $totals = $this->service->calculateCategoryTotals($items);

        // Assert
        $this->assertEquals(1, $totals['quantity']);
        $this->assertEquals(150.00, $totals['total']);
    }

    /**
     * Test 12: Calculate product totals
     */
    public function testCalculateProductTotals(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 2, 300.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99213', '2025-01-02', 1, 150.00, '101.102'),
        ];

        // Act
        $totals = $this->service->calculateProductTotals($items);

        // Assert
        $this->assertEquals(3, $totals['quantity']);
        $this->assertEquals(450.00, $totals['total']);
    }
}
