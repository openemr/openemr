<?php

/**
 * Encounters that hold a credit for the patient, for netting on statements.
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

/**
 * A patient statement is built from the encounters the biller checked on the
 * statements page. An encounter that was overpaid (a prepayment, a copay
 * collected and then covered by insurance, ...) has nothing due, so it is never
 * listed and never checked, and the patient is billed the open encounters in
 * full. This finds those credit encounters so the statement can list them and
 * net the amount due.
 */
final class PatientCreditEncounters
{
    /**
     * The patient's encounters, other than those in $excludedEncounterIds
     * (form_encounter.id values), whose charges (services and product sales)
     * less payments and adjustments are below zero. The arithmetic is the one
     * InvoiceSummary::arGetInvoiceSummary() uses for the statement lines, so a
     * credit found here nets exactly on the statement.
     *
     * @param list<int> $excludedEncounterIds
     * @return list<array{id: int, encounter: int, date: string, stmt_count: int, balance: string}>
     */
    public static function find(int $pid, array $excludedEncounterIds): array
    {
        $binds = [$pid];
        $exclusion = '';
        if ($excludedEncounterIds !== []) {
            $exclusion = ' AND f.id NOT IN (' . implode(', ', array_fill(0, count($excludedEncounterIds), '?')) . ')';
            $binds = array_merge($binds, $excludedEncounterIds);
        }

        $rows = QueryUtils::fetchRecords(
            "SELECT f.id, f.encounter, f.date, f.stmt_count, " .
            "COALESCE((SELECT SUM(b.fee) FROM billing AS b " .
            "WHERE b.pid = f.pid AND b.encounter = f.encounter AND b.activity = 1), 0) " .
            "+ COALESCE((SELECT SUM(s.fee) FROM drug_sales AS s " .
            "WHERE s.pid = f.pid AND s.encounter = f.encounter), 0) " .
            "- COALESCE((SELECT SUM(a.pay_amount + a.adj_amount) FROM ar_activity AS a " .
            "WHERE a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL), 0) AS balance " .
            "FROM form_encounter AS f " .
            "WHERE f.pid = ?" . $exclusion . " " .
            "HAVING balance < 0 " .
            "ORDER BY f.date, f.encounter",
            $binds
        );

        $credits = [];
        foreach ($rows as $row) {
            if (
                !is_numeric($row['id'] ?? null)
                || !is_numeric($row['encounter'] ?? null)
                || !is_numeric($row['balance'] ?? null)
            ) {
                continue;
            }
            $credits[] = [
                'id' => (int) $row['id'],
                'encounter' => (int) $row['encounter'],
                'date' => is_string($row['date'] ?? null) ? $row['date'] : '',
                'stmt_count' => is_numeric($row['stmt_count'] ?? null) ? (int) $row['stmt_count'] : 0,
                'balance' => sprintf('%.2f', (float) $row['balance']),
            ];
        }

        return $credits;
    }
}
