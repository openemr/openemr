<?php

/**
 * Collections Report Totals Service
 * Built with Warp Terminal
 * Calculates and formats grand totals for the Collections Report.
 * Aggregates financial data and aging buckets across all patients/insurance groups.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Services;

class TotalsService
{
    /**
     * Calculate grand totals from patient or insurance groups
     *
     * @param array $groups Array of patient groups or insurance groups
     * @return array Grand totals array
     */
    public function calculateGrandTotals(array $groups): array
    {
        if (empty($groups)) {
            return [
                'charges' => 0.0,
                'adjustments' => 0.0,
                'paid' => 0.0,
                'balance' => 0.0,
                'aging_buckets' => [],
            ];
        }

        $grandTotals = [
            'charges' => 0.0,
            'adjustments' => 0.0,
            'paid' => 0.0,
            'balance' => 0.0,
            'aging_buckets' => [],
        ];

        $hasAgingBuckets = false;
        $agingBucketCount = 0;

        foreach ($groups as $group) {
            $totals = $group['totals'] ?? [];

            // Accumulate financial totals
            $grandTotals['charges'] += (float)($totals['charges'] ?? 0);
            $grandTotals['adjustments'] += (float)($totals['adjustments'] ?? 0);
            $grandTotals['paid'] += (float)($totals['paid'] ?? 0);
            $grandTotals['balance'] += (float)($totals['balance'] ?? 0);

            // Accumulate aging buckets if present
            if (!empty($totals['aging_buckets'])) {
                $hasAgingBuckets = true;
                $buckets = $totals['aging_buckets'];

                if (empty($grandTotals['aging_buckets'])) {
                    // Initialize aging buckets on first encounter
                    $agingBucketCount = count($buckets);
                    $grandTotals['aging_buckets'] = array_fill(0, $agingBucketCount, 0.0);
                }

                // Add this group's buckets to grand total
                foreach ($buckets as $index => $amount) {
                    if (isset($grandTotals['aging_buckets'][$index])) {
                        $grandTotals['aging_buckets'][$index] += (float)$amount;
                    }
                }
            }
        }

        return $grandTotals;
    }

    /**
     * Format totals for display with money formatting
     *
     * @param array $totals Raw totals array
     * @return array Formatted totals array
     */
    public function formatTotals(array $totals): array
    {
        $formatted = [
            'charges' => $this->formatMoney($totals['charges'] ?? 0),
            'adjustments' => $this->formatMoney($totals['adjustments'] ?? 0),
            'paid' => $this->formatMoney($totals['paid'] ?? 0),
            'balance' => $this->formatMoney($totals['balance'] ?? 0),
            'aging_buckets' => [],
        ];

        // Format aging buckets if present
        if (!empty($totals['aging_buckets'])) {
            foreach ($totals['aging_buckets'] as $amount) {
                $formatted['aging_buckets'][] = $this->formatMoney($amount);
            }
        }

        return $formatted;
    }

    /**
     * Format money value with thousands separator and 2 decimal places
     *
     * @param float $amount Amount to format
     * @return string Formatted money value
     */
    private function formatMoney(float $amount): string
    {
        $formatted = number_format(abs($amount), 2, '.', ',');

        if ($amount < 0) {
            return '-' . $formatted;
        }

        return $formatted;
    }
}
