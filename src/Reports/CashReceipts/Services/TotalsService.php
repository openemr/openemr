<?php

/**
 * TotalsService - Calculate totals for cash receipts report
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Model\ProviderSummary;

/**
 * Service for calculating totals across providers
 */
class TotalsService
{
    /**
     * Calculate grand totals from provider summaries
     *
     * @param ProviderSummary[] $providerSummaries
     * @return array{professional: float, clinic: float, grand: float}
     */
    public function calculateGrandTotals(array $providerSummaries): array
    {
        $professional = 0.0;
        $clinic = 0.0;

        foreach ($providerSummaries as $summary) {
            $professional += $summary->getProfessionalTotal();
            $clinic += $summary->getClinicTotal();
        }

        return [
            'professional' => $professional,
            'clinic' => $clinic,
            'grand' => $professional + $clinic,
        ];
    }

    /**
     * Calculate total receipt count
     *
     * @param ProviderSummary[] $providerSummaries
     * @return int
     */
    public function getTotalReceiptCount(array $providerSummaries): int
    {
        return array_reduce($providerSummaries, fn($carry, $summary) => $carry + $summary->getReceiptCount(), 0);
    }

    /**
     * Calculate total encounter count
     *
     * @param ProviderSummary[] $providerSummaries
     * @return int
     */
    public function getTotalEncounterCount(array $providerSummaries): int
    {
        return array_reduce($providerSummaries, fn($carry, $summary) => $carry + $summary->getEncounterCount(), 0);
    }

    /**
     * Get average receipt amount across all providers
     *
     * @param ProviderSummary[] $providerSummaries
     * @return float
     */
    public function getAverageReceiptAmount(array $providerSummaries): float
    {
        $totalCount = $this->getTotalReceiptCount($providerSummaries);
        if ($totalCount === 0) {
            return 0.0;
        }

        $totals = $this->calculateGrandTotals($providerSummaries);
        return $totals['grand'] / $totalCount;
    }
}
