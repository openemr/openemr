<?php

/* LOAD CATEGORY DATA */
function getCategories($cat) {
	$layout_cats = array();
	$valid = false;

	$cat .= "_Forms";
	
	$query = "SELECT option_id AS cat_id, title AS cat_name FROM list_options ";
	$query .= "WHERE list_id = ? ORDER BY seq ";
	
	$result = sqlStatement($query, array($cat));
	while ($record = sqlFetchArray($result)) {
		$valid = true;
		$layout_cats[$record['cat_id']] = $record['cat_name'];
	}
	if (!$valid) die ("FATAL ERROR: Category [$cat] does not exist in [list_options] forms table.");
	
	return $layout_cats;
}

/* LOAD LAYOUT STRUCTURE */
function getLayoutList($key) {
	$layout_list = array();
	
	$query = "SELECT * FROM layout_options ";
	$query .= "WHERE form_id LIKE ? AND uor > 0 ";
	$query .= "ORDER BY group_name, seq ";

	$result = sqlStatement($query,array($key));
	while ($record = sqlFetchArray($result)) {
		$layout_list[] = $record;
	}
	
	return $layout_list;
}

/* LOAD LAYOUT DATA */
function getLayoutData($type,$group,$key) {
	$lbf_data = array();
	if ($key) { // current data record exists
		$lbf_record = sqlQuery("SELECT * FROM form_wcc_details WHERE parent_id = ".$key." AND form_id = '".$type."' AND group_name = '".$group."' " );
		$lbf_data['comments'] = $lbf_record['comments']; // special case
		for ($idx = 0; $idx < 40; $idx++) {
			if ($lbf_record['code'.$idx]) $lbf_data[$lbf_record['code'.$idx]] = $lbf_record['data'.$idx];
		}
	}
	return $lbf_data;
}

