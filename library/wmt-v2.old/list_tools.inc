<?php

if(!function_exists('BuildFilter')) {
function BuildFilter($filter = '', $mode='active') {
	if(substr($filter,0,1) != ' ') $fitler = ' '.$filter;
	$flds = sqlListFields('list_options');
	if($mode == 'active') $filter .= ' AND (UPPER(notes) '.
		'NOT LIKE "%DO NOT USE%") AND seq >= 0';
	if($mode == 'inactive') $filter .= ' AND ((UPPER(notes) '.
		'LIKE "%DO NOT USE%") || seq < 0)';
	if(in_array('activity', $flds)) {
		if($mode == 'active') $filter .= ' AND activity > 0';
		if($mode == 'inactive') $filter .= ' AND activity = 0';
	}
	return $filter;
}
}

if(!function_exists('LoadList')) {
function LoadList($thisList, $mode='active', $order='seq', $key='', $nt_filter='') {
	$filter = BuildFilter($nt_filter, $mode);
  $sql = 'SELECT * FROM list_options WHERE list_id=? '.
		$filter . ' ORDER BY ' . $order;
  $rlist = sqlStatement($sql, array($thisList));
	$all = array();
	$iter = 0;
  while($row = sqlFetchArray($rlist)) {
		if($key) {
			if($row{$key} != '') {
				$all[$row{$key}] = $row;
			} else {
				$all[$row{'option_id'}] = $row;
			}
		} else {
  		$all[$iter] = $row;
		}
		$iter++;
	}
	return $all;
}

function GetNextSequence($list) {
	if($list == 'lists') return 0;
	$sql = 'SELECT MAX(`seq`) as max FROM `list_options` WHERE list_id=?';
	$row = sqlQuery($sql, array($list));
	return ($row{'max'} + 10);
}

function AddToList($args, $auto_inc = true) {
	$fields = sqlListFields('list_options');
	if(in_array('active', $fields) && !isset($args['active'])) $args['active']=1;
	foreach($fields as $field) {
		if(!isset($args[$field])) $args[$field] = '';
	}
	if(!$args['option_id']) return false;
	if(!$args['list_id']) return false;
	if(!$args['title']) return false;
	if(!$args['seq'] && $auto_inc) 
		$args['seq'] = GetNextSequence($args['list_id']);
	$sql = 'INSERT INTO `list_options` SET ';
	$binds = array();
	$first = true;
	foreach($args as $key => $val) {
		if(!$first) $sql .= ', ';
		$first = false;	
		$sql .= "`$key`=?";
		$binds[] = $val;
	}
	$sql .= ' ON DUPLICATE KEY UPDATE `option_id`=?';
	$binds[] = $args['option_id'];
	// echo "This is our add: $sql<br>\n";
	// print_r($binds);
	// echo "<br>\n";
	sqlStatement($sql, $binds);
}

function GetListTitleByKey($key='', $list='', $mode = 'all', $filter = '') {
	if(!$key) return '';
	if(!$list) return '';
	$filter = BuildFilter($filter, $mode);
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=? $filter", array($key, $list));
	return($frow{'title'});	
}

function GetListCodeByKey($key='', $list='', $mode = 'all') {
	if(!$key) return '';
	if(!$list) return '';
	$filter = BuildFilter('', $mode);
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=? $filter", array($key, $list));
	return($frow{'codes'});	
}

function GetListNoteByKey($key='', $list='', $mode = 'all') {
	if(!$key) return '';
	if(!$list) return '';
	$filter = BuildFilter('', $mode);
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=? $filter", array($key, $list));
	return($frow{'notes'});	
}

function GetListSectionByKey($key='', $key_list='', $key_cats='') {
	if(!$key) return '';
	if(!$key_list) return '';
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=?", array($key, $key_list));
	$code = $frow{'notes'};
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=?", array($code, $key_cats));
	$desc = trim($frow{'title'});
	if($desc != '' && $frow{'notes'}) $desc .= ' - ';
	$desc .= $frow{'notes'};
	return($desc);	
}

}

?>
