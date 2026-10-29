<?php

/**
 * PatientFinderColumnRegistry
 *
 * Ordered collection of the columns the Patient Finder displays. Core seeds it
 * from the active `ptlistcols` list and then dispatches
 * {@see \OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent} so
 * modules may append their own columns. Both the page (dynamic_finder.php) and
 * the server-side endpoint (dynamic_finder_ajax.php) build the registry the same
 * way, so the two sides agree on the column set and ordering.
 *
 * Registration order is preserved (append-only); the keys are unique, so a later
 * registration with an existing key replaces the earlier one in place.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

class PatientFinderColumnRegistry
{
    /** @var array<string, PatientFinderColumn> keyed by column key, insertion-ordered */
    private array $columns = [];

    public function add(PatientFinderColumn $column): void
    {
        $this->columns[$column->key] = $column;
    }

    /**
     * Insert (or move) a column immediately before an existing one. When
     * $beforeKey is unknown the column is appended. Lets a module place its
     * column ahead of a core column rather than only at the end.
     */
    public function addBefore(string $beforeKey, PatientFinderColumn $column): void
    {
        if (!isset($this->columns[$beforeKey])) {
            $this->add($column);
            return;
        }

        $rebuilt = [];
        foreach ($this->columns as $key => $existing) {
            if ($key === $column->key) {
                // Drop any existing copy so the column lands at the new position.
                continue;
            }
            if ($key === $beforeKey) {
                $rebuilt[$column->key] = $column;
            }
            $rebuilt[$key] = $existing;
        }
        $this->columns = $rebuilt;
    }

    /**
     * Remove a column by key. Lets a module hide a core column (e.g. SSN) from
     * the finder. No-op when the key is absent.
     */
    public function remove(string $key): void
    {
        unset($this->columns[$key]);
    }

    /**
     * Drop every column. Intended for a module that wants to define the finder's
     * column set from scratch rather than build on the core defaults.
     */
    public function clear(): void
    {
        $this->columns = [];
    }

    public function has(string $key): bool
    {
        return isset($this->columns[$key]);
    }

    public function get(string $key): ?PatientFinderColumn
    {
        return $this->columns[$key] ?? null;
    }

    /**
     * @return array<int, PatientFinderColumn> columns in registration order
     */
    public function all(): array
    {
        return array_values($this->columns);
    }

    /**
     * @return array<int, string> the column keys in registration order
     */
    public function keys(): array
    {
        return array_keys($this->columns);
    }

    public function count(): int
    {
        return count($this->columns);
    }
}
