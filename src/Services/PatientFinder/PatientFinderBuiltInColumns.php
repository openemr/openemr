<?php

/**
 * PatientFinderBuiltInColumns
 *
 * The combined columns core offers in the Patient Finder besides plain
 * `patient_data` fields: alternative name orders, address lines, age, the next
 * and last appointment, and the primary insurance. An administrator turns one on
 * by adding a `ptlistcols` entry whose option id is the column's key (one of
 * {@see PatientFinderBuiltInColumn}); {@see PatientFinderColumnRegistryFactory} then seeds it
 * through {@see self::column()} instead of as a `patient_data` field.
 *
 * Every column is a {@see PatientFinderColumn::fromSql()} column, so it is searched
 * and sorted on the server like any other. Values from other tables come from
 * correlated subqueries that return at most one value per patient, so the
 * finder's row counts are unaffected. None of the expressions holds a `?`: the
 * finder re-embeds a search expression in WHERE and ORDER BY without binds.
 *
 * Appointment columns are shown in the site's date and time display formats but
 * searched and sorted on the stored date and time, so a search matches a
 * YYYY-MM-DD prefix and the sort is chronological whatever the display format.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Core\OEGlobalsBag;

final readonly class PatientFinderBuiltInColumns
{
    /** MySQL DATE_FORMAT patterns for the date_display_format global (0, 1, 2). */
    private const DATE_FORMATS = ['%Y-%m-%d', '%m/%d/%Y', '%d/%m/%Y'];

    /** MySQL DATE_FORMAT patterns for the time_display_format global (0 = 24 hr, 1 = 12 hr). */
    private const TIME_FORMATS = ['%H:%i', '%l:%i %p'];

    /**
     * Appointment statuses that are not an appointment the patient has or had:
     * "x" canceled and "%" canceled less than 24 hours ahead (apptstat list).
     */
    private const CANCELED_STATUSES = "'x', '%'";

    private string $dateTimeFormat;

    /**
     * @param int $dateDisplayFormat the date_display_format global (0 YYYY-MM-DD, 1 MM/DD/YYYY, 2 DD/MM/YYYY)
     * @param int $timeDisplayFormat the time_display_format global (0 24 hr, 1 12 hr)
     */
    public function __construct(int $dateDisplayFormat = 0, int $timeDisplayFormat = 0)
    {
        $this->dateTimeFormat = (self::DATE_FORMATS[$dateDisplayFormat] ?? self::DATE_FORMATS[0])
            . ' ' . (self::TIME_FORMATS[$timeDisplayFormat] ?? self::TIME_FORMATS[0]);
    }

    /**
     * The columns, using the site's date and time display formats.
     */
    public static function fromGlobals(OEGlobalsBag $globals): self
    {
        return new self($globals->getInt('date_display_format'), $globals->getInt('time_display_format'));
    }

    /**
     * The option ids an administrator can give a finder column list entry to show one of
     * these columns.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_map(
            static fn(PatientFinderBuiltInColumn $column): string => $column->value,
            PatientFinderBuiltInColumn::cases()
        );
    }

    /**
     * Whether a column list option id is one of these columns.
     */
    public static function has(string $key): bool
    {
        return PatientFinderBuiltInColumn::tryFrom($key) !== null;
    }

    /**
     * The ACL section and value a user needs to see a column, beyond the finder's own
     * patients/demo, or null when the finder's access is enough (or the key is not one
     * of these columns).
     *
     * @return array{string, string}|null
     */
    public static function requiredAcl(string $key): ?array
    {
        return PatientFinderBuiltInColumn::tryFrom($key)?->requiredAcl();
    }

    /**
     * The column for a built-in key, or null when the key is not one of them.
     *
     * @param string $title          the header label, already translated
     * @param string $defaultSortDir "asc" or "desc"
     */
    public function column(string $key, string $title, string $defaultSortDir = 'asc'): ?PatientFinderColumn
    {
        $column = PatientFinderBuiltInColumn::tryFrom($key);
        if ($column === null) {
            return null;
        }
        $expression = match ($column) {
            PatientFinderBuiltInColumn::NextAppointment, PatientFinderBuiltInColumn::LastAppointment
                => $this->appointmentColumn($column, $title, $defaultSortDir),
            PatientFinderBuiltInColumn::NameFirstLast => self::joined(' ', ['fname', 'lname']),
            PatientFinderBuiltInColumn::NameFirstLastMiddle => self::joined(' ', ['fname', 'mname', 'lname']),
            PatientFinderBuiltInColumn::CityState => self::joined(', ', ['city', 'state']),
            PatientFinderBuiltInColumn::AddressFull => self::address(),
            PatientFinderBuiltInColumn::AddressFullCountry => 'CONCAT_WS(\', \', NULLIF(' . self::address() . ', \'\'), '
                . self::field('country_code') . ')',
            PatientFinderBuiltInColumn::Age => 'CASE WHEN patient_data.DOB IS NULL OR patient_data.DOB = \'0000-00-00\' THEN NULL'
                . ' ELSE TIMESTAMPDIFF(YEAR, patient_data.DOB, CURDATE()) END',
            PatientFinderBuiltInColumn::InsurancePrimaryProviderName => self::primaryInsurance('ic.name'),
            PatientFinderBuiltInColumn::InsurancePrimaryPolicy => self::primaryInsurance('ins.policy_number'),
        };

        $built = $expression instanceof PatientFinderColumn
            ? $expression
            : PatientFinderColumn::fromSql($key, $title, $expression, defaultSortDir: $defaultSortDir);
        // The appointment and insurance columns search through a correlated subquery. The
        // search box would run it against every patient on each keystroke, so only their
        // own column filters search them.
        return $column->usesSubquery() ? $built->withoutGlobalSearch() : $built;
    }

    /**
     * An appointment column: shown in the site's date and time formats, searched and sorted
     * on the stored date and time.
     */
    private function appointmentColumn(
        PatientFinderBuiltInColumn $column,
        string $title,
        string $defaultSortDir
    ): PatientFinderColumn {
        $when = $this->appointment($column === PatientFinderBuiltInColumn::NextAppointment);
        return PatientFinderColumn::fromSql(
            $column->value,
            $title,
            "DATE_FORMAT($when, '{$this->dateTimeFormat}')",
            searchExpression: $when,
            defaultSortDir: $defaultSortDir
        );
    }

    /**
     * A patient_data field as NULL when blank, so CONCAT_WS skips it along with its separator.
     */
    private static function field(string $name): string
    {
        return "NULLIF(TRIM(patient_data.$name), '')";
    }

    /**
     * @param list<string> $fields patient_data field names
     */
    private static function joined(string $separator, array $fields): string
    {
        return "CONCAT_WS('$separator', " . implode(', ', array_map(self::field(...), $fields)) . ')';
    }

    /**
     * "street, street line 2, city, state  postal code", leaving out blank parts. Two spaces
     * separate the state from the postal code; with no state the code follows a comma.
     * CONCAT_WS returns '' rather than NULL when every part is NULL, so a nested one is
     * wrapped in NULLIF for the outer CONCAT_WS to skip it along with its separator.
     */
    private static function address(): string
    {
        return "CONCAT_WS(', ', " . self::field('street') . ', ' . self::field('street_line_2') . ', '
            . self::field('city') . ', ' . "NULLIF(CONCAT_WS('  ', " . self::field('state') . ', '
            . self::field('postal_code') . "), ''))";
    }

    /**
     * The soonest appointment after now, or the latest one before now, leaving out canceled
     * ones. pid is compared as text because pc_pid is a varchar; that keeps its index usable.
     *
     * A repeating appointment counts by its series' first date (pc_eventDate): its later
     * dates are worked out in PHP (fetchEvents() in appointments.inc.php), which a SQL
     * column cannot do. Kept as SQL so the list can sort and search on these columns.
     */
    private function appointment(bool $next): string
    {
        $at = "TIMESTAMP(e.pc_eventDate, COALESCE(e.pc_startTime, '00:00:00'))";
        return '(SELECT ' . ($next ? 'MIN' : 'MAX') . "($at) FROM openemr_postcalendar_events e"
            . ' WHERE e.pc_pid = CAST(patient_data.pid AS CHAR)'
            . " AND $at " . ($next ? '>' : '<') . ' NOW()'
            . ' AND e.pc_apptstatus NOT IN (' . self::CANCELED_STATUSES . '))';
    }

    /**
     * A value from the patient's primary insurance in effect today: started on or before
     * today (or undated) and not ended before today (a zero end date means no end), the most
     * recent start winning.
     */
    private static function primaryInsurance(string $value): string
    {
        return "(SELECT $value FROM insurance_data ins"
            . ' LEFT JOIN insurance_companies ic ON ic.id = ins.provider'
            . " WHERE ins.pid = patient_data.pid AND ins.type = 'primary'"
            . " AND (ins.date IS NULL OR ins.date <= CURDATE())"
            . " AND (ins.date_end IS NULL OR ins.date_end = '0000-00-00' OR ins.date_end >= CURDATE())"
            . " AND ins.provider IS NOT NULL AND ins.provider != ''"
            . ' ORDER BY ins.date DESC LIMIT 1)';
    }
}
