<?php

/**
 * Per-slot accumulator for the day sheet report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\DaySheet;

final class SlotTotals
{
    public function __construct(
        public readonly string $key,
        public float $fee = 0.0,
        public float $insPay = 0.0,
        public float $insAdj = 0.0,
        public float $insRef = 0.0,
        public float $patAdj = 0.0,
        public float $patPay = 0.0,
        public float $patRef = 0.0,
    ) {
    }

    /**
     * Apply one row to this slot's totals using the legacy num1 split-refund
     * policy: insurance and patient amounts split into payment vs refund
     * buckets based on sign and code type.
     */
    public function applyRow(BillRow $row): void
    {
        $this->fee += $row->fee;
        $this->insAdj += $row->insAdjustDollar;
        $this->patAdj += $row->patAdjustDollar;

        if ($row->codeType === 'Insurance Payment') {
            if ($row->insCode > 0) {
                $this->insPay += $row->insCode;
            } elseif ($row->insCode < 0) {
                $this->insRef += $row->insCode;
            }
        }

        if ($row->codeType === 'Patient Payment') {
            if ($row->patCode > 0) {
                $this->patPay += $row->patCode;
            } elseif ($row->patCode < 0 && $row->payType !== 'PCP') {
                $this->patRef += $row->patCode;
            }
        }
    }

    public function isAllZero(): bool
    {
        return $this->fee === 0.0
            && $this->insPay === 0.0
            && $this->insAdj === 0.0
            && $this->insRef === 0.0
            && $this->patAdj === 0.0
            && $this->patPay === 0.0
            && $this->patRef === 0.0;
    }
}
