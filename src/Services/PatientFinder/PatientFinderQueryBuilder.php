<?php

/**
 * PatientFinderQueryBuilder
 *
 * Pure SQL builder for the Patient Finder's server-side endpoint. Given the
 * active columns (in the client's order), a parsed {@see PatientFinderRequest},
 * and the custom {@see BoundFilter} contributed by
 * {@see \OpenEMR\Events\PatientFinder\PatientFinderFilterEvent}, it produces a
 * {@see PatientFinderQueryPlan}: the paged data query plus the total and
 * post-filter COUNT queries, each with its bound values in parameter order.
 *
 * It reproduces the behaviour of the legacy dynamic_finder_ajax.php — the
 * exact / prefix ("like") / contains ("any") match modes, the special
 * `name` => (lname, fname, mname) expansion, the `DOB` date search, and the
 * filter-clause composition — and extends it to columns drawn from other tables
 * (see {@see PatientFinderColumn}). `patient_data` (and other whitelisted-table)
 * columns are validated through the injected identifier escaper; SQL columns use
 * their author-supplied, parameter-free search expression; enriched columns are
 * display-only and never reach the SQL.
 *
 * Building touches no database. The identifier escaper and the date-search
 * helper are injectable so the builder is unit-testable in isolation; they
 * default to OpenEMR core's implementations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\BoundFilter;

final class PatientFinderQueryBuilder
{
    /** The patient-name column, expanded to last/first/middle name parts. */
    public const NAME_KEY = 'name';

    /** The date-of-birth column, which uses the fuzzy date search. */
    public const DOB_KEY = 'DOB';

    /** @var callable(string, array<int, string>): string */
    private $columnEscaper;

    /** @var callable(string): string */
    private $dateSearch;

    /**
     * @param callable(string, array<int, string>): string|null $columnEscaper
     *        Validates/quotes a DB identifier against a table whitelist
     *        (defaults to core's escape_sql_column_name()).
     * @param callable(string): string|null $dateSearch
     *        Turns a user date fragment into a MySQL LIKE pattern (defaults to
     *        the legacy finder behaviour).
     */
    public function __construct(?callable $columnEscaper = null, ?callable $dateSearch = null)
    {
        $this->columnEscaper = $columnEscaper ?? escape_sql_column_name(...);
        $this->dateSearch = $dateSearch ?? self::legacyDateSearch(...);
    }

    /**
     * @param array<int, PatientFinderColumn> $columns Active (displayed) columns in client order.
     * @param array<int, PatientFinderColumn>|null $globalSearchColumns Columns the global search
     *        term applies to, when it differs from the displayed set — the `search_any` any-column
     *        mode searches the DEM layout fields rather than the displayed columns. Null (the
     *        default) searches the displayed columns, as the normal mode does.
     */
    public function build(
        array $columns,
        PatientFinderRequest $request,
        BoundFilter $filter,
        ?array $globalSearchColumns = null
    ): PatientFinderQueryPlan {
        [$selectList, $dataJoinMap, $selectBinds] = $this->buildSelect($columns);
        [$searchWhere, $searchBinds, $whereJoinMap] = $this->buildWhere($columns, $request, $globalSearchColumns);
        $orderBy = $this->buildOrderBy($columns, $request);

        $customWhere = $filter->getFilterClause();
        $filterBinds = $filter->getBoundValues();

        $fullWhere = $searchWhere === ''
            ? $customWhere
            : $customWhere . ' AND ( ' . $searchWhere . ' )';

        $limitSql = '';
        $limitBinds = [];
        if ($request->hasPaging()) {
            $limitSql = ' LIMIT ? OFFSET ?';
            $limitBinds = [$request->displayLength, $request->displayStart];
        }

        // The data query needs every JOIN referenced anywhere in it: the displayed
        // SQL columns (SELECT) plus any searched SQL column's JOIN — including a
        // global-search column that is not displayed, whose JOIN would otherwise be
        // absent from FROM and make the WHERE reference an unknown table.
        $dataJoins = $dataJoinMap;
        foreach ($whereJoinMap as $clause => $joinBinds) {
            $dataJoins[$clause] ??= $joinBinds;
        }

        // Bind values follow the SQL left-to-right: SELECT placeholders, then the
        // JOIN placeholders (once per distinct JOIN, in JOIN order), then the
        // WHERE (filter then search), then LIMIT.
        $dataSql = 'SELECT ' . implode(', ', $selectList)
            . ' FROM patient_data' . $this->joinSql(array_keys($dataJoins))
            . ' WHERE ' . $fullWhere . $orderBy . $limitSql;
        $dataBinds = array_merge(
            $selectBinds,
            $this->joinBinds($dataJoins),
            $filterBinds,
            $searchBinds,
            $limitBinds
        );

        // Total count: the custom filter only, against patient_data with no
        // joins and no search, so it needs only the filter's bound values.
        $totalCountSql = 'SELECT COUNT(id) AS count FROM patient_data WHERE ' . $customWhere;

        // Filtered count: include the joins of any SQL column referenced in the
        // WHERE so those references resolve (and bind their parameters), and count
        // DISTINCT patients so a 1:many join cannot inflate the total.
        $countExpr = $whereJoinMap === [] ? 'COUNT(id)' : 'COUNT(DISTINCT patient_data.pid)';
        $filteredCountSql = 'SELECT ' . $countExpr . ' AS count FROM patient_data'
            . $this->joinSql(array_keys($whereJoinMap)) . ' WHERE ' . $fullWhere;
        $filteredCountBinds = array_merge($this->joinBinds($whereJoinMap), $filterBinds, $searchBinds);

        return new PatientFinderQueryPlan(
            $dataSql,
            $dataBinds,
            $totalCountSql,
            $filterBinds,
            $filteredCountSql,
            $filteredCountBinds
        );
    }

    /**
     * @param array<int, PatientFinderColumn> $columns
     * @return array{0: array<int, string>, 1: array<string, array<int, string|int>>, 2: array<int, string|int>}
     *         [select fragments, JOIN clause => its bind values (distinct, first owner wins), select bind values]
     */
    private function buildSelect(array $columns): array
    {
        // Qualify pid so it is unambiguous once a module adds a JOIN whose table
        // also has a pid column.
        $select = ['patient_data.pid'];
        $joins = [];
        $binds = [];

        foreach ($columns as $column) {
            if ($column->key === 'pid') {
                continue;
            }
            if ($column->key === self::NAME_KEY) {
                $select[] = 'lname';
                $select[] = 'fname';
                $select[] = 'mname';
                continue;
            }
            if ($column->isEnriched()) {
                continue; // populated post-query, not selected in SQL
            }
            if ($column->isSql()) {
                $select[] = (string) $column->selectExpression . ' AS ' . $this->alias($column->key);
                foreach ($column->selectBoundValues as $bind) {
                    $binds[] = $bind;
                }
                $this->collectJoin($joins, $column);
                continue;
            }
            $select[] = ($this->columnEscaper)($column->key, [$column->table]);
        }

        return [$select, $joins, $binds];
    }

    /**
     * @param array<int, PatientFinderColumn> $columns
     * @param array<int, PatientFinderColumn>|null $globalSearchColumns
     * @return array{0: string, 1: array<int, string|int>, 2: array<string, array<int, string|int>>}
     *         [WHERE fragment (without the filter clause), bind values, JOIN clause => its bind values]
     */
    private function buildWhere(
        array $columns,
        PatientFinderRequest $request,
        ?array $globalSearchColumns = null
    ): array {
        $parts = [];
        $binds = [];
        $joins = [];

        // Global search: one OR group across every searchable column (the DEM
        // layout fields in search_any mode, otherwise the displayed columns).
        if ($request->hasGlobalSearch()) {
            $term = $request->globalSearch;
            $ors = [];
            foreach (($globalSearchColumns ?? $columns) as $column) {
                if (!$column->searchable) {
                    continue; // enriched + any module display-only column
                }
                if ($column->key === self::NAME_KEY) {
                    $ors[] = 'lname LIKE ? OR fname LIKE ? OR mname LIKE ?';
                    $value = $request->exactSearch ? $term : $term . '%';
                    $binds[] = $value;
                    $binds[] = $value;
                    $binds[] = $value;
                    continue;
                }
                $ors[] = $this->searchTarget($column) . ' LIKE ?';
                if ($column->isSql()) {
                    $this->collectJoin($joins, $column);
                }
                // Match mode precedence matches the legacy script: exact wins,
                // then the any-column "contains" mode, otherwise prefix.
                if ($request->exactSearch) {
                    $binds[] = $term;
                } elseif ($request->anySearch) {
                    $binds[] = '%' . $term . '%';
                } else {
                    $binds[] = $term . '%';
                }
            }
            if ($ors !== []) {
                $parts[] = '( ' . implode(' OR ', $ors) . ' )';
            }
        }

        // Per-column search.
        foreach ($columns as $index => $column) {
            if (!isset($request->columnSearches[$index])) {
                continue;
            }
            if (!$column->searchable) {
                continue;
            }
            $term = $request->columnSearches[$index];
            if ($column->key === self::NAME_KEY) {
                // Parenthesised so the name OR-group binds tighter than the ANDs
                // joining the other per-column filters; otherwise a first-/
                // middle-name match could bypass another column's filter (and the
                // data query and filtered count would then disagree).
                $parts[] = '( lname LIKE ? OR fname LIKE ? OR mname LIKE ? )';
                $value = $request->exactSearch ? $term : $term . '%';
                $binds[] = $value;
                $binds[] = $value;
                $binds[] = $value;
                continue;
            }
            if ($column->key === self::DOB_KEY) {
                $parts[] = ($this->columnEscaper)($column->key, [$column->table]) . ' LIKE ?';
                $binds[] = ($this->dateSearch)($term);
                continue;
            }
            $parts[] = $this->searchTarget($column) . ' LIKE ?';
            if ($column->isSql()) {
                $this->collectJoin($joins, $column);
            }
            $binds[] = $request->exactSearch ? $term : $term . '%';
        }

        return [implode(' AND ', $parts), $binds, $joins];
    }

    /**
     * @param array<int, PatientFinderColumn> $columns
     */
    private function buildOrderBy(array $columns, PatientFinderRequest $request): string
    {
        $clauses = [];
        foreach ($request->sortColumns as $sort) {
            $column = $columns[$sort['index']] ?? null;
            if ($column === null || !$column->sortable) {
                continue;
            }
            $dir = $sort['dir']; // already normalised to ASC|DESC by the request
            if ($column->key === self::NAME_KEY) {
                $clauses[] = "lname $dir, fname $dir, mname $dir";
            } elseif ($column->isSql()) {
                $clauses[] = '(' . $column->searchExpression . ") $dir";
            } else {
                $clauses[] = ($this->columnEscaper)($column->key, [$column->table]) . " $dir";
            }
        }

        return $clauses === [] ? '' : ' ORDER BY ' . implode(', ', $clauses);
    }

    /**
     * The SQL used to search a non-name column: the parameter-free author search
     * expression for a SQL column, otherwise the whitelisted identifier.
     */
    private function searchTarget(PatientFinderColumn $column): string
    {
        if ($column->isSql()) {
            return '(' . $column->searchExpression . ')';
        }
        return ($this->columnEscaper)($column->key, [$column->table]);
    }

    /**
     * Register a SQL column's JOIN (if any), keyed by its clause text so a JOIN
     * shared by several columns is emitted — and its parameters bound — only once.
     * The first column to own a given JOIN clause supplies its bind values.
     *
     * @param array<string, array<int, string|int>> $joins JOIN clause => its bind values
     */
    private function collectJoin(array &$joins, PatientFinderColumn $column): void
    {
        if ($column->joinClause !== null) {
            $joins[$column->joinClause] ??= $column->joinBoundValues;
        }
    }

    /**
     * Flatten an ordered JOIN-clause => bind-values map into a single bind list,
     * in JOIN order, matching the order the clauses appear in the SQL.
     *
     * @param array<string, array<int, string|int>> $joins
     * @return array<int, string|int>
     */
    private function joinBinds(array $joins): array
    {
        return $joins === [] ? [] : array_merge(...array_values($joins));
    }

    /**
     * @param array<int, string> $joins
     */
    private function joinSql(array $joins): string
    {
        return $joins === [] ? '' : ' ' . implode(' ', $joins);
    }

    private function alias(string $key): string
    {
        return '`' . str_replace('`', '', $key) . '`';
    }

    /**
     * Legacy fuzzy date search: turn a partial date into a MySQL LIKE pattern.
     * Ported verbatim from dynamic_finder_ajax.php so the default behaviour is
     * unchanged; callers may inject their own implementation for testing.
     */
    private static function legacyDateSearch(string $sSearch): string
    {
        $globals = OEGlobalsBag::getInstance();
        $dateDisplayFormat = $globals->getInt('date_display_format');
        $mdy = $dateDisplayFormat === 0
            ? ($globals->getInt('phone_country_code') === 1)
            : ($dateDisplayFormat === 1);

        $mystr = "%$sSearch%";
        if (preg_match('/[^0-9]/', $sSearch)) {
            $parts = preg_split('/[^0-9]/', $sSearch) ?: [];
            $parts[0] ??= '';
            $parts[1] ??= '';
            $parts[2] ??= '';
            if (strlen($parts[0]) <= 2) {
                $parts = $mdy
                    ? [$parts[2], $parts[0], $parts[1]]
                    : [$parts[2], $parts[1], $parts[0]];
            }
            $parts[0] = substr($parts[0] . '____', 0, 4);
            if (strlen($parts[1]) === 0) {
                $parts[1] = '__';
            } elseif (strlen($parts[1]) === 1) {
                $parts[1] = '0' . $parts[1];
            }
            if (strlen($parts[2]) === 0) {
                $parts[2] = '__';
            } elseif (strlen($parts[2]) === 1) {
                $parts[2] = '0' . $parts[2];
            }
            $mystr = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
        }

        return $mystr;
    }
}
