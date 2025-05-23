<?php

/**
 * FhirSearchWhereClauseBuilder.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class FhirSearchWhereClauseBuilder
{
    /**
     * Given a list of ISearchField objects it constructs a WHERE clause query that can be used in a query statement
     *
     * @param ISearchField[] $search Hashmap of string => ISearchField where the key is the field name of the search field
     * @param bool $isAndCondition Whether to join each search field with a logical OR or a logical AND.
     * @return string The where clause query string.
     */
    public static function build($search, $isAndCondition = true): SearchQueryFragment
    {
        $sqlBindArray = [];
        $whereClauses = array(
            'and' => []
        ,'or' => []
        );

        if (!empty($search)) {
            // make sure all the parameters are actual search fields and clean up any field that is a uuid
            foreach ($search as $key => $field) {
                if (!$field instanceof ISearchField) {
                    // developer logic error
                    // treat the field as an exact string match if they send us a primitive
                    if (is_string($field) || is_numeric($field)) {
                        $field = new StringSearchField($key, $field, SearchModifier::EXACT);
                    } else {
                        throw new \BadMethodCallException("Method called with invalid parameter.  Expected string, number, or SearchField object for parameter '" . $key . "'");
                    }
                }
                $whereType = $isAndCondition ? "and" : "or";

                $whereClauses[$whereType][] = SearchFieldStatementResolver::getStatementForSearchField($field);
            }
        }
        $where = '';

        if (! (empty($whereClauses['or']) && empty($whereClauses['and']) )) {
            $where = " WHERE ";
            $andClauses = [];
            foreach ($whereClauses['and'] as $clause) {
                $andClauses[] = $clause->getFragment();
                $sqlBindArray = array_merge($sqlBindArray, $clause->getBoundValues());
            }
            $where = empty($andClauses) ? $where : $where . implode(" AND ", $andClauses);

            $orClauses = [];
            foreach ($whereClauses['or'] as $clause) {
                $orClauses[] = $clause->getFragment();
                $sqlBindArray = array_merge($sqlBindArray, $clause->getBoundValues());
            }
            $where = empty($orClauses) ? $where : $where . "(" . implode(" OR ", $orClauses) . ")";
        }
        return new SearchQueryFragment($where, $sqlBindArray);
    }
}
