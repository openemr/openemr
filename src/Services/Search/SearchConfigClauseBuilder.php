<?php

/**
 * SearchPaginationClauseBuilder handles the building of the pagination query clause for the search query.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Database\QueryPagination;
use OpenEMR\Common\Database\QueryUtils;

class SearchConfigClauseBuilder
{
    public static function buildSortOrderClauseFromConfig(SearchQueryConfig $config)
    {
        $searchOrders = $config->getSearchFieldOrders();
        if (empty($searchOrders)) {
            return "";
        }

        $clauses = [];
        foreach ($searchOrders as $search) {
            $clauses[] = $search->getField() . " " . ($search->isAscending() ? "ASC" : "DESC");
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
