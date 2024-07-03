<?php

/**
 * Edit demographics.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/validation/LBF_Validation.php");
require_once("$srcdir/patientvalidation.inc.php");
require_once("$srcdir/pid.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Forms\FormActionBarSettings;
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
    $updateEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($updateEvent, UpdateEvent::EVENT_HANDLE, 10);

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
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker','common','select2', 'erx']);
?>
<title><?php echo xlt('Edit Current Patient'); ?></title>

<?php include_once($GLOBALS['srcdir'] . "/options.js.php"); ?>

<script>

// Support for beforeunload handler.
var somethingChanged = false;

$(function () {
    tabbify();

    $('.swapIns').hide();

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

 return errMsgs.length < 1;
}

</script>

<style>
        div.demographicsEditContainer div.label_custom {
            font-size: 0.8rem;
            display: grid;
            align-items: normal;
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
<input type="hidden" name="isSwapClicked" value="" />

    <div class="container-xl">
        <div class="row">
            <?php if (FormActionBarSettings::shouldDisplayTopActionBar()) { ?>
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
            <?php } else { ?>
            <div class="col-12">
                <h2><?php echo xlt('Edit Current Patient');?></h2>
            </div>
            <?php } ?>
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
<?php if (FormActionBarSettings::shouldDisplayBottomActionBar()) { ?>
<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <hr>
            <div class="btn-group">
                <button type="submit" class="btn btn-primary btn-save" id="submit_btn" disabled="disabled" value="<?php echo xla('Save'); ?>">
                    <?php echo xlt('Save'); ?>
                </button>
                <a class="btn btn-secondary btn-cancel" href="demographics.php" onclick="top.restoreSession()">
                    <?php echo xlt('Cancel'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php } ?>
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
<?php require $GLOBALS['fileroot'] . "/library/options_listadd.inc.php"; ?>

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
