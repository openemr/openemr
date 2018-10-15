<?php
/**
 * ListService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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

namespace OpenEMR\Services;
use Particle\Validator\Validator;

class ListService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function validate($list)
    {
        $validator = new Validator();

        $validator->required('title')->lengthBetween(2, 255);
        $validator->required('type')->lengthBetween(2, 255);
        $validator->required('pid')->numeric();
        $validator->optional('diagnosis')->lengthBetween(2, 255);
        $validator->required('begdate')->datetime('Y-m-d');
        $validator->optional('enddate')->datetime('Y-m-d');

        return $validator->validate($list);
    }

    public function getAll($pid, $list_type)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? ORDER BY date DESC";

        $statementResults = sqlStatement($sql, array($pid, $list_type));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOptionsByListName($list_name)
    {
        $sql = "SELECT * FROM list_options WHERE list_id = ?";

        $statementResults = sqlStatement($sql, $list_name);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getOne($pid, $list_type, $list_id)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? AND id=? ORDER BY date DESC";

        return sqlQuery($sql, array($pid, $list_type, $list_id));
    }

    public function insert($data)
    {
        $sql  = " INSERT INTO lists SET";
        $sql .= "     date=NOW(),";
        $sql .= "     pid='" . add_escape_custom($data['pid']) . "',";
        $sql .= "     type='" . add_escape_custom($data['type']) . "',";
        $sql .= "     title='" . add_escape_custom($data["title"]) . "',";
        $sql .= "     begdate='" . add_escape_custom($data["begdate"]) . "',";
        $sql .= "     enddate='" . add_escape_custom($data["enddate"]) . "',";
        $sql .= "     diagnosis='" . add_escape_custom($data["diagnosis"]) . "'";

        return sqlInsert($sql);
    }

    public function update($data)
    {
        $sql  = " UPDATE lists SET";
        $sql .= "     title='" . add_escape_custom($data["title"]) . "',";
        $sql .= "     begdate='" . add_escape_custom($data["begdate"]) . "',";
        $sql .= "     enddate='" . add_escape_custom($data["enddate"]) . "',";
        $sql .= "     diagnosis='" . add_escape_custom($data["diagnosis"]) . "'";
        $sql .= " WHERE id='" . add_escape_custom($data["id"]) . "'";

        return sqlStatement($sql);
    }
}
