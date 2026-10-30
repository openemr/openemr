<?php

/**
 * PatientFinderRowEnrichEvent
 *
 * Dispatched by the Patient Finder's server-side endpoint
 * (dynamic_finder_ajax.php) once the pids for the current page of results are
 * known, but before the rows are serialised. It is the batch hook for display
 * data: a listener is handed every pid on the page (one query's worth) together
 * with the enriched columns that were registered, and returns the values to drop
 * into those cells.
 *
 * Returning values here is display-only; it does not affect filtering, sorting
 * or the result counts. Columns that must be searched or sorted server-side
 * should instead be registered as SQL columns (see {@see PatientFinderColumn}).
 * For excluding patients from results (privacy / security / information
 * blocking), add a SQL WHERE fragment through
 * {@see PatientFinderFilterEvent} so the row counts stay correct.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use Symfony\Contracts\EventDispatcher\Event;

final class PatientFinderRowEnrichEvent extends Event
{
    /**
     * Subscribe to this event to supply values for enriched finder columns.
     */
    public const EVENT_HANDLE = 'patientFinder.enrichRows';

    /**
     * @var array<int, array<string, string>> pid => (column key => display value)
     */
    private array $values = [];

    /**
     * @param array<int, int>                  $pids    The pids on the current result page.
     * @param array<int, PatientFinderColumn>  $columns The registered enriched (display-only) columns.
     */
    public function __construct(
        private readonly array $pids,
        private readonly array $columns
    ) {
    }

    /**
     * @return array<int, int>
     */
    public function getPids(): array
    {
        return $this->pids;
    }

    /**
     * The enriched columns the finder is asking listeners to populate.
     *
     * @return array<int, PatientFinderColumn>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Supply a single cell value for one patient and one column.
     */
    public function setValue(int $pid, string $columnKey, string $value): void
    {
        $this->values[$pid][$columnKey] = $value;
    }

    /**
     * Merge a batch of values at once: pid => (column key => value).
     *
     * @param array<int, array<string, string>> $values
     */
    public function addValues(array $values): void
    {
        foreach ($values as $pid => $columns) {
            foreach ($columns as $columnKey => $value) {
                $this->values[(int) $pid][$columnKey] = (string) $value;
            }
        }
    }

    /**
     * The collected values for a given pid (column key => value), or an empty
     * array when nothing was supplied for it.
     *
     * @return array<string, string>
     */
    public function getValuesForPid(int $pid): array
    {
        return $this->values[$pid] ?? [];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getAllValues(): array
    {
        return $this->values;
    }
}
