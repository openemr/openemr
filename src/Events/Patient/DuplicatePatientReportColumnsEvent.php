<?php

/**
 * DuplicatePatientReportColumnsEvent is dispatched by the Duplicate Patient Management page to
 * build the column list its table and CSV export share.
 *
 * A module changes what the report shows by listening for this event. Columns that read a
 * patient_data field need nothing beyond {@see DuplicatePatientColumn::forField()}; columns that
 * need a lookup should pass a $prepare closure so the lookup runs once for the whole report rather
 * than once per row.
 *
 * A worked example -- swap core's Gender column for the SSN, and add a facility name resolved in a
 * single query:
 *
 * <code>
 * $dispatcher->addListener(
 *     DuplicatePatientReportColumnsEvent::EVENT_NAME,
 *     function (DuplicatePatientReportColumnsEvent $event): void {
 *         $event->remove('sex');
 *         $event->insertAfter('DOB', DuplicatePatientColumn::forField('ss', xl('SSN')));
 *
 *         // Both closures must capture $names BY REFERENCE. An arrow function would capture it by
 *         // value -- the empty array it holds before prepare() has run.
 *         $names = [];
 *         $event->add(new DuplicatePatientColumn(
 *             'home_facility',
 *             xl('Home Facility'),
 *             function (DuplicatePatientRow $row) use (&$names): string {
 *                 return $names[$row->field('home_facility')] ?? '';
 *             },
 *             function (array $rows) use (&$names): void {
 *                 $names = $this->facilityNamesFor($rows);
 *             },
 *         ));
 *     }
 * );
 * </code>
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Patient;

use OpenEMR\Services\Patient\DuplicatePatientColumn;
use Symfony\Contracts\EventDispatcher\Event;

class DuplicatePatientReportColumnsEvent extends Event
{
    public const EVENT_NAME = 'duplicate_patient_report_columns.filter';

    /**
     * @param list<DuplicatePatientColumn> $columns
     */
    public function __construct(private array $columns = [])
    {
    }

    /**
     * @return list<DuplicatePatientColumn>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param list<DuplicatePatientColumn> $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * Append a column, replacing any existing column with the same key.
     */
    public function add(DuplicatePatientColumn $column): void
    {
        $this->remove($column->key);
        $this->columns[] = $column;
    }

    /**
     * Place a column directly after the named one, or at the end when that key is not present.
     */
    public function insertAfter(string $key, DuplicatePatientColumn $column): void
    {
        $this->remove($column->key);

        $reordered = [];
        $placed = false;
        foreach ($this->columns as $existing) {
            $reordered[] = $existing;
            if ($existing->key === $key) {
                $reordered[] = $column;
                $placed = true;
            }
        }
        if (!$placed) {
            $reordered[] = $column;
        }

        $this->columns = $reordered;
    }

    public function remove(string $key): void
    {
        $this->columns = array_values(
            array_filter($this->columns, static fn(DuplicatePatientColumn $c): bool => $c->key !== $key)
        );
    }

    public function has(string $key): bool
    {
        foreach ($this->columns as $column) {
            if ($column->key === $key) {
                return true;
            }
        }

        return false;
    }
}
