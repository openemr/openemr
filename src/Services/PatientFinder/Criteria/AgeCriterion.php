<?php

/**
 * Patient Finder criterion: age in whole years today, between two ages, at least or at most one.
 *
 * Age is a range on the date of birth, so an index on it still applies. A patient without a
 * usable date of birth (blank, zero or in the future) never matches.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class AgeCriterion implements PatientFinderCriterion
{
    private const MAX_AGE = 150;

    public function key(): string
    {
        return 'age';
    }

    public function label(): string
    {
        return xl('Age');
    }

    public function operators(): array
    {
        return [
            'between' => new PatientFinderOperator(xl('between'), PatientFinderInput::Range, 0, self::MAX_AGE),
            'at_least' => new PatientFinderOperator(xl('at least'), PatientFinderInput::Number, 0, self::MAX_AGE),
            'at_most' => new PatientFinderOperator(xl('at most'), PatientFinderInput::Number, 0, self::MAX_AGE),
        ];
    }

    public function options(): array
    {
        return [];
    }

    public function normalize(string $operator, mixed $value): ?array
    {
        $rule = $this->rule($operator);
        if ($rule === null) {
            return null;
        }
        if ($operator !== 'between') {
            $age = $rule->wholeNumber($value);
            return $age === null ? null : [$age];
        }
        if (!is_array($value) || count($value) !== 2) {
            return null;
        }
        $low = $rule->wholeNumber(array_values($value)[0]);
        $high = $rule->wholeNumber(array_values($value)[1]);
        if ($low === null || $high === null) {
            return null;
        }
        return $low <= $high ? [$low, $high] : [$high, $low];
    }

    /**
     * The input rules of an operator without its translated label, so reading a request needs no
     * translation lookups.
     */
    private function rule(string $operator): ?PatientFinderOperator
    {
        return match ($operator) {
            'between' => new PatientFinderOperator('', PatientFinderInput::Range, 0, self::MAX_AGE),
            'at_least', 'at_most' => new PatientFinderOperator('', PatientFinderInput::Number, 0, self::MAX_AGE),
            default => null,
        };
    }

    public function toWhere(string $operator, array $values): array
    {
        $parts = ["patient_data.DOB IS NOT NULL AND patient_data.DOB > '1000-01-01' AND patient_data.DOB <= CURDATE()"];
        $binds = [];
        $atLeast = match ($operator) {
            'between', 'at_least' => $values[0] ?? null,
            default => null,
        };
        $maximum = match ($operator) {
            'between' => $values[1] ?? null,
            'at_most' => $values[0] ?? null,
            default => null,
        };
        if ($atLeast !== null) {
            // Aged N or more: born on or before the date N years ago.
            $parts[] = 'patient_data.DOB <= DATE_SUB(CURDATE(), INTERVAL ? YEAR)';
            $binds[] = is_int($atLeast) ? $atLeast : 0;
        }
        if ($maximum !== null) {
            // Aged N or less: born after the date N + 1 years ago.
            $parts[] = 'patient_data.DOB > DATE_SUB(CURDATE(), INTERVAL ? YEAR)';
            $binds[] = (is_int($maximum) ? $maximum : 0) + 1;
        }
        return [implode(' AND ', $parts), $binds];
    }
}
