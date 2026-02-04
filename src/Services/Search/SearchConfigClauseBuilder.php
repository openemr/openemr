<?php

/**
 * SearchPaginationClauseBuilder handles the building of the pagination query clause for the search query.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Database\QueryPagination;
use OpenEMR\Common\Database\QueryUtils;

class SearchConfigClauseBuilder
{
    /**
     * Build an ORDER BY clause from the search config.
     *
     * @param SearchQueryConfig $config The search configuration
     * @param array $allowedColumns Whitelist of allowed column names for sorting.
     *                              Only columns in this list are allowed.
     * @return string The ORDER BY clause, or empty string if no valid sort fields
     */
    public static function buildSortOrderClauseFromConfig(
        SearchQueryConfig $config,
        array $allowedColumns
    ): string {
        $searchOrders = $config->getSearchFieldOrders();
        if ($searchOrders === []) {
            return "";
        }

        $clauses = [];
        foreach ($searchOrders as $search) {
            $field = $search->getField();
            if (!in_array($field, $allowedColumns, true)) {
                continue;
            }
            $clauses[] = $field . " " . ($search->isAscending() ? "ASC" : "DESC");
        }

        if ($clauses === []) {
            return "";
        }
        return "ORDER BY " . implode(", ", $clauses);
    }
    public static function buildQueryPaginationClause(QueryPagination|null $pagination): string
    {
        $clause = "";
        $limit = $pagination != null ? $pagination->getLimit() : 0;
        if ($limit > 0) { // we do nothing if its 0
            // we go one beyond the pagination limit to see if we need to add a next link
            $clause = "LIMIT " . QueryUtils::escapeLimit($pagination->getCurrentOffsetId()) . ", " . QueryUtils::escapeLimit($pagination->getLimit() + 1);
        }

        return $clause;
    }
}
