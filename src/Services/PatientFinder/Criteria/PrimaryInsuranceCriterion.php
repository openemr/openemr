<?php

/**
 * Patient Finder criterion: whether the patient has a current primary insurance policy.
 *
 * "Current" follows {@see \OpenEMR\Services\Insurance\EffectiveInsuranceRule}: among the primary
 * rows in force today (started on or before today, not ended before today) the latest start wins,
 * the highest id breaking a tie, and it counts only if it names an insurance company. A later
 * "Unassigned" row therefore ends the coverage of an older open policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class PrimaryInsuranceCriterion implements PatientFinderCriterion
{
    public function key(): string
    {
        return 'insurance';
    }

    public function label(): string
    {
        return xl('Primary insurance');
    }

    public function operators(): array
    {
        return [
            'has' => new PatientFinderOperator(xl('has a current policy')),
            'none' => new PatientFinderOperator(xl('has no current policy')),
        ];
    }

    public function options(): array
    {
        return [];
    }

    public function normalize(string $operator, mixed $value): ?array
    {
        return $operator === 'has' || $operator === 'none' ? [] : null;
    }

    public function toWhere(string $operator, array $values): array
    {
        $inForce = $this->inForce('ins');
        $laterInForce = $this->inForce('later');
        $covered = 'SELECT 1 FROM insurance_data ins WHERE ins.pid = patient_data.pid AND ' . $inForce
            . " AND ins.provider REGEXP '^[[:space:]]*0*[1-9][0-9]*[[:space:]]*$'"
            . ' AND NOT EXISTS (SELECT 1 FROM insurance_data later WHERE later.pid = ins.pid AND ' . $laterInForce
            . " AND (COALESCE(later.date, '') > COALESCE(ins.date, '')"
            . " OR (COALESCE(later.date, '') = COALESCE(ins.date, '') AND later.id > ins.id)))";
        return [($operator === 'has' ? 'EXISTS' : 'NOT EXISTS') . " ($covered)", []];
    }

    /**
     * The conditions for a primary policy in force today, for the insurance_data row aliased $row:
     * started on or before today (a blank start counts) and not ended before today (a blank or
     * zero end counts).
     */
    private function inForce(string $row): string
    {
        return "$row.type = 'primary' AND ($row.date IS NULL OR $row.date <= CURDATE())"
            . " AND ($row.date_end IS NULL OR $row.date_end = '0000-00-00' OR $row.date_end >= CURDATE())";
    }
}
