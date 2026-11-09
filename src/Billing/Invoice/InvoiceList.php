<?php

/**
 * The invoices an invoice search lists, with their balances and dunning counts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Invoice;

use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Database\QueryUtils;

final class InvoiceList
{
    /**
     * Each encounter's charges (services and product sales, as the statement
     * sums them) and its patient payments, copays (stored in ar_activity with
     * account code PCP) and adjustments. Paid-up encounters are left for
     * InvoiceListRow to drop.
     */
    private const QUERY = 'SELECT f.id, f.pid, f.encounter, f.date, '
        . 'f.last_level_billed, f.last_level_closed, f.last_stmt_date, f.stmt_count, f.in_collection, '
        . 'p.fname, p.mname, p.lname, p.pubpid, p.billing_note, '
        . '( COALESCE(( SELECT SUM(b.fee) FROM billing AS b WHERE '
        . "b.pid = f.pid AND b.encounter = f.encounter AND b.activity = 1 AND b.code_type != 'COPAY' ), 0) "
        . '+ COALESCE(( SELECT SUM(s.fee) FROM drug_sales AS s WHERE '
        . 's.pid = f.pid AND s.encounter = f.encounter ), 0) ) AS charges, '
        . '( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE '
        . "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL AND a.payer_type = 0 AND a.account_code = 'PCP')*-1 AS copays, "
        . '( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE '
        . "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL AND a.account_code != 'PCP') AS payments, "
        . '( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE '
        . 'a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL ) AS adjustments '
        . 'FROM form_encounter AS f '
        . 'JOIN patient_data AS p ON p.pid = f.pid '
        . 'WHERE %s '
        . 'ORDER BY p.lname, p.fname, p.mname, f.pid, f.encounter';

    /**
     * The encounters the condition matches (counted before the category filters,
     * as an ERA's remittances are checked against it), and the rows the category keeps.
     *
     * @param string $condition from InvoiceSearchCriteria
     * @param list<string> $binds
     * @param bool $fromEra listing an ERA's invoices: paid-up ones stay listed
     * @return array{int, list<InvoiceListRow>}
     */
    public static function find(string $condition, array $binds, InvoiceCategory $category, bool $fromEra = false, bool $onlyWithDebt = false): array
    {
        $records = QueryUtils::fetchRecords(sprintf(self::QUERY, $condition), $binds);
        $rows = [];
        // Insurance levels per patient and service date: a patient's invoices often share a date.
        $payerCounts = [];
        foreach ($records as $record) {
            $row = [];
            foreach ($record as $column => $value) {
                if (is_string($column)) {
                    $row[$column] = $value;
                }
            }
            $key = (is_scalar($row['pid'] ?? null) ? (string) $row['pid'] : '') . '|' . (is_scalar($row['date'] ?? null) ? (string) $row['date'] : '');
            $payerCount = $payerCounts[$key] ??= self::payerCount($row);
            $listed = InvoiceListRow::fromQueryRow($row, $payerCount, $category, $fromEra, $onlyWithDebt);
            if ($listed !== null) {
                $rows[] = $listed;
            }
        }
        return [count($records), $rows];
    }

    /**
     * The patient's insurance levels (primary, secondary, tertiary) on the service date.
     *
     * @param array<string, mixed> $row
     */
    private static function payerCount(array $row): int
    {
        $count = 0;
        while ($count < 3 && SLEOB::arGetPayerID($row['pid'] ?? 0, $row['date'] ?? '', $count + 1)) {
            ++$count;
        }
        return $count;
    }
}
