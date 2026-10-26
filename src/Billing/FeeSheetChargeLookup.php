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
     * the given code type (its key, or its code_types id), active rows first, then the row without a
     * modifier, then the oldest. When no row of that type has the code (or
     * no type is given), a row of any type is chosen the same way, so an item
     * still gets a description, units, fee and modifier. The fee is the price at the
     * patient's price level, or null when that level has no price; it is the price of
     * one unit. The modifier is the row's own (e.g. JZ on a drug code), or ''.
     *
     * @return array{code_text: string, units: string, fee: ?string, modifier: string}|null null when no row has this code
     */
    public function find(string $codeType, string $code, int $pid): ?array
    {
        $row = $codeType !== '' ? $this->row($code, $pid, $codeType) : null;
        $row ??= $this->row($code, $pid, null);
        if ($row === null) {
            return null;
        }

        return [
            'code_text' => self::text($row['code_text'] ?? null) ?? '',
            'units' => self::text($row['units'] ?? null) ?? '',
            'fee' => self::text($row['pr_price'] ?? null),
            'modifier' => self::text($row['modifier'] ?? null) ?? '',
        ];
    }

    /**
     * @return array<mixed>|null
     */
    private function row(string $code, int $pid, ?string $codeType): ?array
    {
        $sql = "SELECT c.code_text, c.units, c.modifier, p.pr_price FROM codes AS c"
            . " LEFT JOIN code_types AS ct ON ct.ct_id = c.code_type"
            . " LEFT JOIN patient_data AS pd ON pd.pid = ?"
            . " LEFT JOIN prices AS p ON p.pr_id = c.id AND p.pr_selector = '' AND p.pr_level = pd.pricelevel"
            . " WHERE c.code = ?";
        $binds = [$pid, $code];
        if ($codeType !== null) {
            // A code type is its key (CPT4), or its code_types id when all digits.
            $sql .= ctype_digit($codeType) ? " AND ct.ct_id = ?" : " AND ct.ct_key = ?";
            $binds[] = $codeType;
        }
        $sql .= " ORDER BY c.active DESC, c.modifier, c.id LIMIT 1";

        $row = QueryUtils::querySingleRow($sql, $binds);
        return is_array($row) ? $row : null;
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
