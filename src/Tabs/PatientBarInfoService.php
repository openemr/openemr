<?php

/**
 * Reads what the patient bar shows under the patient's name.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Psr\Clock\ClockInterface;

final readonly class PatientBarInfoService
{
    public function __construct(
        private PatientService $patientService,
        private ClockInterface $clock,
    ) {
    }

    /**
     * The patient's ID, Sex, date of birth and age (at death, for a patient who
     * has died); null when there is no such patient.
     */
    public function forPid(int $pid): ?PatientBarInfo
    {
        $row = QueryUtils::querySingleRow(
            "SELECT p.DOB, p.deceased_date, lo.title AS sex_title
               FROM patient_data AS p
               LEFT JOIN list_options AS lo
                 ON lo.list_id = 'sex' AND lo.option_id = p.sex AND lo.activity = 1
              WHERE p.pid = ?",
            [$pid]
        );
        if (!is_array($row)) {
            return null;
        }

        $dob = self::date($row['DOB'] ?? null);
        $deceasedOn = self::date($row['deceased_date'] ?? null);
        $diedAlready = $deceasedOn !== '' && $deceasedOn <= $this->clock->now()->format('Y-m-d');
        $age = '';
        if ($dob !== '') {
            $age = $diedAlready
                ? $this->patientService->getPatientAge($dob, str_replace('-', '', $deceasedOn)) // wants YYYYMMDD
                : $this->patientService->getPatientAgeDisplay($dob);
        }

        $sexTitle = $row['sex_title'] ?? null;
        return new PatientBarInfo(
            pid: $pid,
            sex: is_string($sexTitle) && $sexTitle !== '' ? xl_list_label($sexTitle) : '',
            dob: $dob === '' ? '' : self::shortDate($dob),
            age: is_int($age) || is_string($age) ? (string) $age : '',
            deceased: $diedAlready,
        );
    }

    /**
     * A Y-m-d date from a DATE or DATETIME column; '' for an empty or zero date.
     */
    private static function date(mixed $value): string
    {
        if (!is_string($value) || $value === '' || str_starts_with($value, '0000-00-00')) {
            return '';
        }
        return substr($value, 0, 10);
    }

    private static function shortDate(string $ymd): string
    {
        $formatted = DateFormatterUtils::oeFormatShortDate($ymd);
        return is_string($formatted) ? $formatted : $ymd;
    }
}
