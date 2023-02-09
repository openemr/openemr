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

<title><?php echo xlt('Add / Edit Policies'); ?></title>

<?php Header::setupHeader(['datetime-picker','common','select2','dialog','opener']); ?>

<style>
    .form-control {
        width: auto;
        display: inline;
        height: auto;
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
        /*
        dlgopen('', '', 700, 460, '', title, {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
        */
    });

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
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
 console.log('Setting In the policy popup code');
 var thesel = document.getElementById('i' + insurance_index + 'provider');
 var theopts = thesel.options; // the array of Option objects
 var i = 0;
 var found = false;
 for (; i < theopts.length; ++i) {
  theopts[i].selected = false;
  if (theopts[i].value == ins_id) {
   theopts[i].selected = true;
   found = true;
  }
 }
 // no matching option was found so create one, append it to the
 // end of the list, and select it.
 if(!found) theopts[i] = new Option(ins_name, ins_id, false, true);
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

 for (var i = 1; i <= 3; ++i) {
  subprov = 'i' + i + 'provider';
	// alert('Checking: '+i);
  if (!document.getElementById(subprov) || document.getElementById(subprov).selectedIndex <= 0) continue;
  var subpfx = 'i' + i + 'subscriber_';
  var subrelat = document.getElementById('form_' + subpfx + 'relationship');
  var samename =
   document.getElementById(subpfx + 'fname').value == '<?php echo attr($result['fname']); ?>' &&
   document.getElementById(subpfx + 'mname').value == '<?php echo attr($result['mname']); ?>' &&
   document.getElementById(subpfx + 'lname').value == '<?php echo attr($result['lname']); ?>';
  var ss_regexp=/[0-9][0-9][0-9]-?[0-9][0-9]-?[0-9][0-9][0-9][0-9]/;
  var samess=true;
  var ss_valid=false;
  samess = document.getElementById(subpfx + 'ss').value == '<?php echo attr($result['ss']); ?>';;
  ss_valid = ss_regexp.test(document.getElementById(subpfx + 'ss').value) && ss_regexp.test('<?php echo attr($result['ss']); ?>');
  if (subrelat.options[subrelat.selectedIndex].value == "self") {
   if (!samename) {
    if (!confirm("<?php echo xls('Subscriber relationship is self but name is different! Is this really OK?'); ?>")) {
     document.getElementById('submit_btn').disabled = false;
 		 $('#save-notification').hide();
     return false;
    }
   }
   if (!samess && ss_valid) {
    if(!confirm("<?php echo xls('Subscriber relationship is self but SS number is different!')." ". xls("Is this really OK?"); ?>")) {
     document.getElementById('submit_btn').disabled = false;
 		 $('#save-notification').hide();
     return false;
    }
   }
  } // end self
  else {
   if (samename) {
    if (!confirm("<?php echo xls('Subscriber relationship is not self but name is the same! Is this really OK?'); ?>")) {
     document.getElementById('submit_btn').disabled = false;
 		 $('#save-notification').hide();
     return false;
    }
   }
   if (samess && ss_valid)  {
    if(!confirm("<?php echo xls('Subscriber relationship is not self but SS number is the same!') ." ". xls("Is this really OK?"); ?>")) {
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
		/*
		Object.keys(newInsurance).forEach(key => {
			var val = newInsurance[key];
			console.log("Key: ["+key+"]  Value: ("+val+")");
			Object.keys(val).forEach(fld => {
				var data = val[fld];
				console.log("Key: ["+fld+"]  Value: ("+data+")");
			});
		});
		*/
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

// Onkeyup handler for policy number.  Allows only A-Z and 0-9.
function policykeyup(e) {
 var v = e.value.toUpperCase();
 var filteredString="";
 for (var i = 0; i < v.length; ++i) {
  var c = v.charAt(i);
  if ((c >= '0' && c <= '9') ||
     (c >= 'A' && c <= 'Z') ||
     (c == '*') ||
     (c == '-') ||
     (c == '_') ||
     (c == '(') ||
     (c == ')') ||
     (c == '#'))
     {
         filteredString+=c;
     }
 }
 e.value = filteredString;
 return;
}

$(document).ready(function() {
    <?php for ($i=1; $i<=3; $i++) { ?>
  $("#form_i<?php echo $i?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo $i?>(); });
    <?php } ?>

});

</script>
</head>

<body class="body_top">
<input type='hidden' name='pid' id="pid" class="form-control" value='<?php echo $pid; ?>' />
<input type='hidden' name='case_dt' id="case_dt" class="form-control" value='<?php echo $case_dt; ?>' />
<div id="save-notification" class="notification" style="left: 45%; top: 40%; z-index: 850; display: none;">
	<hs>Saving...</h2>
</div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                    <h2><?php echo xlt('Edit Current Policies');?></h2>
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


<?php
if (! $GLOBALS['simplified_demographics']) {
    $insurance_headings = array(xl("Primary Insurance Provider"), xl("Secondary Insurance Provider"), xl("Tertiary Insurance provider"));
    $insurance_info = array();
    $insurance_info[1] = getInsuranceData($pid, "primary");
    $insurance_info[2] = getInsuranceData($pid, "secondary");
    $insurance_info[3] = getInsuranceData($pid, "tertiary");

    ?>
    <div class="section-header">
       <span class="text"><b><?php echo xlt("Insurance")?></b></span>
    </div>
    <div id="INSURANCE" >
       <ul class="tabNav">
        <?php
        foreach (array('primary','secondary','tertiary') as $instype) {
            ?><li <?php echo $instype == 'primary' ? 'class="current"' : '' ?>><a href="#"><?php $CapInstype=ucfirst($instype);
echo xlt($CapInstype); ?></a></li><?php
        }
        ?>
        </ul>

    <div class="tabContainer">

    <?php
    for ($i=1; $i<=3; $i++) {
        $result3 = $insurance_info[$i];
    ?>

     <div class="tab <?php echo $i == 1 ? 'current': '' ?>" style='height:auto;width:auto'>     <!---display icky, fix to auto-->

      <div class="row">
        <div class="col-md-6">
         <table border="0">

           <tr>
            <td valign='top'>
            <label class='required'><?php echo text($insurance_headings[$i -1])."&nbsp;"?></label>
            </td>
            <td class='required'>:</td>
            <td>
             <a href="<?php echo $GLOBALS['webroot']; ?>/interface/practice/ins_search.php?dlg=true" class="medium_modal css_button" onclick="ins_search(<?php echo $i?>)">
             <!-- a href="javascript:;" class="css_button" onclick="ins_search(<?php echo $i?>); wmtOpen('<?php // echo $GLOBALS['webroot']; ?>/interface/practice/ins_search.php?close=close', '_blank', '35%', '90%');" -->
             <span><?php echo xlt('Search/Add') ?></span>
                  </a>
             <select name="i<?php echo $i?>provider" id="i<?php echo $i; ?>provider" class="form-control sel2" style="width: 300;">
             <option value=""><?php echo xlt('Unassigned'); ?></option>
                <?php
                foreach ($insurancei as $iid => $iname) {
                    echo "<option value='" . attr($iid) . "'";
                    if (strtolower($iid) == strtolower($result3{"provider"})) {
                        echo " selected";
                    }

                    echo ">" . text($iname) . "</option>\n";
                }
                ?>
               </select>

              </td>
             </tr>

            <tr>
             <td>
              <label class='required'><?php echo xlt('Plan Name'); ?> </label>
             </td>
             <td class='required'>:</td>
             <td>
              <input type='entry' class='form-control' size='20' name='i<?php echo $i?>plan_name' value="<?php echo attr($result3{"plan_name"}); ?>"
               onchange="capitalizeMe(this);" />&nbsp;&nbsp;
             </td>
            </tr>

            <tr>
             <td>
              <label class='required'><?php echo xlt('Effective Date'); ?></label>
             </td>
             <td class='required'>:</td>
             <td>
              <input type='entry' size='16' class='datepicker form-control' id='i<?php echo $i ?>effective_date' name='i<?php echo $i ?>effective_date'
               value='<?php echo attr(oeFormatShortDate($result3['date'])); ?>'
                />
             </td>
            </tr>

            <tr>
             <td><label class=required><?php echo xlt('Policy Number'); ?></label></td>
             <td class='required'>:</td>
             <td><input type='entry' class='form-control' size='16' name='i<?php echo $i?>policy_number' value="<?php echo attr($result3{"policy_number"}); ?>"
              onkeyup='policykeyup(this)'></td>
            </tr>

            <tr>
             <td><label class=required><?php echo xlt('Group Number'); ?></label></td>
             <td class='required'>:</td>
             <td><input type=entry class='form-control' size=16 name=i<?php echo $i?>group_number value="<?php echo attr($result3{"group_number"}); ?>" onkeyup='policykeyup(this)'></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display:none'";
} ?>>
             <td class='required'><?php echo xlt('Subscriber Employer (SE)'); ?><br><label style='font-weight:normal'>
              (<?php echo xlt('if unemployed enter Student'); ?>,<br><?php echo xlt('PT Student, or leave blank'); ?>) </label></td>
              <td class='required'>:</td>
             <td><input type=entry class='form-control' size=25 name=i<?php echo $i?>subscriber_employer id='i<?php echo $i?>subscriber_employer'
              value="<?php echo attr($result3{"subscriber_employer"}); ?>"
               onchange="capitalizeMe(this);" /></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display:none'";
} ?>>
             <td><label class=required><?php echo xlt('SE Address'); ?></label></td>
             <td class='required'>:</td>
             <td><input type=entry class='form-control' size=25 name=i<?php echo $i?>subscriber_employer_street id=i<?php echo $i?>subscriber_employer_street
              value="<?php echo attr($result3{"subscriber_employer_street"}); ?>"
               onchange="capitalizeMe(this);" /></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display:none'";
} ?>>
             <td colspan="3">
              <table>
               <tr>
                <td><label class=required><?php echo xlt('SE City'); ?>: </label></td>
                <td><input type=entry class='form-control' size=15 name=i<?php echo $i?>subscriber_employer_city id=i<?php echo $i?>subscriber_employer_city
               value="<?php echo attr($result3{"subscriber_employer_city"}); ?>"
                onchange="capitalizeMe(this);" /></td>
                <td><label class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE State') : xlt('SE Locality') ?>: </label></td>
            <td>
                <?php
                 // Modified 7/2009 by BM to incorporate data types
                generate_form_field(array('data_type'=>$GLOBALS['state_data_type'],'field_id'=>('i'.$i.'subscriber_employer_state'),'list_id'=>$GLOBALS['state_list'],'fld_length'=>'15','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_employer_state']);
                ?>
                </td>
               </tr>
               <tr>
                <td><label class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE Zip Code') : xlt('SE Postal Code') ?>: </label></td>
                <td><input type=entry class='form-control' size=15 name=i<?php echo $i?>subscriber_employer_postal_code v id=i<?php echo $i?>subscriber_employer_postal_code value="<?php echo text($result3{"subscriber_employer_postal_code"}); ?>"></td>
                <td><label class=required><?php echo xlt('SE Country'); ?>: </label></td>
            <td>
                    <?php
                  // Modified 7/2009 by BM to incorporate data types
                    generate_form_field(array('data_type'=>$GLOBALS['country_data_type'],'field_id'=>('i'.$i.'subscriber_employer_country'),'list_id'=>$GLOBALS['country_list'],'fld_length'=>'10','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_employer_country']);
                    ?>
            </td>
               </tr>
              </table>
             </td>
            </tr>

           </table>
          </div>

          <div class="col-md-6">
        <table border="0">
            <tr>
                <td><label class=required><?php echo xlt('Relationship'); ?></label></td>
                <td class=required>:</td>
                <td colspan=3><?php
                 // Modified 6/2009 by BM to use list_options and function
                 generate_form_field(array('data_type'=>1,'field_id'=>('i'.$i.'subscriber_relationship'),'list_id'=>'sub_relation','empty_title'=>' '), $result3['subscriber_relationship']);
                    ?>

                <a href="javascript:popUp('<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/browse.php?browsenum=<?php echo $i?>')" class=text>(<?php echo xlt('Browse'); ?>)</a></td>
                <td></td><td></td><td></td><td></td>
            </tr>
                      <tr>
                <td width=120><label class=required><?php echo xlt('Subscriber'); ?> </label></td>
                <td class=required>:</td>
                <td colspan=3><input type=entry class='form-control'size=10 name=i<?php echo $i?>subscriber_fname id=i<?php echo $i; ?>subscriber_fname value="<?php echo attr($result3{"subscriber_fname"}); ?>" onchange="capitalizeMe(this);" />
                <input type=entry class='form-control' size=3 name=i<?php echo $i?>subscriber_mname id=i<?php echo $i; ?>subscriber_mname value="<?php echo attr($result3{"subscriber_mname"}); ?>" onchange="capitalizeMe(this);" />
                <input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_lname id=i<?php echo $i; ?>subscriber_lname value="<?php echo attr($result3{"subscriber_lname"}); ?>" onchange="capitalizeMe(this);" /></td>
                <td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td><label class=bold><?php echo xlt('D.O.B.'); ?> </label></td>
                <td class=required>:</td>
                <td><input type='entry' size='11' class='datepicker form-control' id='i<?php echo $i?>subscriber_DOB' name='i<?php echo $i?>subscriber_DOB' value='<?php echo attr(oeFormatShortDate($result3['subscriber_DOB'])); ?>' />
        </td>
                <td><label class=bold><?php echo xlt('Sex'); ?>: </label></td>
                <td><?php
                 // Modified 6/2009 by BM to use list_options and function
                 generate_form_field(array('data_type'=>1,'field_id'=>('i'.$i.'subscriber_sex'),'list_id'=>'sex'), $result3['subscriber_sex']);
                    ?>
                </td>
                <td></td><td></td> <td></td><td></td>
            </tr>
            <tr>
                <td class=leftborder><label class=bold><?php echo xlt('S.S.'); ?> </label></td>
                <td class=required>:</td>
                <td><input type=entry class='form-control' size=11 name=i<?php echo $i?>subscriber_ss id=i<?php echo $i; ?>subscriber_ss value="<?php echo attr(trim($result3{"subscriber_ss"})); ?>"></td>
            </tr>

            <tr>
                <td><label class=required><?php echo xlt('Subscriber Address'); ?> </label></td>
                <td class=required>:</td>
                <td><input type=entry class='form-control' size=20 name=i<?php echo $i?>subscriber_street id=i<?php echo $i; ?>subscriber_street value="<?php echo attr($result3{"subscriber_street"}); ?>" onchange="capitalizeMe(this);" /></td>

                <td><label class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('State') : xlt('Locality') ?>: </label></td>
                <td>
                    <?php
                    // Modified 7/2009 by BM to incorporate data types
                    generate_form_field(array('data_type'=>$GLOBALS['state_data_type'],'field_id'=>('i'.$i.'subscriber_state'),'list_id'=>$GLOBALS['state_list'],'fld_length'=>'15','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_state']);
                ?>
                </td>
            </tr>
            <tr>
                <td class=leftborder><label class=required><?php echo xlt('City'); ?></label></td>
                <td class=required>:</td>
                <td><input type=entry class='form-control' size=11 name=i<?php echo $i?>subscriber_city id=i<?php echo $i; ?>subscriber_city value="<?php echo attr($result3{"subscriber_city"}); ?>" onchange="capitalizeMe(this);" /></td><td class=leftborder><label class='required'<?php if ($GLOBALS['omit_employers']) {
                    echo " style='display:none'";
} ?>><?php echo xlt('Country'); ?>: </label></td><td>
                    <?php
                    // Modified 7/2009 by BM to incorporate data types
                    generate_form_field(array('data_type'=>$GLOBALS['country_data_type'],'field_id'=>('i'.$i.'subscriber_country'),'list_id'=>$GLOBALS['country_list'],'fld_length'=>'10','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_country']);
                    ?>
                </td>
</tr>
            <tr>
                <td><label class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Zip Code') : xlt('Postal Code') ?> </label></td>
                <td class=required>:</td><td><input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_postal_code id=i<?php echo $i; ?>subscriber_postal_code value="<?php echo attr($result3{"subscriber_postal_code"}); ?>"></td>

                <td colspan=2>
                </td><td></td>
            </tr>
            <!-- tr>
                <td><label class=bold><?php echo xlt('Subscriber Phone'); ?></label></td>
                <td class=required>:</td>
                <td><input type='text' class='form-control' size='20' name='i<?php echo $i?>subscriber_phone' id='i<?php echo $i; ?>subscriber_phone' value='<?php echo attr($result3["subscriber_phone"]); ?>' onkeyup='phonekeyup(this,mypcc)' /></td>
                <td colspan=2><label class=bold><?php echo xlt('CoPay'); ?>: <input type=text class='form-control' size="6" name=i<?php echo $i?>copay id=i<?php echo $i; ?>copay value="<?php echo attr($result3{"copay"}); ?>"></label></td>
                <td colspan=2>
                </td><td></td><td></td>
            </tr -->
            <tr>
                <td colspan=0><label class='required'><?php echo xlt('Accept Assignment'); ?></label></td>
                <td class=required>:</td>
                <td colspan=2>
                    <select class='form-control' name=i<?php echo $i?>accept_assignment id=i<?php echo $i; ?>accept_assignment>
                     <option value="TRUE" <?php if (strtoupper($result3{"accept_assignment"}) == "TRUE") {
                            echo "selected";
}?>><?php echo xlt('YES'); ?></option>
                     <option value="FALSE" <?php if (strtoupper($result3{"accept_assignment"}) == "FALSE") {
                            echo "selected";
}?>><?php echo xlt('NO'); ?></option>
                    </select>
                </td>
                <td></td><td></td>
                <td colspan=2>
                </td><td></td>
            </tr>
      <!-- tr>
        <td><label class='bold'><?php echo xlt('Secondary Medicare Type'); ?></label></td>
        <td class='bold'>:</td>
        <td colspan='6'>
          <select class='form-control sel2' name=i<?php echo $i?>policy_type id=i<?php echo $i?>policy_type >
<?php
foreach ($policy_types as $key => $value) {
    echo "            <option value ='" . attr($key) . "'";
    if ($key == $result3['policy_type']) {
        echo " selected";
    }

    echo ">" . text($value) . "</option>\n";
}
?>
        </select>
      </td>
    </tr -->
      </table>

        </div>
      </div>

      </div>

    <?php
    } //end insurer for loop ?>

   </div>
</div>

<?php } // end of "if not simplified_demographics" ?>
</div></div>

<br>

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
</script>
<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot']."/library/options_listadd.inc"; ?>

</body>

</html>
