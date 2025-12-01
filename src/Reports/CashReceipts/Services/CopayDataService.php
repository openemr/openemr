<?php

/**
 * CopayDataService - Process copay receipt data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Model\Receipt;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;

/**
 * Service for processing copay data into Receipt objects
 */
class CopayDataService
{
    /**
     * @var CashReceiptsRepository
     */
    private CashReceiptsRepository $repository;

    /**
     * @var bool Whether to use invoice display mode (show patient names)
     */
    private bool $useInvoiceDisplay;

    /**
     * Constructor
     *
     * @param CashReceiptsRepository $repository
     * @param bool $useInvoiceDisplay
     */
    public function __construct(CashReceiptsRepository $repository, bool $useInvoiceDisplay = false)
    {
        $this->repository = $repository;
        $this->useInvoiceDisplay = $useInvoiceDisplay;
    }

    /**
     * Process copay records into Receipt objects
     *
     * @param array $filters Filter parameters
     * @return Receipt[] Array of Receipt objects
     */
    public function processReceipts(array $filters): array
    {
        $records = $this->repository->getCopayReceipts($filters);
        $receipts = [];
        $skippedTransIds = [];

        foreach ($records as $record) {
            $transId = $record['trans_id'];

            // Skip if already marked to skip
            if (isset($skippedTransIds[$transId])) {
                continue;
            }

            // Check diagnosis filter if provided
            if (!empty($filters['diagnosis_code']) && !empty($filters['diagnosis_code_type'])) {
                if (!$this->hasDiagnosisCode($record, $filters)) {
                    $skippedTransIds[$transId] = true;
                    continue;
                }
            }

            // Create receipt object
            $receiptData = $this->mapRecordToReceiptData($record);
            $receipts[] = new Receipt($receiptData);
        }

        return $receipts;
    }

    /**
     * Check if record has required diagnosis code
     *
     * @param array $record
     * @param array $filters
     * @return bool
     */
    private function hasDiagnosisCode(array $record, array $filters): bool
    {
        return $this->repository->hasDiagnosisCode(
            (int)$record['pid'],
            (int)$record['encounter'],
            $filters['diagnosis_code_type'],
            $filters['diagnosis_code']
        );
    }

    /**
     * Map database record to Receipt constructor data
     *
     * @param array $record
     * @return array
     */
    private function mapRecordToReceiptData(array $record): array
    {
        $transDate = substr($record['date'], 0, 10);
        
        return [
            'pid' => $record['pid'],
            'encounter' => $record['encounter'],
            'provider_id' => $record['docid'],
            'trans_date' => $transDate,
            'amount' => $record['fee'],
            'type' => 'copay',
            'code' => null,
            'code_type' => null,
            'payer_id' => 0,
            'invoice_refno' => $record['invoice_refno'] ?? null,
            'patient_name' => $record['patient_name'] ?? null,
            'is_clinic' => false, // Copays are always professional fees
        ];
    }

    /**
     * Get total amount from receipts
     *
     * @param Receipt[] $receipts
     * @return float
     */
    public function getTotalAmount(array $receipts): float
    {
        return array_reduce($receipts, function ($carry, $receipt) {
            return $carry + $receipt->getAmount();
        }, 0.0);
    }

    /**
     * Get count of receipts
     *
     * @param Receipt[] $receipts
     * @return int
     */
    public function getReceiptCount(array $receipts): int
    {
        return count($receipts);
    }
}
