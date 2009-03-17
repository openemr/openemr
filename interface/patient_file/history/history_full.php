<?php
require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

$CPR = 4; // cells per row

// Check authorization.
$thisauth = acl_check('patients', 'med');
if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
}
if ($thisauth != 'write' && $thisauth != 'addonly')
  die("Not authorized.");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header ?>" type="text/css">

<style>
body, td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

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

</style>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script LANGUAGE="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

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
<?php generate_layout_validation('HIS'); ?>
 return true;
}

function submitme() {
 var f = document.forms[0];
 if (validate(f)) {
  top.restoreSession();
  f.submit();
 }
}

</script>

</head>
<body class="body_top">

<?php
$result = getHistoryData($pid);
if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);
}

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 " .
  "ORDER BY group_name, seq");
?>

<form action="history_save.php" name='history_form' method='post' onsubmit='return validate(this)'>
<input type='hidden' name='mode' value='save'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href='history.php' onclick='top.restoreSession()'>
<?php } else { ?>
<a href='patient_history.php' target='Main' onclick='top.restoreSession()'>
<?php } ?>
<font class='title'><?php xl('Patient History / Lifestyle','e'); ?></font>
<font class=back><?php echo $tback;?></font></a><br>

<!-- Start New Stuff -->

<?php

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
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

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = isset($result[$field_id]) ? $result[$field_id] : '';

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    $group_seq  = substr($this_group, 0, 1);
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
    echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
      "onclick='return divclick(this,\"div_$group_seq\");'";
    if ($display_style == 'block') echo " checked";
    echo " /><b>$group_name</b></span>\n";
    echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
    echo " <table border='0' cellpadding='0'>\n";
    $display_style = 'none';
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td colspan='$titlecols' valign='top'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  echo "<b>";
  if ($frow['title']) echo $frow['title'] . ":";
  echo "</b>";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_form_field($frow, $currvalue);
}

end_group();
?>

<!-- Old Stuff Commented Out:

<table border='0' cellpadding='5' width='100%'>

 <tr>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><?php xl('Family History','e'); ?>:</td></tr>
    <tr><td class='text'><?php xl('Father','e'); ?></td><td><input type='text' size='20' name='history_father' value="<?php echo $result{"history_father"};?>"></tr>
    <tr><td class='text'><?php xl('Mother','e'); ?></td><td><input type='text' size='20' name='history_mother' value="<?php echo $result{"history_mother"};?>"></tr>
    <tr><td class='text'><?php xl('Siblings','e'); ?></td><td><input type='text' size='20' name='history_siblings' value="<?php echo $result{"history_siblings"};?>"></tr>
    <tr><td class='text'><?php xl('Spouse','e'); ?></td><td><input type='text' size='20' name='history_spouse' value="<?php echo $result{"history_spouse"};?>"></tr>
    <tr><td class='text'><?php xl('Offspring','e'); ?>&nbsp;</td><td><input type='text' size='20' name='history_offspring' value="<?php echo $result{"history_offspring"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><?php xl('Relatives','e'); ?>:</td></tr>
    <tr><td class='text'><?php xl('Cancer','e'); ?></td><td><input type='text' size='20' name='relatives_cancer' value="<?php echo $result{"relatives_cancer"};?>"></tr>
    <tr><td class='text'><?php xl('Tuberculosis','e'); ?></td><td><input type='text' size='20' name='relatives_tuberculosis' value="<?php echo $result{"relatives_tuberculosis"};?>"></tr>
    <tr><td class='text'><?php xl('Diabetes','e'); ?></td><td><input type='text' size='20' name='relatives_diabetes' value="<?php echo $result{"relatives_diabetes"};?>"></tr>
    <tr><td class='text'><?php xl('High Blood Pressure','e'); ?>&nbsp;</td><td><input type='text' size='20' name='relatives_high_blood_pressure' value="<?php echo $result{"relatives_high_blood_pressure"};?>"></tr>
    <tr><td class='text'><?php xl('Heart Problems','e'); ?></td><td><input type='text' size='20' name='relatives_heart_problems' value="<?php echo $result{"relatives_heart_problems"};?>"></tr>
    <tr><td class='text'><?php xl('Stroke','e'); ?></td><td><input type='text' size='20' name='relatives_stroke' value="<?php echo $result{"relatives_stroke"};?>"></tr>
    <tr><td class='text'><?php xl('Epilepsy','e'); ?></td><td><input type='text' size='20' name='relatives_epilepsy' value="<?php echo $result{"relatives_epilepsy"};?>"></tr>
    <tr><td class='text'><?php xl('Mental Illness','e'); ?></td><td><input type='text' size='20' name='relatives_mental_illness' value="<?php echo $result{"relatives_mental_illness"};?>"></tr>
    <tr><td class='text'><?php xl('Suicide','e'); ?></td><td><input type='text' size='20' name='relatives_suicide' value="<?php echo $result{"relatives_suicide"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
   <table border=0 cellpadding=0 cellspacing=0>
    <tr><td colspan=2 class=bold><?php xl('Lifestyle','e'); ?>:</td></tr>
    <tr><td class='text'><?php xl('Coffee','e'); ?></td><td><input type='text' size='20' name='coffee' value="<?php echo $result{"coffee"};?>"></tr>
    <tr><td class='text'><?php xl('Tobacco','e'); ?></td><td><input type='text' size='20' name='tobacco' value="<?php echo $result{"tobacco"};?>"></tr>
    <tr><td class='text'><?php xl('Alcohol','e'); ?></td><td><input type='text' size='20' name='alcohol' value="<?php echo $result{"alcohol"};?>"></tr>
    <tr><td class='text'><?php xl('Sleep Patterns','e'); ?></td><td><input type='text' size='20' name='sleep_patterns' value="<?php echo $result{"sleep_patterns"};?>"></tr>
    <tr><td class='text'><?php xl('Exercise Patterns','e'); ?></td><td><input type='text' size='20' name='exercise_patterns' value="<?php echo $result{"exercise_patterns"};?>"></tr>
    <tr><td class='text'><?php xl('Seatbelt Use','e'); ?></td><td><input type='text' size='20' name='seatbelt_use' value="<?php echo $result{"seatbelt_use"};?>"></tr>
    <tr><td class='text'><?php xl('Counseling','e'); ?></td><td><input type='text' size='20' name='counseling' value="<?php echo $result{"counseling"};?>"></tr>
    <tr><td class='text'><?php xl('Hazardous Activities','e'); ?>&nbsp;</td><td><input type='text' size='20' name='hazardous_activities' value="<?php echo $result{"hazardous_activities"};?>"></tr>
   </table>
  </td>
  <td valign='top'>
  </td>
 </tr>
</table>

<table border='0' cellpadding='5' width='100%'>
 <tr>
  <td valign='top' width='10%'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr>
     <td colspan='2' class='bold'><?php xl('Date/Notes of Last','e'); ?>:</td>
     <td class='bold'><?php xl('Nor','e'); ?>&nbsp;</td>
     <td class='bold'><?php xl('Abn','e'); ?></td>
    </tr>
<?php
 foreach ($exams as $key => $value) {
  $testresult = substr($result['last_exam_results'], substr($value, 0, 2), 1);
  echo "    <tr>\n";
  echo "     <td class='text' nowrap>" . substr($value, 3) . "&nbsp;</td>\n";
  echo "     <td nowrap><input type='text' size='30' name='$key' value='" .
       addslashes($result[$key]) . "'>&nbsp;</td>\n";
  echo "     <td nowrap><input type='radio' name='rb_$key' value='1'";
  if ($testresult == '1') echo " checked";
  echo " /></td>\n";
  echo "     <td nowrap><input type='radio' name='rb_$key' value='2'";
  if ($testresult == '2') echo " checked";
  echo " /></td>\n";
  echo "    </tr>\n";
 }

 $needwarning = false;
 foreach ($obsoletes as $key => $value) {
  if ($result[$key] && $result[$key] != '0000-00-00 00:00:00') {
   $needwarning = true;
   echo "    <tr>\n";
   echo "     <td class='text' nowrap><font color='red'>$value&nbsp;</font></td>\n";
   echo "     <td class='bold' colspan='3' nowrap><input type='text' size='10' name='$key' value='" .
        substr($result[$key], 0, 10) . "'>&nbsp;<font color='red'>**</font></td>\n";
   echo "    </tr>\n";
  }
 }
 if ($needwarning) {
  echo "    <tr>\n";
  echo "     <td class='text' colspan='4' nowrap><font color='red'>" . xl('** Please move surgeries to Issues!'). "</font></td>\n";
  echo "    </tr>\n";
 }
?>
   </table>
  </td>
  <td align='center' valign='top'>
   <table border='0' cellpadding='0' cellspacing='0'>
    <tr><td colspan='2' class='bold'><?php xl('Additional History','e'); ?>:</td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_1' value="<?php echo $result{"name_1"}?>">:</td><td><input type='text' size='20' name='value_1' value="<?php echo $result{"value_1"}?>"></td></tr>
    <tr><td class='text'><input type='text' size='20' name='name_2' value="<?php echo $result{"name_2"}?>">:</td><td><input type='text' size='20' name='value_2' value="<?php echo $result{"value_2"}?>"></td></tr>
   </table><br>
   <textarea cols="50" rows="5" name="additional_history"><?php echo $result{"additional_history"}?></textarea>
   <p>
   <input type='submit' value='<?php xl('Save','e'); ?>.' />&nbsp;
   <input type='button' value='<?php xl('To Issues','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="top.restoreSession();parent.left_nav.setRadio(window.name,'iss');location='../summary/stats_full.php';" />&nbsp;
<?php } else { ?>
    onclick="top.restoreSession();location='../summary/stats_full.php';" />&nbsp;
<?php } ?>
   <input type='button' value='<?php xl('Back','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="top.restoreSession();location='history.php';" />
<?php } else { ?>
    onclick="top.restoreSession();location='patient_history.php';" />
<?php } ?>
  </td>
 </tr>
</table>

-->



<center><br />
   <input type='submit' value='<?php xl('Save','e'); ?>' />&nbsp;
   <input type='button' value='<?php xl('To Issues','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="top.restoreSession();parent.left_nav.setRadio(window.name,'iss');location='../summary/stats_full.php';" />&nbsp;
<?php } else { ?>
    onclick="top.restoreSession();location='../summary/stats_full.php';" />&nbsp;
<?php } ?>
   <input type='button' value='<?php xl('Back','e'); ?>'
<?php if ($GLOBALS['concurrent_layout']) { ?>
    onclick="top.restoreSession();location='history.php';" />
<?php } else { ?>
    onclick="top.restoreSession();location='patient_history.php';" />
<?php } ?>
</center>



</form>

<script language="JavaScript">
<?php echo $date_init; // setup for popup calendars ?>
</script>

</body>
</html>
