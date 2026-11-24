<?php

/**
 * Row formatting for Reports -> Visits -> Appointments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Reports\Appointments;

use Closure;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Builds the CSV export rows and the on-screen date of birth for the
 * appointments report. The category and status lookups are passed in so the
 * formatting can be tested without a database.
 */
final readonly class AppointmentsReportFormatter
{
    /**
     * Column headings of the CSV export, in order.
     */
    public const CSV_FIELDS = ['Provider', 'Date', 'Time', 'Patient', 'Address', 'DOB', 'Type', 'Status'];

    /**
     * @param bool                    $showAddress       whether "Show Patient Address" is checked
     * @param Closure(string): string $translateCategory appointment category name to its display text
     * @param Closure(string): string $statusTitle       appointment status code to its title
     */
    public function __construct(
        private bool $showAddress,
        private Closure $translateCategory,
        private Closure $statusTitle,
    ) {
    }

    /**
     * One CSV row per appointment, keyed by the headings in CSV_FIELDS. The
     * Address key is only present when the address option is on, which leaves
     * the column out of the export.
     *
     * @param iterable<array<string, mixed>> $appointments rows from fetchAppointments()
     * @return list<array<string, string>>
     */
    public function csvRows(iterable $appointments): array
    {
        $rows = [];
        foreach ($appointments as $appointment) {
            $row = [
                'Provider' => self::str($appointment, 'ulname') . ',' . self::str($appointment, 'ufname')
                    . ' ' . self::str($appointment, 'umname'),
                'Date' => self::str($appointment, 'pc_eventDate'),
                'Time' => self::str($appointment, 'pc_startTime'),
                'Patient' => self::str($appointment, 'fname') . ' ' . self::str($appointment, 'lname'),
            ];
            if ($this->showAddress) {
                $row['Address'] = self::address($appointment);
            }
            $row['DOB'] = self::str($appointment, 'DOB');
            $row['Type'] = ($this->translateCategory)(self::str($appointment, 'pc_catname'));
            $row['Status'] = ($this->statusTitle)(self::str($appointment, 'pc_apptstatus'));
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Street address on one line: "address1, address2", or address1 alone.
     *
     * @param array<string, mixed> $appointment
     */
    public static function address(array $appointment): string
    {
        $address1 = self::str($appointment, 'address1');
        $address2 = self::str($appointment, 'address2');
        return $address2 === '' ? $address1 : $address1 . ', ' . $address2;
    }

    /**
     * The patient's date of birth in the site's date format, or an empty
     * string for a row with no patient (an available slot).
     *
     * @param array<string, mixed> $appointment
     * @param ?ParameterBag        $globals     optional globals bag for testing
     */
    public static function displayDob(array $appointment, ?ParameterBag $globals = null): string
    {
        $dob = self::str($appointment, 'DOB');
        if ($dob === '') {
            return '';
        }
        $formatted = DateFormatterUtils::oeFormatShortDate($dob, true, $globals);
        return is_string($formatted) ? $formatted : $dob;
    }

    /**
     * @param array<string, mixed> $appointment
     */
    private static function str(array $appointment, string $key): string
    {
        $value = $appointment[$key] ?? '';
        return is_scalar($value) ? (string) $value : '';
    }
}
