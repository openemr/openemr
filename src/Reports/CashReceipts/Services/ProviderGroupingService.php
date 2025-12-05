<?php

/**
 * ProviderGroupingService - Group receipts by provider
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
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;

/**
 * Service for grouping receipts by provider
 */
class ProviderGroupingService
{
    /**
     * @var CashReceiptsRepository
     */
    private CashReceiptsRepository $repository;

    /**
     * Constructor
     *
     * @param CashReceiptsRepository $repository
     */
    public function __construct(CashReceiptsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Group receipts by provider
     *
     * @param Receipt[] $receipts
     * @return ProviderSummary[] Array of ProviderSummary objects indexed by provider ID
     */
    public function groupByProvider(array $receipts): array
    {
        // Sort receipts by sorting key
        usort($receipts, function ($a, $b) {
            return strcmp($a->getSortingKey(), $b->getSortingKey());
        });

        $providers = [];

        foreach ($receipts as $receipt) {
            $providerId = $receipt->getProviderId();
            $providerName = $this->repository->getProviderName($providerId);
            $providers[$providerId] ??= new ProviderSummary($providerId, $providerName);
            $providers[$providerId]->addReceipt($receipt);
        }

        return $providers;
    }

    /**
     * Get provider summaries sorted by provider ID
     *
     * @param Receipt[] $receipts
     * @return ProviderSummary[]
     */
    public function getSortedProviderSummaries(array $receipts): array
    {
        $providers = $this->groupByProvider($receipts);
        ksort($providers);
        return array_values($providers);
    }
}
