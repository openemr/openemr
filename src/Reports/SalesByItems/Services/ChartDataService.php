<?php

namespace OpenEMR\Reports\SalesByItems\Services;

/**
 * ChartDataService - Generates Chart.js compatible data for Sales by Item visualizations
 *
 * Converts report rows into structured data suitable for Chart.js pie, bar, and other chart types.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class ChartDataService
{
    // Colorblind-friendly palette with 12+ distinct colors
    private array $palette = [
        '#4e79a7', // Blue
        '#f28e2b', // Orange
        '#e15759', // Red
        '#76b7b2', // Teal
        '#59a14f', // Green
        '#edc949', // Yellow
        '#af7aa1', // Purple
        '#ff9da7', // Pink
        '#9c755f', // Brown
        '#bab0ab', // Gray
        '#1f77b4', // Dark Blue
        '#2ca02c', // Dark Green
    ];

    /**
     * Build category pie chart data
     *
     * Aggregates sales by category for pie chart visualization
     *
     * @param array $rows Report rows from data service
     * @param bool $isDetailed Whether this is detailed or summary report mode
     *
     * @return array|null Chart data with labels, data, and colors or null if no data
     */
    public function buildCategoryPieData(array $rows, bool $isDetailed = false): ?array
    {
        $categoryTotals = [];

        foreach ($rows as $row) {
            $rowType = $row['type'] ?? null;

            // In detailed mode, aggregate items by category
            if ($isDetailed && $rowType === 'item') {
                $category = (string)$row['item']->getCategory();
                $amount = (float)$row['item']->getAmount();
            } elseif (!$isDetailed && $rowType === 'product_row') {
                // In summary mode, product_row has direct category info
                $category = (string)($row['category'] ?? '');
                $amount = (float)($row['total'] ?? 0);
            } else {
                continue;
            }

            if ($category === '') {
                $category = 'Uncategorized';
            }

            $categoryTotals[$category] = ($categoryTotals[$category] ?? 0.0) + $amount;
        }

        if (empty($categoryTotals)) {
            return null;
        }

        return $this->formatChartData(
            $categoryTotals,
            'category_pie'
        );
    }

    /**
     * Build top items bar chart data
     *
     * Gets top N items by amount for bar chart visualization
     *
     * @param array $rows Report rows from data service
     * @param int $limit Maximum number of items to show (default 10)
     * @param bool $isDetailed Whether this is detailed or summary report mode
     *
     * @return array|null Chart data with labels, data arrays, and colors or null if no data
     */
    public function buildTopItemsBarData(array $rows, int $limit = 10, bool $isDetailed = false): ?array
    {
        $itemTotals = [];

        foreach ($rows as $row) {
            $rowType = $row['type'] ?? null;

            if ($isDetailed && $rowType === 'item') {
                $itemDesc = (string)$row['item']->getDescription();
                $amount = (float)$row['item']->getAmount();
                $quantity = (int)$row['item']->getQuantity();
                $key = $itemDesc; // Use description (which contains the code)
            } elseif (!$isDetailed && $rowType === 'product_row') {
                $itemId = (string)($row['product'] ?? '');
                $quantity = (int)($row['quantity'] ?? 0);
                $amount = (float)($row['total'] ?? 0);
                $key = $itemId;
            } else {
                continue;
            }

            if (!isset($itemTotals[$key])) {
                $itemTotals[$key] = [
                    'amount' => 0.0,
                    'quantity' => 0,
                ];
            }

            $itemTotals[$key]['amount'] += $amount;
            $itemTotals[$key]['quantity'] += $quantity;
        }

        if (empty($itemTotals)) {
            return null;
        }

        // Sort by amount descending and limit
        uasort($itemTotals, static function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });
        $itemTotals = array_slice($itemTotals, 0, $limit, true);

        // Format for Chart.js bar chart with dual datasets
        $labels = array_values(array_keys($itemTotals));
        $amounts = array_values(array_map(static function ($item) { return round($item['amount'], 2); }, $itemTotals));
        $quantities = array_values(array_map(static function ($item) { return $item['quantity']; }, $itemTotals));

        $colors = $this->generateColors(count($labels));

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $amounts,
                    'backgroundColor' => $colors,
                ],
                [
                    'label' => 'Qty',
                    'data' => $quantities,
                    'backgroundColor' => $this->lightenColors($colors),
                ],
            ],
        ];
    }

    /**
     * Format aggregated data into Chart.js compatible structure
     *
     * @param array $aggregated Key-value pairs where key is label and value is amount
     * @param string $type Chart type for proper formatting
     *
     * @return array Chart.js compatible data structure
     */
    private function formatChartData(array $aggregated, string $type): array
    {
        // Sort by value descending
        arsort($aggregated);

        $labels = array_keys($aggregated);
        $data = array_values(array_map(static function ($v) {
            return round((float)$v, 2);
        }, $aggregated));

        $colors = $this->generateColors(count($labels));

        if ($type === 'category_pie') {
            return [
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ];
        }

        // Default bar chart format
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
        ];
    }

    /**
     * Generate colors for chart elements
     *
     * Cycles through the palette, repeating if necessary
     *
     * @param int $count Number of colors needed
     *
     * @return array Array of hex color codes
     */
    private function generateColors(int $count): array
    {
        $colors = [];
        $paletteCount = count($this->palette);

        for ($i = 0; $i < $count; $i++) {
            $colors[] = $this->palette[$i % $paletteCount];
        }

        return $colors;
    }

    /**
     * Lighten colors by adjusting RGB values
     *
     * Used for secondary datasets to differentiate from primary
     *
     * @param array $colors Array of hex color codes
     *
     * @return array Lightened hex color codes
     */
    private function lightenColors(array $colors): array
    {
        return array_map(static function ($hex) {
            $hex = str_replace('#', '', $hex);
            $r = (int)hexdec(substr($hex, 0, 2));
            $g = (int)hexdec(substr($hex, 2, 2));
            $b = (int)hexdec(substr($hex, 4, 2));

            // Lighten by 30%
            $r = (int)min(255, $r + (255 - $r) * 0.3);
            $g = (int)min(255, $g + (255 - $g) * 0.3);
            $b = (int)min(255, $b + (255 - $b) * 0.3);

            return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
                . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
                . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
        }, $colors);
    }
}
