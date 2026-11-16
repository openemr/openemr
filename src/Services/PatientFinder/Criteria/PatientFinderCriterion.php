<?php

/**
 * One filter the Patient Finder offers above its lists (sex, age, primary insurance, ...).
 *
 * A criterion names itself, lists the operators it supports and what value each takes, checks
 * and normalises a value coming from the request, and turns a chosen operator and value into a
 * WHERE fragment with bind values. The finder's filter panel is drawn from these definitions, so
 * adding a filter means writing one class and registering it in
 * {@see PatientFinderCriteriaRegistry}; the panel needs no change.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder\Criteria;

interface PatientFinderCriterion
{
    /** The stable id used in requests, e.g. "age". */
    public function key(): string;

    /** The translated name shown in the filter panel. */
    public function label(): string;

    /**
     * The operators this criterion supports, keyed by operator id, in display order.
     *
     * @return array<string, PatientFinderOperator>
     */
    public function operators(): array;

    /**
     * The choices for operators whose input is a select list.
     *
     * @return list<array{id: string, title: string}>
     */
    public function options(): array;

    /**
     * Check a value from the request for an operator. The value is whatever the request carried
     * (a scalar, or a two-item array for a range). Returns the cleaned values, in the order
     * {@see self::toWhere()} expects them (empty for an operator that takes no value), or null
     * when the value is not usable and the criterion must be ignored.
     *
     * @return list<string|int>|null
     */
    public function normalize(string $operator, mixed $value): ?array;

    /**
     * The WHERE fragment and bind values, in placeholder order, for an operator and the values
     * {@see self::normalize()} returned. The fragment is written against patient_data and must
     * not add JOINs.
     *
     * @param list<string|int> $values
     * @return array{0: string, 1: list<string|int>}
     */
    public function toWhere(string $operator, array $values): array;
}
