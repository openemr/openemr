<?php

/**
 * CSV Export Service for Sales by Item report
 *
 * Handles formatting of sales data into CSV format with proper escaping
 * and quote handling for RFC 4180 compliance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class CsvExportService
{
    /**
     * Generate CSV headers for detailed report
     *
     * @return string CSV header line
     */
    public function getDetailedHeaders(): string
    {
        return $this->escapeAndJoin([
            'Category',
            'Item',
            'Date',
            'Qty',
            'Amount'
        ]);
    }

    /**
     * Generate CSV headers for summary report
     *
     * @return string CSV header line
     */
    public function getSummaryHeaders(): string
    {
        return $this->escapeAndJoin([
            'Category',
            'Item',
            'Qty',
            'Total'
        ]);
    }

    /**
     * Format a sales item row for CSV export (detailed mode)
     *
     * @param SalesItem $item The sales item to format
     * @return string CSV formatted row
     */
    public function formatDetailedRow(SalesItem $item): string
    {
        return $this->escapeAndJoin([
            $item->getCategory(),
            $item->getDescription(),
            $item->getTransactionDate(),
            (string)$item->getQuantity(),
            $this->formatCurrency($item->getAmount())
        ]);
    }

    /**
     * Format a product total row for CSV export (summary mode)
     *
     * @param string $category The category name
     * @param string $product The product/item description
     * @param int $quantity Total quantity
     * @param float $total Total amount
     * @return string CSV formatted row
     */
    public function formatProductTotalRow(string $category, string $product, int $quantity, float $total): string
    {
        return $this->escapeAndJoin([
            $category,
            $product,
            (string)$quantity,
            $this->formatCurrency($total)
        ]);
    }

    /**
     * Format a category total row for CSV export (summary mode)
     *
     * @param string $category The category name
     * @param int $quantity Total quantity
     * @param float $total Total amount
     * @return string CSV formatted row
     */
    public function formatCategoryTotalRow(string $category, int $quantity, float $total): string
    {
        $label = 'Total for category ' . $category;
        return $this->escapeAndJoin([
            $label,
            '',
            (string)$quantity,
            $this->formatCurrency($total)
        ]);
    }

    /**
     * Format a grand total row for CSV export
     *
     * @param int $quantity Grand total quantity
     * @param float $total Grand total amount
     * @return string CSV formatted row
     */
    public function formatGrandTotalRow(int $quantity, float $total): string
    {
        return $this->escapeAndJoin([
            'Grand Total',
            '',
            (string)$quantity,
            $this->formatCurrency($total)
        ]);
    }

    /**
     * Escape a field for CSV according to RFC 4180
     *
     * Rules:
     * - If field contains quote, comma, or newline, wrap in quotes
     * - Double any quotes inside the field
     *
     * @param string $field The field to escape
     * @return string The escaped field
     */
    public function escapeField(string $field): string
    {
        // If field contains special characters, quote and escape
        if (strpos($field, '"') !== false || 
            strpos($field, ',') !== false || 
            strpos($field, "\n") !== false ||
            strpos($field, "\r") !== false) {
            // Escape quotes by doubling them
            $field = str_replace('"', '""', $field);
            // Wrap in quotes
            return '"' . $field . '"';
        }

        return $field;
    }

    /**
     * Format currency value for CSV
     *
     * @param float $amount The amount to format
     * @return string Formatted currency string
     */
    public function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Escape and join fields into a CSV line
     *
     * @param array $fields The fields to join
     * @return string CSV formatted line
     */
    private function escapeAndJoin(array $fields): string
    {
        $escaped = array_map([$this, 'escapeField'], $fields);
        return implode(',', $escaped) . "\n";
    }

    /**
     * Build complete CSV export for detailed report
     *
     * @param array $rows Array of formatted rows from DataPreparationService
     * @return string Complete CSV content
     */
    public function buildDetailedCsv(array $rows): string
    {
        $csv = $this->getDetailedHeaders();

        foreach ($rows as $row) {
            if ($row['type'] === 'item') {
                $csv .= $this->formatDetailedRow($row['item']);
            } elseif ($row['type'] === 'product_total') {
                $csv .= $this->formatProductTotalRow(
                    $row['category'],
                    $row['product'],
                    $row['quantity'],
                    $row['total']
                );
            } elseif ($row['type'] === 'category_total') {
                $csv .= $this->formatCategoryTotalRow(
                    $row['category'],
                    $row['quantity'],
                    $row['total']
                );
            } elseif ($row['type'] === 'grand_total') {
                $csv .= $this->formatGrandTotalRow(
                    $row['quantity'],
                    $row['total']
                );
            }
        }

        return $csv;
    }

    /**
     * Build complete CSV export for summary report
     *
     * @param array $rows Array of formatted rows from DataPreparationService
     * @return string Complete CSV content
     */
    public function buildSummaryCsv(array $rows): string
    {
        $csv = $this->getSummaryHeaders();

        foreach ($rows as $row) {
            if ($row['type'] === 'product_row') {
                $csv .= $this->formatProductTotalRow(
                    $row['category'],
                    $row['product'],
                    $row['quantity'],
                    $row['total']
                );
            } elseif ($row['type'] === 'category_total') {
                $csv .= $this->formatCategoryTotalRow(
                    $row['category'],
                    $row['quantity'],
                    $row['total']
                );
            } elseif ($row['type'] === 'grand_total') {
                $csv .= $this->formatGrandTotalRow(
                    $row['quantity'],
                    $row['total']
                );
            }
        }

        return $csv;
    }
}
