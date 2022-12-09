<?php

$portal_data_layout = array(
'id' => '',
'pid' => '',
'form_name' => '',
'field_name' => '',
'pat_touch' => '',
'staff_touch' => '',
'doc_touch' => '',
'content' => '',
'pat_status' => '',
'staff_status' => '',
'doc_status' => '');

function addOrUpdatePortalData($pid, $data, $form, $field, $pstat = 1, $dstat = 0, $sstat = 0) {
	$sql = "SELECT * FROM wmt_portal_data WHERE pid=? AND form_name=? AND ".
						"field_name=?";
	$fres = sqlQueryNoLog($sql, array($pid, $form, $field));
	if(!$fres{'id'}) {
		$sql = "INSERT INTO wmt_portal_data (pid, form_name, field_name, " .
			"content, pat_touch, pat_status, staff_status, doc_status) VALUES " .
			"(?, ?, ?, ?, NOW(), ?, ?, ?)";
		$binds = array($pid, $form, $field, $data, $pstat, $sstat, $dstat);
		$new_id = sqlInsert($sql, $binds);
		return $new_id;
	}
	// No data change, no reset of the touch date or statuses
	if($data != $fres['content']) {
		$sql = "UPDATE wmt_portal_data SET pat_touch=NOW(), content=?, " .
			"pat_status=?, staff_status=?, doc_status=? WHERE pid=? AND ".
			"form_name=? AND field_name=?";
		$binds = array($data, $pstat, $sstat, $dstat, $pid, $form, $field);
		sqlStatement($sql, $binds);
	}
}

function getAllPortalFormContent($pid, $form) {
	$all = array();
	$sql = "SELECT * FROM wmt_portal_data WHERE pid=? AND form_name=?";
	$fres = sqlStatementNoLog($sql, array($pid, $form));
	while($frow = sqlFetchArray($fres)) {
		$all[$frow{'field_name'}] = $frow{'content'};
	}
	return($all);
}

function getAllPortalFormData($pid, $form) {
	$all = array();
	$sql = "SELECT * FROM wmt_portal_data WHERE pid=? AND form_name=?";
	$fres = sqlStatementNoLog($sql, array($pid, $form));
	$iter = 0;
	while($frow = sqlFetchArray($fres)) {
		$all[$iter] = $frow;
		$iter++;
	}
	return($all);
}

?>
