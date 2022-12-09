<?php
$use_time = checkSettingMode('wmt::prgress_nt_time','',$frmdir);
$use_counselor = checkSettingMode('wmt::prgress_nt_counselor','',$frmdir);
$separate_users = checkSettingMode('wmt::progress_nt_multi_user','',$frmdir);
$rows = checkSettingMode('wmt::progress_nt_rows','',$frmdir);
if(!$rows) $rows = 5;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($nt_type)) $nt_type = 'progress';
$flds = sqlListFields('form_progress_nt');
foreach($flds as $fld) {
	if(substr($fld,0,4) != 'prg_') continue;
	if(!isset($dt[$fld])) $dt[$fld] = '';
}
if(!isset($dt[$field_prefix.'form_dt'])) $dt[$field_prefix.'form_dt'] = '';
if(!isset($dt['tmp_hist_prg_nt_disp'])) $dt['tmp_hist_prg_nt_disp'] = 'block';
if($form_mode != 'new' && $form_mode != 'update') {
	if($id) {
		unset($prg);
		$prg = array();
		$prg['encounter'] = $encounter;
		$prg['form_complete'] = $dt['form_complete'];
		$prg['form_priority'] = $dt['form_priority'];
		$prg['approved_by'] = $dt['approved_by'];
		$prg['approved_dt'] = $dt['approved_dt'];
		$prg['link_id'] = $id;
		$prg['link_form'] = $frmdir;
		$prg['form_dt'] = $dt['form_dt'];
		$prg['prg_nt_type'] = $nt_type;
		$len = iconv_strlen($field_prefix);
		$tlen = iconv_strlen($field_prefix.'prg_nt_');
		foreach($_POST as $k => $var) {
			if(substr($k,0,4) == 'tmp_') continue;
			if(substr($k,0,$tlen) != $field_prefix.'prg_nt_') continue;
			if(is_string($var)) $var = trim($var);
			if($len) $k = substr($k, $len);
			if($k) $prg[$k] = $var;
		}
		if(!isset($prg['prg_nt_counselor']) || !$prg['prg_nt_counselor']) 
			$prg['prg_nt_counselor'] = $_SESSION['authUserID'];
		if(!isset($prg['prg_nt_progress_flag'])) $prg['prg_nt_progress_flag'] = '';
		if(!isset($prg['prg_nt_late_flag'])) $prg['prg_nt_late_flag'] = '';
		$exists = sqlQuery('SELECT * FROM form_progress_nt WHERE pid=? AND '.
			'link_id=? AND link_form = ? AND prg_nt_type = ?', 
			array($pid, $id, $frmdir,$nt_type));
		if(!isset($exists{'id'})) $exists{'id'} = '';
		if($exists{'id'}) {
  		$binds = array($_SESSION['authProvider'], $_SESSION['authUser'],
							$_SESSION['userauthorized']);
  		$q1 = '';
  		foreach ($prg as $key => $val){
    		$q1 .= "$key=?, ";
				$binds[] = $val;
  		}
			$binds[] = $pid;
			$binds[] = $frmdir;
			$binds[] = $id;
			$binds[] = $nt_type;
  		sqlStatement('UPDATE form_progress_nt SET groupname=?, user=?, '.
						"authorized=?, activity=1, $q1 date=NOW() WHERE pid=? ".
						'AND link_form=? AND link_id=? AND prg_nt_type=?' , $binds);
		} else {
			if($prg['prg_nt_notes']) 
  				wmtFormSubmit('form_progress_nt', $prg,'',$_SESSION['userauthorized'],$pid);
		}
	}
}
if(($form_mode != 'save' || $continue) && $draw_display) {
	$sql = 'SELECT * FROM form_progress_nt WHERE pid = ? AND prg_nt_type = ? ';
	$binds = array($pid, $nt_type);
	if($dt['form_dt']) {
		$sql .= 'AND form_dt <= ? ';
		$binds[] = DateToYYYYMMDD($dt['form_dt']) . ' 00:00:00';
	}
	if($id) {
		$sql .= 'AND link_id != ? AND link_form != ? ';
		$binds[] = $id;
		$binds[] = $formdir;
	}
	$sql .= 'ORDER BY form_dt ASC';
	$res = sqlStatement($sql, $binds);
	$prior = array();
	while($row = sqlFetchArray($res)) {
		$text = substr($row{'form_dt'},0,10);
		if($row{'prg_nt_progress_flag'}) $text .= ' [ Case Note ]';
		if($row['prg_nt_converted']) {
			$text .= ' (Imported/Converted Note)';
		} else if($use_time) {
			if($row['prg_nt_start_hour'] != '') $text .= ' from '.
				$row{'prg_nt_start_hour'}.':'.$row{'prg_nt_start_min'}.
				' '.$row{'prg_nt_start_apm'};
			if($row['prg_nt_end_hour'] != '') $text .= ' to ' . 
				$row{'prg_nt_end_hour'} . ':' . $row{'prg_nt_end_min'} . ' ' .
				$row{'prg_nt_end_apm'};
		}
	if(!$use_counselor) {
		$tmp = wmtPrintVisit::getEncounter($row{'encounter'});
		$dr = $tmp->provider_full;
		// if($row{'prg_nt_counselor'}) $dr = UserDispNameFromID($row{'prg_nt_counselor'});
	} else $dr = UserDispNameFromID($row{'prg_nt_counselor'});
		$text .= ' By: '.$dr;
		if($row{'prg_nt_late_flag'}) $text .= ' ( Late Entry )';
		$prior[] = $text;
		$prior[] = $row{'prg_nt_notes'};
	}
	$class = 'wmtLabel';
	$nts_exist = true;
	if(!count($prior)) {
		$prior[] = 'No Prior Notes On File';
		$class = 'wmtBody';
		$nts_exist = false;
	}

	if($id) {
		$sql = 'SELECT * FROM form_progress_nt WHERE pid=? ';
		$sql .= 'AND link_id = ? AND link_form = ? AND prg_nt_type = ?';
		$binds = array($pid);
		$binds[] = $id;
		$binds[] = $frmdir;
		$binds[] = $nt_type;
		$res = sqlQuery($sql, $binds);
		foreach($flds as $fld) {
			if(substr($fld,0,7) != 'prg_nt_') continue;
			$dt[$field_prefix.$fld] = $res[$fld];
		}
	}
	
	?>
<div style="float: left; margin-left: 12px;">&nbsp;
	<span style="color: <?php echo $nts_exist ? 'green' : 'red'; ?>;"><?php echo $nts_exist ? '&oplus;' : '&odash;'; ?></span>
	<input name="tmp_hist_prg_nt_disp" id="tmp_hist_prg_nt_disp" type="checkbox" style="width:25px" <?php echo $dt['tmp_hist_prg_nt_disp'] == 'block' ? 'checked' : ''; ?> onchange="ToggleDivDisplay('hist_prg_nt_div', 'tmp_hist_prg_nt_disp');" value="<?php echo $dt['tmp_hist_prg_nt_disp']; ?>" />
	<label class="wmtLabel" for="tmp_hist_prg_nt_disp">Show / Hide Previous Notes</label>
</div>
<br>
<div style="float: left; width: 100%;" id="hist_prg_nt_div">
	<fieldset style="margin: 6px 12px 6px 12px; border: solid 1px gray;"><legend class="wmtLabel">&nbsp;Previous Notes&nbsp;</legend>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php foreach($prior as $entry) { ?>
			<tr>
				<td class="<?php echo $class; ?>" style="width: 100%;">
				<?php echo $entry; ?>
				</td>
			</tr>	
			<?php $class = ($class == 'wmtLabel') ? 'wmtBody' : 'wmtLabel'; ?>
			<?php } ?>
		</table>
	</fieldset>
	<input type="hidden" name="tmp_hist_prg_nt_disp" id="tmp_hist_prg_nt_disp" value="<?php echo $dt['tmp_hist_prg_nt_disp']; ?>" />
</div>
<br>

<div style="float: left; width: 100%;" id="hist_prg_nt_div">
	<fieldset style="margin: 6px 12px 6px 12px; border: solid 1px gray;"><legend class="wmtLabel">&nbsp;Current Note&nbsp;</legend>
   <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php if($use_time) { ?>
		<tr>
			<td class="wmtLabel" nowrap> Start Time: 
				<select name="<?php echo $field_prefix; ?>prg_nt_start_hour" id="<?php echo $field_prefix; ?>prg_nt_start_hour" class="wmtInput wmtR" style="width:50px" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> >
				<?php NumSel($dt[$field_prefix.'prg_nt_start_hour'], 1, 12, 1, '', TRUE, '--', '0', 2); ?>
				</select>
				:
				<select name="<?php echo $field_prefix; ?>prg_nt_start_min" id="<?php echo $field_prefix; ?>prg_nt_start_min" class="wmtInput wmtR" style="width:50px" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> >
				<?php NumSel($dt[$field_prefix.'prg_nt_start_min'], 0, 59, 1, '', TRUE, '--', 0, 2); ?>
				</select>
				<select name="<?php echo $field_prefix; ?>prg_nt_start_apm" id="<?php echo $field_prefix; ?>prg_nt_start_apm" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> class="wmtInput wmtR" style="width:50px">
					<option value="AM" <?php if ($dt[$field_prefix.'prg_nt_start_apm'] == 'AM') echo 'selected'?>>AM&nbsp;</option>
					<option value="PM" <?php if ($dt[$field_prefix.'prg_nt_start_apm'] == 'PM') echo 'selected'?>>PM&nbsp;</option>
				</select>
			</td>
			<td><div style="float: right; padding-right: 12px;"><a href='javascript:;' tabindex='-1' onclick='setTime("<?php echo $field_prefix; ?>", "start");'  class="css_button_small"><span>Set To Now</span></a></div>
			</td>

			<td class="wmtLabel" nowrap> End Time: 
				<select name="<?php echo $field_prefix; ?>prg_nt_end_hour" id="<?php echo $field_prefix; ?>prg_nt_end_hour" class="wmtInput wmtR" style="width:50px" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> >
				<?php NumSel($dt[$field_prefix.'prg_nt_end_hour'], 1, 12, 1, '', TRUE, '--', '0', 2); ?>
				</select>
				:
				<select name="<?php echo $field_prefix; ?>prg_nt_end_min" id="<?php echo $field_prefix; ?>prg_nt_end_min" class="wmtInput wmtR" style="width:50px" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> >
				<?php NumSel($dt[$field_prefix.'prg_nt_end_min'], 0, 59, 1, '', TRUE, '--', '0', 2); ?>
				</select>
				<select name="<?php echo $field_prefix; ?>prg_nt_end_apm" id="<?php echo $field_prefix; ?>prg_nt_end_apm" <?php echo ($dt[$field_prefix.'prg_nt_progress_flag'] || $dt[$field_prefix.'prg_nt_late_flag']) ? 'disabled' : ''; ?> class="wmtInput wmtR" style="width:50px">
					<option value="AM" <?php if ($dt[$field_prefix.'prg_nt_end_apm'] == 'AM') echo 'selected'?>>AM&nbsp;</option>
					<option value="PM" <?php if ($dt[$field_prefix.'prg_nt_end_apm'] == 'PM') echo 'selected'?>>PM&nbsp;</option>
				</select>
			</td>
			<td><div style="float: right; padding-right: 12px;"><a href='javascript:;' tabindex='-1' onclick='setTime("<?php echo $field_prefix; ?>", "end");'  class="css_button_small"><span>Set To Now</span></a></div>
			</td>
		</tr>
		<?php } ?>
									
		<?php if($frmdir == 'psy_exam1') { ?>
		<tr>
			<td class="wmtLabel" nowrap colspan="3">
				<input class="wmtCheck" type="checkbox" name="<?php echo $field_prefix; ?>prg_nt_progress_flag" id="<?php echo $field_prefix; ?>prg_nt_progress_flag" <?php echo $dt[$field_prefix.'prg_nt_progress_flag'] ? 'checked' : ''; ?> value="1" onclick="resetNoteTime(this, '<?php echo $field_prefix; ?>');" />
				Case Note
				<input class="wmtCheck" type="checkbox" name="<?php echo $field_prefix; ?>prg_nt_late_flag" id="<?php echo $field_prefix; ?>prg_nt_late_flag" style="margin-left:30px" <?php echo $dt[$field_prefix.'prg_nt_late_flag'] ? 'checked' : ''; ?> value="1" onclick="resetNoteTime(this, '<?php echo $field_prefix; ?>');" />
				Late Entry
			</td>
			<?php if($use_counselor) { ?>
			<td class="wmtLabel"nowrap>
				Clinician: 
				<select class="wmtInput" name='<?php echo $field_prefix; ?>prg_nt_counselor' id='<?php echo $field_prefix; ?>prg_nt_counselor' style="min-width:200px">
					<option value='_blank'>-- select --</option>
					<?php 
					$counselor = $dt[$field_prefix.'prg_nt_counselor'] ? 
								$dt[$field_prefix.'prg_nt_counselor'] : $_SESSION['authId'];

					$rlist= sqlStatement("SELECT * FROM users WHERE authorized=1 AND active=1 ORDER BY lname");
					while ($rrow= sqlFetchArray($rlist)) {
 						echo "<option value='" . $rrow['id'] . "'";
						if ($counselor == $rrow['id']) echo " selected";
						echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
 						echo "</option>";
 					}
					?>
				</select>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="5"><textarea name="<?php echo $field_prefix; ?>prg_nt_notes" id="<?php echo $field_prefix; ?>prg_nt_notes" class="wmtFullInput" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'prg_nt_notes'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>
  </table>
	</fieldset>
	<input type="hidden" name="tmp_prg_nt_disp_mode" id="tmp_prg_nt_disp_mode" value="<?php echo $dt['tmp_prg_nt_disp_mode']; ?>" />
	<input type="hidden" name="<?php echo $field_prefix; ?>prg_nt_converted" id="<?php echo $field_prefix; ?>prg_nt_converted" value="<?php echo $dt[$field_prefix.'prg_nt_converted']; ?>" />
</div>

<script type="text/javascript">
function resetNoteTime(chk, prefix) {
	<?php if($use_time) { ?>
	if(chk.checked) {
		document.getElementById(prefix+'prg_nt_start_hour').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_start_hour').disabled = true;	
		document.getElementById(prefix+'prg_nt_start_min').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_start_min').disabled = true;	
		document.getElementById(prefix+'prg_nt_start_apm').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_start_apm').disabled = true;	
		document.getElementById(prefix+'prg_nt_end_hour').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_end_hour').disabled = true;	
		document.getElementById(prefix+'prg_nt_end_min').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_end_min').disabled = true;	
		document.getElementById(prefix+'prg_nt_end_apm').selectedIndex = 0;	
		document.getElementById(prefix+'prg_nt_end_apm').disabled = true;	
	} else {
		document.getElementById(prefix+'prg_nt_start_hour').disabled = false;	
		document.getElementById(prefix+'prg_nt_start_min').disabled = false;
		document.getElementById(prefix+'prg_nt_start_apm').disabled = false;
		document.getElementById(prefix+'prg_nt_end_hour').disabled = false;
		document.getElementById(prefix+'prg_nt_end_min').disabled = false;
		document.getElementById(prefix+'prg_nt_end_apm').disabled = false;
	}
	<?php } ?>
}

function setTime(prefix, which) {
	var chk = document.getElementById(prefix+'prg_nt_progress_flag');
	if(chk != null) {
		if(chk.checked) return false;
	}
	var chk = document.getElementById(prefix+'prg_nt_late_flag');
	if(chk != null) {
		if(chk.checked) return false;
	}
	var hr = GetCurrentHour();
	var apm = 'AM';
	var apmIndex = 0;
	if(hr > 12) {
		hr = hr - 12;
		hr = '00'+hr;
		hr = hr.slice(-2);
		apm = 'PM';
		apmIndex = 1;
	}
	var mn = GetCurrentMinutes();
	mn = parseInt(mn) + 1;
	document.forms[0].elements[prefix+'prg_nt_'+which+'_hour'].selectedIndex = 
		parseInt(hr);
	document.forms[0].elements[prefix+'prg_nt_'+which+'_min'].selectedIndex = 
		parseInt(mn);
	document.forms[0].elements[prefix+'prg_nt_'+which+'_apm'].selectedIndex = 
		apmIndex;
}
</script>

<?php
}
?>
