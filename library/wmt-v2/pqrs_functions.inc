<?php

function GenerateHiddenPQRS($id, $chc='') {
	$chc = trim($chc);
	if(!$chc) return '';
	echo "			<tr>\n";
	echo "				<td><input name='$id' id='$id'  type='hidden' value='".htmlspecialchars($chc, ENT_QUOTES)."' /></td>\n";
	echo "			</tr>\n";
}

function GetPQRSTitleByKey($key='', $list = 'PQRS_Keys') {
	if(!$key) return '';
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=?", array($key, $list));
	return($frow{'title'});	
}

function GetPQRSNoteByKey($key='', $list = 'PQRS_Keys') {
	if(!$key) return '';
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=?", array($key, $list));
	return($frow{'notes'});	
}

function GetPQRSSectionByKey($key='', $key_list='PQRS_Keys', $key_cats='PQRS_Sections') {
	if(!$key) return '';
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

function GetPQRSGroupByKey($key='', $list = 'PQRS_Keys') {
	if(!$key) { return ''; }
	$frow = sqlQuery("SELECT title, notes, codes FROM list_options WHERE ".
		"option_id=? AND list_id=?", array($key, $list));
	return($frow{'notes'});	
}

?>
