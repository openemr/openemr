<?php

/**
 * Receipt Model - Represents a single cash receipt transaction
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Model;

/**
 * Receipt data object representing a single cash receipt transaction
 */
class Receipt
{
    /**
     * @var int Patient ID
     */
    private int $patientId;

    /**
     * @var int Encounter ID
     */
    private int $encounterId;

    /**
     * @var int Provider/Doctor ID
     */
    private int $providerId;

    /**
     * @var string Transaction date (YYYY-MM-DD format)
     */
    private string $transactionDate;

    /**
     * @var float Transaction amount
     */
    private float $amount;

    /**
     * @var string Transaction type (copay or ar_activity)
     */
    private string $type;

    /**
     * @var string|null Procedure code (if applicable)
     */
    private ?string $procedureCode;

    /**
     * @var string|null Code type (CPT4, HCPCS, etc.)
     */
    private ?string $codeType;

    /**
     * @var int Insurance company/payer ID (0 if patient payment)
     */
    private int $payerId;

    /**
     * @var string|null Invoice reference number
     */
    private ?string $invoiceRefNo;

    /**
     * @var string|null Patient full name
     */
    private ?string $patientName;

    /**
     * @var bool Whether this is a clinic receipt (vs professional)
     */
    private bool $isClinicReceipt;

    /**
     * Constructor
     *
     * @param array $data Array of receipt data from database
     */
    public function __construct(array $data)
    {
        $this->patientId = (int)($data['pid'] ?? 0);
        $this->encounterId = (int)($data['encounter'] ?? 0);
        $this->providerId = (int)($data['provider_id'] ?? $data['docid'] ?? 0);
        $this->transactionDate = $data['trans_date'] ?? '';
        $this->amount = (float)($data['amount'] ?? 0.0);
        $this->type = $data['type'] ?? 'copay';
        $this->procedureCode = $data['code'] ?? null;
        $this->codeType = $data['code_type'] ?? null;
        $this->payerId = (int)($data['payer_id'] ?? 0);
        $this->invoiceRefNo = $data['invoice_refno'] ?? null;
        $this->patientName = $data['patient_name'] ?? null;
        $this->isClinicReceipt = (bool)($data['is_clinic'] ?? false);
    }

    /**
     * Get patient ID
     *
     * @return int
     */
    public function getPatientId(): int
    {
        return $this->patientId;
    }

    /**
     * Get encounter ID
     *
     * @return int
     */
    public function getEncounterId(): int
    {
        return $this->encounterId;
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
     * Get transaction date
     *
     * @return string
     */
    public function getTransactionDate(): string
    {
        return $this->transactionDate;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get transaction type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get procedure code
     *
     * @return string|null
     */
    public function getProcedureCode(): ?string
    {
        return $this->procedureCode;
    }

    /**
     * Get code type
     *
     * @return string|null
     */
    public function getCodeType(): ?string
    {
        return $this->codeType;
    }

    /**
     * Get payer ID
     *
     * @return int
     */
    public function getPayerId(): int
    {
        return $this->payerId;
    }

    /**
     * Get invoice reference number
     *
     * @return string|null
     */
    public function getInvoiceRefNo(): ?string
    {
        return $this->invoiceRefNo;
    }

    /**
     * Get patient name
     *
     * @return string|null
     */
    public function getPatientName(): ?string
    {
        return $this->patientName;
    }

    /**
     * Check if this is a clinic receipt
     *
     * @return bool
     */
    public function isClinicReceipt(): bool
    {
        return $this->isClinicReceipt;
    }

    /**
     * Set whether this is a clinic receipt
     *
     * @param bool $isClinic
     * @return void
     */
    public function setIsClinicReceipt(bool $isClinic): void
    {
        $this->isClinicReceipt = $isClinic;
    }

    /**
     * Get invoice display (either patient.encounter or invoice ref number)
     *
     * @return string
     */
    public function getInvoiceDisplay(): string
    {
        if (!empty($this->invoiceRefNo)) {
            return $this->invoiceRefNo;
        }
        return "{$this->patientId}.{$this->encounterId}";
    }

    /**
     * Get sorting key for provider/date/patient/encounter ordering
     *
     * @return string
     */
    public function getSortingKey(): string
    {
        return sprintf(
            "%08d%s%08d%08d",
            $this->providerId,
            $this->transactionDate,
            $this->patientId,
            $this->encounterId
        );
    }

    /**
     * Convert to array for template rendering
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'patient_id' => $this->patientId,
            'encounter_id' => $this->encounterId,
            'provider_id' => $this->providerId,
            'transaction_date' => $this->transactionDate,
            'amount' => $this->amount,
            'type' => $this->type,
            'procedure_code' => $this->procedureCode,
            'code_type' => $this->codeType,
            'payer_id' => $this->payerId,
            'invoice_refno' => $this->invoiceRefNo,
            'patient_name' => $this->patientName,
            'is_clinic' => $this->isClinicReceipt,
            'invoice_display' => $this->getInvoiceDisplay(),
        ];
    }
}
