<?php

/**
 * PatientFinderColumn
 *
 * Immutable description of a single column shown in the Patient Finder
 * (dynamic_finder.php) and its server-side DataTables endpoint
 * (dynamic_finder_ajax.php).
 *
 * Core seeds one column per active `ptlistcols` entry (all resolving against
 * `patient_data`). Modules may contribute additional columns via
 * {@see \OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent},
 * in one of two flavours:
 *
 *  - TYPE_SQL      a column whose value is produced by a caller-supplied,
 *                  already-parameterised SQL expression (optionally with a
 *                  JOIN). Because the expression is author-controlled it is NOT
 *                  run through the `patient_data` identifier whitelist, so the
 *                  contributing module is responsible for its safety. Such a
 *                  column can be searched/sorted server-side.
 *  - TYPE_ENRICHED a display-only column whose values are supplied in a batch,
 *                  after the page's pids are known, via
 *                  {@see \OpenEMR\Events\PatientFinder\PatientFinderRowEnrichEvent}.
 *                  It is never searched or sorted server-side.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

final class PatientFinderColumn
{
    /** A column resolved against a database table (whitelisted by identifier). */
    public const TYPE_PATIENT_DATA = 'patient_data';

    /** A column produced by an author-supplied, parameterised SQL expression. */
    public const TYPE_SQL = 'sql';

    /** A display-only column whose values are batch-supplied post-query. */
    public const TYPE_ENRICHED = 'enriched';

    /**
     * @param string            $key             Stable identifier used in sColumns and row output.
     * @param string            $title           Display label (already translated/translatable).
     * @param string            $type            One of the TYPE_* constants.
     * @param bool              $searchable      Whether the finder may filter on this column.
     * @param bool              $sortable        Whether the finder may ORDER BY this column.
     * @param string            $table           Owning table for a TYPE_PATIENT_DATA column (identifier whitelist).
     * @param string|null $selectExpression TYPE_SQL: SQL yielding the value, aliased to the key.
     * @param string|null $joinClause TYPE_SQL: optional JOIN appended to the base FROM.
     * @param string|null $searchExpression TYPE_SQL: SQL used in WHERE/ORDER BY (see fromSql()).
     * @param array<int, string|int> $boundValues TYPE_SQL: values bound to ?s in the select/join clauses.
     * @param string $defaultSortDir Initial sort direction the finder page requests for this
     *        column ("asc" or "desc"); drives the DataTables "order" option.
     */
    private function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $type,
        public readonly bool $searchable,
        public readonly bool $sortable,
        public readonly string $table = 'patient_data',
        public readonly ?string $selectExpression = null,
        public readonly ?string $joinClause = null,
        public readonly ?string $searchExpression = null,
        public readonly array $boundValues = [],
        public readonly string $defaultSortDir = 'asc'
    ) {
    }

    /**
     * A core column backed by a `patient_data` (or other whitelisted-table) field.
     * The $key is the field id and is validated against the table's real columns
     * by the query service before it ever reaches SQL.
     */
    public static function fromTable(
        string $key,
        string $title,
        bool $searchable = true,
        bool $sortable = true,
        string $table = 'patient_data',
        string $defaultSortDir = 'asc'
    ): self {
        return new self(
            $key,
            $title,
            self::TYPE_PATIENT_DATA,
            $searchable,
            $sortable,
            $table,
            defaultSortDir: $defaultSortDir
        );
    }

    /**
     * A module column whose value comes from an author-supplied SQL expression,
     * drawn from a table other than `patient_data`. The expression is
     * author-controlled and is NOT run through the identifier whitelist, so it
     * must already be safe (and parameterised if it embeds any values).
     *
     * There are two shapes:
     *
     *  - Correlated subquery (recommended default): $selectExpression is a
     *    self-contained sub-SELECT, e.g.
     *        (SELECT i.ihi_number FROM ihi_data i WHERE i.pid = patient_data.pid LIMIT 1)
     *    The base FROM stays `patient_data`, so the finder's COUNT(*) totals are
     *    unaffected, and the LIMIT/aggregate guarantees one value per patient
     *    (no row multiplication). Leave $joinClause null.
     *  - JOIN: pass a $joinClause (e.g. "LEFT JOIN ihi_data ihi ON ihi.pid =
     *    patient_data.pid") and a column reference as $selectExpression (e.g.
     *    "ihi.ihi_number"). The finder appends the join to the data AND count
     *    queries and counts DISTINCT pids so a 1:many join can't inflate totals.
     *    Prefer this only when the relation is effectively 1:1 and you want index
     *    performance.
     *
     * $searchExpression is the SQL used in WHERE (for searching) and ORDER BY
     * (for sorting). It must be a real expression, NOT the column alias, because
     * MySQL forbids SELECT aliases in WHERE; it therefore defaults to
     * $selectExpression. The finder appends "<searchExpression> LIKE ?" and binds
     * the user's search term itself, exactly as it does for a native column. For
     * a searchable column keep $searchExpression free of its own bound
     * parameters (as the examples above are); $boundValues are only for values
     * embedded in the SELECT/JOIN clauses, and bind ahead of the finder's WHERE
     * and LIMIT values. Set $searchable/$sortable false to make the column
     * display-only.
     *
     * @param array<int, string|int> $boundValues
     */
    public static function fromSql(
        string $key,
        string $title,
        string $selectExpression,
        ?string $joinClause = null,
        ?string $searchExpression = null,
        array $boundValues = [],
        bool $searchable = true,
        bool $sortable = true,
        string $defaultSortDir = 'asc'
    ): self {
        return new self(
            $key,
            $title,
            self::TYPE_SQL,
            $searchable,
            $sortable,
            'patient_data',
            $selectExpression,
            $joinClause,
            $searchExpression ?? $selectExpression,
            $boundValues,
            $defaultSortDir
        );
    }

    /**
     * A display-only module column whose values are supplied in a batch after the
     * page's pids are known (see PatientFinderRowEnrichEvent). Never searched or
     * sorted server-side.
     */
    public static function enriched(string $key, string $title): self
    {
        return new self($key, $title, self::TYPE_ENRICHED, false, false);
    }

    public function isEnriched(): bool
    {
        return $this->type === self::TYPE_ENRICHED;
    }

    public function isSql(): bool
    {
        return $this->type === self::TYPE_SQL;
    }

    /**
     * Return a copy of this column with a different initial sort direction.
     */
    public function withDefaultSortDir(string $dir): self
    {
        return new self(
            $this->key,
            $this->title,
            $this->type,
            $this->searchable,
            $this->sortable,
            $this->table,
            $this->selectExpression,
            $this->joinClause,
            $this->searchExpression,
            $this->boundValues,
            $dir
        );
    }
}
