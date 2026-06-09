<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\SalesByItems\Services\GroupingService;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

/**
 * SalesByItemsGroupingServiceTest - TDD tests for grouping logic (Phase 2)
 *
 * Tests the service that groups sales items by category/product and calculates subtotals.
 * Replaces the complex state-based logic from thisLineItem() in the original code.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SalesByItemsGroupingServiceTest extends TestCase
{
    private GroupingService $service;

    protected function setUp(): void
    {
        $this->service = new GroupingService();
    }

    /**
     * Test 1: Group items by category
     */
    public function testGroupItemsByCategory(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 1, 200.00, '101.102'),
            new SalesItem(102, 103, 'Products', 'Bandage', '2025-01-03', 10, 50.00, '102.103'),
        ];

        // Act
        $grouped = $this->service->groupItems($items);

        // Assert
        $this->assertCount(2, $grouped); // 2 categories
        $this->assertArrayHasKey('Office Visit', $grouped);
        $this->assertArrayHasKey('Products', $grouped);
        $this->assertCount(2, $grouped['Office Visit']); // 2 products in Office Visit
        $this->assertCount(1, $grouped['Products']); // 1 product in Products
    }

    /**
     * Test 2: Group items by product within category
     */
    public function testGroupItemsByProductWithinCategory(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99213', '2025-01-02', 1, 150.00, '101.102'),
            new SalesItem(102, 103, 'Office Visit', '99214', '2025-01-03', 1, 200.00, '102.103'),
        ];

        // Act
        $grouped = $this->service->groupItems($items);

        // Assert
        $this->assertCount(2, $grouped['Office Visit']);
        $this->assertArrayHasKey('99213', $grouped['Office Visit']);
        $this->assertArrayHasKey('99214', $grouped['Office Visit']);

        // 99213 should have 2 items aggregated
        $group99213 = $grouped['Office Visit']['99213'];
        $this->assertEquals(2, $group99213->getQuantity());
        $this->assertEquals(300.00, $group99213->getTotal());
    }

    /**
     * Test 3: Calculate category totals
     */
    public function testCalculateCategoryTotals(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 2, 400.00, '101.102'),
        ];

        $grouped = $this->service->groupItems($items);
        $categoryItems = $grouped['Office Visit'];

        // Act
        $totals = $this->service->getCategoryTotals($categoryItems);

        // Assert
        $this->assertEquals(3, $totals['quantity']);
        $this->assertEquals(550.00, $totals['total']);
    }

    /**
     * Test 4: Calculate grand totals
     */
    public function testCalculateGrandTotals(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 2, 400.00, '101.102'),
            new SalesItem(102, 103, 'Products', 'Bandage', '2025-01-03', 10, 50.00, '102.103'),
        ];

        $grouped = $this->service->groupItems($items);

        // Act
        $totals = $this->service->getGrandTotals($grouped);

        // Assert
        $this->assertEquals(13, $totals['quantity']);
        $this->assertEquals(600.00, $totals['total']);
    }

    /**
     * Test 5: Format grouped data with detail rows
     */
    public function testFormatGroupedDataWithDetails(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99213', '2025-01-02', 1, 150.00, '101.102'),
        ];

        $grouped = $this->service->groupItems($items);

        // Act
        $formatted = $this->service->formatGroupedData($grouped);

        // Assert
        // Should contain: category header, product header, 2 items, product total, category total, grand total
        $this->assertGreaterThan(0, count($formatted));

        // Verify row types exist
        $types = array_column($formatted, 'type');
        $this->assertContains('category_header', $types);
        $this->assertContains('product_header', $types);
        $this->assertContains('item', $types);
        $this->assertContains('product_total', $types);
        $this->assertContains('category_total', $types);
        $this->assertContains('grand_total', $types);
    }

    /**
     * Test 6: Format grouped data summary (no detail items)
     */
    public function testFormatGroupedDataSummary(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99213', '2025-01-02', 1, 150.00, '101.102'),
        ];

        $grouped = $this->service->groupItems($items);

        // Act
        $formatted = $this->service->formatGroupedDataSummary($grouped);

        // Assert
        // Should NOT have 'item' rows or 'product_header' rows
        $types = array_column($formatted, 'type');
        $this->assertNotContains('item', $types);
        $this->assertContains('product_row', $types);
        $this->assertContains('category_total', $types);
        $this->assertContains('grand_total', $types);
    }

    /**
     * Test 7: Handle empty items array
     */
    public function testHandleEmptyItemsArray(): void
    {
        // Arrange
        $items = [];

        // Act
        $grouped = $this->service->groupItems($items);

        // Assert
        $this->assertIsArray($grouped);
        $this->assertEmpty($grouped);
    }

    /**
     * Test 8: Handle missing category (should default to 'None')
     */
    public function testHandleMissingCategory(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, '', 'Description', '2025-01-01', 1, 100.00, '100.101'),
        ];

        // Act
        $grouped = $this->service->groupItems($items);

        // Assert
        $this->assertArrayHasKey('None', $grouped);
    }

    /**
     * Test 9: Handle missing description (should default to 'Unknown')
     */
    public function testHandleMissingDescription(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '', '2025-01-01', 1, 100.00, '100.101'),
        ];

        // Act
        $grouped = $this->service->groupItems($items);
        $unknown = $grouped['Office Visit']['Unknown'] ?? null;

        // Assert
        $this->assertNotNull($unknown);
        $this->assertEquals('Unknown', $unknown->getProduct());
    }

    /**
     * Test 10: Multiple categories with multiple products
     */
    public function testMultipleCategoriesAndProducts(): void
    {
        // Arrange
        $items = [
            new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-01', 1, 150.00, '100.101'),
            new SalesItem(101, 102, 'Office Visit', '99214', '2025-01-02', 2, 400.00, '101.102'),
            new SalesItem(102, 103, 'Lab Work', 'Blood Test', '2025-01-03', 1, 75.00, '102.103'),
            new SalesItem(103, 104, 'Lab Work', 'Urinalysis', '2025-01-04', 1, 50.00, '103.104'),
            new SalesItem(104, 105, 'Products', 'Bandage', '2025-01-05', 10, 50.00, '104.105'),
        ];

        // Act
        $grouped = $this->service->groupItems($items);
        $totals = $this->service->getGrandTotals($grouped);

        // Assert
        $this->assertCount(3, $grouped);
        $this->assertEquals(15, $totals['quantity']); // 1+2+1+1+10
        $this->assertEquals(725.00, $totals['total']); // 150+400+75+50+50
    }
}
