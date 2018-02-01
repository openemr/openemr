<?php
/**
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Brady Miller <brady.g.miller@gmail.com>
* @link      http://www.open-emr.org
*/

include_once("../globals.php");

if ($GLOBALS['full_new_patient_form']) {
    require("new_comprehensive.php");
    exit;
}

// For a layout field return 0=unused, 1=optional, 2=mandatory.
function getLayoutUOR($form_id, $field_id)
{
    $crow = sqlQuery("SELECT uor FROM layout_options WHERE " .
    "form_id = '$form_id' AND field_id = '$field_id' LIMIT 1");
    return 0 + $crow['uor'];
}

// Determine if the registration date should be requested.
$regstyle = getLayoutUOR('DEM', 'regdate') ? "" : " style='display:none'";

$form_pubpid    = $_POST['pubpid'   ] ? trim($_POST['pubpid'   ]) : '';
$form_title     = $_POST['title'    ] ? trim($_POST['title'    ]) : '';
$form_fname     = $_POST['fname'    ] ? trim($_POST['fname'    ]) : '';
$form_mname     = $_POST['mname'    ] ? trim($_POST['mname'    ]) : '';
$form_lname     = $_POST['lname'    ] ? trim($_POST['lname'    ]) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_sex       = $_POST['sex'      ] ? trim($_POST['sex'      ]) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_dob       = $_POST['DOB'      ] ? trim($_POST['DOB'      ]) : '';
$form_regdate   = $_POST['regdate'  ] ? trim($_POST['regdate'  ]) : date('Y-m-d');
?>
<html>

<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo xl($css_header, 'e');?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css">

<script type="text/javascript" src="../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js"></script>

<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script LANGUAGE="JavaScript">

 var mypcc = '1';

 function validate() {
  var f = document.forms[0];
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
  if (f.refsource.selectedIndex <= 0) {
   alert('Please select a referral source!');
   return false;
  }
<?php } ?>
<?php if (getLayoutUOR('DEM', 'sex') == 2) { ?>
  if (f.sex.selectedIndex <= 0) {
   alert('Please select a value for sex!');
   return false;
  }
<?php } ?>
<?php if (getLayoutUOR('DEM', 'DOB') == 2) { ?>
  if (f.DOB.value.length == 0) {
   alert('Please select a birth date!');
   return false;
  }
<?php } ?>
  top.restoreSession();
  return true;
 }

$(document).ready(function(){
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

</script>

</head>

<body class="body_top" onload="javascript:document.new_patient.fname.focus();">

<form name='new_patient' method='post' action="new_patient_save.php"
 onsubmit='return validate()'>
<span class='title'><?php xl('Add Patient Record', 'e');?></span>

<br><br>

<center>

<?php if ($GLOBALS['omit_employers']) { ?>
   <input type='hidden' name='title' value='' />
<?php } ?>

<table border='0'>

<?php if (!$GLOBALS['omit_employers']) { ?>
 <tr>
  <td>
   <span class='bold'><?php xl('Title', 'e');?>: </span>
  </td>
  <td>
   <select name='title'>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'titles' AND activity = 1 ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
    echo "    <option value='" . $orow['option_id'] . "'";
    if ($orow['option_id'] == $form_title) {
        echo " selected";
    }

    echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?php xl('First Name', 'e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='fname' value='<?php echo $form_fname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Middle Name', 'e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='mname' value='<?php echo $form_mname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Last Name', 'e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='lname' value='<?php echo $form_lname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Sex', 'e'); ?>: </span>
  </td>
  <td>
   <select name='sex'>
    <option value=''>Unassigned</option>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'sex' AND activity = 1 ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
    echo "    <option value='" . $orow['option_id'] . "'";
    if ($orow['option_id'] == $form_sex) {
        echo " selected";
    }

    echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
 <tr>
  <td>
   <span class='bold'><?php xl('Referral Source', 'e'); ?>: </span>
  </td>
  <td>
   <select name='refsource'>
    <option value=''>Unassigned</option>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'refsource' AND activity = 1 ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
    echo "    <option value='" . $orow['option_id'] . "'";
    if ($orow['option_id'] == $form_refsource) {
        echo " selected";
    }

    echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?php xl('Birth Date', 'e');?>: </span>
  </td>
  <td>
   <input type='text' size='10' class='datepicker' name='DOB' id='DOB'
    value='<?php echo $form_dob; ?>'
    title='yyyy-mm-dd' />
  </td>
 </tr>

 <tr<?php echo $regstyle ?>>
  <td>
   <span class='bold'><?php xl('Registration Date', 'e');?>: </span>
  </td>
  <td>
   <input type='text' size='10' class='datepicker' name='regdate' id='regdate'
    value='<?php echo $form_regdate; ?>'
    title='yyyy-mm-dd' />
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Patient Number', 'e');?>: </span>
  </td>
  <td>
   <input type='entry' size='5' name='pubpid' value='<?php echo $form_pubpid; ?>'>
   <span class='text'><?php xl('omit to autoassign', 'e');?> &nbsp; &nbsp; </span>
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;<br>
   <input type='submit' name='form_create' value=<?php xl('Create New Patient', 'e'); ?> />
  </td>
  <td>
  </td>
 </tr>

</table>
</center>
</form>
<script language="Javascript">
<?php
if ($form_pubpid) {
    echo "alert('" . xl('This patient ID is already in use!') . "');\n";
}
?>
</script>

</body>
</html>
