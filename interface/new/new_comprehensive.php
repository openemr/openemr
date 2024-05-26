<?php

/**
 * New patient or search patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2009-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/validation/LBF_Validation.php");
require_once("$srcdir/patientvalidation.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Check authorization.
if (!AclMain::aclCheckCore('patients', 'demo', '', array('write','addonly'))) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Search or Add Patient")]);
    exit;
}

$CPR = 4; // cells per row

$searchcolor = empty($GLOBALS['layout_search_color']) ?
  'var(--yellow)' : $GLOBALS['layout_search_color'];

$WITH_SEARCH = ($GLOBALS['full_new_patient_form'] == '1' || $GLOBALS['full_new_patient_form'] == '2' );
$SHORT_FORM  = ($GLOBALS['full_new_patient_form'] == '2' || $GLOBALS['full_new_patient_form'] == '3' || $GLOBALS['full_new_patient_form'] == '4');

$grparr = array();
getLayoutProperties('DEM', $grparr, '*');

$TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

function getLayoutRes()
{
    global $SHORT_FORM;
    return sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
    ($SHORT_FORM ? "AND ( uor > 1 OR edit_options LIKE '%N%' ) " : "") .
    "ORDER BY group_id, seq");
}

// Determine layout field search treatment from its data type:
// 1 = text field
// 2 = select list
// 0 = not searchable
//
function getSearchClass($data_type)
{
    switch ($data_type) {
        case 1: // single-selection list
        case 10: // local provider list
        case 11: // provider list
        case 12: // pharmacy list
        case 13: // squads
        case 14: // address book list
        case 26: // single-selection list with add
        case 35: // facilities
            return 2;
        case 2: // text field
        case 3: // textarea
        case 4: // date
            return 1;
    }

    return 0;
}

$fres = getLayoutRes();
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['common','datetime-picker','select2', 'erx']); ?>
<title><?php echo xlt("Search or Add Patient"); ?></title>
<style>
.form-group {
    margin-bottom: 0.25rem;
}
</style>

<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script><!--
//Visolve - sync the radio buttons - Start
if((top.window.parent) && (parent.window)){
        var wname = top.window.parent.left_nav;
        fname = (parent.window.name)?parent.window.name:window.name;
        wname.syncRadios();
}//Visolve - sync the radio buttons - End

var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

// This may be changed to true by the AJAX search script.
var force_submit = false;

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

<?php for ($i = 1; $i <= 3; $i++) { ?>
function auto_populate_employer_address<?php echo $i ?>(){
 var f = document.demographics_form;
 if (f.form_i<?php echo $i?>subscriber_relationship.options[f.form_i<?php echo $i?>subscriber_relationship.selectedIndex].value == "self") {
  f.i<?php echo $i?>subscriber_fname.value=f.form_fname.value;
  f.i<?php echo $i?>subscriber_mname.value=f.form_mname.value;
  f.i<?php echo $i?>subscriber_lname.value=f.form_lname.value;
  f.i<?php echo $i?>subscriber_street.value=f.form_street.value;
  f.i<?php echo $i?>subscriber_street_line_2.value=f.form_street_line_2.value;
  f.i<?php echo $i?>subscriber_city.value=f.form_city.value;
  f.form_i<?php echo $i?>subscriber_state.value=f.form_state.value;
  f.i<?php echo $i?>subscriber_postal_code.value=f.form_postal_code.value;
  if (f.form_country_code)
    f.form_i<?php echo $i?>subscriber_country.value=f.form_country_code.value;
  f.i<?php echo $i?>subscriber_phone.value=f.form_phone_home.value;
  f.i<?php echo $i?>subscriber_DOB.value=f.form_DOB.value;
  f.i<?php echo $i?>subscriber_ss.value=f.form_ss.value;
  f.form_i<?php echo $i?>subscriber_sex.value = f.form_sex.value;
  f.i<?php echo $i?>subscriber_employer.value=f.form_em_name.value;
  f.i<?php echo $i?>subscriber_employer_street.value=f.form_em_street.value;
  f.i<?php echo $i?>subscriber_employer_city.value=f.form_em_city.value;
  f.form_i<?php echo $i?>subscriber_employer_state.value=f.form_em_state.value;
  f.i<?php echo $i?>subscriber_employer_postal_code.value=f.form_em_postal_code.value;
  if (f.form_em_country)
    f.form_i<?php echo $i?>subscriber_employer_country.value=f.form_em_country.value;
 }
}

<?php } ?>

function upperFirst(string,text) {
 return replace(string,text,text.charAt(0).toUpperCase() + text.substring(1,text.length));
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

// Indicates which insurance slot is being updated.
var insurance_index = 0;

// The OnClick handler for searching/adding the insurance company.
function ins_search(ins) {
 insurance_index = ins;
 return false;
}

function checkNum () {
 var re= new RegExp();
 re = /^\d*\.?\d*$/;
 str=document.forms[0].monthly_income.value;
 if(re.exec(str))
 {
 }else{
  alert(<?php echo xlj("Please enter a dollar amount using only numbers and a decimal point."); ?>);
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

function validate(f) {
  var errMsgs = new Array();
    <?php generate_layout_validation('DEM'); ?>
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
  var msg = "";
  msg += <?php echo xlj('The following fields are required'); ?> + ":\n\n";
  for ( var i = 0; i < errMsgs.length; i++ ) {
         msg += errMsgs[i] + "\n";
  }
  msg += "\n" + <?php echo xlj('Please fill them in before continuing.'); ?>;


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
if(errMsgs.length > 0 || dateVal > currentDate) {
  if(errMsgs.length > 0) {
    alert(msg);
  }
  if(dateVal > currentDate) {
    alert(<?php echo xlj("Deceased Date should not be greater than Today"); ?>);
    return false;
  }
}
 return true;
}

function toggleSearch(elem) {
 var f = document.forms[0];
<?php if ($WITH_SEARCH) { ?>
 // Toggle background color.
 if (elem.style.backgroundColor == '')
  elem.style.backgroundColor = <?php echo js_escape($searchcolor); ?>;
 else
  elem.style.backgroundColor = '';

 if (!elem.classList.contains("is-invalid") && $.trim($(elem).val()) == '') {
  elem.classList.add("is-invalid");
} else {
  if($.trim($(elem).val()) != '') {
    elem.classList.remove("is-invalid");
    elem.classList.remove("is-valid");
  }
}
<?php } ?>
 if (force_submit) {
  force_submit = false;
  f.create.value = <?php echo xlj('Create New Patient'); ?>;
 }
 return true;
}

// If a <select> list is dropped down, this is its name.
var open_sel_name = '';

function selClick(elem) {
 if (open_sel_name == elem.name) {
  open_sel_name = '';
 }
 else {
  open_sel_name = elem.name;
  toggleSearch(elem);
 }
 return true;
}

function selBlur(elem) {
 if (open_sel_name == elem.name) {
  open_sel_name = '';
 }
 return true;
}

// This invokes the patient search dialog.
function searchme() {
 var f = document.forms[0];
 var url = '../main/finder/patient_select.php?popup=1&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>';

<?php
$lres = getLayoutRes();

while ($lrow = sqlFetchArray($lres)) {
    $field_id  = $lrow['field_id'];
    if (strpos($field_id, 'em_') === 0) {
        continue;
    }

    $data_type = $lrow['data_type'];
    $fldname = "form_$field_id";
    switch (getSearchClass($data_type)) {
        case 1:
            echo
            " if (f." . attr($fldname) . ".style.backgroundColor != '' && trimlen(f." . attr($fldname) . ".value) > 0) {\n" .
            "  url += '&" . attr($field_id) . "=' + encodeURIComponent(f." . attr($fldname) . ".value);\n" .
            " }\n";
            break;
        case 2:
            echo
            " if (f." . attr($fldname) . ".style.backgroundColor != '' && f." . attr($fldname) . ".selectedIndex > 0) {\n" .
            "  url += '&" . attr($field_id) . "=' + encodeURIComponent(f." . attr($fldname) . ".options[f." . attr($fldname) . ".selectedIndex].value);\n" .
            " }\n";
            break;
    }
}
?>

 dlgopen(url, '_blank', 700, 500);
}
function srchDone(pid){
    top.restoreSession();
    document.location.href = "./../../patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(pid);
}
//-->

</script>
</head>

<body class="body_top">

<?php
/*Get the constraint from the DB-> LBF forms accordinf the form_id*/
$constraints = LBF_Validation::generate_validate_constraints("DEM");
?>
<script> var constraints = <?php echo $constraints; ?>; </script>
    <div class="container-xl">
        <div class="row">
            <div class="col-md-12">
                <h2><?php echo xlt('Search or Add Patient');?></h2>
            </div>
        </div>
        <div class="row">
            <div class="<?php echo $BS_COL_CLASS; ?>-12">
                <div class="accordion" id="dem_according">
                <form action='new_comprehensive_save.php' name='demographics_form' id='DEM'
                      method='post'
                      onsubmit='return submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,"DEM",constraints)'>
                    <!--  Was: class='form-inline' -->
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                    <table class='table table-sm w-100' cellspacing='8'>
                    <tr>
                      <td class="text-left align-top">
                    <?php
                    if ($SHORT_FORM) {
                        echo "<div class='mx-auto'>";
                    } ?>
                    <?php

                    function end_cell()
                    {
                        global $item_count;
                        if ($item_count > 0) {
                            echo "</div>"; // end BS column
                            $item_count = 0;
                        }
                    }

                    function end_row()
                    {
                        global $cell_count, $CPR, $BS_COL_CLASS;
                        end_cell();
                        if ($cell_count > 0 && $cell_count < $CPR) {
                            // Create a cell occupying the remaining bootstrap columns.
                            // BS columns will be less than 12 if $CPR is not 2, 3, 4, 6 or 12.
                            $bs_cols_remaining = ($CPR - $cell_count) * intval(12 / $CPR);
                            echo "<div class='$BS_COL_CLASS-$bs_cols_remaining'></div>";
                        }
                        if ($cell_count > 0) {
                            echo "</div><!-- End BS row -->\n";
                        }
                        $cell_count = 0;
                    }

                    function end_group()
                    {
                        global $last_group, $SHORT_FORM;
                        if (strlen($last_group) > 0) {
                            end_row();
                            echo "</div>\n"; // end BS container
                            if (!$SHORT_FORM) {
                                echo "</div>\n";
                            }
                        }
                        echo "</div>";
                    }

                    $last_group    = '';
                    $cell_count    = 0;
                    $item_count    = 0;
                    $display_style = 'block';
                    $group_seq     = 0; // this gives the DIV blocks unique IDs
                    $condition_str = '';

                    while ($frow = sqlFetchArray($fres)) {
                        $this_group = $frow['group_id'];
                        $titlecols  = $frow['titlecols'];
                        $datacols   = $frow['datacols'];
                        $data_type  = $frow['data_type'];
                        $field_id   = $frow['field_id'];
                        $list_id    = $frow['list_id'];
                        $currvalue  = '';

                        // Accumulate action conditions into a JSON expression for the browser side.
                        accumActionConditions($frow, $condition_str);

                        if (strpos($field_id, 'em_') === 0) {
                            $tmp = substr($field_id, 3);
                            if (isset($result2[$tmp])) {
                                $currvalue = $result2[$tmp];
                            }
                        } else {
                            if (isset($result[$field_id])) {
                                $currvalue = $result[$field_id];
                            }
                        }

                        // Handle a data category (group) change.
                        if (strcmp($this_group, $last_group) != 0) {
                            if (!$SHORT_FORM) {
                                end_group();
                                $group_seq++;    // ID for DIV tags
                                $group_name = $grparr[$this_group]['grp_title'];

                                $group_seq_attr = attr($group_seq);
                                $checked = ($display_style == 'block') ? "show" : "";
                                $group_name_xl = text(xl_layout_label($group_name));
                                $onclick = attr_js("div_" . $group_seq);
                                $init_open = $grparr[$this_group]['grp_init_open'];
                                if ($checked != "show") {
                                    $checked = ($init_open == 1) ? $checked . " show" : $checked;
                                }
                                echo <<<HTML
                                <div class="card">
                                    <div class="card-header p-0 bg-secondary" id="header_{$group_seq_attr}">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link btn-block text-light text-left" type="button" data-toggle="collapse" data-target="#div_{$group_seq_attr}" aria-expanded="true" aria-controls="{$group_seq_attr}">$group_name_xl</button>
                                        </h2>
                                    </div>
                                    <div id="div_{$group_seq_attr}" class="bg-light collapse {$checked}" aria-labelledby="header_{$group_seq_attr}" >
                                        <div class="container-xl card-body">
                                HTML;
                                $display_style = 'none';
                            } elseif (strlen($last_group) == 0) {
                                echo " <div class='container-xl'>\n";
                            }
                            $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];
                            $last_group = $this_group;
                        }

                      // Handle starting of a new row.
                        if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
                            end_row();
                            echo "<div class='form-group row'>";
                        }

                        if ($item_count == 0 && $titlecols == 0) {
                            $titlecols = 1;
                        }

                        $field_id_label = 'label_' . $frow['field_id'];

                        // Handle starting of a new label cell.
                        if ($titlecols > 0) {
                            end_cell();
                            $bs_cols = $titlecols * intval(12 / $CPR);
                            echo "<div class='$BS_COL_CLASS-$bs_cols ";
                            echo ($frow['uor'] == 2) ? "required" : "";
                            echo "' id='" . attr($field_id_label) . "'";
                            echo ">";
                            $cell_count += $titlecols;
                        }

                        // $item_count is the number of title and data items in the current cell.
                        ++$item_count;

                        if ($datacols == 0) {
                            // Data will be in the same cell, so prevent wrapping between title and data.
                            echo "<span class='text-nowrap mr-2'>"; // mb-2 doesn't work here
                        }


                        // Modified 6-09 by BM - Translate if applicable
                        if ($frow['title']) {
                            echo (text(xl_layout_label($frow['title'])) . ":");
                        }

                        // Handle starting of a new data cell.
                        if ($datacols > 0) {
                            $id_field_text = "text_" . $frow['field_id'];
                            end_cell();
                            $bs_cols = $datacols * intval(12 / $CPR);
                            echo "<div class='$BS_COL_CLASS-$bs_cols'";
                            echo " id='" . attr($id_field_text) . "'";
                            echo ">";
                            $cell_count += $datacols;
                        }

                        ++$item_count;

                        if ($item_count > 1) {
                            echo "&nbsp;";
                        }

                        // 'smallform' can be used to add arbitrary CSS classes. Note the leading space.
                        $frow['smallform'] = ' form-control-sm mw-100' . ($datacols ? '' : ' mb-1');

                        // set flag so we don't bring in session pid data for a new pt form
                        $frow['blank_form'] = false;
                        if (
                            $frow['data_type'] == "52"
                            || $frow['data_type'] == "53"
                            || $frow['data_type'] == "54"
                        ) {
                            $frow['blank_form'] = true;
                        }
                        generate_form_field($frow, $currvalue);

                        if ($datacols == 0) {
                            // End nowrap
                            echo "</span> "; // space to allow wrap between spans
                        }
                    }

                    end_group();
                    ?>

                    <?php
                    if (!$GLOBALS['simplified_demographics']) {
                        $insurancei = getInsuranceProviders();
                        $pid = 0;
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
                        $insuranceTitle = xlt("Insurance");
                        echo <<<HTML
                        <div class="card">
                            <div class="card-header p-0 bg-secondary" id="header_ins">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-light text-left" type="button" data-toggle="collapse" data-target="#div_ins" aria-expanded="true" aria-controls="ins">$insuranceTitle</button>
                                </h2>
                            </div>
                            <div id="div_ins" class="bg-light collapse" aria-labelledby="header_ins" >
                                <div class="container-xl card-body">
                        HTML;

                        for ($i = 1; $i <= sizeof($insurance_info); $i++) {
                            $result3 = $insurance_info[$i];
                            ?>
                        <div class="row p-3">
                          <div class="col-md-12 mb-2">
                            <div class="input-group">
                              <label class='col-form-label mr-2 required'><?php echo text($insurance_headings[$i - 1]) . ":"?></label>
                              <select name="i<?php echo attr($i); ?>provider" class="form-control">
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
                              <div class="input-group-append">
                                <a class='btn btn-primary text-white medium_modal' href='../practice/ins_search.php' onclick='ins_search(<?php echo attr_js($i); ?>)'><?php echo xlt('Search/Add Insurer'); ?></a>
                              </div>
                            </div>
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Plan Name'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='20' name='i<?php echo attr($i); ?>plan_name' value="<?php echo attr($result3["plan_name"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Subscriber'); ?>:</label>
                          <div class="col-md-5 mb-2 form-inline">
                            <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_fname' value="<?php echo attr($result3["subscriber_fname"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                            <input type='entry' class='form-control' size='3' name='i<?php echo attr($i); ?>subscriber_mname' value="<?php echo attr($result3["subscriber_mname"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                            <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_lname' value="<?php echo attr($result3["subscriber_lname"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Effective Date'); ?>: </label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' size='11' class='datepicker form-control' name='i<?php echo attr($i); ?>effective_date' id='i<?php echo attr($i); ?>effective_date' value='<?php echo attr($result3['date'] ?? ''); ?>' />
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Relationship'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => 1,'field_id' => ('i' . $i . 'subscriber_relationship'),'list_id' => 'sub_relation','empty_title' => ' ', 'smallform' => 'true'), ($result3['subscriber_relationship'] ?? ''));
                            ?>
                            <a href="javascript:popUp('../../interface/patient_file/summary/browse.php?browsenum=<?php echo attr_url($i); ?>')" class='text'>(<?php echo xlt('Browse'); ?>)</a>
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Policy Number'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='16' name='i<?php echo attr($i); ?>policy_number' value="<?php echo attr($result3["policy_number"] ?? ''); ?>" onkeyup='policykeyup(this)' />
                          </div>
                          <label class='col-form-label col-md-1 mb-2'><?php echo xlt('D.O.B.'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' size='11' class='datepicker-past form-control' name='i<?php echo attr($i); ?>subscriber_DOB' id='i<?php echo attr($i); ?>subscriber_DOB' value='<?php echo attr($result3['subscriber_DOB'] ?? ''); ?>' />
                          </div>
                          <label class='col-form-label col-md-1 required'><?php echo xlt('Group Number'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='16' name='i<?php echo attr($i); ?>group_number' value="<?php echo attr($result3["group_number"] ?? ''); ?>" onkeyup='policykeyup(this)' />
                          </div>
                          <label class='col-form-label col-md-1 mb-2'><?php echo xlt('S.S.'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='11' name='i<?php echo attr($i); ?>subscriber_ss' value="<?php echo attr($result3["subscriber_ss"] ?? ''); ?>" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                            <label class="col-form-label col-md-1 mb-2 required"><?php echo xlt('Subscriber Employer (SE)'); ?>:</label>
                            <div class="col-md-5 mb-2">
                              <input type='entry' class='form-control' aria-describedby="seHelpBlock" size='25' name='i<?php echo attr($i); ?>subscriber_employer' value="<?php echo attr($result3["subscriber_employer"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                              <small id="seHelpBlock" class="form-text text-muted">
                                <?php echo xlt('if unemployed enter Student'); ?>, <?php echo xlt('PT Student, or leave blank'); ?>.
                              </small>
                            </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2'><?php echo xlt('Sex'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => 1,'field_id' => ('i' . $i . 'subscriber_sex'),'list_id' => 'sex', 'smallform' => 'true'), $result3['subscriber_sex'] ?? '');
                            ?>
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                            <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('SE Address'); ?>:</label>
                            <div class="col-md-5 mb-2">
                              <input type='entry' class='form-control' size='25' name='i<?php echo attr($i); ?>subscriber_employer_street' value="<?php echo attr($result3["subscriber_employer_street"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                            </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Subscriber Address Line 1'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='25' name='i<?php echo attr($i); ?>subscriber_street' value="<?php echo attr($result3["subscriber_street"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Subscriber Address Line 2'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='25' name='i<?php echo attr($i); ?>subscriber_street_line_2' value="<?php echo attr($result3["subscriber_street_line_2"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('SE City'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='15' name='i<?php echo attr($i); ?>subscriber_employer_city' value="<?php echo attr($result3["subscriber_employer_city"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('City'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='15' name='i<?php echo attr($i); ?>subscriber_city' value="<?php echo attr($result3["subscriber_city"] ?? ''); ?>" onchange="capitalizeMe(this);" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE State') : xlt('SE Locality') ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => $GLOBALS['state_data_type'],'field_id' => ('i' . $i . 'subscriber_employer_state'),'list_id' => $GLOBALS['state_list'],'fld_length' => '15','max_length' => '63','edit_options' => 'C', 'smallform' => 'true'), ($result3['subscriber_employer_state'] ?? ''));
                            ?>
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('State') : xlt('Locality') ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => $GLOBALS['state_data_type'], 'field_id' => ('i' . $i . 'subscriber_state'),'list_id' => $GLOBALS['state_list'],'fld_length' => '15','max_length' => '63','edit_options' => 'C', 'smallform' => 'true'), ($result3['subscriber_state'] ?? ''));
                            ?>
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE Zip Code') : xlt('SE Postal Code') ?>: </label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='10' name='i<?php echo $i?>subscriber_employer_postal_code' value="<?php echo attr($result3["subscriber_employer_postal_code"] ?? ''); ?>" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Zip Code') : xlt('Postal Code') ?>: </label>
                          <div class="col-md-5 mb-2">
                            <input type='entry' class='form-control' size='10' name='i<?php echo attr($i); ?>subscriber_postal_code' value="<?php echo attr($result3["subscriber_postal_code"] ?? ''); ?>" />
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "<div class='d-none'>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('SE Country'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => $GLOBALS['country_data_type'],'field_id' => ('i' . $i . 'subscriber_employer_country'),'list_id' => $GLOBALS['country_list'],'fld_length' => '10','max_length' => '63','edit_options' => 'C', 'smallform' => 'true'), ($result3['subscriber_employer_country'] ?? ''));
                            ?>
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Country'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <?php
                            generate_form_field(array('data_type' => $GLOBALS['country_data_type'],'field_id' => ('i' . $i . 'subscriber_country'),'list_id' => $GLOBALS['country_list'],'fld_length' => '10','max_length' => '63','edit_options' => 'C', 'smallform' => 'true'), ($result3['subscriber_country'] ?? ''));
                            ?>
                          </div>
                            <?php echo ($GLOBALS['omit_employers']) ? "</div>" : ""; ?>
                          <label class='col-form-label col-md-1 mb-2'><?php echo xlt('Subscriber Phone'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='text' class='form-control' size='20' name='i<?php echo attr($i); ?>subscriber_phone' value='<?php echo attr($result3["subscriber_phone"] ?? ''); ?>' onkeyup='phonekeyup(this,mypcc)' />
                          </div>
                          <label class='col-form-label col-md-1 mb-2'><?php echo xlt('Co-Pay'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <input type='text' class='form-control' size="6" name='i<?php echo attr($i); ?>copay' value="<?php echo attr($result3["copay"] ?? ''); ?>" />
                          </div>
                          <label class='col-form-label col-md-1 mb-2 required'><?php echo xlt('Accept Assignment'); ?>:</label>
                          <div class="col-md-5 mb-2">
                            <select class='form-control' name='i<?php echo attr($i); ?>accept_assignment'>
                                <option value="TRUE" <?php echo (strtoupper($result3["accept_assignment"] ?? '') == "TRUE") ? "selected" : ""; ?>><?php echo xlt('YES'); ?></option>
                                <option value="FALSE" <?php echo (strtoupper($result3["accept_assignment"] ?? '') == "FALSE") ? "selected" : ""; ?>><?php echo xlt('NO'); ?></option>
                            </select>
                          </div>
                        </div>
                        <hr />
                            <?php
                        }

                        echo "</div>\n";
                    } // end of "if not simplified_demographics"
                    ?>

                    <?php
                    if ($SHORT_FORM) {
                        echo "  </div>\n";
                    } ?>

                            </td>
                            <td class="text-right align-top text-nowrap" width='1%'>
                            <!-- Image upload stuff was here but got moved. -->
                            </td>
                        </tr>
                    </table>
                </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group">
                    <?php if ($WITH_SEARCH) { ?>
                        <button type="button" class="btn btn-secondary btn-search" id="search" value="<?php echo xla('Search'); ?>">
                            <?php echo xlt('Search'); ?>
                        </button>
                    <?php } ?>
                    <button type="button" class="btn btn-primary btn-save" name='create' id="create" value="<?php echo xla('Create New Patient'); ?>">
                        <?php echo xlt('Create New Patient'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div> <!--end of container div -->
<!-- include support for the list-add selectbox feature -->
<?php require($GLOBALS['fileroot'] . "/library/options_listadd.inc.php"); ?>
<script>

// hard code validation for old validation, in the new validation possible to add match rules
<?php if ($GLOBALS['new_validate'] == 0) { ?>
// fix inconsistently formatted phone numbers from the database
var f = document.forms[0];
if (f.form_phone_contact) phonekeyup(f.form_phone_contact,mypcc);
if (f.form_phone_home   ) phonekeyup(f.form_phone_home   ,mypcc);
if (f.form_phone_biz    ) phonekeyup(f.form_phone_biz    ,mypcc);
if (f.form_phone_cell   ) phonekeyup(f.form_phone_cell   ,mypcc);

<?php }?>

<?php echo $date_init; ?>

// -=- jQuery makes life easier -=-

// var matches = 0; // number of patients that match the demographic information being entered
// var override = false; // flag that overrides the duplication warning

$(function () {
    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 650, 460, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            //onClosed: 'refreshme',
            allowResize: false,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });
    // added to integrate insurance stuff
    <?php for ($i = 1; $i <= 3; $i++) { ?>
    $("#form_i<?php echo $i?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo $i?>(); });
    <?php } ?>

    $('#search').click(function() { searchme(); });
    $('#create').click(function() { check()});

    var check = function(e) {
        var f = document.forms[0];
        <?php if ($GLOBALS['new_validate']) {?>
            var valid = submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>, e, "DEM", constraints);
        <?php } else {?>
            top.restoreSession();
            var valid = validate(f);
        <?php }?>
        if (valid) {
            if (force_submit) {
                // In this case dups were shown already and Save should just save.
                top.restoreSession();
                f.submit();
                return;
            }

        <?php
        // D in edit_options indicates the field is used in duplication checking.
        // This constructs a list of the names of those fields.
        $mflist = "";
        $mfres = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' AND " .
            "(edit_options LIKE '%D%' OR  edit_options LIKE '%W%' )" .
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
        }
        ?>
        <?php if (($GLOBALS['full_new_patient_form'] == '4') && (checkIfPatientValidationHookIsActive())) :?>
            // Use zend module patient validation hook to open the controller and send the dup-checker fields.
            var url ='<?php echo $GLOBALS['web_root'] . "/interface/modules/zend_modules/public/patientvalidation"; ?>';
        <?php else :?>
            // Build and invoke the URL to create the dup-checker dialog.
            var url = 'new_search_popup.php';
        <?php endif;?>

        var flds = new Array(<?php echo $mflist; ?>);
        var separator = '?';
        for (var i = 0; i < flds.length; ++i) {
            var fval = $('#form_' + flds[i]).val();
            if (fval && fval != '') {
                url += separator;
                separator = '&';
                url += 'mf_' + flds[i] + '=' + encodeURIComponent(fval);
            }
        }
        if (flds == '') {
            url += "?close";
        } else {
            url+="&close";
        }
        dlgopen(url, '_blank', 875, 500);
        } // end function
    } // end function

// Set onclick/onfocus handlers for toggling background color.
<?php
$lres = getLayoutRes();
while ($lrow = sqlFetchArray($lres)) {
    $field_id  = $lrow['field_id'];
    if (strpos($field_id, 'em_') === 0) {
        continue;
    }

    switch (getSearchClass($lrow['data_type'])) {
        case 1:
            echo "    \$(" . js_escape("#form_" . $field_id) . ").click(function() { toggleSearch(this); });\n";
            break;
        case 2:
            echo "    \$(" . js_escape("#form_" . $field_id) . ").click(function() { selClick(this); });\n";
            echo "    \$(" . js_escape("#form_" . $field_id) . ").blur(function() { selBlur(this); });\n";
            break;
    }
}
?>

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


}); // end document.ready

// callback for new patient save confirm from new_search_popup.php
// note that dlgclose() callbacks mostly need to init outside dom.
function srcConfirmSave() {
    document.forms[0].submit();
}

</script>
<?php /*Include the validation script and rules for this form*/
$form_id = "DEM";
?>

<?php
//LBF forms use the new validation depending on the global value
$use_validate_js = $GLOBALS['new_validate'];
include_once("$srcdir/validation/validation_script.js.php");?>
<script>
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [
        <?php echo $condition_str; ?>
    ];
    checkSkipConditions();
    $("input").change(function() {
        checkSkipConditions();
    });
    $("select").change(function() {
        checkSkipConditions();
    });
</script>
</body>
</html>
