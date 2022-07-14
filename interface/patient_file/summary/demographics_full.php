<?php

/**
 * Edit demographics.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
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
$set_pid = $_GET["set_pid"] ?? ($_GET["pid"] ?? null);
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
?>
<!DOCTYPE html>
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

    $(".select-previous-names").select2({
        theme: "bootstrap4",
        dropdownAutoWidth: true,
        width: 'resolve',
        <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
    }).on("select2:unselecting", function (e) {
        $(this).data('state', 'unselected');
        var data = e.params.args.data;
        const message = "<span>" + xl("Are You Sure you want to delete this name?") + "</span>";
        const ele = opener.document.getElementById('form_name_history');
        dialog.confirm(message).then(returned => {
            if (returned !== true) {
                if (data !== false) {
                    $(".select-previous-names > option").prop("selected", "selected").trigger("change");
                }
                return false;
            }
            // delete from table.
            const url = top.webroot_url + '/library/ajax/specialty_form_ajax.php?delete=true';
            let doData = new FormData();
            doData.append('csrf_token_form', <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
            doData.append('id', data.id);
            doData.append('task_name_history', 'delete');
            fetch(url, {
                method: 'POST',
                body: doData
            }).then(rtn => rtn.json()).then((rtn) => {
                dialog.alert(xl("Returned: " + rtn));
                if (rtn === 'Success') {
                    $(".select-previous-names option[value=" + data.id + "]").remove();
                }
            });
        });
    }).on("select2:open", function (e) {
        if ($(this).data('state') === 'unselected') {
            $(this).removeData('state');
            let self = $(this);
            setTimeout(function () {
                self.select2('close');
            }, 1);
        }
    }).on('select2:opening select2:closing', function (event) {
        let $search = $(this).parent().find('.select2-search__field');
        $search.prop('disabled', true);
    });

    // careteam select2
    $(".select-dropdown").select2({
        theme: "bootstrap4",
        dropdownAutoWidth: true,
        width: 'resolve',
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
        dlgopen('', '', 700, 600, '', title, {
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
  f.i<?php echo attr($i); ?>subscriber_street_line_2.value=f.form_street_line_2.value;
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
  f.i<?php echo attr($i); ?>subscriber_employer_street_line_2.value=f.form_em_street_line_2.value;
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

function address_verify() {
    top.restoreSession();
    var f = document.demographics_form;

    dlgopen('../../practice/address_verify.php?address1=' + encodeURIComponent(f.form_street.value) +
    '&address2=' + encodeURIComponent(f.form_street_line_2.value) +
    '&city=' + encodeURIComponent(f.form_city.value) +
    '&state=' + encodeURIComponent(f.form_state.value) +
    '&zip5=' + encodeURIComponent(f.form_postal_code.value.substring(0,5)) +
    '&zip4=' + encodeURIComponent(f.form_postal_code.value.substring(5,9))
    , '_blank', 400, 150, '', xl('Address Verify'));

    return false;
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

// Added 06/2009 by BM to make compatible with list_options table and functions - using jquery
$(function () {

    <?php for ($i = 1; $i <= 3; $i++) { ?>
  $("#form_i<?php echo attr($i); ?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo attr($i); ?>(); });
    <?php } ?>

});

</script>

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

        <?php
        if (!empty($GLOBALS['right_justify_labels_demographics']) && ($_SESSION['language_direction'] == 'ltr')) { ?> 
        div.label_custom {
            text-align: right !important;
        }

        div.tab td.data, div.data {
            padding-left: 0.5em;
            padding-right: 2em;
        }
            <?php
        }  ?>
</style>

</head>

<?php
/*Get the constraint from the DB-> LBF forms accordinf the form_id*/
$constraints = LBF_Validation::generate_validate_constraints("DEM");
?>
<script> var constraints = <?php echo $constraints;?>; </script>

<body class="body_top">

<form action='demographics_save.php' name='demographics_form' id="DEM" method='post' class='form-inline'
 onsubmit="submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'DEM',constraints)">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type='hidden' name='mode' value='save' />
<input type='hidden' name='db_id' value="<?php echo attr($result['id']); ?>" />

    <div class="container-xl">
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
$condition_str = '';
?>
<br />
<div class='container-xl demographicsEditContainer'>
    <div class="section-header">
        <span class="text font-weight-bold"><?php echo xlt("Demographics")?></span>
    </div>
    <ul class="tabNav">
        <?php display_layout_tabs('DEM', $result, $result2); ?>
    </ul>

    <div class="tabContainer">
        <?php display_layout_tabs_data_editable('DEM', $result, $result2); ?>
    </div>
</div>
<br />

<div class='container-xl'>

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
    <div id="INSURANCE" class="insuranceEditContainer">
       <ul class="tabNav">
        <?php
        foreach ($insurance_array as $instype) {
            ?>
            <li <?php echo $instype == 'primary' ? 'class="current"' : '' ?>><a href="#"><?php $CapInstype = ucfirst($instype);
            echo xlt($CapInstype); ?></a></li><?php } ?>
        </ul>

    <div class="tabContainer">

    <?php
    for ($i = 1; $i <= 3; $i++) {
        $result3 = $insurance_info[$i];
        ?>

     <div class="tab <?php echo $i == 1 ? 'current' : '' ?> h-auto w-auto">
      <div class="form-row">
        <div class="col-md-6"><!-- start left column -->

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 label_custom pb-3">
              <span class='required'><?php echo text($insurance_headings[$i - 1]); ?>:</span>
            </div>
            <div class="col-md-9">
              <a href="../../practice/ins_search.php" class="medium_modal btn btn-primary"
               onclick="ins_search(<?php echo attr_js($i); ?>)"><?php echo xlt('Search/Add') ?></a>
              <select name="i<?php echo attr($i); ?>provider" class="form-control form-control-sm sel2 mb-1" style="width: 250px;">
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

          <div class="form-row"><!-- start nested row -->
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

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('Subscriber Phone'); ?>:</span>
            </div>
            <div class="col-md-9">
              <input type='text' class='form-control form-control-sm mb-1' size='20'
               name='i<?php echo attr($i); ?>subscriber_phone'
               value='<?php echo attr($result3["subscriber_phone"] ?? ''); ?>'
               onkeyup='phonekeyup(this,mypcc)' />
            </div>
          </div><!-- end nested row -->

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

          <div class="form-row"><!-- start nested row -->
            <div class="col-md-3 pb-1 label_custom">
              <span><?php echo xlt('Secondary Medicare Type'); ?>:</span>
            </div>
            <div class="col-md-9">
              <select class='form-control form-control-sm mb-1 sel2' name='i<?php echo attr($i); ?>policy_type'>
                <?php
                if (!empty($policy_types)) {
                    foreach ($policy_types as $key => $value) {
                        echo "            <option value ='" . attr($key) . "'";
                        if (!empty($result3['policy_type']) && ($key == $result3['policy_type'])) {
                            echo " selected";
                        }
                        echo ">" . text($value) . "</option>\n";
                    }
                }
                ?>
              </select>
            </div>
          </div><!-- end nested row -->

        </div><!-- end right column -->
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
            theme: "bootstrap4",
            dropdownAutoWidth: true,
            width: 'resolve',
        <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
        });

        <?php if ($GLOBALS['usps_webtools_enable']) { ?>
            $("#value_id_text_postal_code").append(
                "<input type='button' class='btn btn-sm btn-secondary mb-1' onclick='address_verify()' value='<?php echo xla('Verify Address') ?>' />");
        <?php } ?>
    })
</script>

</body>
</html>
