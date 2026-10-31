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
     * An encounter's balance: charges (services and product sales) less
     * payments and adjustments, the arithmetic InvoiceSummary::arGetInvoiceSummary()
     * uses for the statement lines.
     */
    private const BALANCE_SQL =
        "COALESCE((SELECT SUM(b.fee) FROM billing AS b " .
        "WHERE b.pid = f.pid AND b.encounter = f.encounter AND b.activity = 1), 0) " .
        "+ COALESCE((SELECT SUM(s.fee) FROM drug_sales AS s " .
        "WHERE s.pid = f.pid AND s.encounter = f.encounter), 0) " .
        "- COALESCE((SELECT SUM(a.pay_amount + a.adj_amount) FROM ar_activity AS a " .
        "WHERE a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL), 0)";

    /**
     * The statement balance of each encounter given (form_encounter.id
     * values), grouped by patient: pid => [form_encounter.id => balance].
     * One query for any number of encounters.
     *
     * @param list<int> $encounterIds
     * @return array<int, array<int, float>>
     */
    public static function balancesByPatient(array $encounterIds): array
    {
        if ($encounterIds === []) {
            return [];
        }
        $rows = QueryUtils::fetchRecords(
            "SELECT f.id, f.pid, " . self::BALANCE_SQL . " AS balance FROM form_encounter AS f " .
            "WHERE f.id IN (" . self::placeholders($encounterIds) . ")",
            $encounterIds
        );
        $balances = [];
        foreach ($rows as $row) {
            if (is_numeric($row['id'] ?? null) && is_numeric($row['pid'] ?? null) && is_numeric($row['balance'] ?? null)) {
                $balances[(int) $row['pid']][(int) $row['id']] = (float) $row['balance'];
            }
        }
        return $balances;
    }

    /**
     * The credits (negative balances) on the given patients' encounters, other
     * than those in $excludedEncounterIds, grouped by patient: pid => list of
     * balances. One query for any number of patients; find() is the per-patient
     * version with the encounter details the statement lines need.
     *
     * @param list<int> $pids
     * @param list<int> $excludedEncounterIds
     * @return array<int, list<float>>
     */
    public static function creditsByPatient(array $pids, array $excludedEncounterIds): array
    {
        if ($pids === []) {
            return [];
        }
        $exclusion = $excludedEncounterIds === []
            ? ''
            : ' AND f.id NOT IN (' . self::placeholders($excludedEncounterIds) . ')';
        $rows = QueryUtils::fetchRecords(
            "SELECT f.pid, " . self::BALANCE_SQL . " AS balance FROM form_encounter AS f " .
            "WHERE f.pid IN (" . self::placeholders($pids) . ")" . $exclusion . " " .
            "HAVING balance < 0",
            array_merge($pids, $excludedEncounterIds)
        );
        $credits = [];
        foreach ($rows as $row) {
            if (is_numeric($row['pid'] ?? null) && is_numeric($row['balance'] ?? null)) {
                $credits[(int) $row['pid']][] = (float) $row['balance'];
            }
        }
        return $credits;
    }

    /**
     * @param non-empty-list<int> $values
     */
    private static function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }

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
            "SELECT f.id, f.encounter, f.date, f.stmt_count, " . self::BALANCE_SQL . " AS balance " .
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
