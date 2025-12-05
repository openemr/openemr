<?php

/**
 * ArActivityDataService - Process AR activity payment data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Services;

use OpenEMR\Reports\CashReceipts\Enums\DateMode;
use OpenEMR\Reports\CashReceipts\Model\Receipt;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;

/**
 * Service for processing AR activity data into Receipt objects
 */
class ArActivityDataService
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
     * @var callable|null Function to determine if code is clinic receipt
     */
    private $isClinicCallback;

    /**
     * Constructor
     *
     * @param CashReceiptsRepository $repository
     * @param bool $useInvoiceDisplay
     * @param callable|null $isClinicCallback Function that takes code and returns bool
     */
    public function __construct(
        CashReceiptsRepository $repository,
        bool $useInvoiceDisplay = false,
        ?callable $isClinicCallback = null
    ) {
        $this->repository = $repository;
        $this->useInvoiceDisplay = $useInvoiceDisplay;
        $this->isClinicCallback = $isClinicCallback;
    }

    /**
     * Process AR activity records into Receipt objects
     *
     * @param array $filters Filter parameters including 'date_mode'
     * @return Receipt[] Array of Receipt objects
     */
    public function processReceipts(array $filters): array
    {
        $records = $this->repository->getArActivityReceipts($filters);
        $receipts = [];
        $skippedTransIds = [];

        foreach ($records as $record) {
            $transId = $record['trans_id'];

            // Skip if already marked to skip
            if (isset($skippedTransIds[$transId])) {
                continue;
            }

            // Determine transaction date based on date mode
            $dateMode = $filters['date_mode'] ?? DateMode::PAYMENT->value;
            $transDate = $this->getTransactionDate($record, $dateMode);

            // Check if date is within range (secondary filter)
            if (!$this->isDateInRange($transDate, $filters['from_date'], $filters['to_date'])) {
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
            $receiptData = $this->mapRecordToReceiptData($record, $transDate);
            $receipt = new Receipt($receiptData);

            // Determine if clinic receipt
            if ($this->isClinicCallback && !empty($record['code'])) {
                $isClinic = call_user_func($this->isClinicCallback, $record['code']);
                $receipt->setIsClinicReceipt($isClinic);
            }

            $receipts[] = $receipt;
        }

        return $receipts;
    }

    /**
     * Get transaction date based on date mode
     *
     * @param array $record
     * @param int $dateMode
     * @return string Date in Y-m-d format
     */
    private function getTransactionDate(array $record, int $dateMode): string
    {
        switch ($dateMode) {
            case DateMode::SERVICE->value:
                return substr($record['date'], 0, 10);
            
            case DateMode::ENTRY->value:
                return substr($record['post_time'], 0, 10);
            
            case DateMode::PAYMENT->value:
            default:
                if (!empty($record['deposit_date'])) {
                    return $record['deposit_date'];
                }
                return substr($record['post_time'], 0, 10);
        }
    }

    /**
     * Check if date is within range
     *
     * @param string $date Date in Y-m-d format
     * @param string $fromDate
     * @param string $toDate
     * @return bool
     */
    private function isDateInRange(string $date, string $fromDate, string $toDate): bool
    {
        return strcmp($date, $fromDate) >= 0 && strcmp($date, $toDate) <= 0;
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
     * @param string $transDate
     * @return array
     */
    private function mapRecordToReceiptData(array $record, string $transDate): array
    {
        // Determine provider ID (prefer billing provider, fallback to encounter provider)
        $providerId = !empty($record['provider_id']) ? $record['provider_id'] : $record['docid'];
        
        return [
            'pid' => $record['pid'],
            'encounter' => $record['encounter'],
            'provider_id' => $providerId,
            'trans_date' => $transDate,
            'amount' => abs($record['pay_amount']), // Ensure positive amount
            'type' => 'ar_activity',
            'code' => $record['code'] ?? null,
            'code_type' => $record['ar_code_type'] ?? null,
            'payer_id' => $record['payer_id'] ?? 0,
            'invoice_refno' => $record['invoice_refno'] ?? null,
            'patient_name' => $record['patient_name'] ?? null,
            'is_clinic' => false, // Will be determined by callback
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
