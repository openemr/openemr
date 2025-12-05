<?php

/**
 * ProviderSummary Model - Represents aggregated data for a provider
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Model;

/**
 * ProviderSummary data object representing aggregated cash receipts for a provider
 */
class ProviderSummary
{
    /**
     * @var int Provider ID
     */
    private int $providerId;

    /**
     * @var string Provider name (formatted: fname lname)
     */
    private string $providerName;

    /**
     * @var Receipt[] Array of receipts for this provider
     */
    private array $receipts;

    /**
     * @var float Total professional fees
     */
    private float $professionalTotal;

    /**
     * @var float Total clinic fees
     */
    private float $clinicTotal;

    /**
     * Constructor
     *
     * @param int $providerId
     * @param string $providerName
     */
    public function __construct(int $providerId, string $providerName)
    {
        $this->providerId = $providerId;
        $this->providerName = $providerName;
        $this->receipts = [];
        $this->professionalTotal = 0.0;
        $this->clinicTotal = 0.0;
    }

    /**
     * Get provider ID
     *
     * @return int
     */
    public function getProviderId(): int
    {
        return $this->providerId;
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * Get all receipts
     *
     * @return Receipt[]
     */
    public function getReceipts(): array
    {
        return $this->receipts;
    }

    /**
     * Add a receipt to this provider's summary
     *
     * @param Receipt $receipt
     * @return void
     */
    public function addReceipt(Receipt $receipt): void
    {
        $this->receipts[] = $receipt;
        
        if ($receipt->isClinicReceipt()) {
            $this->clinicTotal += $receipt->getAmount();
        } else {
            $this->professionalTotal += $receipt->getAmount();
        }
    }

    /**
     * Get professional total
     *
     * @return float
     */
    public function getProfessionalTotal(): float
    {
        return $this->professionalTotal;
    }

    /**
     * Get clinic total
     *
     * @return float
     */
    public function getClinicTotal(): float
    {
        return $this->clinicTotal;
    }

    /**
     * Get combined total (professional + clinic)
     *
     * @return float
     */
    public function getGrandTotal(): float
    {
        return $this->professionalTotal + $this->clinicTotal;
    }

    /**
     * Get count of receipts
     *
     * @return int
     */
    public function getReceiptCount(): int
    {
        return count($this->receipts);
    }

    /**
     * Get count of unique encounters
     *
     * @return int
     */
    public function getEncounterCount(): int
    {
        $encounters = [];
        foreach ($this->receipts as $receipt) {
            $key = $receipt->getPatientId() . '.' . $receipt->getEncounterId();
            $encounters[$key] = true;
        }
        return count($encounters);
    }

    /**
     * Get average receipt amount
     *
     * @return float
     */
    public function getAverageReceiptAmount(): float
    {
        $count = $this->getReceiptCount();
        if ($count === 0) {
            return 0.0;
        }
        return $this->getGrandTotal() / $count;
    }

    /**
     * Get date range covered by receipts
     *
     * @return array{start: string, end: string}
     */
    public function getDateRange(): array
    {
        if (empty($this->receipts)) {
            return ['start' => '', 'end' => ''];
        }

        $dates = array_map(function ($receipt) {
            return $receipt->getTransactionDate();
        }, $this->receipts);

        return [
            'start' => min($dates),
            'end' => max($dates),
        ];
    }

    /**
     * Convert to array for template rendering
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'provider_id' => $this->providerId,
            'provider_name' => $this->providerName,
            'professional_total' => $this->professionalTotal,
            'clinic_total' => $this->clinicTotal,
            'grand_total' => $this->getGrandTotal(),
            'receipt_count' => $this->getReceiptCount(),
            'encounter_count' => $this->getEncounterCount(),
            'average_receipt' => $this->getAverageReceiptAmount(),
            'date_range' => $this->getDateRange(),
            'receipts' => array_map(function ($receipt) {
                return $receipt->toArray();
            }, $this->receipts),
        ];
    }
}
