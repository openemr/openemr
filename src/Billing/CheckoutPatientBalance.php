<?php

/**
 * Whether checkout shows a visit's balance as due from the patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Insurance\CoverageDay;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;

/**
 * Front payment (interface/patient_file/front_payment.php) and the portal's
 * payment page (portal/portal_payment.php) decide it the same way: a visit's
 * balance is the patient's when no insurance level is in force on the visit
 * date, when the visit is closed for every level (form_encounter.
 * last_level_closed 4), or when as many levels have closed it as the patient
 * has on file.
 */
final class CheckoutPatientBalance
{
    /** @var array<string, int> the levels on file, by patient, read once per page */
    private array $levelsOnFile = [];

    /** @var array<int, list<array<mixed>>> each patient's policies as the rule reads them, read once per page */
    private array $policies = [];

    /**
     * @param mixed $pid             the patient, as the page holds it
     * @param mixed $visitDate       the visit's date (form_encounter.date); one that can't be read has no level in force
     * @param mixed $lastLevelClosed form_encounter.last_level_closed: 0 for none, 1 to 3 for that level, 4 for all
     */
    public function isDue(mixed $pid, mixed $visitDate, mixed $lastLevelClosed): bool
    {
        $levelsOnFile = $this->levelsOnFile($pid);
        $closed = is_numeric($lastLevelClosed) ? (int) $lastLevelClosed : 0;
        return $this->levelsInForce($pid, $visitDate) === 0 || $closed === 4 || $levelsOnFile === $closed;
    }

    /**
     * The insurance levels in force on the visit date, by the coverage rule
     * (EffectiveInsuranceRule). It reads the patient's policies itself, once
     * per page, rather than through InsuranceService, whose constructor fills
     * in missing uuids across three tables, since the portal's payment page
     * asks this too.
     */
    private function levelsInForce(mixed $pid, mixed $visitDate): int
    {
        $patientId = EffectiveInsuranceRule::storedId($pid);
        $visitDay = CoverageDay::fromStored($visitDate);
        if ($patientId === null || $visitDay === null) {
            return 0;
        }
        $this->policies[$patientId] ??= QueryUtils::fetchRecords(
            "SELECT `id`, `uuid`, `type`, `provider`, `date`, `date_end` FROM `insurance_data` WHERE `pid` = ?",
            [$patientId]
        );
        return count(EffectiveInsuranceRule::choose($this->policies[$patientId], $visitDay));
    }

    /**
     * The insurance levels with a company on any of the patient's policies, whatever their dates.
     */
    private function levelsOnFile(mixed $pid): int
    {
        $key = is_scalar($pid) ? (string) $pid : '';
        if (!isset($this->levelsOnFile[$key])) {
            $levels = QueryUtils::fetchSingleValue(
                "SELECT COUNT(DISTINCT type) AS levels FROM insurance_data WHERE pid = ? AND provider > 0",
                'levels',
                [$pid]
            );
            $this->levelsOnFile[$key] = is_numeric($levels) ? (int) $levels : 0;
        }
        return $this->levelsOnFile[$key];
    }
}
