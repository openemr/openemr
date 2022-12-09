<?php
// Copyright (C) 2018 Williams Medical Technologies <rgenandt@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains functions to:
//   --manage patient education items in the amc_ed_data_log sql table 

function amcEdAdd($pid, $complete=TRUE, $link_type='', $link_id='', 
		$ed_type='', $ed_code='', $date='', $by='', $method='', $ref='') {

	if($by == '') $by = $_SESSION['authUserID'];
	if($date == '') $date = date('Y-m-d H:i:s');
  $empty_item = amcEdCollect($pid, $link_type, $link_id, $ed_type, $ed_code, 
		'0000-00-00', 0, $method);
  $this_item = amcEdCollect($pid, $link_type, $link_id, $ed_type, $ed_code, 
		$date, $by, $method);

  if(!$empty_item && !$this_item) {
		if(!$complete) $date = '0000-00-00 00:00:00';
    $sqlBindArray = array($pid,$link_type,$link_id,$ed_type,$ed_code,$date,
			$by,$method,$ref);
      sqlStatement("INSERT INTO `amc_ed_data_log` (`pid`,`link_type`,`link_id`,`ed_type`,`ed_code`,`date_provided`,`provided_by`,`method`,`reference`,`date_created`) VALUES(?,?,?,?,?,?,?,?,?,NOW())", $sqlBindArray);
  }
  else if($empty_item){
    // already exist, so only ensure complete date is set (if applicable)
    if ($complete) {
      amcEdComplete($empty_item, $by);
    }
  }
}

// Function to add an item to the amc_ed_data_log sql table
//  This function will allow duplicates (unlike the above amcEdAdd function)
function amcEdAddForce($pid, $complete=TRUE, $link_type='', $link_id='', 
		$ed_type='', $ed_code='', $date='', $by='', $method='', $ref='') {

	if($by == '') $by = $_SESSION['authUserID'];
	if($date == '') $date = date('Y-m-d H:i:s');

	if(!$complete) $date = '0000-00-00 00:00:00';
  $sqlBindArray = array($pid,$link_type,$link_id,$ed_type,$ed_code,$date,
		$by,$method,$ref);
  sqlStatement("INSERT INTO `amc_ed_data_log` (`pid`,`link_type`,`link_id`,`ed_type`,`ed_code`,`date_provided`,`provided_by`,`method`,`reference`,`date_reated`) VALUES(?,?,?,?,?,?,?,?,?,NOW())", $sqlBindArray);

}

function amcEdRemove($id, $pid) {
  sqlStatement("DELETE FROM `amc_ed_data_log` WHERE `id`=? AND `pid`=?", array($id,$pid) );
}

function amcEdComplete($id, $by) {
  sqlStatement("UPDATE `amc_ed_data_log` SET `date_provided`=NOW(), `provided_by` = ? WHERE `id` = ?", array($by, $id) );
}

function amcEdUncomplete($id) {
  sqlStatement("UPDATE `amc_ed_data_log` SET `date_provided`='0000-00-00 00:00:00', `provided_by`=0 WHERE `id`=?", array($id) );
}

function amcEdExists($pid, $link_type, $link_id, $ed_type, $ed_code, $method) {
	$sql = 'SELECT id, date_provided FROM amc_ed_data_log WHERE pid=? AND ' .
		'link_type=? AND link_id=? AND ed_type=? AND ed_code=? AND method=?';
	$binds = array($pid, $link_type, $link_id, $ed_type, $ed_code, $method);
	$rrow = sqlQuery($sql, $binds);
	if(!isset($rrow{'date_provided'})) $rrow{'date_provided'} = '';
	return($rrow{'date_provided'});
}

function amcEdCollect($pid, $link_type, $link_id, $ed_type, $ed_code, $date, 
		$by = '', $method = '') {
	if(!$date) $date = date('Y-m-d');
	if($by === '') $by = $_SESSION['authUserID'];
	$sql = 'SELECT id, pid FROM amc_ed_data_log WHERE pid=? AND link_type=? AND link_id=? AND ed_type=? AND ed_code=? AND SUBSTRING(date_provided,1,10)=? AND provided_by=? AND method=?';
	$binds = array($pid, $link_type, $link_id, $ed_type, $ed_code, substr($date,0,10), $by, $method);
	$rrow = sqlQuery($sql, $binds);
	if(!isset($rrow{'id'})) $rrow{'id'} = '';
	return($rrow{'id'});
}

function amcEdMostRecent($pid, $ed_type, $ed_code, $method, $encounter = '') {
	$sql = 'SELECT amc_ed_data_log.id, date_provided AS date, provided_by, ' .
		'lname, fname, mname FROM amc_ed_data_log LEFT JOIN users ON ' .
		'(provided_by = users.id) WHERE pid = ? AND ed_type = ? AND ed_code = ? ' .
		'AND method = ? ';
	if($encounter) $sql .= 'AND link_id = ? AND link_type = "form_encounter" ';
	$sql .= 'ORDER BY date_provided DESC LIMIT 1';
	$binds = array($pid, $ed_type, $ed_code, $method);
	if($encounter) $binds[] = $encounter;
	$rrow = sqlQuery($sql, $binds);
	if($rrow) {
		$rrow{'full_name'} = $rrow{'fname'};
		if($rrow{'mname'}) $rrow{'full_name'} .= ' ' . $rrow{'mname'};
		$rrow{'full_name'} .= ' ' . $rrow{'lname'};
	}
	return($rrow);
}

?>
