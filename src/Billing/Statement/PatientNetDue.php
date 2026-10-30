<?php

/**
 * Patients whose open invoices leave nothing due once their credits are netted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

/**
 * A Due Pt statement run nets each patient's checked invoices against the
 * credits on their other encounters (PatientCreditEncounters) and sends no
 * statement when nothing is left due. The statements page lists invoice by
 * invoice, so without this it shows (and pre-checks) invoices the run will
 * skip.
 */
final class PatientNetDue
{
    /**
     * The patients, among those given, whose invoices net to zero or less
     * against the credits on their other encounters. Balances come from the
     * statement's own arithmetic (product sales included), not the search's.
     * Two queries in all, however many patients a Due Pt search lists.
     *
     * @param array<int, list<int>> $encounterIds pid => form_encounter.id of the invoices a run would include
     * @return array<int, string> pid => net amount due (zero or negative), as "%.2f"
     */
    public static function nothingDue(array $encounterIds): array
    {
        if ($encounterIds === []) {
            return [];
        }
        $allIds = array_merge(...array_values($encounterIds));
        $open = PatientCreditEncounters::balancesByPatient($allIds);
        $credits = PatientCreditEncounters::creditsByPatient(array_keys($encounterIds), $allIds);

        $nothingDue = [];
        foreach ($encounterIds as $pid => $ids) {
            $balances = array_intersect_key($open[$pid] ?? [], array_flip($ids));
            $net = self::net(array_values($balances), $credits[$pid] ?? []);
            if ($net <= 0.0) {
                $nothingDue[$pid] = sprintf('%.2f', $net);
            }
        }
        return $nothingDue;
    }

    /**
     * Open balances plus credits (negative balances), rounded to cents.
     *
     * @param list<float> $openBalances
     * @param list<float> $credits
     */
    public static function net(array $openBalances, array $credits): float
    {
        return round(array_sum($openBalances) + array_sum($credits), 2);
    }
}
