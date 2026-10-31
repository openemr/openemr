<?php

/**
 * PatientFinderRequest
 *
 * Immutable, parsed representation of the (legacy DataTables) request the
 * Patient Finder's server-side endpoint receives. It captures the quirks of the
 * wire format — the `sEcho` echo token, `iDisplayStart`/`iDisplayLength` paging
 * (where -1 means "not applicable"), the global `sSearch` term, the per-column
 * `bSearchable_N`/`sSearch_N` filters, the `iSortCol_N`/`sSortDir_N`/
 * `bSortable_N` sort instructions, the client-side column order in `sColumns`
 * (which may differ from the server's because of ColReorder), the exact-vs-like
 * `searchType` toggle, and the `search_any` any-column mode — as typed PHP so the
 * query builder and tests never touch `$_GET` directly.
 *
 * Parsing only; it performs no SQL and no database access.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final readonly class PatientFinderRequest
{
    /**
     * @param int                      $echo          The sEcho token echoed back to DataTables.
     * @param int                      $displayStart  Row offset, or -1 when paging is not applicable.
     * @param int                      $displayLength Page size, or -1 when paging is not applicable.
     * @param string                   $globalSearch  The search term applied across all searchable columns.
     * @param bool                     $exactSearch   True for exact matching, false for prefix/"like" matching.
     * @param bool                     $anySearch     True when in `search_any` (any-column, contains) mode.
     * @param array<int, string>       $columnKeys    Column keys in the client's order (from sColumns).
     * @param array<int, array{index: int, dir: string}> $sortColumns Sort instructions (column index + ASC|DESC).
     * @param array<int, string>       $columnSearches Per-column search terms, keyed by client column index.
     */
    public function __construct(
        public int $echo,
        public int $displayStart,
        public int $displayLength,
        public string $globalSearch,
        public bool $exactSearch,
        public bool $anySearch,
        public array $columnKeys,
        public array $sortColumns,
        public array $columnSearches
    ) {
    }

    /**
     * Build a request from a raw GET array (typically $_GET), reproducing the
     * legacy endpoint's interpretation of the DataTables parameters.
     *
     * @param array<string, mixed> $get
     */
    public static function fromArray(array $get): self
    {
        $echo = PatientFinderValue::int($get['sEcho'] ?? null);

        $displayStart = self::intParam($get, 'iDisplayStart', -1);
        $displayLength = self::intParam($get, 'iDisplayLength', -1);

        // `search_any` is only in effect when the any-column term is present AND
        // the normal sSearch box is empty; in that mode the any-term becomes the
        // global search term and matching is "contains".
        $rawSearchAny = trim(PatientFinderValue::string($get['search_any'] ?? null));
        $rawSearch = trim(PatientFinderValue::string($get['sSearch'] ?? null));
        $anySearch = $rawSearchAny !== '' && $rawSearch === '';
        $globalSearch = $anySearch ? $rawSearchAny : $rawSearch;

        $exactSearch = PatientFinderValue::string($get['searchType'] ?? null) === 'true';

        $columnKeys = self::splitColumns(PatientFinderValue::string($get['sColumns'] ?? null));

        return new self(
            $echo,
            $displayStart,
            $displayLength,
            $globalSearch,
            $exactSearch,
            $anySearch,
            $columnKeys,
            self::parseSort($get),
            self::parseColumnSearches($get, count($columnKeys))
        );
    }

    /**
     * True when the client supplied a valid paging window.
     */
    public function hasPaging(): bool
    {
        return $this->displayStart >= 0 && $this->displayLength >= 0;
    }

    /**
     * True when a non-empty global search term was supplied.
     */
    public function hasGlobalSearch(): bool
    {
        return $this->globalSearch !== '';
    }

    /**
     * @param array<string, mixed> $get
     */
    private static function intParam(array $get, string $key, int $default): int
    {
        if (!isset($get[$key]) || !is_numeric($get[$key])) {
            return $default;
        }
        $value = (int) $get[$key];
        return $value >= 0 ? $value : $default;
    }

    /**
     * @return array<int, string>
     */
    private static function splitColumns(string $sColumns): array
    {
        if ($sColumns === '') {
            return [];
        }
        return array_map(trim(...), explode(',', $sColumns));
    }

    /**
     * Reproduce the legacy sort parsing: iterate iSortingCols instructions, keep
     * only those whose target column is flagged sortable (bSortable_<col>).
     *
     * @param array<string, mixed> $get
     * @return array<int, array{index: int, dir: string}>
     */
    private static function parseSort(array $get): array
    {
        if (!isset($get['iSortCol_0'])) {
            return [];
        }
        $count = PatientFinderValue::int($get['iSortingCols'] ?? null);
        $sorts = [];
        for ($i = 0; $i < $count; ++$i) {
            if (!isset($get["iSortCol_$i"])) {
                continue;
            }
            $col = PatientFinderValue::int($get["iSortCol_$i"]);
            if (PatientFinderValue::string($get["bSortable_$col"] ?? null) !== 'true') {
                continue;
            }
            $dir = strtoupper(PatientFinderValue::string($get["sSortDir_$i"] ?? null)) === 'DESC' ? 'DESC' : 'ASC';
            $sorts[] = ['index' => $col, 'dir' => $dir];
        }
        return $sorts;
    }

    /**
     * Reproduce the legacy per-column filtering: for each client column index,
     * keep the term when the column is flagged searchable and the term is set.
     *
     * @param array<string, mixed> $get
     * @return array<int, string>
     */
    private static function parseColumnSearches(array $get, int $columnCount): array
    {
        $searches = [];
        for ($i = 0; $i < $columnCount; ++$i) {
            if (PatientFinderValue::string($get["bSearchable_$i"] ?? null) !== 'true') {
                continue;
            }
            $term = PatientFinderValue::string($get["sSearch_$i"] ?? null);
            if ($term === '') {
                continue;
            }
            $searches[$i] = $term;
        }
        return $searches;
    }
}
