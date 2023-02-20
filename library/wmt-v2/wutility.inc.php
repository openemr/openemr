<?php

function getFormatedDate($format = 'd-m-Y', $value) {
	if($value == '0000-00-00') {
		return $value;
	}

	return (isset($value) && !empty($value)) ? date($format, strtotime($value)) : '';
}

function getLayoutForm($rto_action) {
	$row = sqlQuery("SELECT * FROM layout_group_properties AS gp WHERE gp.grp_rto_action = ? LIMIT 1", array($rto_action));
	return $row;
}

function getLbfFromData($form_id) {
	$result = sqlStatement("SELECT * FROM lbf_data WHERE form_id = ? ", array($form_id));
	$data = array();
	while ($row = sqlFetchArray($result)) {
		$data[] = $row;
	}
	return $data;
}

function fetchAlertLogs($form_id) {
	$result = sqlStatement("SELECT fl.*, u.username as user_name  FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username WHERE fl.form_name = ? AND fl.form_id = ? ", array('form_rto',$form_id));

	$data = array();
	while ($row = sqlFetchArray($result)) {
		$data[] = $row;
	}
	return $data;
}

/* Exam2 */
function ext_general_exam2_module($pid) {
	global $dt;

	$px_sections = array(
		'gen' => 'ge_gen',
		'head' => 'ge_hd',
		'eyes' => 'ge_eye',
		'ears' => 'ge_earr',
		'nose' => 'ge_nose',
		'mouth' => 'ge_mouth',
		'throat' => 'ge_thrt',
		'neck' => 'ge_nk',
		'thyroid' => 'ge_thy',
		'lymph' => 'ge_lym',
		'breast' => 'ge_brr',
		'cardio' => 'ge_cr',
		'pulmo' => 'ge_pul',
		'gastro' => 'ge_gi',
		'neuro' => 'ge_neu',
		'musc' => 'ge_ms',
		'ext' => 'ge_ext',
		'dia' => 'ge_db',
		'test' => 'ge_te',
		'rectal' => 'ge_rc',
		'skin' => 'ge_skin',
		'psych' => 'ge_psych'
	);

	foreach ($px_sections as $px_section => $px_field) {
		$checked = 0;
		foreach ($dt as $dt_k => $dt_value) {
			if(substr($dt_k,0,strlen($px_field)) == $px_field) {
				if(!empty($dt_value)) {
					$checked = 1;
				}
			}
		}

		if($checked === 1) {
			$dt['tmp_ge_'.$px_section] = $checked;
			$dt['tmp_ge_'.$px_section.'_disp'] = 'block';
		} else if($checked === 0){
			$dt['tmp_ge_'.$px_section] = $checked;
			$dt['tmp_ge_'.$px_section.'_disp'] = 'none';
		}

	}

}

function ext_process_after_fetch($pid) {
	global $dt, $img, $surg, $hosp, $pmh, $fh, $diag, $allergies, $meds, $imm, $meds, $diag, $med_hist;

	$filedList = array(
		'img_dt',
		'ps_begdate',
		'dg_begdt',
		'hosp_dt',
		'ps_begdate',
		'dg_enddt'
	);

	$sections = array(
		'img' => array(
			'img_dt',
		),
		'surg' => array(
			'begdate',
			'enddate'
		),
		'hosp' => array(
			'begdate',
			'enddate'
		),
		'fh' => array(
			'begdate',
			'enddate'
		),
		'imm' => array(
			'begdate',
			'enddate',
			'administered_date'
		),
		'pmh' => array(
			'begdate',
			'enddate'
		),
		'allergies' => array(
			'begdate',
			'enddate'
		),
		'meds' => array(
			'begdate',
			'enddate'
		),
		'med_hist' => array(
			'begdate',
			'enddate'
		),
		'diag' => array(
			'begdate',
			'enddate'
		)
	);

	foreach ($sections2 as $sk => $sItem) {
		if(isset(${$sk})) {
			foreach (${$sk} as $k => $value) {
				foreach ($sItem as $fk => $field) {
					if(isset(${$sk}[$k][$field])) {
						${$sk}[$k][$field] = getFormatedDate(DateFormatRead("jquery-datetimepicker"), $value[$field]);
					}
				}
			}
		}
	}

	foreach ($dt as $k => $value) {
		foreach ($filedList2 as $f) {
			if(substr($k,0,strlen($f)) == $f) {
				$dt[$k] = getFormatedDate(DateFormatRead("jquery-datetimepicker"), $value);
			}
		}
	}
}
/* End */

/* RTO */

function getRtoLayoutFormData($pid, $rto_id) {
	$row = sqlQuery("SELECT * FROM form_order_layout AS fol WHERE fol.pid = ? AND fol.rto_id = ? LIMIT 1", array($pid, $rto_id));
	return $row;
}

function manageAction($pid, $rto_id, $id) {
	sqlStatement("DELETE FROM form_order_layout WHERE pid = ? AND rto_id = ? ", array($pid, $rto_id));
	sqlStatement("DELETE FROM lbf_data WHERE form_id = ? ", array($id));
}

function rtoBeforeSave() {
	global $dt, $cnt, $newordermode, $pid, $rto_data_bup;

	if($newordermode !== true) {
		return;
	}

	$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
	$rto_action = isset($dt['rto_action_'.$cnt]) ? $dt['rto_action_'.$cnt] : '';
	
	$layoutData = getLayoutForm($rto_action);
	$layoutFormData = getRtoLayoutFormData($pid, $rto_id);

	if(empty($layoutData)) {
		$layout_form_id = isset($layoutFormData['form_id']) ? $layoutFormData['form_id'] : '';
		$formdir = isset($layoutFormData['grp_form_id']) ? $layoutFormData['grp_form_id'] : '';
		$formname = isset($layoutFormData['grp_title']) ? $layoutFormData['grp_title'] : '';

		manageAction($pid, $rto_id, $layout_form_id);
	}

	if(isset($dt['rto_id_'.$cnt])) {
		$rto_data =  !empty($rto_data_bup) ? $rto_data_bup[0] : array();
		$fieldList = array(
			'rto_action'  => 'rto_action',
			'rto_status'  => 'rto_status',
			'rto_ordered_by' => 'rto_ordered_by',
			'rto_resp_user' => 'rto_resp',
			'rto_notes' => 'rto_notes'
		);

		foreach($fieldList as $key => $fieldItem) {
			$oldV = isset($rto_data[$key]) ? $rto_data[$key] : '';
			$newV = isset($dt[$fieldItem.'_'.$cnt]) ? $dt[$fieldItem.'_'.$cnt] : '';

			$isNeedToLog = ($newV !== $oldV) ? true : false;

			if($isNeedToLog === true) {
				$sql = "INSERT INTO `form_value_logs` ( field_id, form_name, new_value, old_value, pid, form_id, username ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
				sqlInsert($sql, array(
					$key,
					"form_rto",
					$newV,
					$oldV,
					$pid,
					$dt['rto_id_'.$cnt],
					$_SESSION['authUserID']
				));
			}
		}
	}
}

function getLBFFormData($rto_id, $pid, $rtoData, $layoutData) {
	global $newordermode;
	
	if($newordermode !== true) {
		return;
	}

	$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
	$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
	$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';
	$formData = getLbfFromData($form_id);

	if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
		$lformname = $layoutData['grp_form_id'];
		
		ob_start();
		// Use the form's report.php for display.  Forms with names starting with LBF
		// are list-based forms sharing a single collection of code.
		//
		if (substr($form_dir, 0, 3) == 'LBF') {
			include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

			call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
		} else {
			include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
			call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
		} 
		$summeryOutput = ob_get_clean();

		?>
		<div>
			<div class="summeryContainer" data-toggle="tooltip" title="">
				<?php echo $summeryOutput; ?>
			</div>
		</div>

		<button type="button" class="css_button_small lbfbtn" onClick="open_ldf_form('<?php echo $pid; ?>', '<?php echo $lformname; ?>', '<?php echo $rto_id; ?>', '<?php echo $form_id; ?>','<?php echo $form_title; ?>')"><?php echo xlt('Read More'); ?></button>
		<br/>
		<br/>
		<?php
	}

	if(!empty($rto_id) && 1 != 1) {
	?>
	<button type="button" class="css_button_small lbfbtn lbfviewlogs" onClick="open_view_logs('<?php echo $pid; ?>', '<?php echo $rto_id; ?>')"><?php echo xlt('View logs'); ?></button>
	<?php
	}
}

/* End */

function getRTOSummary($rto_id, $pid, $rto_data = array()) {
	$rtoData = getRtoLayoutFormData($pid, $rto_id);
	$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
	$order_action = !empty($rto_data['rto_action']) ? $rto_data['rto_action'] : '';

	$layoutData = getLayoutForm($order_action);
	$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
	$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';

	if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
		$lformname = $layoutData['grp_form_id'];
		?>
		<div class="summeryContainer">
		<?php
			// Use the form's report.php for display.  Forms with names starting with LBF
			// are list-based forms sharing a single collection of code.
			//
			if (substr($form_dir, 0, 3) == 'LBF') {
				include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

				call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
			} else {
				include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
				call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
			}
		?>
		</div>
		<?php
	} else {
		echo htmlspecialchars($rto_data['rto_notes'],ENT_QUOTES);
	}

}

function getImagingOrdersSummary($rto_id, $pid, $rto_data = array()) {
	$rtoData = getRtoLayoutFormData($pid, $rto_id);
	$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
	$order_action = !empty($rto_data['rto_action']) ? $rto_data['rto_action'] : '';

	$layoutData = getLayoutForm($order_action);
	$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
	$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';
	?>
	<div class='rto_note_container'>
	<input type="checkbox" class="read-more-state" id="order-note-<?php echo $rto_id; ?>" />
	
	<?php
	if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
		$lformname = $layoutData['grp_form_id'];
		?>
		<div class="content summeryContainer" data-toggle="tooltip" title="">
		<?php
			// Use the form's report.php for display.  Forms with names starting with LBF
			// are list-based forms sharing a single collection of code.
			//
			if (substr($form_dir, 0, 3) == 'LBF') {
				include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

				call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
			} else {
				include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
				call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
			}
		?>
		</div>
		<?php
	} else {
		?>
		<div class="content summeryNoteContainer" data-toggle='tooltip' title=''>
			<div><?php echo htmlspecialchars($rto_data['rto_notes'],ENT_QUOTES); ?></div>
		</div>
		<?php
	}

	?>	
		<div class="actBtn">
			<label for="order-note-<?php echo $rto_id; ?>" class="readmore css_button" role="button">Read More</label>
			<label for="order-note-<?php echo $rto_id; ?>" class="lessmore css_button" role="button" >Read Less</label>
		</div>
	</div>
	<?php

}

/* Log data*/
function saveOrderLog($type = '', $rtoId = '', $relationId = '', $sentTo = '', $pid = '', $operation = '', $createdBy = '') {
	$sql = "INSERT INTO `rto_action_logs` ( type, rto_id, foreign_id, sent_to, pid, operation, created_by ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
	$responce = sqlInsert($sql, array(
		$type,
		$rtoId,
		$relationId,
		$sentTo,
		$pid,
		$operation,
		$createdBy
	));

	return $responce;
}


function getInternalNote($noteId) {
	$result = sqlQuery("SELECT * FROM `rto_action_logs` WHERE type = ? AND foreign_id = ? ORDER BY created_date DESC LIMIT 1", array("INTERNAL_NOTE", $noteId));
	return $result;
}

function addMessageNote($pid, $noteid) {
	if(!empty($noteid)) {
		$internalData = getInternalNote($noteid);

		if(!empty($internalData)) {
			$type = "INTERNAL_NOTE";
			$createdBy = $_SESSION['authUserID'];
			$relation_id = isset($noteid) && !empty($noteid) ? $noteid : NULL;
			$operationType = 'Forwarded';
			$orderId = isset($internalData['rto_id']) ? $internalData['rto_id'] : NULL;

			saveOrderLog($type, $orderId, $relation_id, NULL, $pid, $operationType, $createdBy);
		}
	}
}

function addMessageOrderLog($pid, $type = '', $orderList = array(), $msgLogId = '', $to = '') {
	if(!empty($orderList)) {
		foreach ($orderList as $oi => $orderItem) {
			$orderId = isset($orderItem['order_id']) ? $orderItem['order_id'] : "";
			if(!empty($orderId)) {
				$type = $type;
				$createdBy = $_SESSION['authUserID'];
				$relation_id = isset($msgLogId) && !empty($msgLogId) ? $msgLogId : NULL;
				$operationType = 'Sent';

				saveOrderLog($type, $orderId, $relation_id, $to, $pid, $operationType, $createdBy);
			}
		}
	}
}
/* End */