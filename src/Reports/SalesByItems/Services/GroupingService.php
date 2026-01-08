<?php

/**
 * Service to group sales items and calculate subtotals
 *
 * Replaces the state-based logic from thisLineItem() in the original code.
 * Groups items by category, then by product within each category.
 * Calculates subtotals at both levels.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Model\SalesItem;
use OpenEMR\Reports\SalesByItems\Model\SalesGroup;

class GroupingService
{
    /**
     * Group items by category and product
     *
     * Returns a structure like:
     * [
     *   'Office Visit' => [
     *       '99213' => SalesGroup,
     *       '99214' => SalesGroup,
     *   ],
     *   'Products' => [
     *       'Bandage' => SalesGroup,
     *   ]
     * ]
     *
     * @param SalesItem[] $items The items to group
     * @return array Grouped items with SalesGroup objects
     */
    public function groupItems(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $category = $item->getCategory() ?: 'None';
            $product = $item->getDescription() ?: 'Unknown';

            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            if (!isset($grouped[$category][$product])) {
                $grouped[$category][$product] = new SalesGroup($category, $product);
            }

            $grouped[$category][$product]->addItem($item);
        }

        return $grouped;
    }

    /**
     * Get category totals (sum of all products in a category)
     *
     * @param SalesGroup[] $productGroups Array of SalesGroup objects in a category
     * @return array ['quantity' => int, 'total' => float]
     */
    public function getCategoryTotals(array $productGroups): array
    {
        $quantity = 0;
        $total = 0.0;

        foreach ($productGroups as $group) {
            $quantity += $group->getQuantity();
            $total += $group->getTotal();
        }

        return [
            'quantity' => $quantity,
            'total' => $total,
        ];
    }

    /**
     * Get grand totals across all groups
     *
     * @param array $grouped The grouped items structure
     * @return array ['quantity' => int, 'total' => float]
     */
    public function getGrandTotals(array $grouped): array
    {
        $quantity = 0;
        $total = 0.0;

        foreach ($grouped as $categoryGroups) {
            $categoryTotals = $this->getCategoryTotals($categoryGroups);
            $quantity += $categoryTotals['quantity'];
            $total += $categoryTotals['total'];
        }

        return [
            'quantity' => $quantity,
            'total' => $total,
        ];
    }

    /**
     * Format grouped data for display
     *
     * Returns flat list of items with category/product grouping info:
     * [
     *   ['type' => 'category_header', 'category' => 'Office Visit'],
     *   ['type' => 'product_header', 'category' => 'Office Visit', 'product' => '99213'],
     *   ['type' => 'item', 'item' => SalesItem],
     *   ['type' => 'item', 'item' => SalesItem],
     *   ['type' => 'product_total', 'category' => 'Office Visit', 'product' => '99213', 'qty' => 2, 'total' => 300],
     *   ['type' => 'category_total', 'category' => 'Office Visit', 'qty' => 5, 'total' => 750],
     *   ...
     * ]
     *
     * @param array $grouped The grouped items structure
     * @return array Formatted rows for display
     */
    public function formatGroupedData(array $grouped): array
    {
        $rows = [];
        $prevCategory = '';

        foreach ($grouped as $category => $productGroups) {
            // Category header (first time seeing this category)
            if ($category !== $prevCategory) {
                $rows[] = [
                    'type' => 'category_header',
                    'category' => $category,
                ];
                $prevCategory = $category;
            }

            // Process each product in this category
            foreach ($productGroups as $product => $group) {
                // Product header
                $rows[] = [
                    'type' => 'product_header',
                    'category' => $category,
                    'product' => $product,
                ];

                // Individual items for this product
                foreach ($group->getItems() as $item) {
                    $rows[] = [
                        'type' => 'item',
                        'item' => $item,
                    ];
                }

                // Product subtotal
                $rows[] = [
                    'type' => 'product_total',
                    'category' => $category,
                    'product' => $product,
                    'quantity' => $group->getQuantity(),
                    'total' => $group->getTotal(),
                ];
            }

            // Category total
            $categoryTotals = $this->getCategoryTotals($productGroups);
            $rows[] = [
                'type' => 'category_total',
                'category' => $category,
                'quantity' => $categoryTotals['quantity'],
                'total' => $categoryTotals['total'],
            ];
        }

        // Grand total
        $grandTotals = $this->getGrandTotals($grouped);
        $rows[] = [
            'type' => 'grand_total',
            'quantity' => $grandTotals['quantity'],
            'total' => $grandTotals['total'],
        ];

        return $rows;
    }

    /**
     * Get summary view (no detail items, just totals)
     *
     * @param array $grouped The grouped items structure
     * @return array Formatted rows without detail items
     */
    public function formatGroupedDataSummary(array $grouped): array
    {
        $rows = [];

        foreach ($grouped as $category => $productGroups) {
            // Product rows (no items inside)
            foreach ($productGroups as $product => $group) {
                $rows[] = [
                    'type' => 'product_row',
                    'category' => $category,
                    'product' => $product,
                    'quantity' => $group->getQuantity(),
                    'total' => $group->getTotal(),
                ];
            }

            // Category total
            $categoryTotals = $this->getCategoryTotals($productGroups);
            $rows[] = [
                'type' => 'category_total',
                'category' => $category,
                'quantity' => $categoryTotals['quantity'],
                'total' => $categoryTotals['total'],
            ];
        }

        // Grand total
        $grandTotals = $this->getGrandTotals($grouped);
        $rows[] = [
            'type' => 'grand_total',
            'quantity' => $grandTotals['quantity'],
            'total' => $grandTotals['total'],
        ];

        return $rows;
    }
}
