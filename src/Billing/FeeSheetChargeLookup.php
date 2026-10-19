<?php

/**
 * Finds the code row the fee sheet would bill for a charge code, with the patient's price.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

final class FeeSheetChargeLookup
{
    /**
     * The code row for $code, chosen as FeeSheet::getPrice() chooses it: of
     * the given code type (any type when empty), active rows first, then the
     * row without a modifier, then the oldest. The fee is the price at the
     * patient's price level, or null when that level has no price.
     *
     * @return array{code_text: string, units: string, fee: ?string}|null null when no row has this code
     */
    public function find(string $codeType, string $code, int $pid): ?array
    {
        $sql = "SELECT c.code_text, c.units, p.pr_price FROM codes AS c"
            . " LEFT JOIN code_types AS ct ON ct.ct_id = c.code_type"
            . " LEFT JOIN patient_data AS pd ON pd.pid = ?"
            . " LEFT JOIN prices AS p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = pd.pricelevel"
            . " WHERE c.code = ?";
        $binds = [$pid, $code];
        if ($codeType !== '') {
            $sql .= " AND ct.ct_key = ?";
            $binds[] = $codeType;
        }
        $sql .= " ORDER BY c.active DESC, c.modifier, c.id LIMIT 1";

        $row = QueryUtils::querySingleRow($sql, $binds);
        if (!is_array($row)) {
            return null;
        }

        return [
            'code_text' => self::text($row['code_text'] ?? null) ?? '',
            'units' => self::text($row['units'] ?? null) ?? '',
            'fee' => self::text($row['pr_price'] ?? null),
        ];
    }

    private static function text(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return null;
    }
}
