<?php

/**
 * Collections Report Aging Service
 * Built with Warp Terminal
 * Dedicated service for aging calculations in the Collections Report.
 * Handles date calculations, bucket determination, and balance distribution.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Services;

class AgingService
{
    /**
     * Calculate aging days between a date and reference date
     *
     * @param string $serviceDate Service date (YYYY-MM-DD)
     * @param string|null $activityDate Last activity date (YYYY-MM-DD), optional
     * @param int|null $referenceTimestamp Reference timestamp (defaults to current time)
     * @return int Number of days aged
     */
    public function calculateAgingDays(string $serviceDate, ?string $activityDate = null, ?int $referenceTimestamp = null): int
    {
        $referenceTime = $referenceTimestamp ?? time();

        // Use activity date if provided, otherwise use service date
        $dateToUse = !empty($activityDate) ? $activityDate : $serviceDate;

        // Handle empty or invalid dates
        if (empty($dateToUse) || $dateToUse === '0000-00-00') {
            return 0;
        }

        // Parse the date
        $dateTime = strtotime($dateToUse);
        if ($dateTime === false) {
            return 0;
        }

        // Calculate days difference
        $days = floor(($referenceTime - $dateTime) / (60 * 60 * 24));

        // Clamp negative values to 0 (future dates)
        return max(0, (int)$days);
    }

    /**
     * Determine which aging bucket a given number of days falls into
     *
     * @param int $days Number of days aged
     * @param array $config Aging configuration (age_cols, age_inc)
     * @return int Bucket index (0-based)
     */
    public function determineBucket(int $days, array $config): int
    {
        $ageCols = (int)($config['age_cols'] ?? 0);
        $ageInc = (int)($config['age_inc'] ?? 30);

        if ($ageCols <= 0 || $ageInc <= 0) {
            return 0;
        }

        // Calculate bucket index
        $bucketIndex = floor($days / $ageInc);

        // Clamp to valid range [0, ageCols-1]
        return (int)min($ageCols - 1, max(0, $bucketIndex));
    }

    /**
     * Distribute a balance into aging buckets
     *
     * @param float $balance Balance amount to distribute
     * @param int $days Number of days aged
     * @param array $config Aging configuration (age_cols, age_inc)
     * @return array Array of bucket values with balance in appropriate bucket
     */
    public function distributeBalance(float $balance, int $days, array $config): array
    {
        $ageCols = (int)($config['age_cols'] ?? 0);

        if ($ageCols <= 0) {
            return [];
        }

        // Initialize all buckets to 0
        $buckets = array_fill(0, $ageCols, 0.0);

        // Determine which bucket this balance belongs in
        $bucketIndex = $this->determineBucket($days, $config);

        // Put entire balance in the appropriate bucket
        $buckets[$bucketIndex] = $balance;

        return $buckets;
    }

    /**
     * Generate human-readable labels for aging buckets
     *
     * @param array $config Aging configuration (age_cols, age_inc)
     * @return array Array of bucket labels (e.g., ["0-29", "30-59", "60+"])
     */
    public function generateBucketLabels(array $config): array
    {
        $ageCols = (int)($config['age_cols'] ?? 0);
        $ageInc = (int)($config['age_inc'] ?? 30);

        if ($ageCols <= 0) {
            return [];
        }

        $labels = [];

        for ($i = 0; $i < $ageCols; $i++) {
            $start = $i * $ageInc;

            if ($i === $ageCols - 1) {
                // Last bucket is open-ended (e.g., "90+")
                $labels[] = $start . '+';
            } else {
                // Regular bucket with range (e.g., "0-29")
                $end = ($i + 1) * $ageInc - 1;
                $labels[] = $start . '-' . $end;
            }
        }

        return $labels;
    }

    /**
     * Calculate aging buckets from row data
     *
     * @param array $rowData Row data containing date fields
     * @param float $balance Invoice balance
     * @param array $config Aging configuration
     * @return array Array of aging bucket values
     */
    public function calculateAgingBucketsFromRow(array $rowData, float $balance, array $config): array
    {
        $serviceDate = $rowData['date'] ?? '';
        $activityDate = $rowData['aging_date'] ?? null;

        // Determine which date to use based on config
        $ageBy = $config['form_ageby'] ?? 'Service Date';
        $useActivityDate = (strpos($ageBy, 'Last') !== false);

        $dateToUse = $useActivityDate ? ($activityDate ?? $serviceDate) : $serviceDate;

        // Calculate aging days
        $days = $this->calculateAgingDays($dateToUse, null, null);

        // Distribute balance into buckets
        return $this->distributeBalance($balance, $days, [
            'age_cols' => $config['form_age_cols'] ?? 0,
            'age_inc' => $config['form_age_inc'] ?? 30,
        ]);
    }
}
