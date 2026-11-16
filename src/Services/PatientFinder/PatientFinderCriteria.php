<?php

/**
 * The filter criteria of the Patient Finder: the drop-down choices above the list (sex, age range,
 * primary insurance, last visit) that narrow it before any column search applies.
 *
 * They arrive as `criteria_*` request parameters. Anything unrecognised is dropped, so a
 * malformed request means "no criterion" rather than an error. {@see self::toWhere()} turns the
 * criteria into one WHERE fragment with bind placeholders, written against patient_data and using
 * EXISTS subqueries, so it adds no JOINs and cannot multiply rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final readonly class PatientFinderCriteria
{
    /** The sex criterion that matches patients with no sex recorded. */
    public const SEX_NOT_RECORDED = '__none__';

    public const INSURANCE_HAS = 'has';
    public const INSURANCE_NONE = 'none';

    public const VISIT_WITHIN = 'within';
    public const VISIT_OLDER = 'older';
    public const VISIT_NEVER = 'never';

    private const MAX_AGE = 150;
    private const MAX_VISIT_DAYS = 3650;
    private const DEFAULT_VISIT_DAYS = 365;
    private const MAX_SEX_LENGTH = 31;

    /**
     * @param string $sex a sex list option id, SEX_NOT_RECORDED, or '' for any
     * @param string $insurance INSURANCE_HAS, INSURANCE_NONE or '' for any: a current primary policy
     * @param string $visit VISIT_WITHIN, VISIT_OLDER, VISIT_NEVER or '' for any: encounters in the last $visitDays days
     */
    public function __construct(
        public string $sex = '',
        public ?int $ageMin = null,
        public ?int $ageMax = null,
        public string $insurance = '',
        public string $visit = '',
        public int $visitDays = self::DEFAULT_VISIT_DAYS
    ) {
    }

    /**
     * @param array<mixed> $get the request parameters
     */
    public static function fromArray(array $get): self
    {
        $ageMin = self::boundedInt($get['criteria_age_min'] ?? null, 0, self::MAX_AGE);
        $ageMax = self::boundedInt($get['criteria_age_max'] ?? null, 0, self::MAX_AGE);
        if ($ageMin !== null && $ageMax !== null && $ageMin > $ageMax) {
            [$ageMin, $ageMax] = [$ageMax, $ageMin];
        }

        return new self(
            substr(trim(PatientFinderValue::string($get['criteria_sex'] ?? null)), 0, self::MAX_SEX_LENGTH),
            $ageMin,
            $ageMax,
            self::oneOf($get['criteria_insurance'] ?? null, [self::INSURANCE_HAS, self::INSURANCE_NONE]),
            self::oneOf($get['criteria_visit'] ?? null, [self::VISIT_WITHIN, self::VISIT_OLDER, self::VISIT_NEVER]),
            self::boundedInt($get['criteria_visit_days'] ?? null, 1, self::MAX_VISIT_DAYS) ?? self::DEFAULT_VISIT_DAYS
        );
    }

    public function isEmpty(): bool
    {
        return $this->sex === '' && $this->ageMin === null && $this->ageMax === null
            && $this->insurance === '' && $this->visit === '';
    }

    /**
     * The WHERE fragment for these criteria and its bind values, in placeholder order. An empty
     * fragment means no criterion is set.
     *
     * @return array{0: string, 1: list<string|int>}
     */
    public function toWhere(): array
    {
        $parts = [];
        $binds = [];

        if ($this->sex === self::SEX_NOT_RECORDED) {
            $parts[] = "(patient_data.sex IS NULL OR patient_data.sex = '')";
        } elseif ($this->sex !== '') {
            $parts[] = 'patient_data.sex = ?';
            $binds[] = $this->sex;
        }

        // Age as a range on DOB, so an index on it still applies. Patients without a usable
        // date of birth never match an age bound.
        if ($this->ageMin !== null || $this->ageMax !== null) {
            $parts[] = "(patient_data.DOB IS NOT NULL AND patient_data.DOB > '1000-01-01')";
        }
        if ($this->ageMin !== null) {
            $parts[] = 'patient_data.DOB <= DATE_SUB(CURDATE(), INTERVAL ? YEAR)';
            $binds[] = $this->ageMin;
        }
        if ($this->ageMax !== null) {
            $parts[] = 'patient_data.DOB > DATE_SUB(CURDATE(), INTERVAL ? YEAR)';
            $binds[] = $this->ageMax + 1;
        }

        if ($this->insurance !== '') {
            $current = "SELECT 1 FROM insurance_data ins WHERE ins.pid = patient_data.pid AND ins.type = 'primary'"
                . " AND ins.provider IS NOT NULL AND ins.provider <> ''"
                . " AND (ins.date_end IS NULL OR ins.date_end = '0000-00-00' OR ins.date_end >= CURDATE())";
            $parts[] = ($this->insurance === self::INSURANCE_HAS ? 'EXISTS' : 'NOT EXISTS') . " ($current)";
        }

        if ($this->visit !== '') {
            $any = 'SELECT 1 FROM form_encounter fe WHERE fe.pid = patient_data.pid';
            $recent = $any . ' AND fe.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)';
            switch ($this->visit) {
                case self::VISIT_WITHIN:
                    $parts[] = "EXISTS ($recent)";
                    $binds[] = $this->visitDays;
                    break;
                case self::VISIT_OLDER:
                    // Seen before, but not within the period.
                    $parts[] = "(EXISTS ($any) AND NOT EXISTS ($recent))";
                    $binds[] = $this->visitDays;
                    break;
                default:
                    $parts[] = "NOT EXISTS ($any)";
            }
        }

        return [implode(' AND ', $parts), $binds];
    }

    private static function boundedInt(mixed $value, int $min, int $max): ?int
    {
        $text = trim(PatientFinderValue::string($value));
        if ($text === '' || !ctype_digit($text)) {
            return null;
        }
        $number = (int) $text;
        return $number >= $min && $number <= $max ? $number : null;
    }

    /**
     * @param list<string> $allowed
     */
    private static function oneOf(mixed $value, array $allowed): string
    {
        $text = PatientFinderValue::string($value);
        return in_array($text, $allowed, true) ? $text : '';
    }
}
