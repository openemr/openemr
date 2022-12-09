<?php

function InjectionSel($thisField, $thisList) {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE list_id =? AND ".
			"notes NOT LIKE '%DO NOT USE%' ORDER BY seq", array($thisList));
  echo "<option value=''";
  if(!$thisField) echo " selected='selected'";
  echo ">&nbsp;</option>";
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . 
				htmlspecialchars($rrow['option_id'],ENT_QUOTES,'',false) . "'";
    if($thisField == $rrow['option_id']) echo " selected='selected'";
    echo ">" . htmlspecialchars($rrow['option_id'],ENT_QUOTES,'',false) . 
							" - " . htmlspecialchars($rrow['title'],ENT_QUOTES,'',false);
    echo "</option>";
  }
}

function InjectionLook($thisField, $thisList) {
  if(!$thisField) return("[Not Specified]");
	$parms = array($thisList, $thisField);
  $rrow = sqlQuery("SELECT * FROM list_options WHERE " .
         "list_id=? AND option_id=?", $parms);
  $test1 = htmlspecialchars(trim($rrow['option_id']),ENT_QUOTES,'',false);
  $test2 = htmlspecialchars(trim($rrow['title']),ENT_QUOTES,'',false);
  if(!empty($test1) || !empty($test2)) return($test1." - ".$test2);
  return("[Not Specified]");
}

function InjectionImmCodeLook($thisField, $thisList) {
  if(!$thisField) return false;
	$parms = array($thisList, $thisField);
  $rrow = sqlQuery("SELECT * FROM list_options WHERE " .
         "list_id=? AND option_id=?", $parms);

	$flds = sqlListFields('list_options');
	if(in_array('codes',$flds)) {
		if($rrow['codes']) {
  		$imm=trim($rrow['codes']);
		} else {
			$imm=trim($rrow['notes']);
		}
	} else {
		$imm=trim($rrow['notes']);
	}
  return $imm;
}

function GetInjections($pid, $cols='*') {
  $sql = "SELECT form_inject1.*, forms.formdir, forms.deleted FROM ".
			"form_inject1 LEFT JOIN forms ON (forms.form_id = form_inject1.id) ".
			"AND (forms.formdir = 'inject1') WHERE form_inject1.pid=? AND deleted=0 ".
			"ORDER BY form_dt DESC";

	$all = array();
  $res = sqlStatement($sql, array($pid));
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function AddImmunization($pid, $code=0, $data=array() ) {
	if(!$pid) return false;
	if(!$code) return false;
	$dt_time_mode = false;
	$fres = sqlStatement("SHOW COLUMNS FROM immunizations");
	while($frow = sqlFetchArray($fres)) {
		foreach($frow as $row) {
			if(strtolower($row{'Field'}) == 'administered_date') {
				if(strtolower($row{'Type'}) == 'datetime') $dt_time_mode = true;
			}
		}
	}
	$adm_date = $data['form_dt'];
	if(!$dt_time_mode) $adm_date = substr($data['form_dt'],0,10);
	$sql = "SELECT id, administered_date, cvx_code FROM immunizations ".
			"WHERE patient_id=? AND cvx_code=? AND administered_date=? LIMIT 1";
	$parms= array($pid, $code, $adm_date); 
	$frow= sqlQuery($sql, $parms);
	// This must be a duplicate
	if($frow{'id'}) return false;
	$flds = sqlListFields('immunizations');
	if(!$data['ij1_vis_stmt']) $data['ij1_vis_stmt'] = substr($data['form_dt'],0,10);
	if(!$data['ij1_vis_date']) $data['ij1_vis_date'] = substr($data['form_dt'],0,10);
	$sql = 'INSERT INTO immunizations (patient_id, administered_date, '.
		'immunization_id, cvx_code, lot_number, administered_by_id, '.
		'education_date, vis_date, note, create_date, update_date, created_by, '.
		'manufacturer';
	if(in_array('amount_administered', $flds)) $sql .= ', amount_administered';
	if(in_array('amount_administered_unit', $flds)) $sql .= ', amount_administered_unit';
	if(in_array('administration_site', $flds)) $sql .= ', administration_site';
	if(in_array('route', $flds)) $sql .= ', route';
	if(in_array('expiration_date', $flds)) $sql .= ', expiration_date';
	$sql .= ') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?';
	if(in_array('amount_administered', $flds)) $sql .= ', ?';
	if(in_array('amount_administered_unit', $flds)) $sql .= ', ?';
	if(in_array('administration_site', $flds)) $sql .= ', ?';
	if(in_array('route', $flds)) $sql .= ', ?';
	if(in_array('expiration_date', $flds)) $sql .= ', ?';
	$sql .= ')';
	$parms= array($pid, $adm_date, 0, $code, $data['ij1_lot'], 
		$_SESSION['authUserID'], $data['ij1_vis_stmt'], $data['ij1_vis_date'], 
		$data['ij1_observation'], $_SESSION['authUserID'], 
		$data['ij1_manufacturer']);
	if(in_array('amount_administered', $flds)) $parms[] = $data['ij1_dose'];
	if(in_array('amount_administered_unit', $flds)) $parms[] = $data['ij1_dose_unit'];
	if(in_array('administration_site', $flds)) $parms[] = $data['ij1_site'];
	if(in_array('route', $flds)) $parms[] = $data['ij1_route'];
	if(in_array('expiration_date', $flds)) $parms[] = $data['ij1_expire'];
	$newid = sqlInsert($sql, $parms);
}

function TranslateNDCUnit($uom=0) {
	if(!$uom) return '';
	$ndc_uom = 'UN';
	if($uom == 1) $ndc_uom = 'ME';
	if($uom > 1 && $drug{'unit'} < 7) $ndc_uom = 'ML';
	if($uom == 8) $ndc_uom = 'GR';
	return $ndc_uom;
}
?>
