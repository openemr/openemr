<?php

/**
 * Patient Finder criterion: sex, chosen from the sex list, or "not recorded".
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class SexCriterion implements PatientFinderCriterion
{
    /** The choice that matches patients with no sex recorded. */
    public const NOT_RECORDED = '__none__';

    /** patient_data.sex is a varchar(255); list option ids are at most 100 characters. */
    private const MAX_LENGTH = 100;

    /**
     * @param list<array{id: string, title: string}> $sexOptions the sex list's active entries
     */
    public function __construct(private array $sexOptions = [])
    {
    }

    public function key(): string
    {
        return 'sex';
    }

    public function label(): string
    {
        return xl('Sex');
    }

    public function operators(): array
    {
        return ['is' => new PatientFinderOperator(xl('is'), PatientFinderOperator::INPUT_SELECT)];
    }

    public function options(): array
    {
        return [...$this->sexOptions, ['id' => self::NOT_RECORDED, 'title' => xl('Not recorded')]];
    }

    public function normalize(string $operator, mixed $value): ?array
    {
        $text = is_scalar($value) ? trim((string) $value) : '';
        if ($operator !== 'is' || $text === '' || strlen($text) > self::MAX_LENGTH) {
            return null;
        }
        return [$text];
    }

    public function toWhere(string $operator, array $values): array
    {
        $sex = (string) ($values[0] ?? '');
        if ($sex === self::NOT_RECORDED) {
            return ["(patient_data.sex IS NULL OR patient_data.sex = '')", []];
        }
        return ['patient_data.sex = ?', [$sex]];
    }
}
