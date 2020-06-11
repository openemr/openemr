<?php

/**
 * de_identification script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 ViCarePlus, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'super')) {
    die(xlt('Not authorized'));
}

?>

<html>
<head>
<title><?php echo xlt('De Identification'); ?></title>
    <?php Header::setupHeader('datetime-picker'); ?>
<style>
.style1 {
    text-align: center;
}</style>

<script>
//get value from popup window
function set_related(s,type) {
 var list;
 if(type == "diagnosis")
    list = "diagnosis_list";
 else if(type == "drugs")
   list = "drug_list";
 else if(type == "immunizations")
   list = "immunization_list";

 var p=s.split("#");
 var elSel = document.getElementById(list);
 var i,k;
 for (i=0;p[i]!=null;i++)
 {
  for(k=0;k<elSel.length;k++)
  {
   if((elSel.options[k].value)==p[i])
    p[i]= " ";
  }
  if(p[i]!= " ")
  {
  var elOptNew = document.createElement('option');
  elOptNew.text = p[i];
  elOptNew.value = p[i];
  try {
    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    elSel.add(elOptNew); // IE only
  }
  }
 }
 show_value(type);
}

function get_values(type)
{
 if(type == "diagnosis")
    dlgopen('find_code_popup.php', '_blank', 500, 400,true);
 else if(type == "drugs")
   dlgopen('find_drug_popup.php', '_blank', 500, 400,true);
 else if(type == "immunizations")
   dlgopen('find_immunization_popup.php', '_blank', 500, 400,true);
}

//remove item selected from list
function remove_selected(type)
{
  var list;
  if(type == "diagnosis")
    list = "diagnosis_list";
 else if(type == "drugs")
   list = "drug_list";
 else if(type == "immunizations")
   list = "immunization_list";
  var elSel = document.getElementById(list);
  var i;
  for (i = elSel.length - 1; i>=0; i--) {
    if (elSel.options[i].selected)
    {
      elSel.remove(i);
    }
  }
  show_value(type);
}

function show_value(type)
{
 var list,text,radio;
  if(type == "diagnosis")
    { radio = "diagnosis"; list = "diagnosis_list"; text="diagnosis_text"; }
 else if(type == "drugs")
  { radio = "drugs";  list = "drug_list"; text="drug_text"; }
 else if(type == "immunizations")
  { radio = "immunizations"; list = "immunization_list"; text="immunization_text"; }
 if(document.getElementById(radio) == "all")
 {
    document.getElementById(text).value="all";
 }
 else
{
 var str;
 var elSel = document.getElementById(list);
  var i;
  for (i = elSel.length - 1; i>=0; i--)
  {
      if(!str)
       str = elSel.options[i].value;
      else
      str = str +"#"+elSel.options[i].value;

    }
 document.getElementById(text).value=str;
 }
}

//disable - enable other checkbox when all checkbox is clicked
function disable_other_chkbox()
{
 var value = document.forms[0].all.checked;
 if(value == 1)
 {
 document.forms[0].history_data.disabled = true;
 document.forms[0].prescriptions.disabled = true;
 document.forms[0].lists.disabled = true;
 document.forms[0].immunization.disabled = true;
 document.forms[0].transactions.disabled = true;
 document.forms[0].billing_data.disabled = true;
 document.forms[0].insurance_data.disabled = true;
 }
 else
 {
 document.forms[0].history_data.disabled = false;
 document.forms[0].prescriptions.disabled = false;
 document.forms[0].lists.disabled = false;
 document.forms[0].immunization.disabled = false;
 document.forms[0].transactions.disabled = false;
 document.forms[0].billing_data.disabled = false;
 document.forms[0].insurance_data.disabled = false;
 }
}

//disable list,add button,remove button when all option is selected
function disable_controls(type)
{
 var list,button1,button2;
 if(type == "diagnosis")
 {  button1 = "add_diagnosis"; button2 = "remove_diagnosis"; list = "diagnosis_list"; text="diagnosis_text";  }
 else if(type == "drugs")
  { button1 = "add_drug";  button2 = "remove_drug"; list = "drug_list";  text="drug_text";}
 else if(type == "immunizations")
  { button1 = "add_immunization"; button2 = "remove_immunization"; list = "immunization_list"; text="immunization_text"; }
  document.getElementById(button1).disabled = true;
  document.getElementById(button2).disabled = true;
  document.getElementById(list).disabled = true;
  document.getElementById(text).value = "all";
}

function enable_controls(type)
{
 var list,button1,button2;
 if(type == "diagnosis")
 {  button1 = "add_diagnosis"; button2 = "remove_diagnosis"; list = "diagnosis_list";  }
 else if(type == "drugs")
  { button1 = "add_drug";  button2 = "remove_drug"; list = "drug_list";  }
 else if(type == "immunizations")
  { button1 = "add_immunization"; button2 = "remove_immunization"; list = "immunization_list";
 }
  document.getElementById(button1).disabled = false;
  document.getElementById(button2).disabled = false;
  document.getElementById(list).disabled = false;
  show_value(type);
}


function form_validate()
{
 if(document.forms[0].begin_date.value >= document.forms[0].end_date.value)
 {
  alert(<?php echo xlj('End date should be greater than Begin date'); ?>);
  return false;
 }

 if(document.forms[0].all.checked == false &&
 document.forms[0].history_data.checked == false &&
 document.forms[0].prescriptions.checked == false &&
 document.forms[0].immunization.checked == false &&
 document.forms[0].lists.checked == false &&
 document.forms[0].transactions.checked == false &&
 document.forms[0].billing_data.checked == false &&
 document.forms[0].insurance_data.checked == false)
 {
  alert(<?php echo xlj('Select Data Required for De Identification'); ?>);
  return false;
 }

 if(document.forms[0].diagnosis_text.value == "undefined" || document.forms[0].diagnosis_text.value == "")
 {
  alert(<?php echo xlj('Select Diagnosis for De Identification request'); ?>);
  return false;
 }
 if(document.forms[0].drug_text.value == "undefined" || document.forms[0].drug_text.value == "")
 {
  alert(<?php echo xlj('Select Drugs for De Identification request'); ?>);
  return false;
 }
 if(document.forms[0].immunization_text.value == "undefined" || document.forms[0].immunization_text.value == "")
 {
  alert(<?php echo xlj('Select Immunizations for De Identification request'); ?>);
  return false;
 }
 alert(<?php echo xlj('De Identification process is started and running in background'); ?> + '\n' + <?php echo xlj('Please visit the screen after some time'); ?>);
 top.restoreSession();
 return true;
}

function download_file()
{
 alert(<?php echo xlj('De-identification files will be saved in'); ?> + ' `' + <?php echo js_escape($GLOBALS['temporary_files_dir']); ?> + '` ' + <?php echo xlj('location of the openemr machine and may contain sensitive data, so it is recommended to manually delete the files after its use'); ?>);
 document.de_identification.submit();
}

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
</head>
<body class="body_top">
<form name="de_identification" id="de_identification" action="de_identification_screen2.php" method="post" onsubmit="return form_validate();">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<strong><?php echo xlt('De Identification'); ?></strong>
<?php
 $row = sqlQuery("SHOW TABLES LIKE 'de_identification_status'");
if (empty($row)) {
    ?>
   <table>  <tr>    <td>&nbsp;</td> <td>&nbsp;</td> </tr>
         <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
 </table>
 <table class="de_identification_status_message" align="center" >
    <tr valign="top">
       <td>&nbsp;</td>
       <td rowspan="3">
       <br />
        <?php echo xlt('Please upgrade OpenEMR Database to include De Identification procedures, function, tables'); ?>
    <br /><br /><a  target="Blank" href="../../contrib/util/de_identification_upgrade.php"><?php echo xlt('Click here');?></a>
    <?php echo xlt('to run');
      echo " de_identification_upgrade.php<br />";?><br />
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </table>
    <?php
} else {
    $query = "select status from de_identification_status";
    $res = sqlStatement($query);
    if ($row = sqlFetchArray($res)) {
         $deIdentificationStatus = $row['status'];
       /* $deIdentificationStatus:
    *  0 - There is no De Identification in progress. (start new De Identification process)
    *  1 - A De Identification process is currently in progress.
    *  2 - The De Identification process completed and xls file is ready to download
    *  3 - The De Identification process completed with error
       */
    }

    if ($deIdentificationStatus == 1) {
        //1 - A De Identification process is currently in progress.
        ?>
    <table>  <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
          <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
    </table>
    <table class="de_identification_status_message" align="center" >
    <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3">
        <br />
        <?php echo xlt('De Identification Process is ongoing');
          echo "<br /><br />";
          echo xlt('Please visit De Identification screen after some time');
        echo "<br />";   ?>      <br />
           </td>
           <td>&nbsp;</td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>
       </table>
        <?php
    } elseif ($deIdentificationStatus == 2) {
        //2 - The De Identification process completed and xls file is ready to download
        $query = "SELECT count(*) as count FROM de_identified_data ";
        $res = sqlStatement($query);
        if ($row = sqlFetchArray($res)) {
            $no_of_items = $row['count'];
        }

        if ($no_of_items <= 1) {
       //start new search - no patient record fount
            $query = "update de_identification_status set status = 0";
            $res = sqlStatement($query);
            ?>
       <table>  <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
          <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
   </table>
   <table class="de_identification_status_message" align="center" >
        <tr valign="top">

        <td>&nbsp;</td>
        <td rowspan="3">
        <br />
            <?php echo xlt('No Patient record found for given Selection criteria');
            echo "<br /><br />";
            echo xlt('Please start new De Identification process');
            echo "<br />"; ?> <br />
          </td>
          <td>&nbsp;</td>
      </tr>
      <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>

      <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
      </table>

      <table align="center">
      <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
        </table>
            <?php
        } else {
            ?>
    <table>  <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
          <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr>
    </table>
    <table class="de_identification_status_message" align="center" >
        <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3">
        <br />
            <?php echo xlt('De Identification Process is completed');
            echo "<br /><br />";
            echo xlt('Please Click download button to download the De Identified data');
            echo "<br />";    ?>      <br />
           </td>
           <td>&nbsp;</td>
       </tr>
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
       </table>
       <table align="center">
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
      <tr>
      <td colspan="2" class="style1">
           <input type="button" name="Download" value=<?php echo xla("Download");?> onclick="download_file()" ></td>
      </tr>
      </table>
            <?php
        }
    } elseif ($deIdentificationStatus == 3) {
        //3 - The De Identification process completed with error
        ?>
      <table>  <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
            <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
      </table>
    <table class="de_identification_status_message" align="center" >
        <tr valign="top">
        <td>&nbsp;</td>
        <td rowspan="3">
        <br />
        <?php echo xlt('Some error has occured during De Identification Process');
          echo "<br /><br />";
          echo xlt('De Identified data may not be complete');
          echo "<br /><br />";
        ?><span class="text"><?php echo xlt('Please view De Identification error log table for more details');
    echo "<br />";   ?></span>   <br />
           </td>
           <td>&nbsp;</td>
       </tr>
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
       </table>
       <table align="center">
       <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
      <tr>
      <td colspan="2" class="style1">
              <input type="button" name="Download" value=<?php echo xla("Download Anyway");?>  onclick="download_file()"></td>
      </tr>
      </table>
    </tr>
    </table>

        <?php
    }

    if ($deIdentificationStatus == 0) {
      //0 - There is no De Identification in progress. (start new De Identification process)
        ?>
  <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;">
  </div>
  <table style="width: 74%" border=0>
    <tr rowspan=2>
        <td>&nbsp;</td>
        <td><span class="text"><?php echo xlt('Begin Date'); ?></span>
        <input type="text" size="10" class="datepicker" name="begin_date" id="begin_date" value="<?php echo $viewmode ? attr(substr($result['date'], 0, 10)) : date('Y-m-d'); ?>" title="<?php echo xla('yyyy-mm-dd Date of service'); ?>" />
        </td>
        <td><span class="text"><?php xl('End Date', 'e'); ?></span>
        <input type="text" size="10" class="datepicker" name="end_date" id="end_date" value="<?php echo $viewmode ? attr(substr($result['date'], 0, 10)) : date('Y-m-d'); ?>" title="<?php echo xla('yyyy-mm-dd Date of service'); ?>" />
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <td>&nbsp;</td> </tr>
        <tr><td>&nbsp;</td>
        <td colspan=2 class="de_identification_input_controls_box"><input type="checkbox" name="unstructured" id="unstructured" value=<?php echo xla("unstructured");?>><span class="text"><?php echo xlt('Include Unstructured data'); ?></span></td>
        <td>&nbsp;</td></tr>
        <tr>
        <td>&nbsp;</td>
        <td colspan="2">
        <table class="de_identification_input_controls_box">
            <tr>
                <td><span class="text"><?php echo xlt('Select data to be included in De Identified data'); ?></span>              <br />
                <input type="checkbox" name="all" id ="all" value='all' onclick="disable_other_chkbox()"><span class="text"><?php echo xlt('All'); ?> </span><br />
                <input type="checkbox" name="history_data" id="history_data" value='history_data'><span class="text"><?php echo xlt('History Data'); ?></span> <br />
                <input type="checkbox" name="immunization" id="immunization" value="immunizations"><span class="text"><?php echo xlt('Immunizations'); ?></span>
                <br />
                <input type="checkbox" name="prescriptions" id="prescriptions" value="prescriptions"><span class="text"><?php echo xlt('Prescriptions'); ?></span>

  &nbsp;</td>     <br />
                <td><br />
                <input type="checkbox" name="lists" id="lists" value="lists"><span class="text"><?php echo xlt('Issues'); ?> </span><br />
                <input type="checkbox" name="transactions" id="transactions" value="transactions"><span class="text"><?php echo xlt('Transactions'); ?></span>
                <br />
                <input type="checkbox" name="insurance_data" id="insurance_data" value="insurance_data"><span class="text"><?php echo xlt('Insurance Data'); ?> </span><br />
                <input type="checkbox" name="billing_data" id="billing_data" value="billing_data"><span class="text"><?php echo xlt('Billing Data'); ?></span> <br />

  &nbsp;</td>
            </tr>
        </table>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="2"><br />
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="3">
        <table style="width: 100%">
            <tr valign="top">
                <!--diagnosis--><td style="width:50%;" class="style1"><span class="text"><?php echo xlt('Enter Diagnosis'); ?></span>
                <input type="radio" id="diagnosis" name="diagnosis" value="all" onclick="disable_controls('diagnosis');" /><span class="text"> <?php echo xlt('All'); ?></span>
                <input type="radio" id="diagnosis" name="diagnosis" value="select_diagnosis" onclick="enable_controls('diagnosis');"    />
                <span class="text"><?php echo xlt('Select Diagnosis'); ?></span>
                <select id="diagnosis_list" name="diagnosis_list" size="10" style="width: 60%">
                </select>

                </td>
                <td style="width:50%;" class="style1">
                <!--drugs--><span class="text"><?php echo xlt('Enter Drugs'); ?></span>
                <input type="radio" id="drugs" name="drugs" value="all"); onclick="disable_controls('drugs')"/><span class="text"> <?php echo xlt('All'); ?></span>
                <input type="radio" id="drugs" name="drugs" value="select_drug" onclick="enable_controls('drugs')" />
                <span class="text"><?php echo xlt('Select Drugs'); ?> <br /></span>
                <select id="drug_list" name="drug_list" size="10" style="width: 60%">
                </select>

                </td>
            </tr>
            <tr> <td class="style1">
                <input type="button" name="add_diagnosis" id = "add_diagnosis" value=<?php echo xla("Add Diagnosis"); ?> onclick="get_values('diagnosis')">
                <input type="button" name="remove_diagnosis" id="remove_diagnosis"value=<?php echo xla("Remove"); ?> onclick="remove_selected('diagnosis')">&nbsp; </td> <td class="style1">
                <input type="button" name="add_drug" id="add_drug" value=<?php echo xla("Add Drug"); ?> onclick="get_values('drugs')">
                <input type="button" name="remove_drug" id="remove_drug" value=<?php echo xla("Remove"); ?> onclick="remove_selected('drugs')">
            </td> </tr>
        </table>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="2" class="style1">
        <!--immunizations--><br />
        <span class="text"><?php echo xlt('Enter Immunizations'); ?></span>
        <input type="radio" id="immunizations" name="immunizations" value="all" onclick="disable_controls('immunizations')"/><span class="text"> <?php echo xlt('All'); ?></span>
        <input type="radio" id="immunizations" name="immunizations" value="select_immunization" onclick="enable_controls('immunizations')" />
        <span class="text"><?php echo xlt('Select Immunizations'); ?></span> <br />
        <select id="immunization_list" name="immunization_list" size="10" width="300" style="width: 30%">
        </select> <br />
        <input type="button" name="add_immunization" id="add_immunization" value="<?php echo xla("Add Immunization"); ?>" onclick="get_values('immunizations')">
        <input type="button" name="remove_immunization" id="remove_immunization" value="<?php echo xla("Remove"); ?>" onclick="remove_selected('immunizations')">
        <br />
  &nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="2" class="style1">
        <input type="submit" name="Submit" value="<?php echo xla("Submit"); ?>" ></td>
        <td>&nbsp;</td>
    </tr>

    <input type="hidden" name="diagnosis_text" id="diagnosis_text"><br />
        <input type="hidden" name="drug_text" id="drug_text"><br />
        <input type="hidden" name="immunization_text" id="immunization_text">
  </table>
        <?php
    }
}

?>
</form>
</body>
</html>
