<?php

/**
 * Patient Finder criterion: when the patient was last seen, by encounter date.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class LastVisitCriterion implements PatientFinderCriterion
{
    private const MAX_DAYS = 3650;

    public function key(): string
    {
        return 'last_visit';
    }

    public function label(): string
    {
        return xl('Last visit');
    }

    public function operators(): array
    {
        return [
            'within' => new PatientFinderOperator(xl('within the last'), PatientFinderInput::Number, 1, self::MAX_DAYS, xl('days')),
            'older' => new PatientFinderOperator(xl('more than'), PatientFinderInput::Number, 1, self::MAX_DAYS, xl('days ago')),
            'never' => new PatientFinderOperator(xl('never')),
        ];
    }

    public function options(): array
    {
        return [];
    }

    public function normalize(string $operator, mixed $value): ?array
    {
        if ($operator === 'never') {
            return [];
        }
        if ($operator !== 'within' && $operator !== 'older') {
            return null;
        }
        $days = (new PatientFinderOperator('', PatientFinderInput::Number, 1, self::MAX_DAYS))->wholeNumber($value);
        return $days === null ? null : [$days];
    }

    public function toWhere(string $operator, array $values): array
    {
        $days = is_int($values[0] ?? null) ? $values[0] : 0;
        // A visit is one that has happened: an encounter dated later than today is not counted.
        $any = 'SELECT 1 FROM form_encounter fe WHERE fe.pid = patient_data.pid AND fe.date < DATE_ADD(CURDATE(), INTERVAL 1 DAY)';
        $recent = $any . ' AND fe.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)';
        return match ($operator) {
            'within' => ["EXISTS ($recent)", [$days]],
            // Seen before, but not within the period.
            'older' => ["EXISTS ($any) AND NOT EXISTS ($recent)", [$days]],
            default => ["NOT EXISTS ($any)", []],
        };
    }
}
