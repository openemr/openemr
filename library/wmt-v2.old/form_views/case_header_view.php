<?php 
$local_fields = array('case_number', 'case_description', 'prior_auth_number',
	'provider_id', 'referral_source', 'cash', 'closed', 'comments', 'id', 
	'ins_data_id1', 'ins_data_id2', 'ins_data_id3', 'auto_accident', 
	'accident_state', 'other_accident', 'employment_related', 'illness',
	'emergency', 'is_unable_to_work', 'injury_date', 'first_consult_date',
	'similar_symptoms', 'similar_symptoms_date', 'off_work_from_date',
	'off_work_to_date', 'total_disability_from_date', 'total_disability_to_date',
	'partial_disability_from_date', 'partial_disability_to_date', 
	'is_hospitalized', 'hospitalization_date_from', 'hospitalization_date_to',
	'medicaid_resubmission_code', 'medicaid_original_reference', 'referring_id',
	'case_guarantor_pid', 'employer', 'similar_symptoms', 'case_dt');
include(FORM_BRICKS . 'module_setup.inc.php');

$sql = '';
$case = array();
if($frmdir == 'cases' && $id) {
	// WAS CALLED AS A STAND-ALONE
	$sql = 'SELECT * FROM form_cases WHERE id = ? AND pid = ?';
	$binds = array($id, $pid);
} else {
	// CALLED AS A MODULE FROM ANOTHER FORM
	// WOULD NEED TO CHECK FOR AN ENCOUNTER LINK HERE
}

// echo "Our ID [$id, $pid] and SQL ($sql)<br>\n";

if($sql) $case = sqlQuery($sql, $binds);

// echo "Our Result: ";
// print_r($case);
// echo "<br>\n";

if($case) {
	$dt['id'] = $case['id'];
	foreach($local_fields as $key) {
		$dt[$field_prefix . $key] = $case{$key};
		// echo "Set (" . $field_prefix . $key .") With Data [ " . $case[$key] . "]<br>";
	}
	if(!$dt[$field_prefix . 'illness']) $dt['tmp_injury'] = 1;
} else {
	$dt[$field_prefix . 'case_guarantor_pid'] = $pid;
	$dt['tmp_injury'] = '';
}

if($dt[$field_prefix . 'case_guarantor_pid'] == $pid) {
	$guar_name = '[ Self ]';
} else {
	$guar = sqlQuery('SELECT lname, fname, DOB FROM patient_data WHERE pid = ?', 
			array($dt[$field_prefix . 'case_guarantor_pid']));
	$guar_name = '[ ' . $guar['lname'] . ', ' . $guar['fname'] . ' DOB: ' . 
		oeFormatShortDate($guar['DOB']) . ' ]';
}
if(!$dt[$field_prefix . 'employer']) {
	$empl_name = '[ None ]';
} else {
	$empl_name = '[ '.OrganizationFromID($dt[$field_prefix . 'employer']).' ]';
}

unset($local_fields);
// SETUP FOR THE INSURANCE SELECTION DIV
$ins_disp_mode = 'style="display: block;"';
$empl_disp_mode = 'style="display: block;"';
if($dt[$field_prefix . 'cash']) $ins_disp_mode = 'style="display: none;"';
$policies = wmtPatData::getPidPoliciesByDate($pid, $dt['form_dt']);
$cnt = 1;
unset($ins);
unset($ins_data);
$ins = array();
$ins_data = array();
while($cnt < 4) {
	if($dt['ins_data_id'.$cnt]) {
		$ins[$cnt] = array('subscriber' => 
			$policies[$dt['ins_data_id'.$cnt]]['subscriber_lname'] . ', ' .
			$policies[$dt['ins_data_id'.$cnt]]['subscriber_fname'], 
			'policy' => $policies[$dt['ins_data_id'.$cnt]]['policy_number'], 
			'group' => $policies[$dt['ins_data_id'.$cnt]]['group_number'], 
			'effective' => $policies[$dt['ins_data_id'.$cnt]]['date']
		); 
	} else {
		$ins[$cnt] = array();
	}
	$cnt++;
}

if($draw_display) {
?>

<script type="text/javascript">
var ins_data = {};
<?php 
foreach($policies as $policy) {
	$ipr = htmlspecialchars($policy['subscriber_lname'], ENT_QUOTES) . 
			', ' . htmlspecialchars($policy['subscriber_fname'], ENT_QUOTES);
?>
var plan_data = {};
plan_data['subscriber'] = "<?php echo $ipr; ?>";
plan_data['policy'] = "<?php echo $policy['policy_number']; ?>";
plan_data['group'] = "<?php echo $policy['group_number']; ?>";
plan_data['effective'] = "<?php echo $policy['date']; ?>";
ins_data[<?php echo $policy['id']; ?>] = plan_data;
<?php
}
?>

function displayPolicy(ins_id)
{
	// alert('This ID : '+ins_id+'   And Should Be (ins_data_id'+ins_id+')');
	var sel = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+ins_id);
	var key = sel.options[sel.selectedIndex].value;
	// alert('This Value: '+key);
	var ins = ins_data[key];
  for( var item in ins ) {
		var val = ins[item];
		// alert('Item: ' + item + '  Value: ' + val);
		var span = document.getElementById('ins_' + item + ins_id);
		if(span != null) {
			while(span.firstChild) {
				span.removeChild(span.firstChild);
			}
			span.appendChild( document.createTextNode(val) );
		}	
	}
	// HERE WE COULD REMOVE THE SELECTED PLAN FROM THE OTHER LISTS IF NECESSARY
}

function setpatient(pid, lname, fname, dob)
{
	if(!pid) return false;
	document.getElementById('<?php echo $field_prefix; ?>case_guarantor_pid').value = pid;
	var n = '[ ' + lname + ', ' + fname + ' DOB: ' + dob + ' ]';
	var span = document.getElementById('tmp_case_guar_name');
	if(span != null) {
		while(span.firstChild) {
			span.removeChild(span.firstChild);
		}
		span.appendChild( document.createTextNode(n) );
	}	
}

function fetchUser(uid) {
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo AJAX_DIR_JS; ?>get_this.ajax.php",
		datatype: "html",
		data: {
			table: 'users',
			columns: 'organization^|street^|city^|state^|phone^|upin',
			keys: 'id^~'+uid
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem retrieving Lot # details\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: false
	});
	return output;
}

function setaddress(uid)
{
	if(!uid) return false;
	document.getElementById('<?php echo $field_prefix; ?>employer').value = uid;
	var dtl = fetchUser(uid);
	var dtls = dtl.split('~%');
	var n = '[ ' + dtls[0] + ' ]';
  // var n = dtls[0] + ' ' + dtls[1] + ' ' + dtls[4];
	// if(dtls.length > 5 && dtls[5]) n += '(Medisoft ID - ' + dtls[5] + ')';

	var span = document.getElementById('tmp_case_empl_name');
	if(span != null) {
		while(span.firstChild) {
			span.removeChild(span.firstChild);
		}
		span.appendChild( document.createTextNode(n) );
	}	
}

</script>

<?php if($frmdir != 'cases') { ?>
<input name="<?php echo $field_prefix; ?>case_dt" id="<?php echo $field_prefix; ?>case_dt" value="<?php echo htmlspecialchars(oeFormatShortDate($dt[$field_prefix . 'case_dt']), ENT_QUOTES); ?>" type="hidden" />
<?php } ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="label"><?php echo xl('Case'); ?> #:</td>
		<td <?php echo $dt[$field_prefix . 'case_number'] ? 'style="color: grey;"' : ''; ?>><?php echo $dt[$field_prefix . 'case_number'] ? htmlspecialchars($dt{$field_prefix . 'case_number'},ENT_QUOTES) : '[ New Case ]'; ?></td>
		<td class="label"><?php echo xl('Description'); ?>:</td>
		<td colspan="3"><input name="<?php echo $field_prefix; ?>case_description" id="<?php echo $field_prefix; ?>case_description" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt[$field_prefix . 'case_description'],ENT_QUOTES); ?>" title="Type in a short description of this case" /></td>
	</tr>
	<tr>
		<td style="width: 160px;" class="label"><?php echo xl('Provider'); ?>:</td>
		<td style="width: 240px;"><select name="<?php echo $field_prefix; ?>provider_id" id="<?php echo $field_prefix; ?>provider_id">
			<?php ProviderSelect($dt[$field_prefix . 'provider_id']); ?></select></td>
		<td style="width: 160px;" class="label"><?php echo xl('Guarantor PID'); ?> #:</td>
		<td style="width: 140px;"><input name="<?php echo $field_prefix; ?>case_guarantor_pid" id="<?php echo $field_prefix; ?>case_guarantor_pid" type="text" onclick="select_patient('<?php echo $GLOBALS['webroot']; ?>');" value="<?php echo htmlspecialchars($dt[$field_prefix . 'case_guarantor_pid'],ENT_QUOTES); ?>" /></td>
		<td colspan="2"><i><span id="tmp_case_guar_name"><?php echo htmlspecialchars($guar_name, ENT_QUOTES); ?></span></i></td>
	</tr>
	<tr>
		<td class="label"><?php echo xl('Prior Authorization'); ?> #:</td>
		<td><input name="<?php echo $field_prefix; ?>prior_auth_number" id="<?php echo $field_prefix; ?>prior_auth_number" type="text" value="<?php echo htmlspecialchars($dt[$field_prefix . 'prior_auth_number'],ENT_QUOTES); ?>" /></td>
		<td class="label"><?php echo xl('Employer'); ?> :</td>
		<td><input name="<?php echo $field_prefix; ?>employer" id="<?php echo $field_prefix; ?>employer" type="text" onclick="select_address('<?php echo $GLOBALS['webroot']; ?>', 'Employer');" value="<?php echo htmlspecialchars($dt[$field_prefix . 'employer'],ENT_QUOTES); ?>" /></td>
		<td colspan="4"><i><span id="tmp_case_empl_name"><?php echo htmlspecialchars($empl_name, ENT_QUOTES); ?></span></i></td>
	</tr>
	<tr>
		<td class="label"><?php echo xl('Referral Source'); ?>:</td>
		<td><select name="<?php echo $field_prefix; ?>referral_source" id="<?php echo $field_prefix; ?>referral_source"><?php ListSel($dt[$field_prefix . 'referral_source'], 'refsource'); ?></select></td>
		<td class="label"><label><input name="<?php echo $field_prefix; ?>cash" id="<?php echo $field_prefix; ?>cash" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'cash'] ? 'checked' : ''; ?> onchage="ToggleDivDisplay('case_header_ins', '<?php echo $field_prefix; ?>cash');" />&nbsp;&nbsp;<?php echo xl('Cash'); ?></label></td>
		<td class="label"><label><input name="<?php echo $field_prefix; ?>closed" id="<?php echo $field_prefix; ?>closed" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'closed'] ? 'checked' : ''; ?> />&nbsp;&nbsp;<?php echo xl('Closed'); ?></label></td>
	</tr>
	<tr>
		<td class="label"><?php echo xl('Notes'); ?>:</td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="<?php echo $field_prefix; ?>comments" id="<?php echo $field_prefix; ?>comments" class="wmtFullInput" rows="3"><?php echo htmlspecialchars($dt[$field_prefix . 'comments'], ENT_QUOTES); ?></textarea></td>
	</tr>
</table>

<div id="case_header_ins" <?php echo $ins_disp_mode; ?>>
<fieldset><legend>&nbsp;Insurance for this case&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<center>
	<tr>
		<td>&nbsp;</td>
		<td><?php echo xl('Company'); ?>&nbsp;</td>
		<td><?php echo xl('Policy'); ?>&nbsp;</td>
		<td><?php echo xl('Group'); ?>&nbsp;</td>
		<td><?php echo xl('Effective'); ?>&nbsp;</td>
		<td><?php echo xl('Subscriber'); ?>&nbsp;</td>
	</tr>
	</center>
	<?php 
	$cnt = 1;
	while($cnt < 4) {
	?>
		<tr>
			<td style="width: 16px;" class="label"><?php echo $cnt; ?>.&nbsp;</td>
			<td style="width: 20%;"><select name="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" id="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" class="wmtFullInput" onchange="displayPolicy(<?php echo $cnt; ?>);">
			<?php wmtPatData::pidPolicySelect($dt[$field_prefix . 'ins_data_id'.$cnt], $policies); ?>
			</select></td>
			<td><span id="ins_policy<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? htmlspecialchars($ins[$cnt]['policy'], ENT_QUOTES) : ''; ?>
</span></td>
			<td><span id="ins_group<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? htmlspecialchars($ins[$cnt]['group'], ENT_QUOTES) : ''; ?>
</span></td>
			<td><span id="ins_effective<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? htmlspecialchars($ins[$cnt]['effective'], ENT_QUOTES) : ''; ?>
</span></td>
			<td><span id="ins_subscriber<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? htmlspecialchars($ins[$cnt]['subscriber'], ENT_QUOTES) : ''; ?>
</span></td>
	<?php
		$cnt++;
	}
	?>
	</tr>
</table>
</fieldset>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="label"><label><input name="tmp_injury" id="tmp_injury" type="checkbox" value="1" <?php echo $dt['tmp_injury'] ? 'checked' : ''; ?> onclick="TogglePair('tmp_injury', '<?php echo $field_prefix; ?>illness');" />&nbsp;<?php echo xl('Injury'); ?></label>&nbsp;&nbsp;&nbsp;
		<label><input name="<?php echo $field_prefix; ?>illness" id="<?php echo $field_prefix; ?>illness" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'illness'] ? 'checked' : ''; ?> onclick="TogglePair('<?php echo $field_prefix; ?>illness', 'tmp_injury');" />&nbsp;<?php echo xl('Illness'); ?></label></td>
		<td class="label"><?php echo xl('Injury/Illness Date'); ?>:</td>
		<td><input name="<?php echo $field_prefix; ?>injury_date" id="<?php echo $field_prefix; ?>injury_date" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt[$field_prefix . 'injury_date']),ENT_QUOTES); ?>" title="Enter as <?php echo $date_title_fmt; ?>" />
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>injury_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="label"><?php echo xl('Initial Treatment Date'); ?>:</td>
		<td><input name="<?php echo $field_prefix; ?>first_consult_date" id="<?php echo $field_prefix; ?>first_consult_date" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt[$field_prefix . 'first_consult_date']),ENT_QUOTES); ?>" title="Enter as <?php echo $date_title_fmt; ?>" />
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>first_consult_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<td class="label"><label><input name="<?php echo $field_prefix; ?>similar_symptoms" id="<?php echo $field_prefix; ?>similar_symptoms" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'similar_symptoms'] ? 'checked' : ''; ?> />&nbsp;<?php echo xl('Same/Similar Symptoms?'); ?></label></td>
		<td class="label"><?php echo xl('Date'); ?>:</td>
		<td><input name="<?php echo $field_prefix; ?>similar_symptoms_date" id="<?php echo $field_prefix; ?>similar_symptoms_date" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt[$field_prefix . 'similar_symptoms_date']),ENT_QUOTES); ?>" title="Enter as <?php echo $date_title_fmt; ?>" />
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>similar_symptoms_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
	</tr>
	<tr>
		<td class="label"><label><input name="<?php echo $field_prefix; ?>auto_accident" id="<?php echo $field_prefix; ?>auto_accident" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'auto_accident'] == 1 ? 'checked' : ''; ?> onclick="TogglePair('<?php echo $field_prefix; ?>auto_accident', 'tmp_other_accident');" /><?php echo xl('Auto'); ?></label>&nbsp;&nbsp;&nbsp;
		<label><input name="<?php echo $field_prefix; ?>auto_accident" id="tmp_other_accident" type="checkbox" value="2" <?php echo $dt[$field_prefix . 'auto_accident'] == 2 ? 'checked' : ''; ?> onclick="TogglePair('tmp_other_accident', '<?php echo $field_prefix; ?>auto_accident');" /><?php echo xl('Other Accident'); ?></label></td>
		<td class="label"><?php echo xl('Accident State'); ?>:</td>
		<td><select name="<?php echo $field_prefix; ?>accident_state" id="<?php echo $field_prefix; ?>accident_state">
		<?php ListSel($dt[$field_prefix . 'accident_state'], 'state'); ?>
		</select></td>
		<td class="label"><?php echo xl('Other Accident Type'); ?>:</td>
		<td><input name="<?php echo $field_prefix; ?>other_accident" id="<?php echo $field_prefix; ?>other_accident" type="text" value="<?php echo htmlspecialchars($dt[$field_prefix . 'other_accident'],ENT_QUOTES); ?>" /></td>
		<td class="label" colspan="2"><label><input name="<?php echo $field_prefix; ?>employment_related" id="<?php echo $field_prefix; ?>employment_related" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'employment_related'] ? 'checked' : ''; ?> />&nbsp;&nbsp;<?php echo xl('Employment Related'); ?></label></td>
	</tr>
</table>

<script type="text/javascript">
Calendar.setup({inputField:"<?php echo $field_prefix; ?>injury_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>injury_dt"});
Calendar.setup({inputField:"<?php echo $field_prefix; ?>first_consult_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>first_consult_dt"});
Calendar.setup({inputField:"<?php echo $field_prefix; ?>similar_symptoms_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>similar_symptoms_dt"});
</script>

<?php 
} // END OF DRAW DISPLAY
?>
