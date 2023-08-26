<?php

/**
 * SearchQueryConfig represents a configuration for a search query.  It handles both the pagination and the search field order
 * for a given search query request.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Database\QueryPagination;

class SearchQueryConfig
{
    private array $searchFieldOrders;
    private QueryPagination $pagination;

    public function __construct()
    {
        $this->searchFieldOrders = [];
        $this->pagination = new QueryPagination();
    }

    /**
     * @return QueryPagination
     */
    public function getPagination(): QueryPagination
    {
        return $this->pagination;
    }

    /**
     * @return array
     */
    public function getSearchFieldOrders(): array
    {
        return $this->searchFieldOrders;
    }

    public function addSearchFieldOrder(SearchFieldOrder $searchFieldOrder)
    {
        $this->searchFieldOrders[] = $searchFieldOrder;
    }

    public static function createFhirConfigFromSearchParams($queryParams)
    {
        $config = new SearchQueryConfig();
        $config->pagination = new QueryPagination(intval($queryParams['_count'] ?? 0), intval($queryParams['_offset'] ?? 0));

        if (!empty($queryParams['_sort'])) {
            foreach ($queryParams['_sort'] as $param) {
                if ($param instanceof SearchFieldOrder) {
                    $config->addSearchFieldOrder($param);
                }
            }
        }
        return $config;
    }

    public static function createConfigFromQueryParams($queryParams)
    {
        $config = new SearchQueryConfig();
        $config->pagination = new QueryPagination(intval($queryParams['_limit'] ?? 0), intval($queryParams['_offset'] ?? 0));

        if (!empty($queryParams['_sort'])) {
            $fields = explode(",", $queryParams['_sort']);
            foreach ($fields as $field) {
                if (strpos($field, '-') === 0) {
                    $field = substr($field, 1);
                    $config->addSearchFieldOrder(new SearchFieldOrder($field, false));
                } else {
                    $config->addSearchFieldOrder(new SearchFieldOrder($field, true));
                }
            }
        }
        return $config;
    }
}
