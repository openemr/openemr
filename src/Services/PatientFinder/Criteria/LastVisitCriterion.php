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
            'within' => new PatientFinderOperator(xl('within the last'), PatientFinderOperator::INPUT_NUMBER, 1, self::MAX_DAYS, xl('days')),
            'older' => new PatientFinderOperator(xl('more than'), PatientFinderOperator::INPUT_NUMBER, 1, self::MAX_DAYS, xl('days ago')),
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
        $days = (new PatientFinderOperator('', PatientFinderOperator::INPUT_NUMBER, 1, self::MAX_DAYS))->wholeNumber($value);
        return $days === null ? null : [$days];
    }

    public function toWhere(string $operator, array $values): array
    {
        $any = 'SELECT 1 FROM form_encounter fe WHERE fe.pid = patient_data.pid';
        $recent = $any . ' AND fe.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)';
        return match ($operator) {
            'within' => ["EXISTS ($recent)", [(int) ($values[0] ?? 0)]],
            // Seen before, but not within the period.
            'older' => ["EXISTS ($any) AND NOT EXISTS ($recent)", [(int) ($values[0] ?? 0)]],
            default => ["NOT EXISTS ($any)", []],
        };
    }
}
