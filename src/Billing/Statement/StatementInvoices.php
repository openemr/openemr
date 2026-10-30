<?php

/**
 * The invoices a statement run reads and the statement counts it updates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Common\Database\QueryUtils;

final class StatementInvoices
{
    /**
     * The checked invoices with their patient's name, address and guardian,
     * ordered by patient (name, then pid) and then date, so each patient's
     * invoices come together.
     *
     * @param list<int> $encounterIds form_encounter.id
     * @return list<array<string, mixed>>
     */
    public static function forRun(array $encounterIds): array
    {
        if ($encounterIds === []) {
            return [];
        }
        $rows = QueryUtils::fetchRecords(
            "SELECT f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, f.last_level_closed, " .
            "f.last_level_billed, f.billing_note AS enc_billing_note, f.provider_id, " .
            "p.fname, p.mname, p.lname, p.street, p.street_line_2, p.city, p.state, p.postal_code, " .
            "p.billing_note AS pat_billing_note, " .
            "p.guardiansname, p.guardianaddress, p.guardiancity, p.guardianstate, p.guardianpostalcode " .
            "FROM form_encounter AS f JOIN patient_data AS p ON p.pid = f.pid " .
            "WHERE f.id IN (" . implode(', ', array_fill(0, count($encounterIds), '?')) . ") " .
            "ORDER BY p.lname, p.fname, f.pid, f.date, f.encounter",
            $encounterIds
        );
        $invoices = [];
        foreach ($rows as $row) {
            $invoice = [];
            foreach ($row as $column => $value) {
                if (is_string($column)) {
                    $invoice[$column] = $value;
                }
            }
            $invoices[] = $invoice;
        }
        return $invoices;
    }

    /**
     * Count the invoices as statemented on $today: one more statement, and
     * today as the last statement date.
     *
     * @param list<int> $encounterIds form_encounter.id
     * @param string $today Y-m-d
     */
    public static function recordStatementRun(array $encounterIds, string $today): void
    {
        foreach ($encounterIds as $encounterId) {
            QueryUtils::sqlStatementThrowException(
                "UPDATE form_encounter SET last_stmt_date = ?, stmt_count = stmt_count + 1 WHERE id = ?",
                [$today, $encounterId]
            );
        }
    }
}
