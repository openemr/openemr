<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\SalesByItems\Services\CsvExportService;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

/**
 * SalesByItemsCsvExportServiceTest - TDD tests for CSV export (Phase 5)
 *
 * Tests CSV formatting, RFC 4180 compliance, escaping, and complete export building.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SalesByItemsCsvExportServiceTest extends TestCase
{
    private CsvExportService $service;

    protected function setUp(): void
    {
        $this->service = new CsvExportService();
    }

    /**
     * Test 1: Detailed headers are generated correctly
     */
    public function testDetailedHeadersGenerated(): void
    {
        // Act
        $headers = $this->service->getDetailedHeaders();

        // Assert
        $this->assertStringContainsString('Category', $headers);
        $this->assertStringContainsString('Item', $headers);
        $this->assertStringContainsString('Date', $headers);
        $this->assertStringContainsString('Qty', $headers);
        $this->assertStringContainsString('Amount', $headers);
        $this->assertStringContainsString("\n", $headers); // Ends with newline
    }

    /**
     * Test 2: Summary headers are generated correctly
     */
    public function testSummaryHeadersGenerated(): void
    {
        // Act
        $headers = $this->service->getSummaryHeaders();

        // Assert
        $this->assertStringContainsString('Category', $headers);
        $this->assertStringContainsString('Item', $headers);
        $this->assertStringContainsString('Qty', $headers);
        $this->assertStringContainsString('Total', $headers);
        $this->assertStringNotContainsString('Date', $headers);
    }

    /**
     * Test 3: Basic field escaping works without special characters
     */
    public function testBasicFieldEscapingNoSpecialChars(): void
    {
        // Act
        $escaped = $this->service->escapeField('Office Visit');

        // Assert
        $this->assertEquals('Office Visit', $escaped);
    }

    /**
     * Test 4: Fields with commas are quoted
     */
    public function testFieldsWithCommasAreQuoted(): void
    {
        // Act
        $escaped = $this->service->escapeField('Smith, John');

        // Assert
        $this->assertEquals('"Smith, John"', $escaped);
    }

    /**
     * Test 5: Fields with quotes are escaped and quoted
     */
    public function testFieldsWithQuotesAreEscaped(): void
    {
        // Act
        $escaped = $this->service->escapeField('Test "quoted" text');

        // Assert
        $this->assertEquals('"Test ""quoted"" text"', $escaped);
    }

    /**
     * Test 6: Fields with newlines are quoted
     */
    public function testFieldsWithNewlinesAreQuoted(): void
    {
        // Act
        $escaped = $this->service->escapeField("Line 1\nLine 2");

        // Assert
        $this->assertStringContainsString('"', $escaped);
        $this->assertStringContainsString("\n", $escaped);
    }

    /**
     * Test 7: Currency formatting is correct
     */
    public function testCurrencyFormatting(): void
    {
        // Act
        $formatted = $this->service->formatCurrency(1234.567);

        // Assert
        $this->assertEquals('1234.57', $formatted);
    }

    /**
     * Test 8: Currency formatting handles small amounts
     */
    public function testCurrencyFormattingSmallAmount(): void
    {
        // Act
        $formatted = $this->service->formatCurrency(0.5);

        // Assert
        $this->assertEquals('0.50', $formatted);
    }

    /**
     * Test 9: Detailed row formatting
     */
    public function testDetailedRowFormatting(): void
    {
        // Arrange
        $item = new SalesItem(
            100, 101, 'Office Visit', '99213',
            '2025-01-15', 1, 150.00, '100.101'
        );

        // Act
        $row = $this->service->formatDetailedRow($item);

        // Assert
        $this->assertStringContainsString('Office Visit', $row);
        $this->assertStringContainsString('99213', $row);
        $this->assertStringContainsString('2025-01-15', $row);
        $this->assertStringContainsString('1', $row);
        $this->assertStringContainsString('150.00', $row);
        $this->assertStringContainsString("\n", $row);
    }

    /**
     * Test 10: Product total row formatting
     */
    public function testProductTotalRowFormatting(): void
    {
        // Act
        $row = $this->service->formatProductTotalRow('Office Visit', '99213', 5, 750.00);

        // Assert
        $this->assertStringContainsString('Office Visit', $row);
        $this->assertStringContainsString('99213', $row);
        $this->assertStringContainsString('5', $row);
        $this->assertStringContainsString('750.00', $row);
    }

    /**
     * Test 11: Category total row formatting
     */
    public function testCategoryTotalRowFormatting(): void
    {
        // Act
        $row = $this->service->formatCategoryTotalRow('Office Visit', 10, 1500.00);

        // Assert
        $this->assertStringContainsString('Total for category', $row);
        $this->assertStringContainsString('Office Visit', $row);
        $this->assertStringContainsString('10', $row);
        $this->assertStringContainsString('1500.00', $row);
    }

    /**
     * Test 12: Grand total row formatting
     */
    public function testGrandTotalRowFormatting(): void
    {
        // Act
        $row = $this->service->formatGrandTotalRow(50, 5000.00);

        // Assert
        $this->assertStringContainsString('Grand Total', $row);
        $this->assertStringContainsString('50', $row);
        $this->assertStringContainsString('5000.00', $row);
    }

    /**
     * Test 13: Build detailed CSV with mixed row types
     */
    public function testBuildDetailedCsv(): void
    {
        // Arrange
        $item = new SalesItem(
            100, 101, 'Office Visit', '99213',
            '2025-01-15', 1, 150.00, '100.101'
        );

        $rows = [
            ['type' => 'item', 'item' => $item],
            ['type' => 'product_total', 'category' => 'Office Visit', 'product' => '99213', 'quantity' => 1, 'total' => 150.00],
            ['type' => 'category_total', 'category' => 'Office Visit', 'quantity' => 1, 'total' => 150.00],
            ['type' => 'grand_total', 'quantity' => 1, 'total' => 150.00],
        ];

        // Act
        $csv = $this->service->buildDetailedCsv($rows);

        // Assert
        $this->assertStringContainsString('Category,Item,Date,Qty,Amount', $csv);
        $this->assertStringContainsString('Office Visit', $csv);
        $this->assertStringContainsString('99213', $csv);
        $this->assertStringContainsString('Grand Total', $csv);
        $lines = explode("\n", trim($csv));
        $this->assertGreaterThanOrEqual(5, count($lines)); // Header + 4 rows
    }

    /**
     * Test 14: Build summary CSV with product rows
     */
    public function testBuildSummaryCsv(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Office Visit', 'product' => '99213', 'quantity' => 5, 'total' => 750.00],
            ['type' => 'category_total', 'category' => 'Office Visit', 'quantity' => 5, 'total' => 750.00],
            ['type' => 'grand_total', 'quantity' => 5, 'total' => 750.00],
        ];

        // Act
        $csv = $this->service->buildSummaryCsv($rows);

        // Assert
        $this->assertStringContainsString('Category,Item,Qty,Total', $csv);
        $this->assertStringNotContainsString('Date', $csv);
        $this->assertStringContainsString('Office Visit', $csv);
        $this->assertStringContainsString('99213', $csv);
        $this->assertStringContainsString('Grand Total', $csv);
        $lines = explode("\n", trim($csv));
        $this->assertGreaterThanOrEqual(4, count($lines)); // Header + 3 rows
    }

    /**
     * Test 15: CSV with special characters is properly escaped
     */
    public function testCsvWithSpecialCharacters(): void
    {
        // Arrange
        $item = new SalesItem(
            100, 101, 'Test "Item"', 'Code, Inc.',
            '2025-01-15', 1, 99.99, '100.101'
        );

        // Act
        $row = $this->service->formatDetailedRow($item);

        // Assert
        $this->assertStringContainsString('Test ""Item""', $row); // Quotes escaped
        $this->assertStringContainsString('"Code, Inc."', $row); // Comma quoted
    }

    /**
     * Test 16: Empty rows list produces headers only
     */
    public function testEmptyRowsList(): void
    {
        // Act
        $csv = $this->service->buildDetailedCsv([]);

        // Assert
        $this->assertStringContainsString('Category,Item,Date,Qty,Amount', $csv);
        $lines = explode("\n", trim($csv));
        $this->assertEquals(1, count($lines)); // Only header
    }

    /**
     * Test 17: CSV output is RFC 4180 compliant (proper line endings)
     */
    public function testCsvLineEndings(): void
    {
        // Act
        $headers = $this->service->getDetailedHeaders();

        // Assert
        $this->assertStringEndsWith("\n", $headers);
        $lines = explode("\n", $headers);
        $this->assertNotEmpty($lines[0]); // First line has content
        $this->assertEquals('', end($lines)); // Last element is empty (because of trailing newline)
    }

    /**
     * Test 18: Multiple rows with same category
     */
    public function testMultipleRowsSameCategory(): void
    {
        // Arrange
        $rows = [
            ['type' => 'product_row', 'category' => 'Office Visit', 'product' => '99213', 'quantity' => 2, 'total' => 300.00],
            ['type' => 'product_row', 'category' => 'Office Visit', 'product' => '99214', 'quantity' => 3, 'total' => 450.00],
            ['type' => 'category_total', 'category' => 'Office Visit', 'quantity' => 5, 'total' => 750.00],
            ['type' => 'grand_total', 'quantity' => 5, 'total' => 750.00],
        ];

        // Act
        $csv = $this->service->buildSummaryCsv($rows);

        // Assert
        $lines = explode("\n", trim($csv));
        $this->assertGreaterThanOrEqual(5, count($lines));
        $this->assertStringContainsString('99213', $csv);
        $this->assertStringContainsString('99214', $csv);
    }
}
