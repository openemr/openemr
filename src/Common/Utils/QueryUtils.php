<?php

/**
 * QueryUtils. This class contains static convenience functions for generating
 * SQL and executing it. It is up to the caller to ensure the SQL is properly
 * escaped, however.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Common\Utils;

class QueryUtils
{
    /**
     * Shared getter for SQL selects.
     *
     * @param $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param $map - Query information (where clause(s), join clause(s), order, data, etc).
     * @return array of associative arrays | one associative array.
     */
    public static function selectHelper($sqlUpToFromStatement, $map)
    {
        $where = isset($map["where"]) ? $map["where"] : null;
        $data  = isset($map["data"])  ? $map["data"]  : null;
        $join  = isset($map["join"])  ? $map["join"]  : null;
        $order = isset($map["order"]) ? $map["order"] : null;
        $limit = isset($map["limit"]) ? $map["limit"] : null;

        $sql = $sqlUpToFromStatement;

        $sql .= !empty($join)  ? " " . $join        : "";
        $sql .= !empty($where) ? " " . $where       : "";
        $sql .= !empty($order) ? " " . $order       : "";
        $sql .= !empty($limit) ? " LIMIT " . $limit : "";

        if (!empty($data)) {
            if (empty($limit) || $limit > 1) {
                $multipleResults = sqlStatement($sql, $data);
                $results = array();

                while ($row = sqlFetchArray($multipleResults)) {
                    array_push($results, $row);
                }

                return $results;
            }

            return sqlQuery($sql, $data);
        }

        if (empty($limit) || $limit > 1) {
            $multipleResults = sqlStatement($sql);
            $results = array();

            while ($row = sqlFetchArray($multipleResults)) {
                array_push($results, $row);
            }

            return $results;
        }

        return sqlQuery($sql);
    }
}
