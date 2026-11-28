<?php

/**
 * The criteria the Patient Finder offers, by key. {@see self::definitions()} describes them for
 * the filter panel; {@see \OpenEMR\Services\PatientFinder\PatientFinderCriteria} looks criteria up
 * here when it reads a request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

final readonly class PatientFinderCriteriaRegistry
{
    /** @var array<string, PatientFinderCriterion> */
    private array $criteria;

    /**
     * @param list<PatientFinderCriterion> $criteria in the order the filter panel offers them
     */
    public function __construct(array $criteria)
    {
        $byKey = [];
        foreach ($criteria as $criterion) {
            $byKey[$criterion->key()] = $criterion;
        }
        $this->criteria = $byKey;
    }

    /**
     * The criteria every installation has.
     *
     * @param list<array{id: string, title: string}> $sexOptions the sex list's active entries,
     *     needed only to draw the panel, not to read a request
     */
    public static function standard(array $sexOptions = []): self
    {
        return new self([
            new SexCriterion($sexOptions),
            new AgeCriterion(),
            new PrimaryInsuranceCriterion(),
            new LastVisitCriterion(),
        ]);
    }

    public function find(string $key): ?PatientFinderCriterion
    {
        return $this->criteria[$key] ?? null;
    }

    /**
     * What the filter panel needs to draw each criterion and its operators.
     *
     * @return list<array{key: string, label: string, options: list<array{id: string, title: string}>, operators: list<array{key: string, label: string, input: string, min: int, max: int, unit: string}>}>
     */
    public function definitions(): array
    {
        $definitions = [];
        foreach ($this->criteria as $criterion) {
            $operators = [];
            foreach ($criterion->operators() as $key => $operator) {
                $operators[] = ['key' => $key] + $operator->toArray();
            }
            $definitions[] = [
                'key' => $criterion->key(),
                'label' => $criterion->label(),
                'options' => $criterion->options(),
                'operators' => $operators,
            ];
        }
        return $definitions;
    }
}
