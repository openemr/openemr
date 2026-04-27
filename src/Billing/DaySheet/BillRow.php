<?php

/**
 * One row of input to the day sheet aggregator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\DaySheet;

final readonly class BillRow
{
    public function __construct(
        public string $user,
        public string $providerId,
        public string $codeType,
        public string $payType,
        public float $fee,
        public float $insCode,
        public float $patCode,
        public float $insAdjustDollar,
        public float $patAdjustDollar,
    ) {
    }

    /**
     * Build a BillRow from a heterogeneous array row produced by
     * getBillsBetweendayReport(). Missing or null numeric fields are
     * treated as 0 — the legacy aggregator relied on PHP's silent null
     * coercion in `+=`, and this preserves that behavior at the boundary
     * instead of letting null propagate further.
     *
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            user: self::asString($row['user'] ?? ''),
            providerId: self::asString($row['provider_id'] ?? ''),
            codeType: self::asString($row['code_type'] ?? ''),
            payType: self::asString($row['paytype'] ?? ''),
            fee: self::asFloat($row['fee'] ?? 0),
            insCode: self::asFloat($row['ins_code'] ?? 0),
            patCode: self::asFloat($row['pat_code'] ?? 0),
            insAdjustDollar: self::asFloat($row['ins_adjust_dollar'] ?? 0),
            patAdjustDollar: self::asFloat($row['pat_adjust_dollar'] ?? 0),
        );
    }

    private static function asString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return '';
    }

    private static function asFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        return 0.0;
    }
}
