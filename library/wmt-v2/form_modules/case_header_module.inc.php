<?php 
$local_fields = array('case_description', 'prior_auth_number',
	'provider_id', 'referral_source', 'cash', 'closed', 'comments', 'id', 
	'ins_data_id1', 'ins_data_id2', 'ins_data_id3', 'auto_accident', 
	'accident_state', 'other_accident', 'employment_related', 'illness',
	'emergency', 'is_unable_to_work', 'injury_date', 'first_consult_date',
	'similar_symptoms', 'similar_symptoms_date', 'off_work_from_date',
	'off_work_to_date', 'total_disability_from_date', 'total_disability_to_date',
	'partial_disability_from_date', 'partial_disability_to_date', 
	'is_hospitalized', 'hospitalization_date_from', 'hospitalization_date_to',
	'medicaid_resubmission_code', 'medicaid_original_reference', 'referring_id',
	'case_guarantor_pid', 'employer', 'similar_symptoms', 'case_dt', 'notes');
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

if($case) {
	$dt['case_id'] = $case['id'];
	foreach($local_fields as $key) {
		$dt[$field_prefix . $key] = $case{$key};
		// echo "Set (" . $field_prefix . $key .") With Data [ " . $case[$key] . "]<br>";
	}
	if(!$dt[$field_prefix . 'illness']) $dt['tmp_injury'] = 1;
} else {
	$dt[$field_prefix . 'case_guarantor_pid'] = $pid;
	$dt['tmp_injury'] = '';
	$dt['case_id'] = '';
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
globalKeyTest($GLOBALS['wmt::limit_case_ins']); 
if($GLOBALS['wmt::limit_case_ins']) {
	$policies = wmtPatData::getPidPoliciesByDate($pid, $dt['form_dt']);
} else {
	$policies = wmtPatData::getPidPolicies($pid);
}
$cnt = 1;
unset($ins);
unset($ins_data);
$ins = array();
$ins_data = array();
$ins_error_msg = '';
$ins_error_cnt = 0;
while($cnt < 4) {
	if($dt['ins_data_id'.$cnt]) {
		foreach($policies as $policy) {
			if($policy['id'] == $dt['ins_data_id'.$cnt]) {
				$ins[$cnt] = array('subscriber' => 
					$policy['subscriber_lname'] . ', ' .
					$policy['subscriber_fname'], 
					'policy' => $policy['policy_number'], 
					'group' => $policy['group_number'], 
					'effective' => $policy['date']
				); 
			}
		}
	} else {
		$ins[$cnt] = array();
	}
	$cnt++;
}
if($ins_error_cnt == 1) {
	$ins_error_msg = 'Policy (' . $ins_error_msg . ') is';
} else if($ins_error_cnt > 1) {
	$ins_error_msg = 'Policies (' . $ins_error_msg . ') are';
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

function sel_guarantor() {
  dlgopen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/find_patient_popup.php', 'findPatient', 650, 300, '', 'Guarantor Search');
}

function sel_address(type) {
  var href =  '<?php echo $GLOBALS['webroot']; ?>/interface/usergroup/addrbook_list.php?popup=true&select=true';
	if(type) href += '&form_abook_type=' + type;
  dlgopen(href, 'findAddress', 650, 600, '', 'EmployerGuarantor  Search');
}

function setCaseGuarantor(pid, lname, fname, dob)
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
				alert('There was a problem retrieving that address\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: false
	});
	return output;
}

async function fetchPolicy(id) {
	const result = await $.ajax({
		type: "POST",
		url: "<?php echo AJAX_DIR_JS; ?>policy_fetch.ajax.php",
		datatype: "json",
		data: {
			policy_id: id,
			pid: '<?php echo $pid; ?>'
		}
	});
	return result;
}

async function showPolicy(ins) {
	var sel = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+ins);
	var policy_id = sel.options[sel.selectedIndex].value;
	const policy = await fetchPolicy(policy_id);
	var jsonPolicy = $.parseJSON(policy);
	var tags = {'policy' : 'policy_number', 'group' : 'group_number',
		'effective' : 'date', 'subscriber' : 'subscriber_full_name'};

  for (var key in tags) {
		var span = document.getElementById('ins_' + key + ins);
		if(span != null) {
			while(span.firstChild) {
				span.removeChild(span.firstChild);
			}
			span.appendChild( document.createTextNode(jsonPolicy[tags[key]]) );
		}	
	}
  var ins_type = jsonPolicy['ins_type_code'];
  // THESE TWO TYPES ARE HARD CODED AS THEY ARE EMPLOYER PROVIDED INS TYPES
  if(ins_type == 23 || ins_type == 25) {
		var empl = document.getElementById('<?php echo $field_prefix; ?>employer').value;
		if(!empl || empl == '0') {
      alert('You MUST specify an employer for that plan type');
		}
  }
}

function setaddress(uid)
{
	if(!uid) return false;
	document.getElementById('<?php echo $field_prefix; ?>employer').value = uid;
	var dtl = fetchUser(uid);
	var dtls = dtl.split('~%');
	var n = '[ ' + dtls[0] + ' ]';

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
<div class="input-group">
<input name="<?php echo $field_prefix; ?>case_dt" id="<?php echo $field_prefix; ?>case_dt" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'case_dt'])); ?>" type="hidden" />
<?php } ?>
<table width="100%" class="table-condensed" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td><b><?php echo xl('Case'); ?> #:</b></td>
		<td <?php echo $dt[$field_prefix . 'id'] ? 'style="color: grey;"' : ''; ?>><?php echo $dt[$field_prefix . 'id'] ? text($dt{$field_prefix . 'id'}) : '[ New Case ]'; ?><input name="case_id" id="case_id" type="hidden" tabindex="-1" value="<?php echo $dt['case_id']; ?>" /></td>
		<td><b><?php echo xl('Description'); ?>:</b></td>
		<td colspan="3"><input name="<?php echo $field_prefix; ?>case_description" id="<?php echo $field_prefix; ?>case_description" class="form-control" type="text" value="<?php echo attr($dt[$field_prefix . 'case_description']); ?>" title="Type in a short description of this case" /></td>
	</tr>
	<tr>
		<td><b><?php echo xl('Provider'); ?>:</b></td>
		<td><select name="<?php echo $field_prefix; ?>provider_id" id="<?php echo $field_prefix; ?>provider_id" class="form-control">
			<?php ProviderSelect($dt[$field_prefix . 'provider_id']); ?></select></td>
		<td><b><?php echo xl('Guarantor PID'); ?> #:</b></td>
		<td><input name="<?php echo $field_prefix; ?>case_guarantor_pid" id="<?php echo $field_prefix; ?>case_guarantor_pid" type="text" class="form-control input-sm" onclick="<?php echo ($v_major > 4 && ($v_minor || $v_patch)) ? 'sel_guarantor();' : "select_patient('" . $GLOBALS['webroot'] . "');"; ?>" value="<?php echo attr($dt[$field_prefix . 'case_guarantor_pid']); ?>" /></td>
		<td colspan="2"><i><span id="tmp_case_guar_name"><?php echo text($guar_name); ?></span></i></td>
	</tr>
	<tr>
		<td><b><?php echo xl('Referral Source'); ?>:</b></td>
		<td><select name="<?php echo $field_prefix; ?>referral_source" id="<?php echo $field_prefix; ?>referral_source" class="form-control"><?php ListSel($dt[$field_prefix . 'referral_source'], 'refsource'); ?></select></td>
		<td><b><?php echo xl('Employer'); ?> :</b></td>
		<td><input name="<?php echo $field_prefix; ?>employer" id="<?php echo $field_prefix; ?>employer" class="form-control input-sm" type="text" onclick="<?php echo ($v_major > 4 && ($v_minor || $v_patch)) ? "sel_address('Employer');" : "select_address('" . $GLOBALS['webroot'] ."', 'Employer');"; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix . 'employer'],ENT_QUOTES); ?>" /></td>
		<td colspan="4"><i><span id="tmp_case_empl_name"><?php echo htmlspecialchars($empl_name, ENT_QUOTES); ?></span></i></td>
	</tr>
	<tr>
		<td><label><input name="<?php echo $field_prefix; ?>cash" id="<?php echo $field_prefix; ?>cash" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'cash'] ? 'checked' : ''; ?> onchange="ToggleDivHide('case_header_ins', '<?php echo $field_prefix; ?>cash'); clear_case_insurance(this);" />&nbsp;&nbsp;<?php echo xl('Cash'); ?></label></td>
		<td><label><input name="<?php echo $field_prefix; ?>closed" id="<?php echo $field_prefix; ?>closed" type="checkbox" value="1" <?php echo $dt[$field_prefix . 'closed'] ? 'checked' : ''; ?> />&nbsp;&nbsp;<?php echo xl('Inactive'); ?></label></td>
		<td><b><?php echo xl('Referring Provider'); ?>:</b></td>
		<td><select name="<?php echo $field_prefix; ?>referring_id" id="<?php echo $field_prefix; ?>referring_id">
			<?php ReferringSelect($dt[$field_prefix . 'referring_id']); ?></select></td>
	</tr>
	<tr>
		<td><b><?php echo xl('Notes'); ?>:</b></td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="<?php echo $field_prefix; ?>comments" id="<?php echo $field_prefix; ?>comments" class="form-control" rows="3"><?php echo htmlspecialchars($dt[$field_prefix . 'comments'], ENT_QUOTES); ?></textarea></td>
	</tr>
</table>

<table width="100%" class="table-condensed" border="0" cellspacing="2" cellpadding="2">
	<tr>
		<td><b><?php echo xl('Injury/Illness Date'); ?>:</b></td>
		<td><input name="<?php echo $field_prefix; ?>injury_date" id="<?php echo $field_prefix; ?>injury_date" type="text" class="datepicker form-control" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'injury_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>" />
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>injury_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
<?php } ?>
		<td><b><?php echo xl('Initial Treatment Date'); ?>:</b></td>
		<td><input name="<?php echo $field_prefix; ?>first_consult_date" id="<?php echo $field_prefix; ?>first_consult_date" type="text" class="datepicker form-control" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'first_consult_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>" />
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>first_consult_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
<?php } ?>
		<td><b><?php echo xl('Accident State'); ?>:</b></td>
		<td><select name="<?php echo $field_prefix; ?>accident_state" id="<?php echo $field_prefix; ?>accident_state" class="form-control">
		<?php ListSel($dt[$field_prefix . 'accident_state'], 'state'); ?>
		</select></td>
	</tr>
</table>

<div id="case_header_ins" <?php echo $ins_disp_mode; ?>>
<h3>&nbsp;Insurance for this case&nbsp;</h3>
<table width="100%" class="table-condensed" border="0" cellspacing="2" cellpadding="2">
	<center>
	<?php if($ins_error_msg) { ?>
	<tr style="color: red;">
		<td>**</td>
		<td colspan="5">Error Condition: <?php echo $ins_error_msg; ?> no longer valid by date</td>
	</tr>
	<?php } ?>
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
			<td style="width: 16px;"><b><?php echo $cnt; ?>.&nbsp;</b></td>
			<td style="width: 20%;"><select name="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" id="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" class="form-control" onchange="showPolicy(<?php echo $cnt; ?>);">
			<?php wmtPatData::pidPolicySelect($dt[$field_prefix . 'ins_data_id'.$cnt], $policies); ?>
			</select></td>
			<td><span id="ins_policy<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['policy']) : ''; ?>
</span></td>
			<td><span id="ins_group<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['group']) : ''; ?>
</span></td>
			<td><span id="ins_effective<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['effective']) : ''; ?>
</span></td>
			<td><span id="ins_subscriber<?php echo $cnt; ?>">
<?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['subscriber']) : ''; ?>
</span></td>
	<?php
		$cnt++;
	}
	?>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><a class="large_modal css_button" id="add_policy_btn" tabindex="-1" href="<?php echo $GLOBALS['webroot']; ?>/custom/policy_popup.php?pid=<?php echo $pid; ?>&callback=set_case_insurance" ><span>Add A Policy</span></a></td>
		<!-- td><a class="medium_modal css_button" tabindex="-1" onclick="addPolicy('<?php // echo $GLOBALS['webroot']; ?>', 'set_case_policies');" href="javascript:;" ><span>Add A Policy</span></a></td -->
	</tr>
	<tr>
		<td colspan="2"><?php echo xl('Email Addresses'); ?>:</td>
		<td colspan="4"><i>**  <?php echo xl('Please use a comma to separate multiple addresses'); ?></i></td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="<?php echo $field_prefix; ?>notes" id="<?php echo $field_prefix; ?>notes" class="form-control" rows="3"><?php echo attr($dt[$field_prefix . 'notes']); ?></textarea></td>
	</tr>
</table>
</fieldset>
</div>
</div>

<script type="text/javascript">
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
alert('Case Calendar');
Calendar.setup({inputField:"<?php echo $field_prefix; ?>injury_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>injury_dt"});
Calendar.setup({inputField:"<?php echo $field_prefix; ?>first_consult_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>first_consult_dt"});
<?php } ?>

function add_policy(base, callback_func)
{
  base += '/custom/policy_popup.php?callback=' + callback_func;
	wmtOpen(base, '_blank', '80%', '80%'); 
}

$(document).ready(function(){
    tabbify();

<?php if($v_major > 4 && ($v_minor || $v_patch)) { ?>
		$('.datepicker').datetimepicker({
      <?php $datetimepicker_timepicker = false; ?>
      <?php $datetimepicker_showseconds = false; ?>
      <?php $datetimepicker_formatInput = true; ?>
    	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
		});
<?php } ?>

    $("#add_policy_btn").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        let title = '<?php echo xla('Insurance Policy Management'); ?>';
				var case_dt = '';
				if(document.getElementById('form_dt').value != '') 
						case_dt = '&case_dt=' + document.getElementById('form_dt').value;
		    var url = '<?php echo $GLOBALS['webroot']; ?>/custom/policy_popup.php?pid=<?php echo $pid; ?>&callback=set_case_insurance' + case_dt;
		
        dlgopen(url, 'policyPop', 'modal-xl', 'modal-xl', '', title);
			/*
        dlgopen('', '', 'modal-xl', 'modal-xl', '', title, {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href') + case_dt
        });
			*/
    });

});

function set_case_insurance(newInsurance) {
	var policies = Object.entries(newInsurance);
	for(var i = 1; i < 4; i++) {
		var sel = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+i);
		// console.log('Testing [<?php echo $field_prefix; ?>ins_data_id'+i+']');
		// console.log('Select: '+sel);
		if(sel) {
			var chosen_policy_id = sel.options[sel.selectedIndex].value;
			// MAKE SURE THAT THE PREVIOUSLY CHOSEN POLICY IS STILL A POLICY
			if (chosen_policy_id) {
				var found = false;
				for (const [policy_id, policy] of policies) {
					if(chosen_policy_id == policy.id) {
						found = true;
						break;
					}
				}
				if(!found) alert('WARNING - Policy ('+i+') Is No Longer Valid');
			}
			sel.options.length = 0;
			sel.options.add(new Option('', '') );
			for (const [policy_id, policy] of policies) {
				sel.options.add(new Option(policy.name, policy.id) );
			}
			if(chosen_policy_id) {
				for(var o = 0; o < sel.options.length; o++) {
					if(sel.options[o].value == chosen_policy_id) sel.selectedIndex = o;
				}
			}
		}
	}
}

function clear_case_insurance(chk) {
	if(!chk.checked) return true;

	for(var i = 1; i < 4; i++) {
		var s = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+i);
		if(s && s != null) s.selectedIndex = 0;
		var s = document.getElementById('<?php echo $field_prefix; ?>ins_policy'+i);
		if(s && s != null) s.innerHTML = '&nbsp;';
		var s = document.getElementById('<?php echo $field_prefix; ?>ins_group'+i);
		if(s && s != null) s.innerHTML = '&nbsp;';
		var s = document.getElementById('<?php echo $field_prefix; ?>ins_effective'+i);
		if(s && s != null) s.innerHTML = '&nbsp;';
		var s = document.getElementById('<?php echo $field_prefix; ?>ins_subscriber'+i);
		if(s && s != null) s.innerHTML = '&nbsp;';
	}
	return true;
}

</script>

<?php 
} // END OF DRAW DISPLAY
?>