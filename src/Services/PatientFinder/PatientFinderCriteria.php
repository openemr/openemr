<?php

/**
 * The filters chosen in the Patient Finder's filter panel (sex, age, primary insurance, last
 * visit, ...) that narrow the lists before any column search applies.
 *
 * They arrive as one `criteria` request parameter holding a JSON list of
 * `{"key": ..., "operator": ..., "value": ...}`. Anything unrecognised is dropped (an unknown
 * key or operator, a value the criterion refuses, a second filter on the same key), so a
 * malformed request means "no filter" rather than an error. {@see self::toWhere()} turns the
 * filters into one WHERE fragment with bind placeholders, written against patient_data and using
 * EXISTS subqueries, so it adds no JOINs and cannot multiply rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use JsonException;
use OpenEMR\Common\Utils\Json;
use OpenEMR\Services\PatientFinder\Criteria\PatientFinderCriteriaRegistry;
use OpenEMR\Services\PatientFinder\Criteria\PatientFinderCriterion;

final readonly class PatientFinderCriteria
{
    /** More filters than there are criteria makes no sense; this also bounds the work for a bad request. */
    private const MAX_FILTERS = 12;

    /**
     * @param list<array{criterion: PatientFinderCriterion, operator: string, values: list<string|int>}> $filters
     */
    public function __construct(public array $filters = [])
    {
    }

    /**
     * @param array<mixed> $get the request parameters
     */
    public static function fromArray(array $get, ?PatientFinderCriteriaRegistry $registry = null): self
    {
        $registry ??= PatientFinderCriteriaRegistry::standard();
        $text = PatientFinderValue::string($get['criteria'] ?? null);
        if ($text === '') {
            return new self();
        }
        try {
            $decoded = Json::decode($text);
        } catch (JsonException) {
            return new self();
        }
        if (!is_array($decoded)) {
            return new self();
        }

        $filters = [];
        foreach (array_slice(array_values($decoded), 0, self::MAX_FILTERS) as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $criterion = $registry->find(PatientFinderValue::string($entry['key'] ?? null));
            $operator = PatientFinderValue::string($entry['operator'] ?? null);
            if ($criterion === null) {
                continue;
            }
            foreach ($filters as $chosen) {
                if ($chosen['criterion']->key() === $criterion->key()) {
                    continue 2;
                }
            }
            $values = $criterion->normalize($operator, $entry['value'] ?? null);
            if ($values !== null) {
                $filters[] = ['criterion' => $criterion, 'operator' => $operator, 'values' => $values];
            }
        }
        return new self($filters);
    }

    public function isEmpty(): bool
    {
        return $this->filters === [];
    }

    /**
     * The WHERE fragment for these filters and its bind values, in placeholder order. An empty
     * fragment means no filter is set.
     *
     * @return array{0: string, 1: list<string|int>}
     */
    public function toWhere(): array
    {
        $parts = [];
        $binds = [];
        foreach ($this->filters as $filter) {
            [$where, $filterBinds] = $filter['criterion']->toWhere($filter['operator'], $filter['values']);
            $parts[] = '(' . $where . ')';
            $binds = [...$binds, ...$filterBinds];
        }
        return [implode(' AND ', $parts), $binds];
    }
}
