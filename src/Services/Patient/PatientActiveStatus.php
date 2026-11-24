<?php

/**
 * Settles a patient's active status before patient_data is written.
 *
 * The active, inactive_reason and inactive_date columns map to FHIR
 * Patient.active. Every write path (demographics form, REST, FHIR) runs its
 * changes through apply() so the same rules hold everywhere:
 *
 *  - active is stored as 1 or 0, whatever form the caller sent it in (the
 *    layout checkbox posts "Yes" or nothing);
 *  - a patient with a deceased date is always inactive, with reason
 *    "deceased" and the date of death as the inactive date unless the caller
 *    gave others;
 *  - an inactive patient always has an inactive date, today when none is
 *    given;
 *  - reactivating a patient clears the reason and date.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

use Psr\Clock\ClockInterface;

final readonly class PatientActiveStatus
{
    /** The patient_inactive_reason option used for a deceased patient. */
    public const DECEASED_REASON = 'deceased';

    /** Columns whose presence in a write means the status must be settled. */
    private const STATUS_COLUMNS = ['active', 'inactive_reason', 'inactive_date', 'deceased_date'];

    public function __construct(private ClockInterface $clock)
    {
    }

    /**
     * @param array<array-key, mixed> $changes the patient_data columns about to be written
     * @param array<array-key, mixed> $current the stored patient_data row; empty for a new patient
     * @return array<array-key, mixed> $changes with active, inactive_reason and inactive_date settled
     */
    public function apply(array $changes, array $current): array
    {
        if (array_intersect(self::STATUS_COLUMNS, array_keys($changes)) === []) {
            return $changes;
        }

        $deceasedDate = self::dateOnly(
            array_key_exists('deceased_date', $changes) ? $changes['deceased_date'] : ($current['deceased_date'] ?? null)
        );
        // A new patient, or a row from before the column existed, is active.
        $wasActive = !array_key_exists('active', $current) || self::toBool($current['active']);
        $active = array_key_exists('active', $changes) ? self::toBool($changes['active']) : $wasActive;
        if ($deceasedDate !== null) {
            $active = false;
        }

        $changes['active'] = $active ? 1 : 0;
        if ($active) {
            $changes['inactive_reason'] = '';
            $changes['inactive_date'] = null;
            return $changes;
        }

        // A patient going inactive starts from nothing; one already inactive keeps
        // what is stored unless the caller sent new values.
        $reason = array_key_exists('inactive_reason', $changes)
            ? $changes['inactive_reason']
            : ($wasActive ? '' : ($current['inactive_reason'] ?? ''));
        $reason = is_string($reason) ? trim($reason) : '';
        if ($reason === '' && $deceasedDate !== null) {
            $reason = self::DECEASED_REASON;
        }

        $inactiveDate = self::dateOnly(
            array_key_exists('inactive_date', $changes)
                ? $changes['inactive_date']
                : ($wasActive ? null : ($current['inactive_date'] ?? null))
        );

        $changes['inactive_reason'] = $reason;
        $changes['inactive_date'] = $inactiveDate ?? $deceasedDate ?? $this->clock->now()->format('Y-m-d');
        return $changes;
    }

    /**
     * Reads an active flag in any form a caller may send it: the stored 1/0, a
     * boolean, or a checkbox's posted value ("Yes", possibly translated).
     */
    public static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        if (!is_string($value)) {
            return false;
        }
        $value = strtolower(trim($value));
        return !in_array($value, ['', '0', 'false', 'no', 'off'], true);
    }

    /**
     * The YYYY-MM-DD part of a date or datetime value, or null when there is
     * no real date (empty, zero date, or not a date at all).
     */
    private static function dateOnly(mixed $value): ?string
    {
        if (!is_string($value) || preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m) !== 1) {
            return null;
        }
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }
        return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
}
