<?php
/**
 *  Office notes library
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

function getOnoteById ($id, $cols = "*")
{
	return sqlQuery("select $cols from onotes where id=? order by date DESC limit 0,1", array($id));
}

function getOnoteByDate ($date, $activity = "1", $cols = "*", $limit="all", $offset="0")
{

        $sqlBindArray = array();
	if($limit == "all")
	{
		$sql = "select $cols from onotes where date like ? ";
                array_push($sqlBindArray,"%".$date."%");
		if ($activity != "all")
			$sql .= "and activity=? ";
                        array_push($sqlBindArray,$activity);
		$sql .= "order by date DESC";
	}
	else
	{
		$sql = "select $cols from onotes where date like ? ";
                array_push($sqlBindArray,"%".$date."%");
		if ($activity != "all")
			$sql .= "and activity =? ";
                        array_push($sqlBindArray,$activity);
		$sql .= "order by date DESC LIMIT ".escape_limit($offset).",".escape_limit($limit);
	}
	
	$res = sqlStatement($sql,$sqlBindArray);

	for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;
}

function addOnote($body, $activity = "1")
{
	return sqlInsert("insert into onotes (date, body, user, groupname, activity) values (NOW(),?,?,?,?)", array($body,$_SESSION['authUser'],$_SESSION['authProvider'],$activity));
}

function disappearOnote($id)
{
	sqlStatement("update onotes set activity = '0' where id=?", array($id));
	return true;
}

function reappearOnote ($id)
{
	sqlStatement("update onotes set activity = '1' where id=?", array($id));
	return true;
}
?>
