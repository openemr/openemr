<?php

/**
 * Sales Item model for Sales by Item report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Model;

class SalesItem
{
    private int $patientId;
    private int $encounterId;
    private string $category;
    private string $description;
    private string $transactionDate;
    private int $quantity;
    private float $amount;
    private string $invoiceNumber;
    private string $invoiceRefNo;

    public function __construct(
        int $patientId,
        int $encounterId,
        string $category,
        string $description,
        string $transactionDate,
        int $quantity,
        float $amount,
        string $invoiceNumber,
        string $invoiceRefNo = ''
    ) {
        $this->patientId = $patientId;
        $this->encounterId = $encounterId;
        $this->category = $category;
        $this->description = $description;
        $this->transactionDate = $transactionDate;
        $this->quantity = $quantity;
        $this->amount = $amount;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceRefNo = $invoiceRefNo;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getEncounterId(): int
    {
        return $this->encounterId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTransactionDate(): string
    {
        return $this->transactionDate;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getInvoiceRefNo(): string
    {
        return $this->invoiceRefNo;
    }
}
