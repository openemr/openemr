<?php

/**
 * Loads and stores a patient's income determination in `fqhc_patient_income`.
 *
 * One current row per patient (upserted on save). History/recertification
 * tracking is a later enhancement. The boundary between OpenEMR's data layer
 * and the typed IncomeDetermination value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Income;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FQHC\Fpl\IncomeDetermination;

final class PatientIncomeRepository
{
    public function findByPid(int $pid): ?IncomeDetermination
    {
        if ($pid <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT household_size, annual_income, income_unknown '
            . 'FROM fqhc_patient_income WHERE pid = ? LIMIT 1',
            [$pid],
        );

        if (!is_array($row)) {
            return null;
        }

        $size = $row['household_size'] ?? null;
        $income = $row['annual_income'] ?? null;
        $unknownRaw = $row['income_unknown'] ?? 0;

        return new IncomeDetermination(
            is_numeric($size) ? (int) $size : null,
            is_numeric($income) ? (float) $income : null,
            is_numeric($unknownRaw) && (int) $unknownRaw === 1,
        );
    }

    public function save(int $pid, IncomeDetermination $income, ?int $recordedBy): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO fqhc_patient_income '
            . '(pid, household_size, annual_income, income_unknown, effective_date, recorded_by) '
            . 'VALUES (?, ?, ?, ?, NOW(), ?) '
            . 'ON DUPLICATE KEY UPDATE '
            . 'household_size = VALUES(household_size), annual_income = VALUES(annual_income), '
            . 'income_unknown = VALUES(income_unknown), effective_date = VALUES(effective_date), '
            . 'recorded_by = VALUES(recorded_by)',
            [
                $pid,
                $income->householdSize,
                $income->annualIncome,
                $income->unknown ? 1 : 0,
                $recordedBy,
            ],
        );
    }
}
