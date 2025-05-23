<?php

/**
 * TableSearchProcessor.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Validators\ProcessingResult;

class TableSearchProcessor
{
    public function buildQuery($selectFields, $joinClauses, $search, $isAndCondition = true)
    {
        $sqlBindArray = array();

//        $selectFields = $this->getSelectFields();

        $selectFields = array_combine($selectFields, $selectFields); // make it a dictionary so we can add/remove this.
        $from = [$this->getTable()];

        $sql = "SELECT " . implode(",", array_keys($selectFields)) . " FROM " . implode(",", $from);

        $join = $this->getSelectJoinClauses();

        $whereClauses = array(
            'and' => []
        ,'or' => []
        );

        if (!empty($search)) {
            // make sure all the parameters are actual search fields and clean up any field that is a uuid
            foreach ($search as $key => $field) {
                if (!$field instanceof ISearchField) {
                    throw new \InvalidArgumentException("Method called with invalid parameter.  Expected SearchField object for parameter '" . $key . "'");
                }
                $whereType = $isAndCondition ? "and" : "or";

                $whereClauses[$whereType][] = SearchFieldStatementResolver::getStatementForSearchField($field);
            }
        }
        $where = '';

        if (!(empty($whereClauses['or']) && empty($whereClauses['and']))) {
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

        $records = $this->selectHelper($sql, [
            'join' => $join
            ,'where' => $where
            ,'data' => $sqlBindArray
        ]);
    }
    /**
     * Returns a list of records matching the search criteria.
     * Search criteria is conveyed by array where key = field/column name, value is an ISearchField
     * If an empty array of search criteria is provided, all records are returned.
     *
     * The search will grab the intersection of all possible values if $isAndCondition is true, otherwise it returns
     * the union (logical OR) of the search.
     *
     * More complicated searches with various sub unions / intersections can be accomplished through a CompositeSearchField
     * that allows you to combine multiple search clauses on a single search field.
     *
     * @param ISearchField[] $search Hashmap of string => ISearchField where the key is the field name of the search field
     * @param bool $isAndCondition Whether to join each search field with a logical OR or a logical AND.
     * @return ProcessingResult The results of the search.
     */
    public function search($selectFields, $search, $isAndCondition = true)
    {
        $sqlBindArray = array();

//        $selectFields = $this->getSelectFields();

        $selectFields = array_combine($selectFields, $selectFields); // make it a dictionary so we can add/remove this.
        $from = [$this->getTable()];

        $sql = "SELECT " . implode(",", array_keys($selectFields)) . " FROM " . implode(",", $from);

        $join = $this->getSelectJoinClauses();

        $whereClauses = array(
            'and' => []
        ,'or' => []
        );

        if (!empty($search)) {
            // make sure all the parameters are actual search fields and clean up any field that is a uuid
            foreach ($search as $key => $field) {
                if (!$field instanceof ISearchField) {
                    throw new \InvalidArgumentException("Method called with invalid parameter.  Expected SearchField object for parameter '" . $key . "'");
                }
                $whereType = $isAndCondition ? "and" : "or";

                $whereClauses[$whereType][] = SearchFieldStatementResolver::getStatementForSearchField($field);
            }
        }
        $where = '';

        if (!(empty($whereClauses['or']) && empty($whereClauses['and']))) {
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

        $records = $this->selectHelper($sql, [
            'join' => $join
            ,'where' => $where
            ,'data' => $sqlBindArray
        ]);

        $processingResult = new ProcessingResult();
        foreach ($records as $row) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }

        return $processingResult;
    }
}
