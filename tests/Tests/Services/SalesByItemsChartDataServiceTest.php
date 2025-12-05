<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\SalesByItems\Services\ChartDataService;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

/**
 * SalesByItemsChartDataServiceTest - Tests for chart data generation
 *
 * Tests Chart.js data generation for pie charts, bar charts, and color handling.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SalesByItemsChartDataServiceTest extends TestCase
{
    private ChartDataService $service;

    protected function setUp(): void
    {
        $this->service = new ChartDataService();
    }

    /**
     * Test 1: Build category pie chart data in detailed mode
     */
    public function testBuildCategoryPieDataDetailed(): void
    {
        // Arrange
        $item1 = new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-15', 1, 150.00, '100.101');
        $item2 = new SalesItem(100, 102, 'Office Visit', '99214', '2025-01-15', 1, 200.00, '100.102');
        $item3 = new SalesItem(100, 103, 'Lab Work', 'LAB001', '2025-01-15', 2, 100.00, '100.103');

        $rows = [
            ['type' => 'item', 'item' => $item1],
            ['type' => 'item', 'item' => $item2],
            ['type' => 'item', 'item' => $item3],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, true);

        // Assert
        $this->assertIsArray($chart);
        $this->assertArrayHasKey('labels', $chart);
        $this->assertArrayHasKey('data', $chart);
        $this->assertArrayHasKey('colors', $chart);
        
        $this->assertCount(2, $chart['labels']); // 2 categories
        $this->assertContains('Lab Work', $chart['labels']);
        $this->assertContains('Office Visit', $chart['labels']);
        
        $this->assertCount(2, $chart['data']);
        // Data is sorted by amount descending
        $this->assertEquals(350.0, $chart['data'][0]); // Office Visit total (highest)
        $this->assertEquals(100.0, $chart['data'][1]); // Lab Work total (lowest)
    }

    /**
     * Test 2: Build category pie chart data in summary mode
     */
    public function testBuildCategoryPieDataSummary(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Office Visit', 'product' => '99213', 'quantity' => 5, 'total' => 750.00],
            ['type' => 'product_row', 'category' => 'Lab Work', 'product' => 'LAB001', 'quantity' => 3, 'total' => 300.00],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, false);

        // Assert
        $this->assertIsArray($chart);
        $this->assertCount(2, $chart['labels']);
        $this->assertCount(2, $chart['data']);
        $this->assertContains(750.0, $chart['data']);
        $this->assertContains(300.0, $chart['data']);
    }

    /**
     * Test 3: Empty rows return null
     */
    public function testBuildCategoryPieDataEmptyRows(): void
    {
        // Act
        $chart = $this->service->buildCategoryPieData([], true);

        // Assert
        $this->assertNull($chart);
    }

    /**
     * Test 4: Category pie chart is sorted by amount descending
     */
    public function testCategoryPieDataSorted(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'A', 'product' => '1', 'quantity' => 1, 'total' => 100.00],
            ['type' => 'product_row', 'category' => 'B', 'product' => '2', 'quantity' => 1, 'total' => 500.00],
            ['type' => 'product_row', 'category' => 'C', 'product' => '3', 'quantity' => 1, 'total' => 300.00],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, false);

        // Assert
        // Sorted by value descending
        $this->assertEquals(['B', 'C', 'A'], $chart['labels']);
        $this->assertEquals([500.0, 300.0, 100.0], $chart['data']);
    }

    /**
     * Test 5: Colors are assigned correctly
     */
    public function testCategoryPieChartColors(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'A', 'product' => '1', 'quantity' => 1, 'total' => 100.00],
            ['type' => 'product_row', 'category' => 'B', 'product' => '2', 'quantity' => 1, 'total' => 200.00],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, false);

        // Assert
        $this->assertCount(2, $chart['colors']);
        // Check colors are hex format
        foreach ($chart['colors'] as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $color);
        }
    }

    /**
     * Test 6: Build top items bar chart data
     */
    public function testBuildTopItemsBarData(): void
    {
        // Arrange
        $item1 = new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-15', 2, 300.00, '100.101');
        $item2 = new SalesItem(100, 102, 'Office Visit', '99214', '2025-01-15', 1, 200.00, '100.102');
        $item3 = new SalesItem(100, 103, 'Lab Work', 'LAB001', '2025-01-15', 5, 100.00, '100.103');

        $rows = [
            ['type' => 'item', 'item' => $item1],
            ['type' => 'item', 'item' => $item2],
            ['type' => 'item', 'item' => $item3],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 10, true);

        // Assert
        $this->assertIsArray($chart);
        $this->assertArrayHasKey('labels', $chart);
        $this->assertArrayHasKey('datasets', $chart);
        $this->assertCount(2, $chart['datasets']); // Amount and Qty datasets
        $this->assertCount(3, $chart['labels']);
    }

    /**
     * Test 7: Top items are sorted by amount descending
     */
    public function testTopItemsBarDataSorted(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item A', 'quantity' => 1, 'total' => 100.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item B', 'quantity' => 1, 'total' => 500.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item C', 'quantity' => 1, 'total' => 300.00],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 10, false);

        // Assert
        $this->assertEquals(['Item B', 'Item C', 'Item A'], $chart['labels']);
        $this->assertEquals([500.0, 300.0, 100.0], $chart['datasets'][0]['data']);
    }

    /**
     * Test 8: Top items respects limit parameter
     */
    public function testTopItemsBarDataRespectLimit(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item 1', 'quantity' => 1, 'total' => 100.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item 2', 'quantity' => 1, 'total' => 200.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item 3', 'quantity' => 1, 'total' => 300.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item 4', 'quantity' => 1, 'total' => 400.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item 5', 'quantity' => 1, 'total' => 500.00],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 3, false);

        // Assert
        $this->assertCount(3, $chart['labels']);
        $this->assertCount(3, $chart['datasets'][0]['data']);
    }

    /**
     * Test 9: Empty rows return null for bar chart
     */
    public function testBuildTopItemsBarDataEmptyRows(): void
    {
        // Act
        $chart = $this->service->buildTopItemsBarData([], 10, true);

        // Assert
        $this->assertNull($chart);
    }

    /**
     * Test 10: Bar chart has both Amount and Qty datasets
     */
    public function testBarChartHasDualDatasets(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item A', 'quantity' => 5, 'total' => 250.00],
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item B', 'quantity' => 3, 'total' => 300.00],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 10, false);

        // Assert
        $this->assertCount(2, $chart['datasets']);
        $this->assertEquals('Amount', $chart['datasets'][0]['label']);
        $this->assertEquals('Qty', $chart['datasets'][1]['label']);
        $this->assertEquals([300.0, 250.0], $chart['datasets'][0]['data']);
        $this->assertEquals([3, 5], $chart['datasets'][1]['data']);
    }

    /**
     * Test 11: Lightened colors for secondary dataset
     */
    public function testLightenedColorsForSecondaryDataset(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Cat', 'product' => 'Item A', 'quantity' => 1, 'total' => 100.00],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 10, false);

        // Assert
        $colors1 = $chart['datasets'][0]['backgroundColor'];
        $colors2 = $chart['datasets'][1]['backgroundColor'];
        
        // Colors should be hex format
        foreach ($colors1 as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $color);
        }
        foreach ($colors2 as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $color);
        }
    }

    /**
     * Test 12: Category totals with multiple items per category
     */
    public function testCategoryTotalsMultipleItems(): void
    {
        // Arrange
        $item1 = new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-15', 1, 150.00, '100.101');
        $item2 = new SalesItem(100, 102, 'Office Visit', '99214', '2025-01-15', 1, 200.00, '100.102');
        $item3 = new SalesItem(100, 103, 'Office Visit', '99215', '2025-01-15', 1, 250.00, '100.103');

        $rows = [
            ['type' => 'item', 'item' => $item1],
            ['type' => 'item', 'item' => $item2],
            ['type' => 'item', 'item' => $item3],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, true);

        // Assert
        $this->assertCount(1, $chart['labels']);
        $this->assertEquals('Office Visit', $chart['labels'][0]);
        $this->assertEquals(600.0, $chart['data'][0]); // Sum of all amounts
    }

    /**
     * Test 13: Uncategorized items are handled
     */
    public function testUncategorizedItemsHandled(): void
    {
        // Arrange
        $item1 = new SalesItem(100, 101, '', '99213', '2025-01-15', 1, 150.00, '100.101'); // Empty category
        $rows = [
            ['type' => 'item', 'item' => $item1],
        ];

        // Act
        $chart = $this->service->buildCategoryPieData($rows, true);

        // Assert
        $this->assertCount(1, $chart['labels']);
        $this->assertEquals('Uncategorized', $chart['labels'][0]);
        $this->assertEquals(150.0, $chart['data'][0]);
    }

    /**
     * Test 14: Detailed item row format for bar chart
     */
    public function testDetailedItemRowFormatBarChart(): void
    {
        // Arrange
        $item1 = new SalesItem(100, 101, 'Office Visit', '99213', '2025-01-15', 2, 300.00, '100.101');
        $item2 = new SalesItem(100, 102, 'Office Visit', '99214', '2025-01-15', 1, 200.00, '100.102');

        $rows = [
            ['type' => 'item', 'item' => $item1],
            ['type' => 'item', 'item' => $item2],
        ];

        // Act
        $chart = $this->service->buildTopItemsBarData($rows, 10, true);

        // Assert
        $this->assertCount(2, $chart['labels']);
        // Labels use the description field (sorted by amount descending)
        $this->assertEquals('99213', $chart['labels'][0]); // 300.00 is first
        $this->assertEquals('99214', $chart['labels'][1]); // 200.00 is second
    }

    /**
     * Test 15: Color palette cycles for many items
     */
    public function testColorPaletteCycles(): void
    {
        // Arrange - create more items than palette colors (12)
        $rows = [];
        for ($i = 1; $i <= 15; $i++) {
            $rows[] = [
                'type' => 'product_row',
                'category' => 'Cat ' . $i,
                'product' => 'Item ' . $i,
                'quantity' => 1,
                'total' => (float)$i * 100,
            ];
        }

        // Act
        $chart = $this->service->buildCategoryPieData($rows, false);

        // Assert
        $this->assertCount(15, $chart['colors']);
        // All colors should be valid hex
        foreach ($chart['colors'] as $color) {
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $color);
        }
    }
}
