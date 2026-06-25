<?php

/**
 * Reads a patient's principal (primary) insurance from OpenEMR.
 *
 * Selects the most recent primary `insurance_data` row with a payer and joins
 * the insurance company for its name and type code. (UDS asks for the principal
 * insurance at the last visit of the year; "most recent primary on file" is the
 * current approximation — refining to the visit-dated coverage is a later
 * enhancement.) Returns null when the patient has no primary coverage.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

use OpenEMR\Common\Database\QueryUtils;

final class PatientPayerRepository
{
    public function findPrimaryByPid(int $pid): ?PatientPrimaryInsurance
    {
        if ($pid <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT ins.plan_name AS plan_name, ic.name AS company_name, ic.ins_type_code AS ins_type_code '
            . 'FROM insurance_data ins '
            . 'LEFT JOIN insurance_companies ic ON ic.id = ins.provider '
            . "WHERE ins.pid = ? AND ins.type = 'primary' "
            . "AND ins.provider IS NOT NULL AND ins.provider <> '' AND ins.provider <> '0' "
            . 'ORDER BY ins.date DESC LIMIT 1',
            [$pid],
        );

        if (!is_array($row)) {
            return null;
        }

        $company = $row['company_name'] ?? null;
        $plan = $row['plan_name'] ?? null;
        $planName = is_string($company) && trim($company) !== ''
            ? trim($company)
            : (is_string($plan) && trim($plan) !== '' ? trim($plan) : null);

        $codeRaw = $row['ins_type_code'] ?? null;
        $code = is_numeric($codeRaw) ? (int) $codeRaw : null;

        return new PatientPrimaryInsurance($planName, $code);
    }
}
