<?php
unset($case);
$case = array();
 // echo "Case Pre and prefix is ($field_prefix)<br>\n";
foreach($_POST as $key => $val) {
	if(substr($key,0,4) == 'tmp_' || substr($key,0,4) == 'pat_') continue;
	if(substr($key,0,4) == 'fyi_' || substr($key,0,4) == 'db_') continue;
	if(is_string($val)) $val = trim($val);
	if($key == 'case_id') {
		$case['id'] = $val;
		unset($_POST['case_id']);
		continue;
	}
	if(!$field_prefix) continue;
	if($field_prefix && 
			(substr($key,0,strlen($field_prefix)) != $field_prefix)) continue;
	if(substr($key, -5) == '_date') $val = DateToYYYYMMDD($val);
	if(substr($key, -3) == '_dt') $val = DateToYYYYMMDD($val);
	$case[substr($key,strlen($field_prefix))] = $val;
	// THIS IS NECESSARY FOR PROCESSING WHEN CALLED AS A FORM
	// THE VALUE NEEDS TO BE PASSED TO THE MAIN PROCESSING ROUTINE
	if($frmdir == 'cases') $_POST[substr($key,strlen($field_prefix))] = $val;
	unset($_POST[$key]);
}
if(!isset($_POST['form_complete'])) $_POST['form_complete'] = '';
if(!isset($_POST['auth_req'])) $_POST['auth_req'] = '';
if(!isset($_POST['cash'])) $_POST['cash'] = '';
if(!isset($_POST['closed'])) $_POST['closed'] = '';
if(!isset($_POST['bc_stat'])) $_POST['bc_stat'] = '';
if($_POST['cash']) {
	$cnt = 1;
	while($cnt < 4) {
		$_POST['ind_data_id'.$cnt] = 0;
		$cnt++;
	}
}

   // echo "Case After Pre-Process: ";
   // print_r($case);
   // echo "<br>\n";
   // echo "POST After Pre-Process: ";
   // print_r($_POST);
   // echo "<br>\n";

if($frmdir == 'cases') {
	$case['id'] = $id;
	$case['case_dt'] = DateToYYYYMMDD($_POST['form_dt']);
	$_POST['case_dt'] = $_POST['form_dt'];
	$case['form_dt'] = DateToYYYYMMDD($_POST['form_dt']);
} else {
	// IN CASE IT'S NOT STAND ALONE
	if($case['id']) {
 		$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
 		$q1 = '';
 		foreach ($case as $key => $val){
			if($key == 'id') continue;
   		$q1 .= "`$key` = ?, ";
			$binds[] = $val;
 		}
		$binds[] = $case['id'];
 		sqlInsert('UPDATE `form_cases` SET `pid` = ?, `groupname` = ?, ' .
			'`user`=?, `authorized` = ?, `activity` = ?, ' . $q1 . '`date` = NOW() ' .
			'WHERE `id`=?', $binds);
	} else {
		unset($case['id']);
 		$newid = 
			wmtFormSubmit('form_cases',$case,'',$_SESSION['userauthorized'],$pid);
		$id = $newid;
	}
}
?>