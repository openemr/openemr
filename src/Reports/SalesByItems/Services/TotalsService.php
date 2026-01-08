<?php

/**
 * Service to calculate and format totals for Sales by Item report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class TotalsService
{
    /**
     * Calculate totals from a list of items
     *
     * @param SalesItem[] $items The items to total
     * @return array ['quantity' => int, 'total' => float]
     */
    public function calculateTotals(array $items): array
    {
        $quantity = 0;
        $total = 0.0;

        foreach ($items as $item) {
            $quantity += $item->getQuantity();
            $total += $item->getAmount();
        }

        return [
            'quantity' => $quantity,
            'total' => $total,
        ];
    }

    /**
     * Calculate totals for a category
     *
     * @param SalesItem[] $items Items in the category
     * @return array ['quantity' => int, 'total' => float]
     */
    public function calculateCategoryTotals(array $items): array
    {
        return $this->calculateTotals($items);
    }

    /**
     * Calculate totals for a product
     *
     * @param SalesItem[] $items Items for the product
     * @return array ['quantity' => int, 'total' => float]
     */
    public function calculateProductTotals(array $items): array
    {
        return $this->calculateTotals($items);
    }

    /**
     * Calculate totals by category
     *
     * Returns array like:
     * [
     *   'Office Visit' => ['quantity' => 5, 'total' => 750.00],
     *   'Products' => ['quantity' => 20, 'total' => 500.00],
     * ]
     *
     * @param SalesItem[] $items Items to aggregate
     * @return array Totals by category
     */
    public function calculateTotalsByCategory(array $items): array
    {
        $byCategory = [];

        foreach ($items as $item) {
            $category = $item->getCategory() ?: 'None';

            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [];
            }

            $byCategory[$category][] = $item;
        }

        $totals = [];
        foreach ($byCategory as $category => $categoryItems) {
            $totals[$category] = $this->calculateTotals($categoryItems);
        }

        return $totals;
    }

    /**
     * Calculate totals by category and product
     *
     * Returns array like:
     * [
     *   'Office Visit' => [
     *       '99213' => ['quantity' => 2, 'total' => 300.00],
     *       '99214' => ['quantity' => 3, 'total' => 450.00],
     *   ],
     *   'Products' => [
     *       'Bandage' => ['quantity' => 20, 'total' => 500.00],
     *   ],
     * ]
     *
     * @param SalesItem[] $items Items to aggregate
     * @return array Totals by category and product
     */
    public function calculateTotalsByCategoryAndProduct(array $items): array
    {
        $byGroup = [];

        foreach ($items as $item) {
            $category = $item->getCategory() ?: 'None';
            $product = $item->getDescription() ?: 'Unknown';

            if (!isset($byGroup[$category])) {
                $byGroup[$category] = [];
            }

            if (!isset($byGroup[$category][$product])) {
                $byGroup[$category][$product] = [];
            }

            $byGroup[$category][$product][] = $item;
        }

        $totals = [];
        foreach ($byGroup as $category => $products) {
            $totals[$category] = [];
            foreach ($products as $product => $items) {
                $totals[$category][$product] = $this->calculateTotals($items);
            }
        }

        return $totals;
    }

    /**
     * Format currency for display
     *
     * @param float $amount The amount to format
     * @param string $format Format string (default: US format)
     * @return string Formatted currency
     */
    public function formatCurrency(float $amount, string $format = '$%.2f'): string
    {
        return sprintf($format, $amount);
    }

    /**
     * Format quantity for display
     *
     * @param int $quantity The quantity
     * @return string Formatted quantity
     */
    public function formatQuantity(int $quantity): string
    {
        return (string)$quantity;
    }

    /**
     * Get summary statistics
     *
     * @param SalesItem[] $items Items to analyze
     * @return array Summary statistics
     */
    public function getSummaryStatistics(array $items): array
    {
        if (empty($items)) {
            return [
                'item_count' => 0,
                'total_quantity' => 0,
                'total_amount' => 0.0,
                'average_amount' => 0.0,
                'min_amount' => 0.0,
                'max_amount' => 0.0,
            ];
        }

        $totals = $this->calculateTotals($items);
        $amounts = array_map(fn($item) => $item->getAmount(), $items);

        return [
            'item_count' => count($items),
            'total_quantity' => $totals['quantity'],
            'total_amount' => $totals['total'],
            'average_amount' => $totals['total'] / count($items),
            'min_amount' => min($amounts),
            'max_amount' => max($amounts),
        ];
    }
}
