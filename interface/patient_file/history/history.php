<?php
 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("history.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<?php
 $thisauth = acl_check('patients', 'med');
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(History not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 $result = getHistoryData($pid);
 if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);	
 }
?>

<?php if ($thisauth == 'write' || $thisauth == 'addonly') { ?>
<a href="history_full.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>
 onclick="top.restoreSession()">
<font class=title><?php xl('Patient History / Lifestyle','e'); ?></font>
<font class=more><?php echo $tmore;?></font></a><br>
<?php } ?>

<!-- New stuff begins here. -->

<table border='0' cellpadding='0' width='100%'>

<?php

$CPR = 4; // cells per row of generic data

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
  }
}

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 " .
  "ORDER BY group_name, seq");
$last_group = '';
$cell_count = 0;
$item_count = 0;

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
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr><td class='bold' valign='top' style='padding-right:5pt'>";
    if ($group_name) {
      echo "<font color='#008800'>$group_name</font>";
      $group_name = '';
    } else {
      echo '&nbsp;';
    }
    echo "</td>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td class='bold' colspan='$titlecols' valign='top'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  if ($frow['title']) echo $frow['title'] . ":";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  echo generate_display_field($frow, $currvalue);
}

end_group();
?>

</table>



<!-- Old stuff commented out:

<table border='0' cellpadding='2' width='100%'>
 <tr>
  <td valign='top' width='34%'>
   <span class='bold'><?php xl('Family History','e'); ?>:</span><br>
   <?php if ($result{"history_father"} != "") {?><span class='text'><?php xl('Father','e'); ?>: </span><span class='text'><?php echo $result{"history_father"};?></span><br><?php } ?>
   <?php if ($result{"history_mother"} != "") {?><span class='text'><?php xl('Mother','e'); ?>: </span><span class='text'><?php echo $result{"history_mother"};?></span><br><?php } ?>
   <?php if ($result{"history_siblings"} != "") {?><span class='text'><?php xl('Siblings','e'); ?>: </span><span class='text'><?php echo $result{"history_siblings"};?></span><br><?php } ?>
   <?php if ($result{"history_spouse"} != "") {?><span class='text'><?php xl('Spouse','e'); ?>: </span><span class='text'><?php echo $result{"history_spouse"};?></span><br><?php } ?>
   <?php if ($result{"history_offspring"} != "") {?><span class='text'><?php xl('Offspring','e'); ?>: </span><span class='text'><?php echo $result{"history_offspring"};?></span><br><?php } ?>
  </td>

  <td valign='top' width='33%'>
   <span class='bold'><?php xl('Relatives','e'); ?>:</span><br>
   <?php if ($result{"relatives_cancer"} != "") {?><span class='text'><?php xl('Cancer','e'); ?>: </span><span class='text'><?php echo $result{"relatives_cancer"};?></span><br><?php } ?>
   <?php if ($result{"relatives_tuberculosis"} != "") {?><span class='text'><?php xl('Tuberculosis','e'); ?>: </span><span class='text'><?php echo $result{"relatives_tuberculosis"};?></span><br><?php } ?>
   <?php if ($result{"relatives_diabetes"} != "") {?><span class='text'><?php xl('Diabetes','e'); ?>: </span><span class='text'><?php echo $result{"relatives_diabetes"};?></span><br><?php } ?>
   <?php if ($result{"relatives_high_blood_pressure"} != "") {?><span class='text'><?php xl('High Blood Pressure','e'); ?>: </span><span class='text'><?php echo $result{"relatives_high_blood_pressure"};?></span><br><?php } ?>
   <?php if ($result{"relatives_heart_problems"} != "") {?><span class='text'><?php xl('Heart Problems','e'); ?>: </span><span class='text'><?php echo $result{"relatives_heart_problems"};?></span><br><?php } ?>
   <?php if ($result{"relatives_stroke"} != "") {?><span class='text'><?php xl('Stroke','e'); ?>: </span><span class='text'><?php echo $result{"relatives_stroke"};?></span><br><?php } ?>
   <?php if ($result{"relatives_epilepsy"} != "") {?><span class='text'><?php xl('Epilepsy','e'); ?>: </span><span class='text'><?php echo $result{"relatives_epilepsy"};?></span><br><?php } ?>
   <?php if ($result{"relatives_mental_illness"} != "") {?><span class='text'><?php xl('Mental Illness','e'); ?>: </span><span class='text'><?php echo $result{"relatives_mental_illness"};?></span><br><?php } ?>
   <?php if ($result{"relatives_suicide"} != "") {?><span class='text'><?php xl('Suicide','e'); ?>: </span><span class='text'><?php echo $result{"relatives_suicide"};?></span><br><?php } ?>
  </td>

  <td valign='top' width='33%'>
   <span class='bold'><?php xl('Lifestyle','e'); ?>:</span><br>
   <?php if ($result{"coffee"} != "") {?><span class='text'><?php xl('Coffee','e'); ?>: </span><span class='text'><?php echo $result{"coffee"};?></span><br><?php } ?>
   <?php if ($result{"tobacco"} != "") {?><span class='text'><?php xl('Tobacco','e'); ?>: </span><span class='text'><?php echo $result{"tobacco"};?></span><br><?php } ?>
   <?php if ($result{"alcohol"} != "") {?><span class='text'><?php xl('Alcohol','e'); ?>: </span><span class='text'><?php echo $result{"alcohol"};?></span><br><?php } ?>
   <?php if ($result{"sleep_patterns"} != "") {?><span class='text'><?php xl('Sleep Patterns','e'); ?>: </span><span class='text'><?php echo $result{"sleep_patterns"};?></span><br><?php } ?>
   <?php if ($result{"exercise_patterns"} != "") {?><span class='text'><?php xl('Exercise Patterns','e'); ?>: </span><span class='text'><?php echo $result{"exercise_patterns"};?></span><br><?php } ?>
   <?php if ($result{"seatbelt_use"} != "") {?><span class='text'><?php xl('Seatbelt Use','e'); ?>: </span><span class='text'><?php echo $result{"seatbelt_use"};?></span><br><?php } ?>
   <?php if ($result{"counseling"} != "") {?><span class='text'><?php xl('Counseling','e'); ?>: </span><span class='text'><?php echo $result{"counseling"};?></span><br><?php } ?>
   <?php if ($result{"hazardous_activities"} != "") {?><span class='text'><?php xl('Hazardous Activities','e'); ?>: </span><span class='text'><?php echo $result{"hazardous_activities"};?></span><br><?php } ?>
  </td>
 </tr>

 <tr>
  <td colspan='3'>&nbsp;</td>
 </tr>

 <tr>
  <td valign='top' class='text'>
   <span class='bold'><?php xl('Date of Last','e'); ?>:</span>
<?php
foreach ($exams as $key => $value) {
  if ($result[$key]) {
   $testresult = substr($result['last_exam_results'], substr($value, 0, 2), 1);
   echo "   <br>";
   if ($testresult == '2') echo "<font color='red'>";
   echo substr($value, 3) . ": " . $result[$key];
   if ($testresult == '2') echo " (abn)</font>";
   echo "\n";
  }
 }

 foreach ($obsoletes as $key => $value) {
  if ($result[$key] && $result[$key] != '0000-00-00 00:00:00') {
   echo "   <br>$value: " . substr($result[$key], 0, 10) . "\n";
  }
 }
?>
  </td>

  <td valign='top' class='text' colspan='2'>
   <span class='bold'><?php xl('Additional History','e'); ?>:</span><br>
   <?php if (!empty($result{"name_1"})) {?><span class='text'><b><?=$result{"name_1"}?></b>: </span><span class='text'><?php echo $result{"value_1"}?></span><br><?php } ?>
   <?php if (!empty($result{"name_2"})) {?><span class='text'><b><?=$result{"name_2"}?></b>: </span><span class='text'><?php echo $result{"value_2"}?></span><br><?php } ?>

   <?php if (!empty($result{"additional_history"})) {?><span class='text'><?php echo $result{"additional_history"}?></span><br><?php } ?>
  </td>
 </tr>
</table>

-->



</body>
</html>
