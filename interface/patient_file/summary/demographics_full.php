<?php

/**
 * Edit demographics.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/validation/LBF_Validation.php");
require_once("$srcdir/patientvalidation.inc.php");
require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Events\PatientDemographics\UpdateEvent;

// Session pid must be right or bad things can happen when demographics are saved!
//
$set_pid = isset($_GET["set_pid"]) ? $_GET["set_pid"] : ($_GET["pid"] ?? null);
if ($set_pid && $set_pid != $_SESSION["pid"]) {
    setpid($set_pid);
}

$result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
$result2 = getEmployerData($pid);

 // Check authorization.
if ($pid) {
    // Create and fire the patient demographics update event
    $updateEvent = new UpdateEvent($pid);
    $updateEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch(UpdateEvent::EVENT_HANDLE, $updateEvent, 10);

    if (
        !$updateEvent->authorized() ||
        !AclMain::aclCheckCore('patients', 'demo', '', 'write')
    ) {
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

// $statii = array('married','single','divorced','widowed','separated','domestic partner');
// $provideri = getProviderInfo();
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

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 " .
  "ORDER BY group_id, seq");
?>
<html>
<head>
<?php Header::setupHeader(['datetime-picker','common','select2']);
    require_once("$srcdir/erx_javascript.inc.php");
?>
<title><?php echo xlt('Edit Current Patient'); ?></title>

<?php include_once($GLOBALS['srcdir'] . "/options.js.php"); ?>

<script>

// Support for beforeunload handler.
var somethingChanged = false;

$(function () {
    tabbify();

    $(".select-dropdown").select2({
        theme: "bootstrap4",
        <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
    });
    if (typeof error !== 'undefined') {
        if (error) {
            alertMsg(error);
        }
    }

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        let title = <?php echo xlj('Insurance Search/Select/Add'); ?>;
        dlgopen('', '', 700, 460, '', title, {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
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
  $('.datepicker-past').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker-past').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datepicker-future').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = '-1970/01/01'; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker-future').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = '-1970/01/01'; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });

  // Support for beforeunload handler.
  $('.tab input, .tab select, .tab textarea').change(function() {
    somethingChanged = true;
  });
  window.addEventListener("beforeunload", function (e) {
    if (somethingChanged && !top.timed_out) {
      var msg = <?php echo xlj('You have unsaved changes.'); ?>;
      e.returnValue = msg;     // Gecko, Trident, Chrome 34+
      return msg;              // Gecko, WebKit, Chrome <34
    }
  });

  if (window.checkSkipConditions) {
    checkSkipConditions();
  }
});

var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

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

<?php for ($i = 1; $i <= 3; $i++) { ?>
function auto_populate_employer_address<?php echo attr($i); ?>(){
 var f = document.demographics_form;
 if (f.form_i<?php echo attr($i); ?>subscriber_relationship.options[f.form_i<?php echo attr($i); ?>subscriber_relationship.selectedIndex].value == "self")
 {
  f.i<?php echo attr($i); ?>subscriber_fname.value=f.form_fname.value;
  f.i<?php echo attr($i); ?>subscriber_mname.value=f.form_mname.value;
  f.i<?php echo attr($i); ?>subscriber_lname.value=f.form_lname.value;
  f.i<?php echo attr($i); ?>subscriber_street.value=f.form_street.value;
  f.i<?php echo attr($i); ?>subscriber_city.value=f.form_city.value;
  f.form_i<?php echo attr($i); ?>subscriber_state.value=f.form_state.value;
  f.i<?php echo attr($i); ?>subscriber_postal_code.value=f.form_postal_code.value;
  if (f.form_country_code)
    f.form_i<?php echo attr($i); ?>subscriber_country.value=f.form_country_code.value;
  f.i<?php echo attr($i); ?>subscriber_phone.value=f.form_phone_home.value;
  f.i<?php echo attr($i); ?>subscriber_DOB.value=f.form_DOB.value;
  if(typeof f.form_ss!="undefined")
    {
        f.i<?php echo attr($i); ?>subscriber_ss.value=f.form_ss.value;
    }
  f.form_i<?php echo attr($i); ?>subscriber_sex.value = f.form_sex.value;
  f.i<?php echo attr($i); ?>subscriber_employer.value=f.form_em_name.value;
  f.i<?php echo attr($i); ?>subscriber_employer_street.value=f.form_em_street.value;
  f.i<?php echo attr($i); ?>subscriber_employer_city.value=f.form_em_city.value;
  f.form_i<?php echo attr($i); ?>subscriber_employer_state.value=f.form_em_state.value;
  f.i<?php echo attr($i); ?>subscriber_employer_postal_code.value=f.form_em_postal_code.value;
  if (f.form_em_country)
    f.form_i<?php echo attr($i); ?>subscriber_employer_country.value=f.form_em_country.value;
 }
}

<?php } ?>

function popUp(URL) {
 day = new Date();
 id = day.getTime();
 top.restoreSession();
 eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=300,left = 440,top = 362');");
}

function checkNum () {
 var re= new RegExp();
 re = /^\d*\.?\d*$/;
 str=document.demographics_form.monthly_income.value;
 if(re.exec(str))
 {
 }else{
  alert(<?php echo xlj('Please enter a monetary amount using only numbers and a decimal point.'); ?>);
 }
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
 var thesel = document.forms[0]['i' + insurance_index + 'provider'];
 var theopts = thesel.options; // the array of Option objects
 var i = 0;
 for (; i < theopts.length; ++i) {
  if (theopts[i].value == ins_id) {
   theopts[i].selected = true;
   return;
  }
 }
 // no matching option was found so create one, append it to the
 // end of the list, and select it.
 theopts[i] = new Option(ins_name, ins_id, false, true);
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

function validate(f) {
 var errCount = 0;
 var errMsgs = new Array();
<?php generate_layout_validation('DEM'); ?>

 var msg = "";
 msg += <?php echo xlj('The following fields are required'); ?> + ":\n\n";
 for ( var i = 0; i < errMsgs.length; i++ ) {
    msg += errMsgs[i] + "\n";
 }
 msg += "\n" + <?php echo xlj('Please fill them in before continuing.'); ?>;

 if ( errMsgs.length > 0 ) {
    alert(msg);
 }

 //Misc  Deceased Date Validation for Future Date
var dateVal = document.getElementById("form_deceased_date").value;
var currentDate;
var d = new Date();
month = '' + (d.getMonth() + 1),
day = '' + d.getDate(),
year = d.getFullYear();
if (month.length < 2) month = '0' + month;
if (day.length < 2) day = '0' + day;
currentDate = year+'-'+month+'-'+day;
if(dateVal > currentDate)
{
    alert (<?php echo xlj("Deceased Date should not be greater than Today"); ?>);
    return false;
}

//Patient Data validations
    <?php if ($GLOBALS['erx_enable']) { ?>
 alertMsg='';
 for(i=0;i<f.length;i++){
  if(f[i].type=='text' && f[i].value)
  {
   if(f[i].name == 'form_fname' || f[i].name == 'form_mname' || f[i].name == 'form_lname')
   {
    alertMsg += checkLength(f[i].name,f[i].value,35);
    alertMsg += checkUsername(f[i].name,f[i].value);
   }
   else if(f[i].name == 'form_street' || f[i].name == 'form_city')
   {
    alertMsg += checkLength(f[i].name,f[i].value,35);
    alertMsg += checkAlphaNumericExtended(f[i].name,f[i].value);
   }
   else if(f[i].name == 'form_phone_home')
   {
    alertMsg += checkPhone(f[i].name,f[i].value);
   }
  }
 }
 if(alertMsg)
 {
   alert(alertMsg);
   return false;
 }
    <?php } ?>
 //return false;

// Some insurance validation.
 for (var i = 1; i <= 3; ++i) {
  subprov = 'i' + i + 'provider';
  if (!f[subprov] || f[subprov].selectedIndex <= 0) continue;
  var subpfx = 'i' + i + 'subscriber_';
  var subrelat = f['form_' + subpfx + 'relationship'];
  var samename =
   f[subpfx + 'fname'].value == f.form_fname.value &&
   f[subpfx + 'mname'].value == f.form_mname.value &&
   f[subpfx + 'lname'].value == f.form_lname.value;
  var ss_regexp=/[0-9][0-9][0-9]-?[0-9][0-9]-?[0-9][0-9][0-9][0-9]/;
  var samess=true;
  var ss_valid=false;
  if(typeof f.form_ss!="undefined")
      {
        samess = f[subpfx + 'ss'].value == f.form_ss.value;
        ss_valid=ss_regexp.test(f[subpfx + 'ss'].value) && ss_regexp.test(f.form_ss.value);
      }
  if (subrelat.options[subrelat.selectedIndex].value == "self") {
   if (!samename) {
    if (!confirm(<?php echo xlj('Subscriber relationship is self but name is different! Is this really OK?'); ?>))
     return false;
   }
   if (!samess && ss_valid) {
    if(!confirm(<?php echo js_escape(xl('Subscriber relationship is self but SS number is different!') . " " . xl("Is this really OK?")); ?>))
    return false;
   }
  } // end self
  else {
   if (samename) {
    if (!confirm(<?php echo xlj('Subscriber relationship is not self but name is the same! Is this really OK?'); ?>))
     return false;
   }
   if (samess && ss_valid)  {
    if(!confirm(<?php echo js_escape(xl('Subscriber relationship is not self but SS number is the same!') . " " . xl("Is this really OK?")); ?>))
    return false;
   }
  } // end not self
 } // end for

 return errMsgs.length < 1;
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

// Added 06/2009 by BM to make compatible with list_options table and functions - using jquery
$(function () {

    <?php for ($i = 1; $i <= 3; $i++) { ?>
  $("#form_i<?php echo attr($i); ?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo attr($i); ?>(); });
    <?php } ?>

});

</script>
</head>

<?php
/*Get the constraint from the DB-> LBF forms accordinf the form_id*/
$constraints = LBF_Validation::generate_validate_constraints("DEM");
?>
<script> var constraints = <?php echo $constraints;?>; </script>

<body class="body_top">

<form action='demographics_save.php' name='demographics_form' id="DEM" method='post' onsubmit="submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'DEM',constraints)">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type='hidden' name='mode' value='save' />
<input type='hidden' name='db_id' value="<?php echo attr($result['id']); ?>" />

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Edit Current Patient');?></h2>
            </div>
            <div class="col-12">
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-save" id="submit_btn" disabled="disabled" value="<?php echo xla('Save'); ?>">
                        <?php echo xlt('Save'); ?>
                    </button>
                    <a class="btn btn-secondary btn-cancel" href="demographics.php" onclick="top.restoreSession()">
                        <?php echo xlt('Cancel'); ?>
                    </a>
                </div>
                <hr>
            </div>
        </div>
    </div>
<?php

function end_cell()
{
    global $item_count, $cell_count;
    if ($item_count > 0) {
        echo "</td>";
        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        end_row();
        echo " </table>\n";
        echo "</div>\n";
    }
}

$last_group = '';
$cell_count = 0;
$item_count = 0;
$display_style = 'block';

$group_seq = 0; // this gives the DIV blocks unique IDs

$condition_str = '';
?>
<br />
  <div class="section-header">
   <span class="text font-weight-bold"><?php echo xlt("Demographics")?></span>
</div>

<div id="DEM">

    <ul class="tabNav">
        <?php display_layout_tabs('DEM', $result, $result2); ?>
    </ul>

    <div class="tabContainer">
        <?php display_layout_tabs_data_editable('DEM', $result, $result2); ?>
    </div>
</div>
<br />

<div id="DEM">


<?php
if (! $GLOBALS['simplified_demographics']) {
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


    ?>
    <div class="section-header">
       <span class="text font-weight-bold"><?php echo xlt("Insurance")?></span>
    </div>
    <div id="INSURANCE">
       <ul class="tabNav">
        <?php
        foreach ($insurance_array as $instype) {
            ?><li <?php echo $instype == 'primary' ? 'class="current"' : '' ?>><a href="#"><?php $CapInstype = ucfirst($instype);
echo xlt($CapInstype); ?></a></li><?php } ?>
        </ul>

    <div class="tabContainer">

    <?php
    for ($i = 1; $i <= 3; $i++) {
        $result3 = $insurance_info[$i];
        ?>

     <div class="tab <?php echo $i == 1 ? 'current' : '' ?> h-auto w-auto">
      <div class="row">
        <div class="col-md-6">
         <table class="table table-borderless">
           <tr>
            <td class="align-top">
            <label class='required'><?php echo text($insurance_headings[$i - 1]) . "&nbsp;"?></label>
            </td>
            <td class='required'>:</td>
            <td class="form-row align-items-center">
                <div class="col-auto">
                    <a href="../../practice/ins_search.php" class="medium_modal btn btn-primary" onclick="ins_search(<?php echo attr_js($i); ?>)"><?php echo xlt('Search/Add') ?></a>
                </div>
                <div class="col-auto">
                     <select name="i<?php echo attr($i); ?>provider" class="form-control sel2" style="width: 250px;">
                     <option value=""><?php echo xlt('Unassigned'); ?></option>
                        <?php
                        foreach ($insurancei as $iid => $iname) {
                            echo "<option value='" . attr($iid) . "'";
                            if (strtolower($iid) == strtolower($result3["provider"])) {
                                echo " selected";
                            }

                            echo ">" . text($iname) . "</option>\n";
                        }
                        ?>
                       </select>
                </div>
              </td>
             </tr>

            <tr>
             <td>
              <label class='required'><?php echo xlt('Plan Name'); ?> </label>
             </td>
             <td class='required'>:</td>
             <td>
              <input type='entry' class='form-control' size='20' name='i<?php echo attr($i); ?>plan_name' value="<?php echo attr($result3["plan_name"]); ?>" onchange="capitalizeMe(this);" />&nbsp;&nbsp;
             </td>
            </tr>

            <tr>
             <td>
              <label class='required'><?php echo xlt('Effective Date'); ?></label>
             </td>
             <td class='required'>:</td>
             <td>
              <input type='entry' size='16' class='datepicker form-control' id='i<?php echo attr($i); ?>effective_date' name='i<?php echo attr($i); ?>effective_date' value='<?php echo attr(oeFormatShortDate($result3['date'])); ?>' />
             </td>
            </tr>

            <tr>
             <td><label class='required'><?php echo xlt('Policy Number'); ?></label></td>
             <td class='required'>:</td>
             <td><input type='entry' class='form-control' size='16' name='i<?php echo attr($i); ?>policy_number' value="<?php echo attr($result3["policy_number"]); ?>" onkeyup='policykeyup(this)'></td>
            </tr>

            <tr>
             <td><label class='required'><?php echo xlt('Group Number'); ?></label></td>
             <td class='required'>:</td>
             <td><input type="text" class='form-control' size='16' name=i<?php echo attr($i); ?>group_number value="<?php echo attr($result3["group_number"]); ?>" onkeyup='policykeyup(this)'></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display: none'";
               } ?>>
             <td class='required'><?php echo xlt('Subscriber Employer (SE)'); ?><br /><label style='font-weight: normal'>
              (<?php echo xlt('if unemployed enter Student'); ?>,<br /><?php echo xlt('PT Student, or leave blank'); ?>) </label></td>
              <td class='required'>:</td>
             <td><input type='entry' class='form-control' size='25' name='i<?php echo attr($i); ?>subscriber_employer' value="<?php echo attr($result3["subscriber_employer"]); ?>" onchange="capitalizeMe(this);" /></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display: none'";
               } ?>>
             <td><label class='required'><?php echo xlt('SE Address'); ?></label></td>
             <td class='required'>:</td>
             <td><input type='entry' class='form-control' size='25' name='i<?php echo attr($i); ?>subscriber_employer_street' value="<?php echo attr($result3["subscriber_employer_street"]); ?>" onchange="capitalizeMe(this);" /></td>
            </tr>

            <tr<?php if ($GLOBALS['omit_employers']) {
                echo " style='display:none'";
               } ?>>
             <td colspan="3">
              <table>
               <tr>
                <td><label class='required'><?php echo xlt('SE City'); ?>: </label></td>
                <td><input type='entry' class='form-control' size='15' name='i<?php echo attr($i); ?>subscriber_employer_city' value="<?php echo attr($result3["subscriber_employer_city"]); ?>" onchange="capitalizeMe(this);" /></td>
                <td><label class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE State') : xlt('SE Locality') ?>: </label></td>
            <td>
                <?php
                 // Modified 7/2009 by BM to incorporate data types
                generate_form_field(array('data_type' => $GLOBALS['state_data_type'],'field_id' => ('i' . $i . 'subscriber_employer_state'),'list_id' => $GLOBALS['state_list'],'fld_length' => '15','max_length' => '63','edit_options' => 'C'), $result3['subscriber_employer_state']);
                ?>
                </td>
               </tr>
               <tr>
                   <td>
                       <label for='i<?php echo attr($i); ?>subscriber_employer_postal_code' class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE Zip Code') : xlt('SE Postal Code') ?>: </label>
                   </td>
                   <td>
                       <input type='entry' class='form-control' size='15' name='i<?php echo attr($i); ?>subscriber_employer_postal_code' value="<?php echo attr($result3["subscriber_employer_postal_code"]); ?>" />
                   </td>
                   <td>
                       <label for='i<?php echo attr($i); ?>subscriber_employer_country' class='required'><?php echo xlt('SE Country'); ?>: </label>
                   </td>
                   <td>
                       <?php
                  // Modified 7/2009 by BM to incorporate data types
                        generate_form_field(array('data_type' => $GLOBALS['country_data_type'],'field_id' => ('i' . $i . 'subscriber_employer_country'),'list_id' => $GLOBALS['country_list'],'fld_length' => '10','max_length' => '63','edit_options' => 'C'), $result3['subscriber_employer_country']);
                        ?>
                   </td>
               </tr>
              </table>
             </td>
            </tr>

           </table>
          </div>

    <div class="col-md-6">
        <table class="table table-borderless">
            <tr>
                <td>
                    <label class='required'><?php echo xlt('Relationship'); ?></label>
                </td>
                <td class='required'>:</td>
                <td colspan='3'>
                <?php
                 // Modified 6/2009 by BM to use list_options and function
                 generate_form_field(array('data_type' => 1,'field_id' => ('i' . $i . 'subscriber_relationship'),'list_id' => 'sub_relation','empty_title' => ' '), $result3['subscriber_relationship']);
                ?>

                <a href="javascript:popUp('browse.php?browsenum=<?php echo attr_url($i); ?>')" class='text'>(<?php echo xlt('Browse'); ?>)</a>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td width='120'>
                    <label class='required'><?php echo xlt('Subscriber'); ?> </label>
                </td>
                <td class='required'>:</td>
                <td colspan='3'>
                    <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_fname' value="<?php echo attr($result3["subscriber_fname"]); ?>" onchange="capitalizeMe(this);" />
                    <input type='entry' class='form-control' size='3' name='i<?php echo attr($i); ?>subscriber_mname' value="<?php echo attr($result3["subscriber_mname"]); ?>" onchange="capitalizeMe(this);" />
                    <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_lname' value="<?php echo attr($result3["subscriber_lname"]); ?>" onchange="capitalizeMe(this);" />
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <label class='font-weight-bold'><?php echo xlt('D.O.B.'); ?> </label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='entry' size='11' class='datepicker form-control' id='i<?php echo attr($i); ?>subscriber_DOB' name='i<?php echo attr($i); ?>subscriber_DOB' value='<?php echo attr(oeFormatShortDate($result3['subscriber_DOB'])); ?>' />
                </td>
                <td>
                    <label class='font-weight-bold'><?php echo xlt('Sex'); ?>: </label>
                </td>
                <td>
                    <?php
                     // Modified 6/2009 by BM to use list_options and function
                     generate_form_field(array('data_type' => 1,'field_id' => ('i' . $i . 'subscriber_sex'),'list_id' => 'sex'), $result3['subscriber_sex']);
                    ?>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class='leftborder'>
                    <label class='font-weight-bold'><?php echo xlt('S.S.'); ?> </label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='entry' class='form-control' size='11' name='i<?php echo attr($i); ?>subscriber_ss' value="<?php echo attr(trim($result3["subscriber_ss"])); ?>" />
                </td>
            </tr>

            <tr>
                <td>
                    <label class='required'><?php echo xlt('Subscriber Address'); ?> </label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='entry' class='form-control' size='20' name='i<?php echo attr($i); ?>subscriber_street' value="<?php echo attr($result3["subscriber_street"]); ?>" onchange="capitalizeMe(this);" />
                </td>

                <td>
                    <label class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('State') : xlt('Locality') ?>: </label>
                </td>
                <td>
                    <?php
                    // Modified 7/2009 by BM to incorporate data types
                    generate_form_field(array('data_type' => $GLOBALS['state_data_type'],'field_id' => ('i' . $i . 'subscriber_state'),'list_id' => $GLOBALS['state_list'],'fld_length' => '15','max_length' => '63','edit_options' => 'C'), $result3['subscriber_state']);
                    ?>
                </td>
            </tr>
            <tr>
                <td class='leftborder'>
                    <label class='required'><?php echo xlt('City'); ?></label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='entry' class='form-control' size='11' name='i<?php echo attr($i); ?>subscriber_city' value="<?php echo attr($result3["subscriber_city"]); ?>" onchange="capitalizeMe(this);" />
                </td>
                <td class='leftborder'>
                    <label class='required'<?php if ($GLOBALS['omit_employers']) {
                        echo " style='display:none'"; } ?>><?php echo xlt('Country'); ?>: </label>
                </td>
                <td>
                    <?php
                    // Modified 7/2009 by BM to incorporate data types
                    generate_form_field(array('data_type' => $GLOBALS['country_data_type'],'field_id' => ('i' . $i . 'subscriber_country'),'list_id' => $GLOBALS['country_list'],'fld_length' => '10','max_length' => '63','edit_options' => 'C'), $result3['subscriber_country']);
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class='required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Zip Code') : xlt('Postal Code') ?> </label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_postal_code' value="<?php echo attr($result3["subscriber_postal_code"]); ?>" />
                </td>

                <td colspan='2'></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <label class='bold'><?php echo xlt('Subscriber Phone'); ?></label>
                </td>
                <td class='required'>:</td>
                <td>
                    <input type='text' class='form-control' size='20' name='i<?php echo attr($i); ?>subscriber_phone' value='<?php echo attr($result3["subscriber_phone"]); ?>' onkeyup='phonekeyup(this,mypcc)' />
                </td>
                <td colspan='2'>
                    <label class='bold'><?php echo xlt('CoPay'); ?>: <input type='text' class='form-control' size="6" name='i<?php echo attr($i); ?>copay' value="<?php echo attr($result3["copay"]); ?>"></label>
                </td>
                <td colspan='2'>
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan='0'>
                    <label class='required'><?php echo xlt('Accept Assignment'); ?></label>
                </td>
                <td class='required'>:</td>
                <td colspan='2'>
                    <select class='form-control' name='i<?php echo attr($i); ?>accept_assignment'>
                     <option value="TRUE" <?php if (strtoupper($result3["accept_assignment"]) == "TRUE") {
                            echo "selected"; }?>><?php echo xlt('YES'); ?></option>
                     <option value="FALSE" <?php if (strtoupper($result3["accept_assignment"]) == "FALSE") {
                            echo "selected"; }?>><?php echo xlt('NO'); ?></option>
                    </select>
                </td>
                <td></td>
                <td></td>
                <td colspan='2'></td>
                <td></td>
            </tr>
            <?php if (!$GLOBALS['insurance_only_one']) { ?>
                <tr>
                    <td>
                        <label class='bold'><?php echo xlt('Secondary Medicare Type'); ?></label>
                    </td>
                    <td class='bold'>:</td>
                    <td colspan='6'>
                        <select class='form-control sel2' name='i<?php echo attr($i); ?>policy_type'>
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
                </tr>
            <?php } ?>
      </table>

    </div>
</div>
</div>

        <?php
    } //end insurer for loop ?>

   </div>
</div>

<?php } // end of "if not simplified_demographics" ?>
</div>
</div>
</form>

<br />

<script>

// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];

// hard code validation for old validation, in the new validation possible to add match rules
<?php if ($GLOBALS['new_validate'] == 0) { ?>
 // fix inconsistently formatted phone numbers from the database
 var f = document.forms[0];
 if (f.form_phone_contact) phonekeyup(f.form_phone_contact,mypcc);
 if (f.form_phone_home   ) phonekeyup(f.form_phone_home   ,mypcc);
 if (f.form_phone_biz    ) phonekeyup(f.form_phone_biz    ,mypcc);
 if (f.form_phone_cell   ) phonekeyup(f.form_phone_cell   ,mypcc);

    <?php if (! $GLOBALS['simplified_demographics']) { ?>
 phonekeyup(f.i1subscriber_phone,mypcc);
 phonekeyup(f.i2subscriber_phone,mypcc);
 phonekeyup(f.i3subscriber_phone,mypcc);
    <?php } ?>

<?php }?>

<?php if ($set_pid) { ?>
 parent.left_nav.setPatient(<?php echo js_escape($result['fname'] . " " . $result['lname']) . "," . js_escape($pid) . "," . js_escape($result['pubpid']) . ",''," . js_escape(" " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($result['DOB_YMD'])); ?>);
<?php } ?>

<?php echo $date_init; ?>
</script>

<!-- include support for the list-add selectbox feature -->
<?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>

<?php /*Include the validation script and rules for this form*/
$form_id = "DEM";
//LBF forms use the new validation depending on the global value
$use_validate_js = $GLOBALS['new_validate'];

?>
<?php  include_once("$srcdir/validation/validation_script.js.php");?>


<script>
    var duplicateFieldsArray=[];

//This code deals with demographics before save action -
    <?php if (($GLOBALS['gbl_edit_patient_form'] == '1') && (checkIfPatientValidationHookIsActive())) :?>
                //Use the Zend patient validation hook.
                //TODO - get the edit part of patient validation hook to work smoothly and then
                //       remove the closeBeforeOpening=1 in the url below.

        var f = $("form");

        // Use hook to open the controller and get the new patient validation .
        // when no params are sent this window will be closed from the zend controller.
        var url ='<?php echo  $GLOBALS['web_root'] . "/interface/modules/zend_modules/public/patientvalidation";?>';
        $("#submit_btn").attr("name","btnSubmit");
        $("#submit_btn").attr("id","btnSubmit");
        $("#btnSubmit").click(function( event ) {

      top.restoreSession();

            if(!submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'DEM',constraints)){
                event.preventDefault();
                return;
            }
            somethingChanged = false;
            <?php
            // D in edit_options indicates the field is used in duplication checking.
            // This constructs a list of the names of those fields.
            $mflist = "";
            $mfres = sqlStatement("SELECT field_id FROM layout_options " .
                "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' AND " .
                "(edit_options LIKE '%D%' OR edit_options LIKE '%E%')  " .
                "ORDER BY group_id, seq");
            while ($mfrow = sqlFetchArray($mfres)) {
                $field_id  = $mfrow['field_id'];
                if (strpos($field_id, 'em_') === 0) {
                    continue;
                }

                if (!empty($mflist)) {
                    $mflist .= ",";
                }

                    $mflist .= js_escape($field_id);
            } ?>

            var flds = new Array(<?php echo $mflist; ?>);
            var separator = '?';
            var valueIsChanged=false;
            for (var i = 0; i < flds.length; ++i) {
                var fval = $('#form_' + flds[i]).val();
                if(duplicateFieldsArray['#form_' + flds[i]]!=fval) {
                    valueIsChanged = true;

                }

                if (fval && fval != '') {
                    url += separator;
                    separator = '&';
                    url += 'mf_' + flds[i] + '=' + encodeURIComponent(fval);
                }
            }


            //Only if check for duplicates values are changed open the popup hook screen
            if(valueIsChanged) {
                event.preventDefault();
                //("value has changed for duplicate check inputs");
            url += '&page=edit&closeBeforeOpening=1&mf_id=' + encodeURIComponent($("[name='db_id']").val());
            dlgopen(url, '_blank', 700, 500);
            }
            else {//other wise submit me is a success just submit the form
                $('#DEM').submit();
            }
        });

    <?php endif;?>

    $(function () {
        //When document is ready collect all the values Marked with D (check duplicate) stored in the db into array duplicateFieldsArray.
        var flds = new Array(<?php echo ($mflist ?? ''); ?>);
        for (var i = 0; i < flds.length; ++i) {
            var fval = $('#form_' + flds[i]).val();
            duplicateFieldsArray['#form_' + flds[i]] = fval;
        }
        $(".sel2").select2({
            <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
        });
    })
</script>

</body>
</html>
