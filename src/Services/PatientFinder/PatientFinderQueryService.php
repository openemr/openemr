<?php

/**
 * PatientFinderQueryService
 *
 * Orchestrates the Patient Finder's server-side endpoint, replacing the body of
 * dynamic_finder_ajax.php. It:
 *
 *   1. builds the column registry — seeds the core `ptlistcols` columns, then
 *      dispatches PatientFinderColumnRegistrationEvent so modules can add,
 *      remove or reorder columns;
 *   2. resolves the client's (possibly ColReorder-ed) column order against the
 *      registry into the active column list;
 *   3. dispatches the existing PatientFinderFilterEvent to obtain the custom
 *      WHERE BoundFilter (privacy / security / information-blocking exclusion);
 *   4. delegates SQL construction to PatientFinderQueryBuilder and runs the
 *      resulting plan;
 *   5. dispatches PatientFinderRowEnrichEvent with the current page's pids to
 *      populate display-only (enriched) columns, then assembles the DataTables
 *      response array.
 *
 * The database accessors, cell formatters and event dispatcher are injected
 * (defaulting to OpenEMR core), so the resolution and row-assembly logic is
 * unit-testable without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Events\BoundFilter;
use OpenEMR\Events\PatientFinder\ColumnFilter;
use OpenEMR\Events\PatientFinder\PatientFinderFilterEvent;
use OpenEMR\Events\PatientFinder\PatientFinderRowEnrichEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class PatientFinderQueryService
{
    private PatientFinderQueryBuilder $builder;

    private PatientFinderColumnRegistryFactory $registryFactory;

    /** @var callable(string, array<int, string|int>): array<int, array<string, mixed>> */
    private $fetchRecords;

    /** @var callable(string, array<int, string|int>): (array<string, mixed>|false|null) */
    private $sqlQuery;

    /** @var callable(mixed): string */
    private $attr;

    /** @var callable(array<string, mixed>, mixed): string */
    private $plaintext;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        ?PatientFinderQueryBuilder $builder = null,
        ?callable $fetchRecords = null,
        ?callable $sqlQuery = null,
        ?callable $attr = null,
        ?callable $plaintext = null,
        ?PatientFinderColumnRegistryFactory $registryFactory = null
    ) {
        $this->builder = $builder ?? new PatientFinderQueryBuilder();
        $this->fetchRecords = $fetchRecords
            ?? static fn(string $sql, array $binds): array => QueryUtils::fetchRecords($sql, $binds);
        $this->sqlQuery = $sqlQuery
            ?? static fn(string $sql, array $binds) => sqlQuery($sql, $binds);
        $this->attr = $attr ?? static fn(mixed $value): string => attr((string) $value);
        $this->plaintext = $plaintext
            ?? static fn(array $fieldInfo, mixed $value): string => generate_plaintext_field($fieldInfo, $value);
        // Share the registry builder with the finder page so both sides agree on
        // the column set; default to one backed by this service's fetcher.
        $this->registryFactory = $registryFactory
            ?? new PatientFinderColumnRegistryFactory($dispatcher, $this->fetchRecords);
    }

    /**
     * Run the finder for a raw GET array and return the DataTables response
     * (sEcho / iTotalRecords / iTotalDisplayRecords / aaData), ready to be
     * JSON-encoded by the caller.
     *
     * @param array<string, mixed> $get
     * @return array<string, mixed>
     */
    public function handle(array $get): array
    {
        $registry = $this->buildColumnRegistry();
        $request = PatientFinderRequest::fromArray($get);
        $activeColumns = $this->resolveActiveColumns($registry, $request);
        $globalSearchColumns = $this->buildGlobalSearchColumns($request);
        $filter = $this->dispatchFilter($activeColumns, $request);

        $plan = $this->builder->build($activeColumns, $request, $filter, $globalSearchColumns);

        $iTotal = $this->count($plan->totalCountSql, $plan->totalCountBinds);
        $iFiltered = $this->count($plan->filteredCountSql, $plan->filteredCountBinds);

        $rows = ($this->fetchRecords)($plan->dataSql, $plan->dataBinds);
        $fieldsInfo = $this->fetchFieldsInfo($activeColumns);
        $enrich = $this->dispatchEnrich($activeColumns, $rows);
        $aaData = $this->assembleRows($activeColumns, $rows, $fieldsInfo, $enrich);

        return [
            'sEcho' => $request->echo,
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFiltered,
            'aaData' => $aaData,
        ];
    }

    /**
     * Build the shared column registry (core ptlistcols columns + module
     * contributions). Delegates to the factory so the finder page and this
     * endpoint produce an identical column set.
     */
    public function buildColumnRegistry(): PatientFinderColumnRegistry
    {
        return $this->registryFactory->create();
    }

    /**
     * Map the client's column order (sColumns) to registered columns, preserving
     * that order and dropping any key the registry does not know. Falls back to
     * the full registry when the client sent no column list.
     *
     * @return array<int, PatientFinderColumn>
     */
    public function resolveActiveColumns(
        PatientFinderColumnRegistry $registry,
        PatientFinderRequest $request
    ): array {
        if ($request->columnKeys === []) {
            return $registry->all();
        }
        $columns = [];
        foreach ($request->columnKeys as $key) {
            $column = $registry->get($key);
            if ($column !== null) {
                $columns[] = $column;
            }
        }
        return $columns;
    }

    /**
     * Build the DataTables aaData rows: a DT_RowId per patient plus one cell per
     * active column, in column order. Name is concatenated, patient_data cells
     * are run through generate_plaintext_field, enriched cells are filled from
     * the enrich event, and every value is escaped.
     *
     * @param array<int, PatientFinderColumn>          $columns
     * @param array<int, array<string, mixed>>         $rows
     * @param array<string, array<string, mixed>>      $fieldsInfo field_id => layout_options row
     * @return array<int, array<int|string, string>>
     */
    public function assembleRows(
        array $columns,
        array $rows,
        array $fieldsInfo,
        PatientFinderRowEnrichEvent $enrich
    ): array {
        $aaData = [];
        foreach ($rows as $row) {
            $pid = (int) ($row['pid'] ?? 0);
            $arow = ['DT_RowId' => 'pid_' . (string) ($row['pid'] ?? '')];
            foreach ($columns as $column) {
                if ($column->key === PatientFinderQueryBuilder::NAME_KEY) {
                    $arow[] = ($this->attr)($this->concatName($row));
                } elseif ($column->isEnriched()) {
                    $values = $enrich->getValuesForPid($pid);
                    $arow[] = ($this->attr)($values[$column->key] ?? '');
                } elseif (isset($fieldsInfo[$column->key])) {
                    $arow[] = ($this->attr)(($this->plaintext)($fieldsInfo[$column->key], $row[$column->key] ?? ''));
                } else {
                    $arow[] = ($this->attr)($row[$column->key] ?? '');
                }
            }
            $aaData[] = $arow;
        }
        return $aaData;
    }

    private function count(string $sql, array $binds): int
    {
        $row = ($this->sqlQuery)($sql, $binds);
        return (int) (is_array($row) ? ($row['count'] ?? 0) : 0);
    }

    /**
     * Dispatch the custom-filter event and return the resulting BoundFilter. The
     * displayed column keys and the user's per-column filters are passed so a
     * listener can tailor its filter.
     *
     * @param array<int, PatientFinderColumn> $columns
     */
    private function dispatchFilter(array $columns, PatientFinderRequest $request): BoundFilter
    {
        $columnKeys = array_map(static fn(PatientFinderColumn $c): string => $c->key, $columns);
        $columnFilters = [];
        foreach ($request->columnSearches as $index => $term) {
            if (isset($columns[$index])) {
                $columnFilters[] = new ColumnFilter($columns[$index]->key, $term);
            }
        }
        $event = new PatientFinderFilterEvent(new BoundFilter(), $columnKeys, $columnFilters);
        $this->dispatcher->dispatch($event, PatientFinderFilterEvent::EVENT_HANDLE);
        return $event->getBoundFilter();
    }

    /**
     * In search_any mode, build the wider set of searchable columns from the DEM
     * layout fields (matching the legacy script's any-column search). Returns
     * null in normal mode, so the global search targets the displayed columns.
     *
     * @return array<int, PatientFinderColumn>|null
     */
    private function buildGlobalSearchColumns(PatientFinderRequest $request): ?array
    {
        if (!$request->anySearch) {
            return null;
        }
        $rows = ($this->fetchRecords)(
            "SELECT field_id FROM layout_options WHERE form_id = 'DEM' "
            . "AND field_id NOT LIKE ? AND field_id NOT LIKE ? AND field_id NOT LIKE ? AND uor != 0",
            ['em\_%', 'add%', 'related\_%']
        );
        $columns = [];
        foreach ($rows as $row) {
            $fieldId = (string) ($row['field_id'] ?? '');
            if ($fieldId !== '') {
                $columns[] = PatientFinderColumn::fromTable($fieldId, $fieldId);
            }
        }
        return $columns;
    }

    /**
     * Look up layout_options metadata for the patient_data columns, used to
     * format their cell values (lists, dates, etc.).
     *
     * @param array<int, PatientFinderColumn> $columns
     * @return array<string, array<string, mixed>>
     */
    private function fetchFieldsInfo(array $columns): array
    {
        $keys = [];
        foreach ($columns as $column) {
            if (
                $column->type === PatientFinderColumn::TYPE_PATIENT_DATA
                && $column->key !== PatientFinderQueryBuilder::NAME_KEY
            ) {
                $keys[] = $column->key;
            }
        }
        if ($keys === []) {
            return [];
        }
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        $rows = ($this->fetchRecords)(
            "SELECT data_type, field_id, list_id FROM layout_options "
            . "WHERE form_id = 'DEM' AND field_id IN ($placeholders)",
            $keys
        );
        $info = [];
        foreach ($rows as $row) {
            $info[(string) $row['field_id']] = $row;
        }
        return $info;
    }

    /**
     * @param array<int, PatientFinderColumn>  $columns
     * @param array<int, array<string, mixed>> $rows
     */
    private function dispatchEnrich(array $columns, array $rows): PatientFinderRowEnrichEvent
    {
        $enrichedColumns = array_values(array_filter(
            $columns,
            static fn(PatientFinderColumn $c): bool => $c->isEnriched()
        ));
        $pids = [];
        foreach ($rows as $row) {
            $pids[] = (int) ($row['pid'] ?? 0);
        }
        $event = new PatientFinderRowEnrichEvent($pids, $enrichedColumns);
        // Only bother dispatching when there is something to enrich.
        if ($enrichedColumns !== []) {
            $this->dispatcher->dispatch($event, PatientFinderRowEnrichEvent::EVENT_HANDLE);
        }
        return $event;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function concatName(array $row): string
    {
        $name = (string) ($row['lname'] ?? '');
        $fname = (string) ($row['fname'] ?? '');
        $mname = (string) ($row['mname'] ?? '');
        if ($name !== '' && $fname !== '') {
            $name .= ', ';
        }
        if ($fname !== '') {
            $name .= $fname;
        }
        if ($mname !== '') {
            $name .= ' ' . $mname;
        }
        return $name;
    }
}
