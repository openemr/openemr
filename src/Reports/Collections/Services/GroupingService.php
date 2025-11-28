<?php

/**
 * Collections Report Grouping Service
 * Built with Warp Terminal
 * Groups and aggregates invoice data by patient or insurance for the Collections Report.
 * Handles patient totals, rowspan calculation, and zebra-striping logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Services;

class GroupingService
{
    /**
     * Group invoices by patient and calculate totals
     *
     * @param array $rows Raw invoice rows from database
     * @param array $config Filter configuration settings
     * @return array Grouped patient data with totals
     */
    public function groupByPatient(array $rows, array $config): array
    {
        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        $currentPatient = null;
        $patientIndex = -1;
        $rowIndex = 0;

        foreach ($rows as $row) {
            $pid = $row['pid'] ?? 0;

            // Start new patient group
            if ($pid !== $currentPatient) {
                // Save previous patient index for alternating colors
                if ($currentPatient !== null) {
                    $rowIndex++;
                }

                $currentPatient = $pid;
                $patientIndex++;

                $grouped[$patientIndex] = [
                    'pid' => $pid,
                    'invoices' => [],
                    'invoice_count' => 0,
                    'rowspan' => 0,
                    'row_index' => $rowIndex,
                    'show_total_row' => false,
                    'totals' => [
                        'charges' => 0,
                        'adjustments' => 0,
                        'paid' => 0,
                        'balance' => 0,
                        'aging_buckets' => $this->initializeAgingBuckets($config),
                    ],
                ];
            }

            // Add invoice to current patient
            $isFirstRow = (count($grouped[$patientIndex]['invoices']) === 0);
            $row['is_first_row'] = $isFirstRow;

            $grouped[$patientIndex]['invoices'][] = $row;
            $grouped[$patientIndex]['invoice_count']++;
            $grouped[$patientIndex]['rowspan']++;

            // Accumulate totals
            $this->accumulateTotals($grouped[$patientIndex]['totals'], $row, $config);
        }

        // Set show_total_row flag for patients with multiple invoices
        foreach ($grouped as $index => $patient) {
            $grouped[$index]['show_total_row'] = ($patient['invoice_count'] > 1);
        }

        return $grouped;
    }

    /**
     * Group invoices by insurance and calculate totals
     *
     * @param array $rows Raw invoice rows from database
     * @param array $config Filter configuration settings
     * @return array Grouped insurance data with totals
     */
    public function groupByInsurance(array $rows, array $config): array
    {
        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        $insuranceMap = [];

        foreach ($rows as $row) {
            $insuranceName = $row['ins1'] ?? 'Unknown';

            // Initialize insurance group if new
            if (!isset($insuranceMap[$insuranceName])) {
                $index = count($grouped);
                $insuranceMap[$insuranceName] = $index;

                $grouped[$index] = [
                    'insurance_name' => $insuranceName,
                    'invoice_count' => 0,
                    'totals' => [
                        'charges' => 0,
                        'adjustments' => 0,
                        'paid' => 0,
                        'balance' => 0,
                        'aging_buckets' => $this->initializeAgingBuckets($config),
                    ],
                ];
            }

            $index = $insuranceMap[$insuranceName];
            $grouped[$index]['invoice_count']++;

            // Accumulate totals
            $this->accumulateTotals($grouped[$index]['totals'], $row, $config);
        }

        return $grouped;
    }

    /**
     * Initialize aging buckets array
     *
     * @param array $config Filter configuration
     * @return array Empty aging buckets or empty array if aging not enabled
     */
    private function initializeAgingBuckets(array $config): array
    {
        $ageCols = (int)($config['form_age_cols'] ?? 0);

        if ($ageCols <= 0) {
            return [];
        }

        return array_fill(0, $ageCols, 0.0);
    }

    /**
     * Accumulate financial totals from row
     *
     * @param array &$totals Totals array to update (passed by reference)
     * @param array $row Invoice row data
     * @param array $config Filter configuration
     * @return void
     */
    private function accumulateTotals(array &$totals, array $row, array $config): void
    {
        $charges = (float)($row['charges'] ?? 0);
        $adjustments = (float)($row['adjustments'] ?? 0);
        $paid = (float)($row['paid'] ?? 0);
        $balance = $charges + $adjustments - $paid;

        $totals['charges'] += $charges;
        $totals['adjustments'] += $adjustments;
        $totals['paid'] += $paid;
        $totals['balance'] += $balance;

        // Accumulate aging buckets if enabled
        if (!empty($totals['aging_buckets'])) {
            $this->accumulateAgingBuckets($totals['aging_buckets'], $row, $balance, $config);
        }
    }

    /**
     * Accumulate balance into appropriate aging bucket
     *
     * @param array &$agingBuckets Aging buckets array (passed by reference)
     * @param array $row Invoice row data
     * @param float $balance Invoice balance
     * @param array $config Filter configuration
     * @return void
     */
    private function accumulateAgingBuckets(array &$agingBuckets, array $row, float $balance, array $config): void
    {
        $ageCols = (int)($config['form_age_cols'] ?? 0);
        $ageInc = (int)($config['form_age_inc'] ?? 30);

        if ($ageCols <= 0) {
            return;
        }

        // Determine age date (service date vs. last activity date)
        $ageBy = $config['form_ageby'] ?? 'Service Date';
        $ageDate = (strpos($ageBy, 'Last') !== false)
            ? ($row['aging_date'] ?? $row['dos'] ?? '')
            : ($row['dos'] ?? '');

        if (empty($ageDate)) {
            return;
        }

        // Calculate days old
        $ageTime = strtotime($ageDate);
        if ($ageTime === false) {
            return;
        }

        $days = floor((time() - $ageTime) / (60 * 60 * 24));

        // Determine which bucket
        $bucketIndex = min($ageCols - 1, max(0, floor($days / $ageInc)));

        // Add balance to appropriate bucket
        $agingBuckets[$bucketIndex] += $balance;
    }
}
