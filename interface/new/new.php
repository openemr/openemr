<?php

/**
 * new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if ($GLOBALS['full_new_patient_form']) {
    require("new_comprehensive.php");
    exit;
}

// For a layout field return 0=unused, 1=optional, 2=mandatory.
function getLayoutUOR($form_id, $field_id)
{
    $crow = sqlQuery("SELECT uor FROM layout_options WHERE " .
    "form_id = ? AND field_id = ? LIMIT 1", array($form_id, $field_id));
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

<?php
    Header::setupHeader('datetime-picker');
    include_once($GLOBALS['srcdir'] . "/options.js.php");
?>

<script>

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

$(function () {
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
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<span class='title'><?php echo xlt('Add Patient Record'); ?></span>

<br /><br />

<center>

<?php if ($GLOBALS['omit_employers']) { ?>
   <input type='hidden' name='title' value='' />
<?php } ?>

<table class="border-0">

<?php if (!$GLOBALS['omit_employers']) { ?>
 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Title'); ?>:</span>
  </td>
  <td>
   <select name='title'>
    <?php
    $ores = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'titles' AND activity = 1 ORDER BY seq");
    while ($orow = sqlFetchArray($ores)) {
        echo "    <option value='" . attr($orow['option_id']) . "'";
        if ($orow['option_id'] == $form_title) {
            echo " selected";
        }

        echo ">" . text($orow['title']) . "</option>\n";
    }
    ?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('First Name'); ?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='fname' value='<?php echo attr($form_fname); ?>' />
  </td>
 </tr>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Middle Name'); ?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='mname' value='<?php echo attr($form_mname); ?>' />
  </td>
 </tr>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Last Name'); ?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='lname' value='<?php echo attr($form_lname); ?>' />
  </td>
 </tr>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Sex'); ?>: </span>
  </td>
  <td>
   <select name='sex'>
    <option value=''>Unassigned</option>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'sex' AND activity = 1 ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
    echo "    <option value='" . attr($orow['option_id']) . "'";
    if ($orow['option_id'] == $form_sex) {
        echo " selected";
    }

    echo ">" . text($orow['title']) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Referral Source'); ?>: </span>
  </td>
  <td>
   <select name='refsource'>
    <option value=''><?php echo xlt("Unassigned"); ?></option>
    <?php
    $ores = sqlStatement("SELECT option_id, title FROM list_options " .
    "WHERE list_id = 'refsource' AND activity = 1 ORDER BY seq");
    while ($orow = sqlFetchArray($ores)) {
        echo "    <option value='" . attr($orow['option_id']) . "'";
        if ($orow['option_id'] == $form_refsource) {
            echo " selected";
        }

        echo ">" . text($orow['title']) . "</option>\n";
    }
    ?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Birth Date'); ?>:</span>
  </td>
  <td>
   <input type='text' size='10' class='datepicker' name='DOB' id='DOB'
    value='<?php echo attr($form_dob); ?>' />
  </td>
 </tr>

 <tr<?php echo $regstyle ?>>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Registration Date'); ?>: </span>
  </td>
  <td>
   <input type='text' size='10' class='datepicker' name='regdate' id='regdate'
    value='<?php echo attr($form_regdate); ?>' />
  </td>
 </tr>

 <tr>
  <td>
   <span class='font-weight-bold'><?php echo xlt('Patient Number'); ?>: </span>
  </td>
  <td>
   <input type='entry' size='5' name='pubpid' value='<?php echo attr($form_pubpid); ?>' />
   <span class='text'><?php echo xlt('omit to autoassign'); ?> &nbsp; &nbsp; </span>
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;<br />
   <input type='submit' name='form_create' value='<?php echo xla('Create New Patient'); ?>' />
  </td>
  <td>
  </td>
 </tr>

</table>
</center>
</form>
<script>
<?php
if ($form_pubpid) {
    echo "alert(" . xlj('This patient ID is already in use!') . ");\n";
}
?>
</script>

</body>
</html>
