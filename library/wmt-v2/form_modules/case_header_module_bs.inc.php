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
	'case_guarantor_pid', 'employer', 'similar_symptoms', 'case_dt', 'notes', 'lb_date', 'lb_notes', 'auth_req', 'auth_start_date', 'auth_end_date', 'auth_num_visit', 'auth_provider', 'bc_date', 'bc_notes', 'bc_notes_dsc', 'bc_stat', 'liability_payer_exists', 'auth_notes');
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

				//Address
				if(isset($policy['provider'])) {
					$address_str = array();
					$icobj = new InsuranceCompany($policy['provider']);
					$adobj = $icobj->get_address();

					if (trim($adobj->get_line1())) {
				        if(!empty($adobj->get_line1())) {
				        	$address_str[] = htmlspecialchars($adobj->get_line1(), ENT_NOQUOTES);
				      	}
				        $address_str[] = htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(), ENT_NOQUOTES);
				    }

				   $address_str[] = htmlspecialchars(xl('PH'), ENT_NOQUOTES) . ': ' . htmlspecialchars($icobj->get_phone(), ENT_NOQUOTES);
				}

				$ins[$cnt] = array('subscriber' => 
					$policy['subscriber_lname'] . ', ' .
					$policy['subscriber_fname'], 
					'policy' => $policy['policy_number'], 
					'group' => $policy['group_number'], 
					'effective' => $policy['date'],
					'address_str' => isset($address_str) && !empty($address_str) ? implode("\n", $address_str) : ''
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

<?php if($frmdir != 'cases') { ?>
<div class="input-group">
<input name="<?php echo $field_prefix; ?>case_dt" id="<?php echo $field_prefix; ?>case_dt" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'case_dt'])); ?>" type="hidden" />
</div>
<?php } ?>

<div>
	<div class="form-row">
    <div class="form-group col-md-6">
      <label for="case_id"><?php echo xl('Case'); ?> #:</label>
      <span class="form-control disabled"><?php echo $dt[$field_prefix . 'id'] ? text($dt[$field_prefix . 'id']) : '[ New Case ]'; ?></span>
      <input name="case_id" id="case_id" type="hidden" class="form-control" value="<?php echo $dt['case_id']; ?>">
    </div>
    <div class="form-group col-md-6">
      <label><?php echo xl('Description'); ?>:</label>
      <input type="text" name="<?php echo $field_prefix; ?>case_description" id="<?php echo $field_prefix; ?>case_description" class="form-control" placeholder="Description" value="<?php echo attr($dt[$field_prefix . 'case_description']); ?>" title="Type in a short description of this case">
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-6">
      <label><?php echo xl('Provider'); ?>:</label>
      <select class="form-control" name="<?php echo $field_prefix; ?>provider_id" id="<?php echo $field_prefix; ?>provider_id">
		    <?php ProviderSelect($dt[$field_prefix . 'provider_id']); ?>
		  </select>
    </div>
    <div class="form-group col-md-6">
      <label><?php echo xl('Guarantor PID'); ?> #:</label>
      <div class="forminput-inline-field">
      	<input type="text" name="<?php echo $field_prefix; ?>case_guarantor_pid" id="<?php echo $field_prefix; ?>case_guarantor_pid" class="form-control" placeholder="Guarantor PID" onclick="<?php echo ($v_major > 4) ? 'sel_guarantor();' : "select_patient('" . $GLOBALS['webroot'] . "');"; ?>" value="<?php echo attr($dt[$field_prefix . 'case_guarantor_pid']); ?>">
      	<i><span class="field-text-info ml-2" id="tmp_case_guar_name"><?php echo text($guar_name); ?></span></i>
      </div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-6">
      <label><?php echo xl('Referral Source'); ?>:</label>
      <select name="<?php echo $field_prefix; ?>referral_source" id="<?php echo $field_prefix; ?>referral_source" class="form-control">
		    <?php ListSel($dt[$field_prefix . 'referral_source'], 'refsource'); ?>
		  </select>
    </div>
    <div class="form-group col-md-6">
      <label><?php echo xl('Employer'); ?>:</label>
      <div class="forminput-inline-field">
      	<input type="text" name="<?php echo $field_prefix; ?>employer" id="<?php echo $field_prefix; ?>employer" class="form-control" placeholder="Employer" onclick="<?php echo ($v_major > 4) ? "sel_address('Employer');" : "select_address('" . $GLOBALS['webroot'] ."', 'Employer');"; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix . 'employer'],ENT_QUOTES); ?>">
      	<i><span class="field-text-info ml-2" id="tmp_case_empl_name"><?php echo htmlspecialchars($empl_name, ENT_QUOTES); ?></span></i>
      </div>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-6">
    	<div class="d-inline-block">
		    <div class="form-check">
		      <input type="checkbox" class="form-check-input" name="<?php echo $field_prefix; ?>cash" id="<?php echo $field_prefix; ?>cash" value="1" <?php echo $dt[$field_prefix . 'cash'] ? 'checked' : ''; ?> onchange="ToggleDivHide('case_header_ins', '<?php echo $field_prefix; ?>cash'); clear_case_insurance(this);">
		      <label class="form-check-label"><?php echo xl('Cash'); ?></label>
		    </div>
		  </div>	

		  <div class="d-inline-block ml-4">
		    <div class="form-check">
		      <input type="checkbox" class="form-check-input" name="<?php echo $field_prefix; ?>closed" id="<?php echo $field_prefix; ?>closed" value="1" <?php echo $dt[$field_prefix . 'closed'] ? 'checked' : ''; ?> >
		      <label class="form-check-label"><?php echo xl('Inactive'); ?></label>
		    </div>
		  </div>	

		  <!-- CM -->
		  <div class="d-inline-block ml-4">
		    <div class="form-check">
		    	<script type="text/javascript">let checked_mode = "";let unchecked_mode = "";</script>
		      <input class="form-check-input auth_req" type="checkbox" name="<?php echo $field_prefix; ?>auth_req" id="<?php echo $field_prefix; ?>auth_req" value="1" <?php echo $dt[$field_prefix . 'auth_req'] ? 'checked' : ''; ?> onchange="ToggleDivDisplay('case_header_auth_req_container', '<?php echo $field_prefix; ?>auth_req');">
		      <label class="form-check-label"><?php echo xl('Authorization Required'); ?></label>
		    </div>
		  </div>	
		  <!-- END -->
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-12">
    	<label for="validationCustom01"><?php echo xl('Notes'); ?>:</label>
      <textarea class="form-control" placeholder="Notes" name="<?php echo $field_prefix; ?>comments" id="<?php echo $field_prefix; ?>comments" rows="3" ><?php echo htmlspecialchars($dt[$field_prefix . 'comments'], ENT_QUOTES); ?></textarea>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group col-md-4">
    	<label><?php echo xl('Injury/Illness Date'); ?>:</label>
      <input type="text" class="form-control injury_datepicker" name="<?php echo $field_prefix; ?>injury_date" id="<?php echo $field_prefix; ?>injury_date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'injury_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>" placeholder="Injury/Illness Date">
      <?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
				<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>injury_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>">
			<?php } ?>
    </div>
    <div class="form-group col-md-4">
    	<label><?php echo xl('Initial Treatment Date'); ?>:</label>
      <input type="text" class="form-control injury_datepicker" name="<?php echo $field_prefix; ?>first_consult_date" id="<?php echo $field_prefix; ?>first_consult_date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'first_consult_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>" placeholder="Initial Treatment Date">
      <?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_<?php echo $field_prefix; ?>first_consult_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>">
			<?php } ?>
    </div>
    <div class="form-group col-md-4">
    	<label><?php echo xl('Accident State'); ?>:</label>
      <select name="<?php echo $field_prefix; ?>accident_state" id="<?php echo $field_prefix; ?>accident_state" class="form-control">
		    <?php ListSel($dt[$field_prefix . 'accident_state'], 'state'); ?>
		  </select>
    </div>
  </div>

  <!-- Payer Section -->
  <div id="case_header_ins" <?php echo $ins_disp_mode; ?> class="form-row mt-4" >
    <div class="col-md-12">
    	<div class="card">
    		<div class="card-header">
		      <h6 class="mb-0 d-inline-block"><?php echo xl('Payer for this case'); ?></h6>
		    </div>
    		<div class="card-body px-2 py-2">

    			<div class="form-row strow">

    				<?php
    				$cnt = 1;
						while($cnt < 4) {
    				?>

	    				<div class="col-md-6 col-lg-4">
			    			<div class="jumbotron jumbotron-fluid px-3 py-3 mb-2 h-100">
			    				
			    				<div>
		  							<label><?php echo $cnt; ?>). Payer</label>
			    					<select class="form-control ins-dropdown" name="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" id="<?php echo $field_prefix; ?>ins_data_id<?php echo $cnt; ?>" data-id="<?php echo $cnt; ?>" onchange="showPolicy(<?php echo $cnt; ?>);">
									    <?php wmtPatData::pidPolicySelect($dt[$field_prefix . 'ins_data_id'.$cnt], $policies); ?>
									  </select>
			    				</div>

			    				<div class="mt-2 c-font-size-sm">
			    					<div>
			    						<span><?php echo xl('Address'); ?>: </span> 
			    						<span id="ins_full_address<?php echo $cnt; ?>"><?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['address_str']) : 'Empty'; ?></span>
			    					</div>
			    					<div>
			    						<span><?php echo xl('Policy'); ?>: </span> 
			    						<span id="ins_policy<?php echo $cnt; ?>"><?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['policy']) : 'Empty'; ?></span></div>
			    					<div>
			    						<span><?php echo xl('Group'); ?>: </span>
			    						<span id="ins_group<?php echo $cnt; ?>"><?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['group']) : 'Empty'; ?></span>
			    					</div>
			    					<div>
			    						<span><?php echo xl('Effective'); ?>: </span>
			    						<span id="ins_effective<?php echo $cnt; ?>"><?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['effective']) : 'Empty'; ?></span>
			    					</div>
			    					<div>
			    						<span><?php echo xl('Subscriber'); ?>: </span>
			    						<span id="ins_subscriber<?php echo $cnt; ?>"><?php echo $dt[$field_prefix . 'ins_data_id'.$cnt] ? text($ins[$cnt]['subscriber']) : 'Empty'; ?></span>
			    					</div>
			    				</div>

			    				<div>
			    					<?php
			    						// OEMRAD - Change
			    						include($GLOBALS['incdir'].'/forms/cases/includes/case_coverage.php'); 
			    					?>
			    				</div>

			    			</div>
			    		</div>

		    		<?php 
		    		$cnt++;
		    		} 
		    		?>
		    	</div>

		    	<div class="mt-2 mb-1">
  					<a type="button" id="add_policy_btn" class="btn btn-primary" href="<?php echo $GLOBALS['webroot']; ?>/custom/policy_popup.php?pid=<?php echo $pid; ?>&callback=set_case_insurance"><?php echo xl('Add/Edit Payer'); ?></a>
  				</div>

    		</div>
    	</div>
    </div>
  </div>

  <?php
  	/* OEMRAD - Changes */
    include($GLOBALS['incdir'].'/forms/cases/includes/case_pi_case_management.php');
    include($GLOBALS['incdir'].'/forms/cases/includes/case_billing_delivery.php');
    include($GLOBALS['incdir'].'/forms/cases/includes/case_authorization.php');
    include($GLOBALS['incdir'].'/forms/cases/includes/case_care_team_providers.php');
    /* End */
	?>

</div>

<script type="text/javascript">
	// Validation Function
	window.formScriptValidations.push(async() => await caselibObj.validate_InsData('<?php echo $pid; ?>'));
</script>

<script type="text/javascript">
	var ins_data = {};
	<?php 
	foreach($policies as $policy) {
		$ipr = htmlspecialchars($policy['subscriber_lname'], ENT_QUOTES) . ', ' . htmlspecialchars($policy['subscriber_fname'], ENT_QUOTES);
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
	  dlgopen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/find_patient_popup.php', 'findPatient', 'modal-mlg', '', '', 'Guarantor Search');
	}

	function sel_address(type) {
	  var href =  '<?php echo $GLOBALS['webroot']; ?>/interface/usergroup/addrbook_list.php?popup=true&select=true';
		if(type) href += '&form_abook_type=' + type;
	  dlgopen(href, 'findAddress', 'modal-xl', '', '', 'EmployerGuarantor  Search');
	}

	function setCaseGuarantor(pid, lname, fname, dob) {
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
		// Set pageloader
		if(caselibObj) caselibObj.setPageLoader(true);

		var sel = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+ins);
		var policy_id = sel.options[sel.selectedIndex].value;
		const policy = await fetchPolicy(policy_id);

		let jsonPolicy = false;
		try {
      jsonPolicy = JSON.parse(policy);
    } catch (e) {
      jsonPolicy = false;
    }

		//var jsonPolicy = $.parseJSON(policy);
		var tags = {'policy' : 'policy_number', 'group' : 'group_number',
			'effective' : 'date', 'subscriber' : 'subscriber_full_name', 'full_address' : 'address_str'};

		// Prepare empty data
		if(jsonPolicy === false) {
			jsonPolicy = {};

			for (var tName in tags) {
				jsonPolicy[tags[tName]] = '';
			}
		}	

	  for (var key in tags) {
			var span = document.getElementById('ins_' + key + ins);
			if(span != null) {
				//while(span.firstChild) {
					//span.removeChild(span.firstChild);
				//}
				span.innerHTML = '';
				span.appendChild(document.createTextNode(jsonPolicy[tags[key]] != '' ? jsonPolicy[tags[key]] : 'Empty') );
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

	  // Set pageloader
		if(caselibObj) caselibObj.setPageLoader(false);
	}

	function setaddress(uid) {
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

<script type="text/javascript">
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
Calendar.setup({inputField:"<?php echo $field_prefix; ?>injury_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>injury_dt"});
Calendar.setup({inputField:"<?php echo $field_prefix; ?>first_consult_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>first_consult_dt"});
<?php } ?>

function add_policy(base, callback_func) {
  base += '/custom/policy_popup.php?callback=' + callback_func;
	wmtOpen(base, '_blank', '80%', '80%'); 
}

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

	for(let i = 1; i < 4; i++) {
		let s = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+i);
		if(s && s != null) s.selectedIndex = 0;
	}

	for(let i = 1; i < 4; i++) {
		let s = document.getElementById('<?php echo $field_prefix; ?>ins_data_id'+i);
		// Dispatch it.
		if(s && s != null) s.dispatchEvent(new Event('change', { bubbles: true }));
	}
	
	return true;
}

$(document).ready(function(){
    tabbify();

		<?php if($v_major > 4) { ?>
		$('.injury_datepicker').datetimepicker({
			maxDate : '0',
      <?php $datetimepicker_timepicker = false; ?>
      <?php $datetimepicker_showseconds = false; ?>
      <?php $datetimepicker_formatInput = true; ?>
    	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
		}).on("change", function(e) {
		    var curDate = $(this).datetimepicker('getValue');
		    var maxDate = new Date();

		    maxDate.setDate(maxDate.getDate()); // add one day
		    maxDate.setHours(0, 0, 0, 0); // clear time portion for correct results
		    if (curDate > maxDate) {
		      $(this).datetimepicker('setOptions', { value : maxDate });
		    }
		});

		$('.datepicker').datetimepicker({
      <?php $datetimepicker_timepicker = false; ?>
      <?php $datetimepicker_showseconds = false; ?>
      <?php $datetimepicker_formatInput = true; ?>
    	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
		});

		// Date picker
		$('.datepicker, .injury_datepicker').attr('autocomplete','off');
		<?php } ?>
		
		// Add new policy
    $("#add_policy_btn").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        let title = '<?php echo xla('Payer Management'); ?>';
				var case_dt = '';
				if(document.getElementById('form_dt').value != '') 
						case_dt = '&case_dt=' + document.getElementById('form_dt').value;
		    var url = '<?php echo $GLOBALS['webroot']; ?>/custom/policy_popup.php?pid=<?php echo $pid; ?>&callback=set_case_insurance' + case_dt;
		
        dlgopen(url, 'policyPop', 'modal-xl', 'modal-xl', '', title);
			/*
        dlgopen('', '', 'modal-xl', 'modal-xl', '', title, {
            buttons: [
                {text: '<?php //echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href') + case_dt
        });
			*/
    });

		// To trigger the event Listener
		document.addEventListener("insurance_change", (e) => {
		    let targetElement = event.target || event.srcElement;
		    caselibObj.handleEligibilityContent(targetElement, '<?php echo $pid; ?>');
		});

		// Insurance Change check other details.
		$('.ins-dropdown').change(async function() {
			await caselibObj.handleInsuranceLiability(this, '<?php echo $pid; ?>');
		});
		/* End */

});

</script>

<?php 
} // END OF DRAW DISPLAY
?>
