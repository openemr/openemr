<?php 
if(!isset($form_mode)) $form_mode = 'new';
$frm_flds = sqlListFields($frmn);
array_slice($frm_flds, 7);
foreach($frm_flds as $fld) {
	$dt[$fld] = '';
}
if(!isset($dt['form_complete'])) $dt['form_complete'] = '';

if($form_mode == 'update') {
  // $save_style = "/forms/$frmdir/new.php?mode=save&id=$id&enc=$encounter".
		// "&pid=$pid";
  $dt = sqlQuery("SELECT * FROM $frmn WHERE id=?",array($id));

} else if($form_mode == 'new' && $first_pass) {
	if(!$noload) {
		$binds = array($frmdir, $pid, 0);
		$old = sqlQuery('SELECT form_id, formdir, SUBSTR(fe.date,1,10) AS dos '.
			'FROM forms AS f LEFT JOIN form_encounter AS fe USING (encounter) '.
			'WHERE formdir = ? AND f.pid = ? AND deleted = ? '.
			'ORDER BY fe.date DESC LIMIT 1', $binds); 
		if(!isset($old{'form_id'})) $old{'form_id'} = '';
		if($old{'form_id'}) {
			unset($dt);
			$dt = formFetch($frmn, $old{'form_id'});
		} else {
			// THERE MAY BE MODULE DATA ATTACHED TO THE ENCOUNTER WITHOUT A FORM
			// THESE STYLE FORMS HAVE THESE COLUMNS
			if(in_array('link_id', $frm_flds) && in_array('link_form', $frm_flds)) {
				$binds = array($pid, $encounter, 'encounter');
				$old = sqlQuery("SELECT id, date FROM $frmn WHERE pid = ? AND ".
					'link_id = ? AND link_form = ? ORDER BY date DESC LIMIT 1', $binds); 
				if(!isset($old{'id'})) $old{'id'} = '';
				if($old{'id'}) {
					unset($dt);
					$dt = formFetch($frmn, $old{'id'});
				}
			}	
		}
	}

} else if($form_mode == 'cancel') {
	$addr = "0";
	if($frmdir == 'cases') {
		$addr = $GLOBALS['rootdir'] . '/forms/cases/case_list.php?mode=' . 
			$list_mode . '&pid=' . $pid;
		if($list_popup) $addr .= '&popup=' . $list_popup;
	}
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>Redirecting.....</title>\n";
	echo '<script type="text/javascript" src="' . 
		$GLOBALS['webroot'] . '/library/restoreSession.php"></script>';
	echo "</head>\n";

	formJump($addr);

} else {
	unset($data);
	unset($pat);
	$data = array();
	$pat = array();

	$draw_display = FALSE;
	foreach($modules as $module) {
		unset($chp_options);
		$chp_options = array();
		if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
		if(!isset($chp_options[0])) $chp_options[0] = '';
		if(!isset($chp_options[1])) $chp_options[1] = '';
		$this_module = $module['option_id'];
		if($chp_options[0]) $this_module = $chp_options[0];
		$field_prefix = $chp_options[1];
		// IS THERE A FORM SPECIFIC MODULE?
		if(is_file('./pre_process/' . $this_module . '_pre.php')) {
			include('./pre_process/' . $this_module . '_pre.php');
		} else if(is_file(FORM_PREPROCESS . $this_module . '_pre.php')) {
			include(FORM_PREPROCESS . $this_module . '_pre.php');
		}
	}

	  // echo "Processing $frmdir - with data: ";
	  // print_r($_POST);
	  // echo "<br>\n";
	foreach ($_POST as $k => $var) {
  	if($var == 'YYYY-MM-DD') $var = NULL;
		if(is_string($var)) $var = trim($var);
		if(substr($k,-3) == '_dt' || substr($k,-4) == '_dob' ||
			(substr($k,-5) == '_date')) {
			$var = DateToYYYYMMDD($var);
  		if($var == '0000-00-00') $var = NULL;
		}
		$dt[$k] = $var;
  	if(($k == 'pid') || ($k == 'date')) continue;
		if(in_array($k, $frm_flds)) $data[$k] = $var;
	}
	  // echo "Form Process Array After Post ($id): ";
	  // print_r($data);
	  // echo "<br>\n";

  if($encounter == '') $encounter = date('Ymd');
	if($data['form_dt'] == '' || $data['form_dt'] == 0) {
		$data['form_dt'] = date('Y-m-d');
	} 
	// else $data['form_dt'] = DateToYYYYMMDD($data['form_dt']);

	// FIX - BUILD A MODULE FOR SENDING AND LOGGING FORM RELATED NOTES
	// if(isset($_POST['tmp_notify_doc'])) {
		// $anl['anl_notify_dr'] = 1;	
	// }

	if(in_array('link_id', $frm_flds)) $data['link_id'] = $encounter;

	if(strtolower($data['form_complete']) == 'a') {
		$data['approved_by'] = $_SESSION['authUser'];
		$data['approved_dt'] = date('Y-m-d H:i:s');
	}

	if(!$id || $id == '') {
		if(in_array('created', $frm_flds)) $data['created'] = date('Y-m-d H:i:s');
  	$newid = wmtFormSubmit($frmn,$data,'',0,$pid);
		$id = $newid;
		if(!checkSettingMode('wmt::suppress_forms_entry','',$frmdir)) {
  		addForm($encounter,$ftitle,$newid,$frmdir,$pid,
					$_SESSION['userauthorized']);
		}

  	$_SESSION['encounter'] = $encounter;
		$log = "INSERT FORM $frmdir MODE [$form_mode] ($pid) '$encounter' " .
			"SAVED NEW FORM ID ($id)";
		if($form_event_logging) auditSQLEvent($log, TRUE);
		// echo "Added ($id)<br>\n";

	} elseif ($id) {
		$sql = "UPDATE $frmn SET `pid` = ?, `groupname` = ?, `user` = ?, ".
				"`authorized` = ?, `activity` = ?, ";
		$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'], 
				$_SESSION['userauthorized'], 1);
		foreach ($data as $key => $val) {
  		$sql .= "`$key` = ?, ";
			$binds[]= $val;
		}
		$sql .= "`date` = NOW() WHERE `id` = ?";
		$binds[]= $id;
		sqlStatement($sql, $binds);
		$log = "INSERT FORM $frmdir MODE [$form_mode] ($pid) '$encounter' " .
			"UPDATED FORM ID ($id)";
		if($form_event_logging) auditSQLEvent($log, TRUE);
		// echo "Updated ($id)<br>\n";
	}

	$draw_display = FALSE;
	foreach($modules as $module) {
		unset($chp_options);
		$chp_options = array();
		if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
		if(!isset($chp_options[0])) $chp_options[0] = '';
		$this_module = $module['option_id'];
		if($chp_options[0]) $this_module = $chp_options[0];
		// IS THERE A FORM SPECIFIC MODULE?
		if(is_file('./post_process/' . $this_module . '_post.php')) {
			include('./post_process/' . $this_module . '_post.php');
		} else if(is_file(FORM_PROCESS . $this_module . '_post.php')) {
			include(FORM_PROCESS . $this_module . '_post.php');
		}
	}
	$draw_display = TRUE;

	// FIX - HERE IS WHERE WE WOULD SEND THE ACTUAL NOTE REGARDING THE FORM
	// if(isset($_POST['tmp_notify_doc'])) {
		// $link = "<a href='".$GLOBALS['rootdir']."/forms/$frmdir/view.php?id=$id&pid=$pid&enc=$encounter' tabindex='-1' target='_blank'>Click here to review</a>";
		// $text = "New Analysis Results Exists - ".$link;
		// addPnote($pid, $text, $_SESSION['userauthorized"'], '1', 'Analysis', 'PAhlering');	
	// }

	if($wrap_mode == 'new' && !$continue) {

	}

  if($continue) { 
		$save_style="/forms/$frmdir/new.php?mode=save&id=$id&pid=$pid".
			"&enc=$encounter";
		$base_action .= "&id=$id";

	} else {
		if(strtolower($data['form_complete']) == 'a') {
			$tst = FormInRepository($pid, $encounter, $id, $frmn);
			if(!$tst) {
				ob_start();
				$rpt_func = $frmdir . '_report';
				$rpt_func($pid, $encounter, "*", $id, true);
				$content = ob_get_contents();
				ob_end_clean();
				$log = "INSERT FORM $frmdir MODE [$form_mode] ($pid) '$encounter:$id'" .
					" ADDING TO REPOSITORY";
				if($form_event_logging) auditSQLEvent($log, TRUE);
				AddFormToRepository($pid, $encounter, $id, $frmn, $content);
			} else {
				echo "This form appears to have been previously archived!<br/>\n";
				echo "Please report this incident to Technical Support!! (Pretty Please)<br/>\n";
				exit;
			}
		}
		// RPG - Take this exit out!
		// exit;
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Redirecting.....</title>\n";
		if($pop_form) {
			echo "\n<script type='text/javascript'>";
			echo "function showNewReport() {\n";
			echo "  if(null == opener) {\n";
			echo "		ploc= top.location;\n";
			echo "		return false;\n";
			echo "	}\n";
			echo "  var ploc = opener.location;\n";
			echo "  var res = String(ploc).match(/patient_file\/encounter\/forms.php/);\n";
			echo "  alert('First Res: '+res);\n";
			echo "	if(null == res) {\n";
			echo "    var res = String(ploc).match(/interface\/forms\/cases\/case_list.php/);\n";
			echo "  }\n";
			echo "	if(null != res) {\n";
			echo "    alert('Got The Right Location');\n";
			echo "		opener.location.reload();\n";
			echo "	}\n";
			echo "}\n";
			echo "showNewReport();";
			echo "window.close();</script>\n";
			exit;
		} else {
			echo '<script type="text/javascript" src="' . 
				$GLOBALS['webroot'] . '/library/restoreSession.php"></script>';
			echo "</head>\n";
			$addr = "0";
			if($frmdir == 'cases') {
				$addr = $GLOBALS['rootdir'] . '/forms/cases/case_list.php?mode=' . 
					$list_mode . '&pid=' . $pid;
				if($list_popup) $addr .= '&popup=' . $list_popup;
				if($caller) $addr .= '&caller=' . $caller;
			}
			formJump($addr);
		}
	}
}
?>