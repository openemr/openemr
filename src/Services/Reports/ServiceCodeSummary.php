<?php

/**
 * Data object representing one row of the Financial Summary by Service Code report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Reports;

readonly class ServiceCodeSummary
{
    public function __construct(
        public string $code,
        public int $units,
        public float $billed,
        public float $paid,
        public float $adjusted,
        public float $balance,
        public ?bool $financialReporting,
    ) {
    }

    /**
     * Build from a database result row.
     *
     * @param array{code: string, units: string, billed: string, paid: string, adjusted: string, financial_reporting: string|null} $row
     */
    public static function fromArray(array $row): self
    {
        $billed = (float) $row['billed'];
        $paid = (float) $row['paid'];
        $adjusted = (float) $row['adjusted'];

        return new self(
            code: $row['code'],
            units: (int) $row['units'],
            billed: $billed,
            paid: $paid,
            adjusted: $adjusted,
            balance: $billed - $paid - $adjusted,
            financialReporting: $row['financial_reporting'] === null ? null : (bool) $row['financial_reporting'],
        );
    }

    /**
     * Convert to the associative array format the report rendering layer expects.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'Procedure codes' => $this->code,
            'Units' => $this->units,
            'Amt Billed' => $this->billed,
            'Paid Amt' => $this->paid,
            'Adjustment Amt' => $this->adjusted,
            'Balance Amt' => $this->balance,
            'financial_reporting' => $this->financialReporting,
        ];
    }
}
