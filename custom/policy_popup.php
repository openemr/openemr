<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/patientvalidation.inc.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

// OEMR - Change
$updateallpayer = true;

$pid = $_SESSION['pid'];
$case_dt = date('Y-m-d');
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(isset($_GET['case_dt'])) $case_dt = DateToYYYYMMDD(strip_tags($_GET['case_dt']));
$set_pid = FALSE;
$callback = '';
if(isset($_GET['callback'])) $callback = strip_tags($_GET['callback']);


include_once("$srcdir/patient.inc");

$result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$result2 = getEmployerData($pid);

 // Check authorization.
if ($pid) {
    if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
        die(xlt('Updating demographics is not authorized.'));
    }

    if ($result['squad'] && ! AclMain::aclCheckCore('squads', $result['squad'])) {
        die(xlt('You are not authorized to access this squad.'));
    }
} else {
    if (!AclMain::aclCheckCore('patients', 'demo', '', array('write','addonly'))) {
        die(xlt('Adding demographics is not authorized.'));
    }
}

$CPR = 4; // cells per row

if ($GLOBALS['insurance_information'] != '0') {
    $insurancei = getInsuranceProvidersExtra();
} else {
    $insurancei = getInsuranceProviders();
}

?>
<html>
<head>

<title><?php echo xlt('Add/Edit Payer'); ?></title>

<?php Header::setupHeader(['datetime-picker','opener','common','select2', 'erx', 'oemr_ad']);
    require_once("$srcdir/erx_javascript.inc.php");
?>

<style>
    div.demographicsEditContainer div.label_custom {
        font-size: 0.8rem;
        display: grid;
        align-items: center;
        line-height: 1.2;
        padding-top: 0 !important;
        margin-bottom: 0.2rem;
    }

    div.insuranceEditContainer div.label_custom span {
        font-size: 0.8rem;
        display: inline-flex;
        height: 100%;
        align-items: center;
        line-height: 1.2;
    }
</style>

<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script type="text/javascript">

<?php require_once("$srcdir/restoreSession.php"); ?>

<?php require_once("$srcdir/wmt-v2/ajax/init_ajax.inc.js"); ?>

// Support for beforeunload handler.
var somethingChanged = false;

$(document).ready(function(){
    tabbify();

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        let title = '<?php echo xla('Insurance Search/Add/Select'); ?>';
        var url = '<?php echo $GLOBALS['webroot']; ?>/interface/practice/ins_search.php?dlg=true';
        dlgopen(url, 'insSearch', 700, 1000, '', title);
    });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php $datetimepicker_minDate = false; ?>
        <?php $datetimepicker_maxDate = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php $datetimepicker_minDate = false; ?>
        <?php $datetimepicker_maxDate = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

});

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

//code used from http://tech.irt.org/articles/js037/
function replace(string,text,by) {
 // Replaces text with by in string
 var strLength = string.length, txtLength = text.length;
 if ((strLength == 0) || (txtLength == 0)) return string;

 var i = string.indexOf(text);
 if ((!i) && (text != string.substring(0,txtLength))) return string;
 if (i == -1) return string;

 var newstr = string.substring(0,i) + by;

 if (i+txtLength < strLength)
  newstr += replace(string.substring(i+txtLength,strLength),text,by);

 return newstr;
}

function upperFirst(string,text) {
 return replace(string,text,text.charAt(0).toUpperCase() + text.substring(1,text.length));
}

<?php for ($i=1; $i<=3; $i++) { ?>
function auto_populate_employer_address<?php echo $i?>(){
 var sel = document.getElementById('form_i<?php echo $i?>subscriber_relationship');
 if (sel.options[sel.selectedIndex].value == "self")
 {
  document.getElementById('i<?php echo $i?>subscriber_fname').value='<?php echo attr($result['fname']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_mname').value='<?php echo attr($result['mname']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_lname').value='<?php echo attr($result['lname']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_street').value='<?php echo attr($result['street']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_city').value='<?php echo attr($result['city']); ?>';
  document.getElementById('form_i<?php echo $i?>subscriber_state').value='<?php echo attr($result['state']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_postal_code').value='<?php echo attr($result['postal_code']); ?>'
  <?php if ($result['country_code']) { ?>
    document.getElementById('form_i<?php echo $i?>subscriber_country').value='<?php echo attr($result['country_code']); ?>';
	<?php } ?>
  // document.getElementById('i<?php echo $i?>subscriber_phone').value='<?php echo attr($result['phone_home']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_DOB').value='<?php echo attr($result['DOB']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_ss').value='<?php echo attr($result['ss']); ?>';
  document.getElementById('form_i<?php echo $i?>subscriber_sex').value = '<?php echo attr($result['sex']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_employer').value='<?php echo attr($result2['name']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_employer_street').value='<?php echo attr($result2['street']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_employer_city').value='<?php echo attr($result2['city']); ?>';
  document.getElementById('form_i<?php echo $i?>subscriber_employer_state').value='<?php echo attr($result2['state']); ?>';
  document.getElementById('i<?php echo $i?>subscriber_employer_postal_code').value='<?php echo attr($result2['postal_code']); ?>';
  <?php if ($result2['country_code']) { ?>
    document.getElementById('form_i<?php echo $i?>subscriber_employer_country').value='<?php echo attr($result2['country_code']); ?>';
	<?php } ?>
 }
}

<?php } ?>

function popUp(URL) {
 day = new Date();
 id = day.getTime();
 top.restoreSession();
 eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 140,top = 120');");
}

// Indicates which insurance slot is being updated.
var insurance_index = 0;

// The OnClick handler for searching/adding the insurance company.
function ins_search(ins) {
    insurance_index = ins;
    return false;
}
function InsSaveClose() {
    top.restoreSession();
    document.location.reload();
}

// The ins_search.php window calls this to set the selected insurance.
function set_insurance(ins_id, ins_name) {
    thesel = $('#i' + insurance_index + 'provider');
    if ($(thesel).find("option[value='" + ins_id  + "']").length) {
        thesel.val(ins_id).trigger('change');
    } else {
        // no matching option was found so create one, append it to the
        // end of the list, and select it.
        let newOption = new Option(ins_name, ins_id, true, true);
        thesel.append(newOption).trigger('change');
    }
}

// This capitalizes the first letter of each word in the passed input
// element.  It also strips out extraneous spaces.
function capitalizeMe(elem) {
 var a = elem.value.split(' ');
 var s = '';
 for(var i = 0; i < a.length; ++i) {
  if (a[i].length > 0) {
   if (s.length > 0) s += ' ';
   s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
  }
 }
 elem.value = s;
}

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

function validate() {
 document.getElementById('submit_btn').disabled = true;
 $('#save-notification').show();

 var f = document.demographics_form;
 // OEMR - Added pcount change
 var pcount = f.ipayercount.value != '' ? f.ipayercount.value : 1; 

 // Some insurance validation.
 for (var i = 1; i <= pcount; ++i) {
  subprov = 'i' + i + 'provider';

  /* OEMR - Changes */
  <?php if($updateallpayer === true) { ?>
  /*
  if(f['i' + i + 'payerid'].value != "" && f[subprov].value == "") {
    alert(<?php //echo xlj('Empty provider not allowed.'); ?>);
    return false;
  }*/
  <?php } ?>
  /* End */

  if (!f[subprov] || f[subprov].selectedIndex <= 0) continue;
  var subpfx = 'i' + i + 'subscriber_';
  var subrelat = f['form_' + subpfx + 'relationship'];
  var samename =
   f[subpfx + 'fname'].value == '<?php echo attr($result['fname']); ?>' &&
   f[subpfx + 'mname'].value == '<?php echo attr($result['mname']); ?>' &&
   f[subpfx + 'lname'].value == '<?php echo attr($result['lname']); ?>';
  var ss_regexp=/[0-9][0-9][0-9]-?[0-9][0-9]-?[0-9][0-9][0-9][0-9]/;
  var samess=true;
  var ss_valid=false;
  //if(typeof f.form_ss!="undefined")
  //    {
        samess = f[subpfx + 'ss'].value == '<?php echo attr($result['ss']); ?>';
        ss_valid=ss_regexp.test(f[subpfx + 'ss'].value) && ss_regexp.test('<?php echo attr($result['ss']); ?>');
  //    }
  if (subrelat.options[subrelat.selectedIndex].value == "self") {
   if (!samename) {
    if (!confirm(<?php echo xlj('Subscriber relationship is self but name is different! Is this really OK?'); ?>)) {
        document.getElementById('submit_btn').disabled = false;
        $('#save-notification').hide();
        return false;
    }
   }
   if (!samess && ss_valid) {
    if(!confirm(<?php echo js_escape(xl('Subscriber relationship is self but SS number is different!') . " " . xl("Is this really OK?")); ?>)) {
        document.getElementById('submit_btn').disabled = false;
        $('#save-notification').hide();
        return false;
    }
   }
  } // end self
  else {
   if (samename) {
    if (!confirm(<?php echo xlj('Subscriber relationship is not self but name is the same! Is this really OK?'); ?>)) {
        document.getElementById('submit_btn').disabled = false;
        $('#save-notification').hide();
        return false;
    }
   }
   if (samess && ss_valid)  {
    if(!confirm(<?php echo js_escape(xl('Subscriber relationship is not self but SS number is the same!') . " " . xl("Is this really OK?")); ?>)) {
        document.getElementById('submit_btn').disabled = false;
        $('#save-notification').hide();
        return false;
    }
   }
  } // end not self
 } // end for

 saveAndClose();

}

async function addPolicy() {
	var data = $(".form-control").serializeArray();

	const result = await $.ajax ({
		type: "POST",
		url: "<?php echo AJAX_DIR_JS; ?>policy_save.ajax.php",
		dataType: "json",
		data: $.param(data)
	});
	return result;
}

async function saveAndClose() {
	try {
		const newInsurance = await addPolicy();
		$('#save-notification').hide();
		<?php if($callback) { ?>
      if(opener.<?php echo $callback; ?>) {
        opener.<?php echo $callback; ?>(newInsurance);
			} else {
			  alert('The opening window was closed or is unavailable!');
			}
		<?php } ?>
			dlgclose();
	} catch (err) {
		console.error(err);
	}
}

$(document).ready(function() {
    <?php for ($i=1; $i<=3; $i++) { ?>
        $("#form_i<?php echo $i?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo $i?>(); });
    <?php } ?>
});

/* OEMR - Changes */
function insChange(e, i) {
    var fvalue = e.value;

    <?php
        $insurancei1 = array();
        $res = sqlStatement("SELECT ic.id, ic.name, ic.ins_type_code from insurance_companies ic where ic.inactive != 1", array());
        while ($insrow = sqlFetchArray($res)) {
            $inscobj = new InsuranceCompany();

            $insrow['inc_type_code_name'] = isset($inscobj->ins_type_code_array[$insrow['ins_type_code']]) ? $inscobj->ins_type_code_array[$insrow['ins_type_code']] : "";

            $insurancei1['i'.$insrow['id']] = $insrow;
        }
    ?>

    var insjson = JSON.parse(<?php echo !empty($insurancei1) ? "'" . json_encode($insurancei1) . "'" : '{}' ?>);
    var insTypeList = ["Automobile Medical", "Workers Compensation Health Plan"]

    var ins_container_ele = document.querySelector('.i'+i+'claim_number_container');
    var ins_claim_number = document.querySelector("input[name='i"+i+"claim_number']");

    if(fvalue != '' && insjson['i'+fvalue]) {
        var insItem = insjson['i'+fvalue];
        var inc_type_code_name = insItem['inc_type_code_name'];

        if(inc_type_code_name != "" && insTypeList.includes(inc_type_code_name)) {
            ins_container_ele.style.display = "-webkit-box";
        } else {
            ins_container_ele.style.display = "none";
            ins_claim_number.value = "";
        }
    } else {
        ins_container_ele.style.display = "none";
        ins_claim_number.value = "";
    }
}

$(document).ready(function() {
    document.querySelectorAll('.ins-provider').forEach((insp) => {
        insp.onchange();
    });
});
/* End */

</script>
</head>

<body class="body_top">

<form action='#' name='demographics_form' id="DEM">    
<input type='hidden' name='pid' id="pid" class="form-control" value='<?php echo $pid; ?>' />
<input type='hidden' name='case_dt' id="case_dt" class="form-control" value='<?php echo $case_dt; ?>' />
<div id="save-notification" class="notification" style="left: 45%; top: 40%; z-index: 850; display: none;">
	<hs>Saving...</h2>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
                <h2><?php echo xlt('Add/Edit Payer');?></h2>
        </div>
        <div class="col-xs-12">
            <div class="btn-group">
                <button class="btn btn-default btn-save" id="submit_btn" value="<?php echo xla('Save'); ?>" onclick="validate();">
                    <?php echo xlt('Save'); ?>
                </button>
                <a class="btn btn-link btn-cancel" href="javacript:;" onclick="top.restoreSession(); dlgclose();">
                    <?php echo xlt('Cancel'); ?>
                </a>
            </div>
            <hr>
        </div>
    </div>
</div>
<br>

<div id="DEM" >
<div>

<?php

if ($GLOBALS['insurance_information'] != '0') {
    $insurancei = getInsuranceProvidersExtra();
} else {
    $insurancei = getInsuranceProviders();
}

//Check to see if only one insurance is allowed
if ($GLOBALS['insurance_only_one']) {
    $insurance_array = array('primary');
} else {
    $insurance_array = array('primary', 'secondary', 'tertiary');
}

if (! $GLOBALS['simplified_demographics']) {
    /* OEMR - Changes */
    if($updateallpayer === true) {
        $insurance_info = array();
        $insurance_info = getInsuranceDataItems($pid);
    } else {
        //Check to see if only one insurance is allowed
        if ($GLOBALS['insurance_only_one']) {
            $insurance_headings = array(xl("Primary Insurance Provider"));
            $insurance_info = array();
            $insurance_info[1] = getInsuranceData($pid, "primary");
        } else {
            $insurance_headings = array(xl("Primary Insurance Provider"), xl("Secondary Insurance Provider"), xl("Tertiary Insurance provider"));
            $insurance_info = array();
            $insurance_info[1] = getInsuranceData($pid, "primary");
            $insurance_info[2] = getInsuranceData($pid, "secondary");
            $insurance_info[3] = getInsuranceData($pid, "tertiary");
        }
    }
    /* End */

    ?>
    <div class="section-header">
       <span class="text font-weight-bold"><?php echo xlt("Insurance")?></span>
    </div>
    <div id="INSURANCE" class="insuranceEditContainer">
        <!-- OEMR - Wrap in if condition -->
        <?php if($updateallpayer === true) { ?>
        <!-- OEMR - added ul list -->
        <ul class="tabNav">
        <?php
        $ti = 0;
        foreach ($insurance_info as $iikey1 => $iItem1) {
            $ti++;
            $inactivestr = ($iItem1['inactive'] == "1") ? ' - <span class="text-danger">IA</span>' : '';
            $tabtitle = !empty(ucfirst($iItem1['provider_name'])) ? $iItem1['provider_name'] : "BLANK";
            ?>
            <li class="<?php echo $ti == 1 ? 'current' : '' ?>"><a href="#"><?php
            echo xlt($tabtitle); ?><?php echo $inactivestr; ?></a></li>
        <?php } ?>
        <li class="<?php echo empty($insurance_info) ? 'current' : '' ?>"><a href="#"><?php echo xlt("Add Payer"); ?></a></li>
        </ul>
        <?php } else { ?>
        <ul class="tabNav">
        <?php
        foreach ($insurance_array as $instype) {
            ?>
            <li <?php echo $instype == 'primary' ? 'class="current"' : '' ?>><a href="#"><?php $CapInstype = ucfirst($instype);
            echo xlt($CapInstype); ?></a></li><?php } ?>
        </ul>
       <?php } ?>
       <!-- End -->

    <div class="tabContainer">

    <?php

    // OEMR - Add address blank
    if(!empty($insurance_info)) array_unshift($insurance_info , array());
    $insurance_info[] = array('id' => '');

    // OEMR - Change
    for ($i = 1; $i <= count($insurance_info); $i++) {
        $result3 = $insurance_info[$i];
        ?>

     <!-- OEMR - Change -->
     <div class="tab <?php echo $i == 1 ? 'current' : '' ?> h-auto w-auto">
      <div class="form-row">
        <div class="col-md-6"><!-- start left column -->

          <!-- OEMR - Change -->
          <input type="hidden" id="i<?php echo attr($i); ?>payerid" name="i<?php echo attr($i); ?>payerid" class="form-control" value="<?php echo attr($result3["id"] ?? ''); ?>" />

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 label_custom pb-3">
              <!-- OEMR - Change -->
              <span class='required'><?php echo xl("Insurance Provider"); ?>:</span>
            </div>
            <div class="col-md-9">
              <a href="../../practice/ins_search.php?ins=" class="medium_modal btn btn-primary"
               onclick="ins_search(<?php echo attr_js($i); ?>)"><?php echo xlt('Search/Add/Edit') ?></a>
              <!-- OEMR - Change -->
              <select id="i<?php echo attr($i); ?>provider" name="i<?php echo attr($i); ?>provider" class="form-control form-control-sm sel2 mb-1 ins-provider" onchange="insChange(this,'<?php echo $i; ?>')" style="width: 250px;">
                <option value=""><?php echo xlt('Unassigned'); ?></option>
                <?php
                foreach ($insurancei as $iid => $iname) {
                    echo "<option value='" . attr($iid) . "'";
                    if (!empty($result3["provider"]) && (strtolower($iid) == strtolower($result3["provider"]))) {
                        echo " selected";
                    }
                    echo ">" . text($iname) . "</option>\n";
                }
                ?>
              </select>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Plan Name'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='20'
               name='i<?php echo attr($i); ?>plan_name'
               value="<?php echo attr($result3["plan_name"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <!-- OEMR - removed effective date -->
          <div class="form-row" <?php echo $updateallpayer === true ? 'style="display:none;"' : ''; ?> ><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom ">
              <span class='required'><?php echo xlt('Effective Date'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' size='16' class='datepicker form-control form-control-sm mb-1'
               id='i<?php echo attr($i); ?>effective_date'
               name='i<?php echo attr($i); ?>effective_date'
               value='<?php echo attr(oeFormatShortDate($result3['date'] ?? '')); ?>' />
            </div>
          </div><!-- end nested row -->

          <!-- OEMR - removed effective date -->
          <div class="form-row" <?php echo $updateallpayer === true ? 'style="display:none;"' : ''; ?> ><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom ">
              <span class='required'><?php echo xlt('Effective Date End'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' size='16' class='datepicker form-control form-control-sm mb-1'
               id='i<?php echo attr($i); ?>effective_date_end'
               name='i<?php echo attr($i); ?>effective_date_end'
               value='<?php echo attr(oeFormatShortDate($result3['date_end'] ?? '')); ?>' />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Policy Number'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='16'
               name='i<?php echo attr($i); ?>policy_number'
               value="<?php echo attr($result3["policy_number"] ?? ''); ?>"
               onkeyup='policykeyup(this)' />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom ">
              <span class='required'><?php echo xlt('Group Number'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type="text" class='form-control form-control-sm mb-1' size='16'
               name='i<?php echo attr($i); ?>group_number'
               value="<?php echo attr($result3["group_number"] ?? ''); ?>"
               onkeyup='policykeyup(this)' />
            </div>
          </div><!-- end nested row -->

          <!-- OEMR - Change -->
          <div class="form-row i<?php echo $i?>claim_number_container"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom ">
              <span class='required'><?php echo xlt('Claim Number'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type="entry" class='form-control form-control-sm mb-1' name=i<?php echo $i?>claim_number value="<?php echo attr($result3{"claim_number"}); ?>">
            </div>
          </div><!-- end nested row -->
          <!-- End -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-4 label_custom">
              <span class='required'><?php echo xlt('Subscriber Employer (SE)'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm' size='25'
               name='i<?php echo attr($i); ?>subscriber_employer'
               value="<?php echo attr($result3["subscriber_employer"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
               <span class='small mb-1'><br /><?php echo xlt('if unemployed enter Student'); ?>,
               <?php echo xlt('PT Student, or leave blank'); ?></span>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('SE Address'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='25'
               name='i<?php echo attr($i); ?>subscriber_employer_street'
               value="<?php echo attr($result3["subscriber_employer_street"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('SE Address Line 2'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='25'
               name='i<?php echo attr($i); ?>subscriber_employer_street_line_2'
               value="<?php echo attr($result3["subscriber_employer_street_line_2"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('SE City'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='15'
               name='i<?php echo attr($i); ?>subscriber_employer_city'
               value="<?php echo attr($result3["subscriber_employer_city"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE State') : xlt('SE Locality') ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                generate_form_field(
                    array(
                        'data_type' => $GLOBALS['state_data_type'],
                        'field_id' => ('i' . $i . 'subscriber_employer_state'),
                        'list_id' => $GLOBALS['state_list'],
                        'fld_length' => '15',
                        'max_length' => '63',
                        'edit_options' => 'C',
                        'smallform' => 'true'
                    ),
                    ($result3['subscriber_employer_state'] ?? '')
                );
                ?>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE Zip Code') : xlt('SE Postal Code') ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='15'
               name='i<?php echo attr($i); ?>subscriber_employer_postal_code'
               value="<?php echo attr($result3["subscriber_employer_postal_code"] ?? ''); ?>" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"<?php echo $GLOBALS['omit_employers'] ? " style='display:none'" : ""; ?>><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('SE Country'); ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                // Modified 7/2009 by BM to incorporate data types
                generate_form_field(
                    array(
                        'data_type' => $GLOBALS['country_data_type'],
                        'field_id' => ('i' . $i . 'subscriber_employer_country'),
                        'list_id' => $GLOBALS['country_list'],
                        'fld_length' => '10',
                        'max_length' => '63',
                        'edit_options' => 'C',
                        'smallform' => 'true'
                    ),
                    ($result3['subscriber_employer_country'] ?? '')
                );
                ?>
            </div>
          </div><!-- end nested row -->

          <!-- OEMRAD - Changes -->
          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Inactive'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='checkbox' class='form-control form-control-sm mb-1' name='i<?php echo attr($i); ?>payer_inactive' value="1" <?php echo $result3["inactive"] == "1" ? 'checked="checked"' : ''; ?> />
            </div>
          </div><!-- end nested row -->
          <!-- End -->

        </div><!-- end left column -->

        <div class="col-md-6"><!-- start right column -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Relationship'); ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                // Modified 6/2009 by BM to use list_options and function
                generate_form_field(
                    array(
                        'data_type' => 1,
                        'field_id' => ('i' . $i . 'subscriber_relationship'),
                        'list_id' => 'sub_relation',
                        'empty_title' => ' ',
                        'smallform' => ' form-control form-control-sm mb-1'
                    ),
                    ($result3['subscriber_relationship'] ?? '')
                );
                ?>
              <a href="javascript:popUp('browse.php?browsenum=<?php echo attr_url($i); ?>')"
               class='text'>(<?php echo xlt('Browse'); ?>)</a>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Subscriber'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='10'
               name='i<?php echo attr($i); ?>subscriber_fname'
               value="<?php echo attr($result3["subscriber_fname"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
              <input type='entry' class='form-control form-control-sm mb-1' size='3'
               name='i<?php echo attr($i); ?>subscriber_mname'
               value="<?php echo attr($result3["subscriber_mname"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
              <input type='entry' class='form-control form-control-sm mb-1' size='10'
               name='i<?php echo attr($i); ?>subscriber_lname'
               value="<?php echo attr($result3["subscriber_lname"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('D.O.B.'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='datepicker form-control form-control-sm mb-1 mw-100'
               id='i<?php echo attr($i); ?>subscriber_DOB' size='11'
               name='i<?php echo attr($i); ?>subscriber_DOB'
               value='<?php echo attr(oeFormatShortDate($result3['subscriber_DOB'] ?? '')); ?>' />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('Sex'); ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                // Modified 6/2009 by BM to use list_options and function
                generate_form_field(
                    array(
                        'data_type' => 1,
                        'field_id' => ('i' . $i . 'subscriber_sex'),
                        'list_id' => 'sex',
                        'smallform' => ' form-control form-control-sm mb-1'
                    ),
                    ($result3['subscriber_sex'] ?? '')
                );
                ?>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('S.S.'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1 mw-100' size='11'
               name='i<?php echo attr($i); ?>subscriber_ss'
               value="<?php echo attr(trim($result3["subscriber_ss"] ?? '')); ?>" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Subscriber Address'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1 mw-100' size='20'
               name='i<?php echo attr($i); ?>subscriber_street'
               value="<?php echo attr($result3["subscriber_street"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Address Line 2'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1 mw-100' size='20'
               name='i<?php echo attr($i); ?>subscriber_street_line_2'
               value="<?php echo attr($result3["subscriber_street_line_2"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('City'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1 mw-100' size='11'
               name='i<?php echo attr($i); ?>subscriber_city'
               value="<?php echo attr($result3["subscriber_city"] ?? ''); ?>"
               onchange="capitalizeMe(this);" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('State') : xlt('Locality') ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                // Modified 7/2009 by BM to incorporate data types
                generate_form_field(
                    array(
                        'data_type' => $GLOBALS['state_data_type'],
                        'field_id' => ('i' . $i . 'subscriber_state'),
                        'list_id' => $GLOBALS['state_list'],
                        'fld_length' => '15',
                        'max_length' => '63',
                        'edit_options' => 'C',
                        'smallform' => 'true'
                    ),
                    ($result3['subscriber_state'] ?? '')
                );
                ?>
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Zip Code') : xlt('Postal Code') ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='entry' class='form-control form-control-sm mb-1' size='15'
               name='i<?php echo attr($i); ?>subscriber_postal_code'
               value="<?php echo attr($result3["subscriber_postal_code"] ?? ''); ?>" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Country'); ?>:</span>
            </div>
            <div class="col-md-9">
              <?php
                // Modified 7/2009 by BM to incorporate data types
                generate_form_field(
                    array(
                        'data_type' => $GLOBALS['country_data_type'],
                        'field_id' => ('i' . $i . 'subscriber_country'),
                        'list_id' => $GLOBALS['country_list'],
                        'fld_length' => '10',
                        'max_length' => '63',
                        'edit_options' => 'C',
                        'smallform' => 'true'
                    ),
                    ($result3['subscriber_country'] ?? '')
                );
                ?>
            </div>
          </div><!-- end nested row -->

          <!-- OEMRAD - Commented -->
          <!-- <div class="form-row">
            <div class="col-md-3 pb-1 label_custom">
              <span><?php //echo xlt('Subscriber Phone'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='text' class='form-control form-control-sm mb-1' size='20'
               name='i<?php //echo attr($i); ?>subscriber_phone'
               value='<?php //echo attr($result3["subscriber_phone"] ?? ''); ?>'
               onkeyup='phonekeyup(this,mypcc)' />
            </div>
          </div>--><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('CoPay'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='text' class='form-control form-control-sm mb-1' size="6"
               name='i<?php echo attr($i); ?>copay'
               value="<?php echo attr($result3["copay"] ?? ''); ?>" />
            </div>
          </div><!-- end nested row -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span class='required'><?php echo xlt('Accept Assignment'); ?>:</span>
            </div>
            <div class="col-md-9">
              <select class='form-control form-control-sm mb-1'
               name='i<?php echo attr($i); ?>accept_assignment'>
                  <option value="TRUE"
                   <?php echo (!empty($result3["accept_assignment"]) && (strtoupper($result3["accept_assignment"]) == "TRUE")) ? "selected" : ""; ?>>
                   <?php echo xlt('YES'); ?></option>
                  <option value="FALSE"
                   <?php echo (!empty($result3["accept_assignment"]) && (strtoupper($result3["accept_assignment"]) == "FALSE")) ? "selected" : ""; ?>>
                   <?php echo xlt('NO'); ?></option>
              </select>
            </div>
          </div><!-- end nested row -->

          <!-- OEMRAD - Commented -->
          <!--<div class="form-row">
            <div class="col-md-3 pb-1 label_custom">
              <span><?php //echo xlt('Secondary Medicare Type'); ?>:</span>
            </div>
            <div class="col-md-9">
              <select class='form-control form-control-sm mb-1 sel2' name='i<?php //echo attr($i); ?>policy_type'>
                <?php
                /*if (!empty($policy_types)) {
                    foreach ($policy_types as $key => $value) {
                        echo "            <option value ='" . attr($key) . "'";
                        if (!empty($result3['policy_type']) && ($key == $result3['policy_type'])) {
                            echo " selected";
                        }
                        echo ">" . text($value) . "</option>\n";
                    }
                }*/
                ?>
              </select>
            </div>
          </div> --> <!-- end nested row -->

        </div><!-- end right column -->
      </div>
    </div>

        <?php
    } //end insurer for loop ?>

    <!-- OEMR - added field -->
    <input type="hidden" id="ipayercount" name="ipayercount" class="form-control" value="<?php echo attr(($i - 1)); ?>" />
    <input type="hidden" id="updateallpayer" name="updateallpayer" class="form-control" value="<?php echo $updateallpayer; ?>" />

   </div>
</div>

<?php } // end of "if not simplified_demographics" ?>

</div>
</div>
<br>

</form>

<script type="text/javascript">

// hard code validation for old validation, in the new validation possible to add match rules
<?php if ($GLOBALS['new_validate'] == 0) { ?>
 // fix inconsistently formatted phone numbers from the database

<?php if (! $GLOBALS['simplified_demographics']) { ?>
 // phonekeyup(document.getElementById('i1subscriber_phone'),mypcc);
 // phonekeyup(document.getElementById('i2subscriber_phone'),mypcc);
 // phonekeyup(document.getElementById('i3subscriber_phone'),mypcc);
<?php } ?>

<?php }?>

<?php echo $date_init; ?>

$(function () {
    $(".sel2").select2({
        theme: "bootstrap4",
        dropdownAutoWidth: true,
        width: 'resolve',
    <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
    });
})

</script>
<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot']."/library/options_listadd.inc"; ?>

</body>

</html>
