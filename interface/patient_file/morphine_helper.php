<?php
/**
 * Morphine Helper
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 function getList()
 {

 	$sql = "SELECT * FROM morphine_list ORDER BY drugname";
 	$list = sqlStatement($sql); 
    $wholeList = array();

    while($row = sqlFetchArray($list)){
    	$wholeList[] = $row; 
    }
 	return $wholeList;

 }

 function saveEntry($id, $drugname, $multiplier, $days)
 {
 	if(empty($id))
 	{
 	$sql = "INSERT INTO morphine_list (id, drugname, multiplier, days) VALUES (?,?,?,?)";
 	$in = array($id, $drugname, $multiplier, $days);
    }
    if(!empty($id))
    {
    $sql = "UPDATE morphine_list SET drugname = ?, multiplier = ?, days = ? WHERE `morphine_list`.`id` = ?";
    $in = array($drugname, $multiplier, $days, $id);	
    }
 	sqlStatement($sql, $in);
 }

 function updateDrug($id)
 {
 	$sql = "SELECT * FROM morphine_list WHERE id = ?";
 	$item = sqlQuery($sql, $id);

 	return $item;
 }

 function deleteEntry($id)
 {
    $sql = "DELETE FROM morphine_list WHERE id = ?";
 	$in = array($id);
 	sqlStatement($sql, $in);    
 }