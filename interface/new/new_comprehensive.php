<?php
/**
 * New patient or search patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/erx_javascript.inc.php");
require_once("$srcdir/validation/LBF_Validation.php");
require_once("$srcdir/patientvalidation.inc.php");

use OpenEMR\Core\Header;

// Check authorization.
if (!acl_check('patients', 'demo', '', array('write','addonly'))) {
    die(xlt("Adding demographics is not authorized."));
}

$CPR = 4; // cells per row

$searchcolor = empty($GLOBALS['layout_search_color']) ?
  '#ffff55' : $GLOBALS['layout_search_color'];

$WITH_SEARCH = ($GLOBALS['full_new_patient_form'] == '1' || $GLOBALS['full_new_patient_form'] == '2' );
$SHORT_FORM  = ($GLOBALS['full_new_patient_form'] == '2' || $GLOBALS['full_new_patient_form'] == '3' || $GLOBALS['full_new_patient_form'] == '4');

$grparr = array();
getLayoutProperties('DEM', $grparr);

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
<html>
<head>
<?php Header::setupHeader(['common','datetime-picker', 'jquery-ui']); ?>
<title><?php echo xlt("Search or Add Patient"); ?></title>

<style>
body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

.form-control {
    width: auto;
    display: inline;
    height: auto;
}
</style>

<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<SCRIPT LANGUAGE="JavaScript"><!--
//Visolve - sync the radio buttons - Start
if((top.window.parent) && (parent.window)){
        var wname = top.window.parent.left_nav;
        fname = (parent.window.name)?parent.window.name:window.name;
        wname.syncRadios();
}//Visolve - sync the radio buttons - End

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

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

<?php for ($i=1; $i<=3; $i++) { ?>
function auto_populate_employer_address<?php echo $i ?>(){
 var f = document.demographics_form;
 if (f.form_i<?php echo $i?>subscriber_relationship.options[f.form_i<?php echo $i?>subscriber_relationship.selectedIndex].value == "self") {
  f.i<?php echo $i?>subscriber_fname.value=f.form_fname.value;
  f.i<?php echo $i?>subscriber_mname.value=f.form_mname.value;
  f.i<?php echo $i?>subscriber_lname.value=f.form_lname.value;
  f.i<?php echo $i?>subscriber_street.value=f.form_street.value;
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
  alert("<?php echo xls("Please enter a dollar amount using only numbers and a decimal point."); ?>");
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
  msg += "<?php echo xla('The following fields are required'); ?>:\n\n";
  for ( var i = 0; i < errMsgs.length; i++ ) {
         msg += errMsgs[i] + "\n";
  }
  msg += "\n<?php echo xla('Please fill them in before continuing.'); ?>";


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
if(errMsgs.length > 0 || dateVal > currentDate)
{
if(errMsgs.length > 0)
    alert(msg);
if(dateVal > currentDate)
    alert ('<?php echo xls("Deceased Date should not be greater than Today"); ?>');
    return false;
}
 return true;
}

function toggleSearch(elem) {
 var f = document.forms[0];
<?php if ($WITH_SEARCH) { ?>
 // Toggle background color.
 if (elem.style.backgroundColor == '')
  elem.style.backgroundColor = '<?php echo $searchcolor; ?>';
 else
  elem.style.backgroundColor = '';
<?php } ?>
 if (force_submit) {
  force_submit = false;
  f.create.value = '<?php echo xla('Create New Patient'); ?>';
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
 var url = '../main/finder/patient_select.php?popup=1';

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
            " if (f.$fldname.style.backgroundColor != '' && trimlen(f.$fldname.value) > 0) {\n" .
            "  url += '&$field_id=' + encodeURIComponent(f.$fldname.value);\n" .
            " }\n";
            break;
        case 2:
            echo
            " if (f.$fldname.style.backgroundColor != '' && f.$fldname.selectedIndex > 0) {\n" .
            "  url += '&$field_id=' + encodeURIComponent(f.$fldname.options[f.$fldname.selectedIndex].value);\n" .
            " }\n";
            break;
    }
}
?>

 dlgopen(url, '_blank', 700, 500);
}
function srchDone(pid){
    top.restoreSession();
    document.location.href = "./../../patient_file/summary/demographics.php?set_pid=" + pid;
}
//-->

</script>
</head>

<body class="body_top">

<?php
/*Get the constraint from the DB-> LBF forms accordinf the form_id*/
$constraints = LBF_Validation::generate_validate_constraints("DEM");
?>
<script> var constraints = <?php echo $constraints;?>; </script>

<form action='new_comprehensive_save.php' name='demographics_form' id="DEM"  method='post' onsubmit='return submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,"DEM",constraints)'>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h2><?php echo xlt('Search or Add Patient');?></h2>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="btn-group">
                    <?php if ($WITH_SEARCH) { ?>
                        <button type="button" class="btn btn-default btn-search" id="search" value="<?php echo xla('Search'); ?>">
                            <?php echo xlt('Search'); ?>
                        </button>
                    <?php } ?>
                    <button type="button" class="btn btn-default btn-save" name='create' id="create" value="<?php echo xla('Create New Patient'); ?>">
                        <?php echo xlt('Create New Patient'); ?>
                    </button>
                </div>
                <hr>
            </div>
        </div>
    </div>

<table width='100%' cellpadding='0' cellspacing='8'>
 <tr>
  <td align='left' valign='top'>
<?php if ($SHORT_FORM) {
    echo "  <center>\n";
} ?>
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
        for (; $cell_count < $CPR;
        ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function end_group()
{
    global $last_group, $SHORT_FORM;
    if (strlen($last_group) > 0) {
        end_row();
        echo " </table>\n";
        if (!$SHORT_FORM) {
            echo "</div>\n";
        }
    }
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
    accumActionConditions($field_id, $condition_str, $frow['conditions']);

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

            if (strlen($last_group) > 0) {
                echo "<br />";
            }

            echo "<span class='bold'><input type='checkbox' name='form_cb_$group_seq' id='form_cb_$group_seq' value='1' " .
            "onclick='return divclick(this,\"div_$group_seq\");'";
            if ($display_style == 'block') {
                echo " checked";
            }

            // Modified 6-09 by BM - Translate if applicable
            echo " /><b>" . xl_layout_label($group_name) . "</b></span>\n";

            echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
            echo " <table border='0' cellpadding='0'>\n";
            $display_style = 'none';
        } else if (strlen($last_group) == 0) {
            echo " <table border='0' cellpadding='0'>\n";
        }

        $last_group = $this_group;
    }

  // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
        end_row();
        echo "  <tr>";
    }

    if ($item_count == 0 && $titlecols == 0) {
        $titlecols = 1;
    }

    $field_id_label='label_'.$frow['field_id'];
  // Handle starting of a new label cell.
    if ($titlecols > 0) {
        end_cell();
        echo "<td colspan='$titlecols' id='$field_id_label'";
        echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
        if ($cell_count == 2) {
            echo " style='padding-left:10pt'";
        }

        echo ">";
        $cell_count += $titlecols;
    }

    ++$item_count;

    echo "<b>";

  // Modified 6-09 by BM - Translate if applicable
    if ($frow['title']) {
        echo (xl_layout_label($frow['title']).":");
    } else {
        echo "&nbsp;";
    }

    echo "</b>";

  // Handle starting of a new data cell.
    if ($datacols > 0) {
        $id_field_text = "text_".$frow['field_id'];
        end_cell();
        echo "<td colspan='$datacols' class='text data'";
        if ($cell_count > 0) {
            echo " style='padding-left:5pt'". " id='".$id_field_text."'";
        }

        echo ">";
        $cell_count += $datacols;
    }

    ++$item_count;
    generate_form_field($frow, $currvalue);
}

end_group();
?>

<?php
if (! $GLOBALS['simplified_demographics']) {
    $insurancei = getInsuranceProviders();
    $pid = 0;
    $insurance_headings = array(xl("Primary Insurance Provider"), xl("Secondary Insurance Provider"), xl("Tertiary Insurance provider"));
    $insurance_info = array();
    $insurance_info[1] = getInsuranceData($pid, "primary");
    $insurance_info[2] = getInsuranceData($pid, "secondary");
    $insurance_info[3] = getInsuranceData($pid, "tertiary");

    echo "<br /><span class='bold'><input type='checkbox' name='form_cb_ins' value='1' " .
    "onclick='return divclick(this,\"div_ins\");'";
    if ($display_style == 'block') {
        echo " checked";
    }

    echo " /><b>" . xlt('Insurance') . "</b></span>\n";
    echo "<div id='div_ins' class='section' style='display:$display_style;'>\n";

    for ($i=1; $i<=3; $i++) {
        $result3 = $insurance_info[$i];
    ?>
  <table border="0">
   <tr>
    <td valign='top' colspan='2'>
     <span class='required'><?php echo text($insurance_headings[$i -1]).":"?></span>
     <select name="i<?php echo $i?>provider" class="form-control">
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
     </select>&nbsp;<a class='medium_modal' href='../practice/ins_search.php' onclick='ins_search(<?php echo $i?>)'>
  <span> <?php echo xlt('Search/Add Insurer'); ?></span></a>
  </td>
 </tr>
 <tr>
  <td valign=top>
   <table border="0">

    <tr>
     <td>
      <span class='required'><?php echo xlt('Plan Name'); ?>: </span>
     </td>
     <td>
      <input type='entry' class='form-control' size='20' name='i<?php echo $i?>plan_name' value="<?php echo attr($result3{"plan_name"}); ?>"
       onchange="capitalizeMe(this);" />&nbsp;&nbsp;
     </td>
    </tr>

    <tr>
     <td>
      <span class='required'><?php echo xlt('Effective Date'); ?>: </span>
     </td>
     <td>
      <input type='entry' size='11' class='datepicker form-control' name='i<?php echo $i ?>effective_date'
       id='i<?php echo $i ?>effective_date'
       value='<?php echo attr($result3['date']); ?>'
       title='yyyy-mm-dd' />
     </td>
    </tr>

    <tr>
     <td><span class=required><?php echo xlt('Policy Number'); ?>: </span></td>
     <td><input type='entry' class='form-control' size='16' name='i<?php echo $i?>policy_number' value="<?php echo attr($result3{"policy_number"}); ?>"
      onkeyup='policykeyup(this)'></td>
    </tr>

    <tr>
     <td><span class=required><?php echo xlt('Group Number'); ?>: </span></td>
    <td><input type=entry class='form-control' size=16 name=i<?php echo $i?>group_number value="<?php echo attr($result3{"group_number"}); ?>" onkeyup='policykeyup(this)'></td>
    </tr>

    <tr<?php echo ($GLOBALS['omit_employers']) ? " style='display:none'" : ""; ?>>
     <td class='required'><?php echo xlt('Subscriber Employer (SE)'); ?><br><span style='font-weight:normal'>
      (<?php echo xlt('if unemployed enter Student'); ?>,<br><?php echo xlt('PT Student, or leave blank'); ?>): </span></td>
     <td><input type=entry class='form-control' size=25 name=i<?php echo $i?>subscriber_employer
      value="<?php echo attr($result3{"subscriber_employer"}); ?>"
       onchange="capitalizeMe(this);" /></td>
    </tr>

    <tr<?php echo ($GLOBALS['omit_employers']) ? " style='display:none'" : ""; ?>>
     <td><span class=required><?php echo xlt('SE Address'); ?>: </span></td>
     <td><input type=entry class='form-control' size=25 name=i<?php echo $i?>subscriber_employer_street
      value="<?php echo attr($result3{"subscriber_employer_street"}); ?>"
       onchange="capitalizeMe(this);" /></td>
    </tr>

    <tr<?php echo ($GLOBALS['omit_employers']) ? " style='display:none'" : ""; ?>>
     <td colspan="2">
      <table>
       <tr>
        <td><span class=required><?php echo xlt('SE City'); ?>: </span></td>
        <td><input type=entry class='form-control' size=15 name=i<?php echo $i?>subscriber_employer_city
         value="<?php echo attr($result3{"subscriber_employer_city"}); ?>"
          onchange="capitalizeMe(this);" /></td>
        <td><span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE State') : xlt('SE Locality') ?>: </span></td>
    <td>
            <?php
            // Modified 7/2009 by BM to incorporate data types
            generate_form_field(array('data_type'=>$GLOBALS['state_data_type'],'field_id'=>('i'.$i.'subscriber_employer_state'),'list_id'=>$GLOBALS['state_list'],'fld_length'=>'15','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_employer_state']);
                ?>
          </td>
         </tr>
         <tr>
            <td><span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('SE Zip Code') : xlt('SE Postal Code') ?>: </span></td>
            <td><input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_employer_postal_code value="<?php echo attr($result3{"subscriber_employer_postal_code"}); ?>"></td>
            <td><span class=required><?php xl('SE Country', 'e'); ?>: </span></td>
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
    </td>

    <td valign=top>
       <span class=required><?php echo xlt('Subscriber'); ?>: </span>
       <input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_fname
        value="<?php echo attr($result3{"subscriber_fname"}); ?>"
      onchange="capitalizeMe(this);" />
       <input type=entry class='form-control' size=3 name=i<?php echo $i?>subscriber_mname
        value="<?php echo attr($result3{"subscriber_mname"}); ?>"
      onchange="capitalizeMe(this);" />
       <input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_lname
        value="<?php echo attr($result3{"subscriber_lname"}); ?>"
      onchange="capitalizeMe(this);" />
     <br>
       <span class=required><?php echo xlt('Relationship'); ?>: </span>
        <?php
      // Modified 6/2009 by BM to use list_options and function
        generate_form_field(array('data_type'=>1,'field_id'=>('i'.$i.'subscriber_relationship'),'list_id'=>'sub_relation','empty_title'=>' '), $result3['subscriber_relationship']);
        ?>
       <a href="javascript:popUp('../../interface/patient_file/summary/browse.php?browsenum=<?php echo $i?>')" class=text>(<?php echo xla('Browse'); ?>)</a><br />

       <span class=bold><?php echo xlt('D.O.B.'); ?>: </span>
       <input type='entry' size='11' class='datepicker form-control' name='i<?php echo $i?>subscriber_DOB'
      id='i<?php echo $i?>subscriber_DOB'
      value='<?php echo attr($result3['subscriber_DOB']); ?>'
    title='yyyy-mm-dd' />

       <span class=bold><?php echo xlt('S.S.'); ?>: </span>
       <input type=entry class='form-control' size=11 name=i<?php echo $i?>subscriber_ss value="<?php echo attr($result3{"subscriber_ss"}); ?>">&nbsp;
       <span class=bold><?php echo xlt('Sex'); ?>: </span>
        <?php
      // Modified 6/2009 by BM to use list_options and function
        generate_form_field(array('data_type'=>1,'field_id'=>('i'.$i.'subscriber_sex'),'list_id'=>'sex'), $result3['subscriber_sex']);
        ?>
     <br>
       <span class=required><?php echo xlt('Subscriber Address'); ?>: </span>
       <input type=entry class='form-control' size=25 name=i<?php echo $i?>subscriber_street
      value="<?php echo attr($result3{"subscriber_street"}); ?>"
    onchange="capitalizeMe(this);" /><br>
       <span class=required><?php echo xlt('City'); ?>: </span>
       <input type=entry class='form-control' size=15 name=i<?php echo $i?>subscriber_city
      value="<?php echo attr($result3{"subscriber_city"}); ?>"
    onchange="capitalizeMe(this);" />
       <span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('State') : xlt('Locality') ?>: </span>
        <?php
      // Modified 7/2009 by BM to incorporate data types
        generate_form_field(array('data_type'=>$GLOBALS['state_data_type'],'field_id'=>('i'.$i.'subscriber_state'),'list_id'=>$GLOBALS['state_list'],'fld_length'=>'15','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_state']);
        ?>
     <br />
       <span class=required><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Zip Code') : xlt('Postal Code') ?>: </span>
       <input type=entry class='form-control' size=10 name=i<?php echo $i?>subscriber_postal_code value="<?php echo attr($result3{"subscriber_postal_code"}); ?>">
       <span class='required'<?php echo ($GLOBALS['omit_employers']) ? " style='display:none'" : ""; ?>>
        <?php echo xlt('Country'); ?>: </span>
        <?php
      // Modified 7/2009 by BM to incorporate data types
        generate_form_field(array('data_type'=>$GLOBALS['country_data_type'],'field_id'=>('i'.$i.'subscriber_country'),'list_id'=>$GLOBALS['country_list'],'fld_length'=>'10','max_length'=>'63','edit_options'=>'C'), $result3['subscriber_country']);
        ?>
     <br />
       <span class=bold><?php echo xlt('Subscriber Phone'); ?>:
       <input type='text' class='form-control' size='20' name='i<?php echo $i?>subscriber_phone' value='<?php echo attr($result3["subscriber_phone"]); ?>' onkeyup='phonekeyup(this,mypcc)' />
     </span><br />
       <span class=bold><?php echo xlt('CoPay'); ?>: <input type=text class='form-control' size="6" name=i<?php echo $i?>copay value="<?php echo attr($result3{"copay"}); ?>">
     </span><br />
       <span class='required'><?php echo xlt('Accept Assignment'); ?>: </span>
       <select class='form-control' name=i<?php echo $i?>accept_assignment>
         <option value="TRUE" <?php echo (strtoupper($result3{"accept_assignment"}) == "TRUE") ? "selected" : ""; ?>><?php echo xlt('YES'); ?></option>
         <option value="FALSE" <?php echo (strtoupper($result3{"accept_assignment"}) == "FALSE") ? "selected" : ""; ?>><?php echo xlt('NO'); ?></option>
       </select>
    </td>
   </tr>

  </table>
  <hr />
    <?php
    }

    echo "</div>\n";
} // end of "if not simplified_demographics"
?>

<?php if ($SHORT_FORM) {
    echo "  </center>\n";
} ?>

  </td>
  <td align='right' valign='top' width='1%' nowrap>
   <!-- Image upload stuff was here but got moved. -->
  </td>
 </tr>
</table>

</form>

<!-- include support for the list-add selectbox feature -->
<?php include($GLOBALS['fileroot']."/library/options_listadd.inc"); ?>

</body>

<script language="JavaScript">

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

$(document).ready(function() {
    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 650, 460, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
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
    <?php for ($i=1; $i<=3; $i++) { ?>
    $("#form_i<?php echo $i?>subscriber_relationship").change(function() { auto_populate_employer_address<?php echo $i?>(); });
    <?php } ?>

    $('#search').click(function() { searchme(); });
    $('#create').click(function() { check()});

    var check = function(e) {
        <?php if ($GLOBALS['new_validate']) {?>
            var valid = submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,e,"DEM",constraints);
        <?php } else {?>
            top.restoreSession();
            var f = document.forms[0];
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

            $mflist .= "'" . htmlentities($field_id) . "'";
        }
?>
        <?php if (($GLOBALS['full_new_patient_form'] == '4') && (checkIfPatientValidationHookIsActive())) :?>
            // Use zend module patient validation hook to open the controller and send the dup-checker fields.
            var url ='<?php echo  $GLOBALS['web_root']."/interface/modules/zend_modules/public/patientvalidation";?>';
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
        url+="&close"
        dlgopen(url, '_blank', 700, 500);
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
            echo "    \$('#form_$field_id').click(function() { toggleSearch(this); });\n";
            break;
        case 2:
            echo "    \$('#form_$field_id').click(function() { selClick(this); });\n";
            echo "    \$('#form_$field_id').blur(function() { selBlur(this); });\n";
            break;
    }
}
?>

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

}); // end document.ready

// callback for new patient save confirm from new_search_popup.php
// note that dlgclose() callbacks mostly need to init outside dom.
function srcConfirmSave() {
    document.forms[0].submit();
}

</script>
<?php /*Include the validation script and rules for this form*/
$form_id="DEM";
?>

<?php
//LBF forms use the new validation depending on the global value
$use_validate_js=$GLOBALS['new_validate'];
include_once("$srcdir/validation/validation_script.js.php");?>
<script language='JavaScript'>
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

</html>
