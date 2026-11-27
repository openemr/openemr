<?php

/**
 * Loads a patient's encounters and billing totals in a fixed number of
 * queries, however many encounters the patient has.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\PatientBalance;

use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Insurance\CoverageDay;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;

/**
 * get_patient_balance() used to run getEffectiveInsurances() and three or
 * four SUM queries for every encounter, so a patient with 345 encounters cost
 * about 2,000 queries per balance. This runs five: the encounters, the
 * patient's insurance_data rows, and one GROUP BY encounter query each for
 * billing, ar_activity and drug_sales.
 */
final class PatientBalanceLoader
{
    /**
     * @param int      $pid       the patient
     * @param int|null $encounter only this encounter, or null for all of them
     */
    public function load(int $pid, ?int $encounter = null): PatientBalanceLedger
    {
        $filter = $encounter === null ? '' : ' AND `encounter` = ?';
        $binds = $encounter === null ? [$pid] : [$pid, $encounter];

        $encounterRows = QueryUtils::fetchRecords(
            "SELECT `encounter`, `date`, `last_level_closed`, `in_collection`"
                . " FROM `form_encounter` WHERE `pid` = ?" . $filter,
            $binds
        );
        if ($encounterRows === []) {
            return new PatientBalanceLedger([]);
        }

        $insuranceRows = QueryUtils::fetchRecords(
            "SELECT * FROM `insurance_data` WHERE `pid` = ?",
            [$pid]
        );
        $billing = self::byEncounter(QueryUtils::fetchRecords(
            "SELECT `encounter`, SUM(`fee`) AS `charges`,"
                . " SUM(CASE WHEN `code_type` = 'copay' THEN `fee` END) AS `copay_charges`"
                . " FROM `billing` WHERE `pid` = ? AND `activity` = 1" . $filter
                . " GROUP BY `encounter`",
            $binds
        ));
        $activity = self::byEncounter(QueryUtils::fetchRecords(
            "SELECT `encounter`, SUM(`pay_amount`) AS `payments`, SUM(`adj_amount`) AS `adjustments`,"
                . " SUM(CASE WHEN `payer_type` = 0 THEN `pay_amount` END) AS `patient_payments`"
                . " FROM `ar_activity` WHERE `deleted` IS NULL AND `pid` = ?" . $filter
                . " GROUP BY `encounter`",
            $binds
        ));
        $drugSales = self::byEncounter(QueryUtils::fetchRecords(
            "SELECT `encounter`, SUM(`fee`) AS `drug_sales`"
                . " FROM `drug_sales` WHERE `pid` = ?" . $filter
                . " GROUP BY `encounter`",
            $binds
        ));

        /** @var array<string, int> $insuranceCountByDay */
        $insuranceCountByDay = [];
        $encounters = [];
        foreach ($encounterRows as $row) {
            $id = self::toInt($row['encounter'] ?? null);
            $day = CoverageDay::fromStored($row['date'] ?? null);
            $insuranceCount = 0;
            if ($day !== null) {
                $key = $day->format('Y-m-d');
                $insuranceCount = $insuranceCountByDay[$key]
                    ??= self::insuranceCount($insuranceRows, $day);
            }
            $encounters[] = new EncounterBalance(
                lastLevelClosed: self::toInt($row['last_level_closed'] ?? null),
                insuranceCount: $insuranceCount,
                inCollection: self::toInt($row['in_collection'] ?? null) === 1,
                charges: self::amount($billing[$id]['charges'] ?? null),
                copayCharges: self::amount($billing[$id]['copay_charges'] ?? null),
                drugSales: self::amount($drugSales[$id]['drug_sales'] ?? null),
                payments: self::amount($activity[$id]['payments'] ?? null),
                adjustments: self::amount($activity[$id]['adjustments'] ?? null),
                patientPayments: self::amount($activity[$id]['patient_payments'] ?? null),
            );
        }
        return new PatientBalanceLedger($encounters);
    }

    /**
     * How many levels are in force on the day, counted from primary and
     * stopping at the first level with no policy. This is count() of what
     * getEffectiveInsurances() returns, without its company-name query.
     *
     * @param list<array<mixed>> $insuranceRows
     */
    private static function insuranceCount(array $insuranceRows, DateTimeImmutable $day): int
    {
        $levels = [];
        foreach (EffectiveInsuranceRule::choose($insuranceRows, $day) as $policy) {
            $levels[$policy->level->value] = true;
        }
        $count = 0;
        foreach (InsuranceLevel::cases() as $level) {
            if (!isset($levels[$level->value])) {
                break;
            }
            $count++;
        }
        return $count;
    }

    /**
     * @param list<array<mixed>> $rows
     * @return array<int, array<mixed>>
     */
    private static function byEncounter(array $rows): array
    {
        $byEncounter = [];
        foreach ($rows as $row) {
            $byEncounter[self::toInt($row['encounter'] ?? null)] = $row;
        }
        return $byEncounter;
    }

    private static function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * A SUM column: NULL when no rows matched, which counts as zero.
     */
    private static function amount(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
