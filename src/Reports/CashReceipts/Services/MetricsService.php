<?php

/**
 * MetricsService - Calculate business metrics and KPIs
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Model\Receipt;
use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;

/**
 * Service for calculating business intelligence metrics
 */
class MetricsService
{
    /**
     * Calculate provider productivity metrics
     *
     * @param ProviderSummary $provider
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getProviderProductivity(ProviderSummary $provider, string $fromDate, string $toDate): array
    {
        $dateRange = $provider->getDateRange();
        $actualFromDate = !empty($dateRange['start']) ? $dateRange['start'] : $fromDate;
        $actualToDate = !empty($dateRange['end']) ? $dateRange['end'] : $toDate;
        
        $dayCount = $this->calculateDaysBetween($actualFromDate, $actualToDate);
        $dayCount = max(1, $dayCount); // Avoid division by zero
        
        return [
            'provider_id' => $provider->getProviderId(),
            'provider_name' => $provider->getProviderName(),
            'total_receipts' => $provider->getGrandTotal(),
            'receipt_count' => $provider->getReceiptCount(),
            'encounter_count' => $provider->getEncounterCount(),
            'avg_per_day' => $provider->getGrandTotal() / $dayCount,
            'avg_per_receipt' => $provider->getAverageReceiptAmount(),
            'avg_per_encounter' => $provider->getEncounterCount() > 0 
                ? $provider->getGrandTotal() / $provider->getEncounterCount() 
                : 0.0,
            'professional_total' => $provider->getProfessionalTotal(),
            'clinic_total' => $provider->getClinicTotal(),
            'day_count' => $dayCount,
        ];
    }

    /**
     * Calculate payment method breakdown
     *
     * @param Receipt[] $receipts
     * @return array
     */
    public function getPaymentMethodBreakdown(array $receipts): array
    {
        $copayTotal = 0.0;
        $copayCount = 0;
        $arActivityTotal = 0.0;
        $arActivityCount = 0;

        foreach ($receipts as $receipt) {
            if ($receipt->getType() === 'copay') {
                $copayTotal += $receipt->getAmount();
                $copayCount++;
            } else {
                $arActivityTotal += $receipt->getAmount();
                $arActivityCount++;
            }
        }

        $total = $copayTotal + $arActivityTotal;

        return [
            'copay' => [
                'total' => $copayTotal,
                'count' => $copayCount,
                'percentage' => $total > 0 ? ($copayTotal / $total) * 100 : 0,
            ],
            'ar_activity' => [
                'total' => $arActivityTotal,
                'count' => $arActivityCount,
                'percentage' => $total > 0 ? ($arActivityTotal / $total) * 100 : 0,
            ],
            'total' => $total,
        ];
    }

    /**
     * Analyze daily cash flow
     *
     * @param Receipt[] $receipts
     * @return array Array indexed by date (Y-m-d)
     */
    public function getDailyCashFlow(array $receipts): array
    {
        $dailyTotals = [];

        foreach ($receipts as $receipt) {
            $date = $receipt->getTransactionDate();
            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = [
                    'date' => $date,
                    'total' => 0.0,
                    'count' => 0,
                    'professional' => 0.0,
                    'clinic' => 0.0,
                ];
            }

            $dailyTotals[$date]['total'] += $receipt->getAmount();
            $dailyTotals[$date]['count']++;
            
            if ($receipt->isClinicReceipt()) {
                $dailyTotals[$date]['clinic'] += $receipt->getAmount();
            } else {
                $dailyTotals[$date]['professional'] += $receipt->getAmount();
            }
        }

        ksort($dailyTotals);
        return array_values($dailyTotals);
    }

    /**
     * Analyze weekly cash flow
     *
     * @param Receipt[] $receipts
     * @return array Array indexed by week start date
     */
    public function getWeeklyCashFlow(array $receipts): array
    {
        $weeklyTotals = [];
        
        // Pre-initialize all weeks from receipts
        foreach ($receipts as $receipt) {
            $weekStart = $this->getWeekStartDate($receipt->getTransactionDate());
            if (!isset($weeklyTotals[$weekStart])) {
                $weeklyTotals[$weekStart] = [
                    'week_start' => $weekStart,
                    'total' => 0.0,
                    'count' => 0,
                    'professional' => 0.0,
                    'clinic' => 0.0,
                ];
            }
        }
        
        // Aggregate data
        foreach ($receipts as $receipt) {
            $weekStart = $this->getWeekStartDate($receipt->getTransactionDate());
            
            $weeklyTotals[$weekStart]['total'] += $receipt->getAmount();
            $weeklyTotals[$weekStart]['count']++;
            
            if ($receipt->isClinicReceipt()) {
                $weeklyTotals[$weekStart]['clinic'] += $receipt->getAmount();
            } else {
                $weeklyTotals[$weekStart]['professional'] += $receipt->getAmount();
            }
        }

        ksort($weeklyTotals);
        return array_values($weeklyTotals);
    }

    /**
     * Get top procedures by revenue
     *
     * @param Receipt[] $receipts
     * @param int $limit
     * @return array
     */
    public function getTopProcedures(array $receipts, int $limit = 10): array
    {
        $procedureTotals = [];

        foreach ($receipts as $receipt) {
            $code = $receipt->getProcedureCode();
            if (empty($code)) {
                continue;
            }

            if (!isset($procedureTotals[$code])) {
                $procedureTotals[$code] = [
                    'code' => $code,
                    'code_type' => $receipt->getCodeType(),
                    'total' => 0.0,
                    'count' => 0,
                ];
            }

            $procedureTotals[$code]['total'] += $receipt->getAmount();
            $procedureTotals[$code]['count']++;
        }

        // Sort by total descending
        usort($procedureTotals, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        return array_slice($procedureTotals, 0, $limit);
    }

    /**
     * Compare two time periods
     *
     * @param array $currentPeriodData
     * @param array $previousPeriodData
     * @return array
     */
    public function comparePeriods(array $currentPeriodData, array $previousPeriodData): array
    {
        $currentTotal = $currentPeriodData['total'] ?? 0.0;
        $previousTotal = $previousPeriodData['total'] ?? 0.0;
        
        $change = $currentTotal - $previousTotal;
        $percentChange = $previousTotal > 0 ? ($change / $previousTotal) * 100 : 0;

        return [
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'change' => $change,
            'percent_change' => $percentChange,
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
        ];
    }

    /**
     * Calculate number of days between two dates
     *
     * @param string $fromDate Y-m-d format
     * @param string $toDate Y-m-d format
     * @return int
     */
    private function calculateDaysBetween(string $fromDate, string $toDate): int
    {
        $from = new \DateTime($fromDate);
        $to = new \DateTime($toDate);
        $diff = $from->diff($to);
        return $diff->days + 1; // Include both start and end date
    }

    /**
     * Get week start date (Monday) for given date
     *
     * @param string $date Y-m-d format
     * @return string Y-m-d format
     */
    private function getWeekStartDate(string $date): string
    {
        $dt = new \DateTime($date);
        $dayOfWeek = (int)$dt->format('N'); // 1 (Monday) through 7 (Sunday)
        $dt->modify('-' . ($dayOfWeek - 1) . ' days');
        return $dt->format('Y-m-d');
    }
}
