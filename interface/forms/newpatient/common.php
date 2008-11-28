<?php
$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if ($viewmode) {
  $id = $_REQUEST['id'];
  $result = sqlQuery("SELECT * FROM form_encounter WHERE id = '$id'");
  $encounter = $result['encounter'];
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
    echo "<body>\n<html>\n";
    echo "<p>You are not authorized to see this encounter.</p>\n";
    echo "</body>\n</html>\n";
    exit();
  }
}

// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b) {
  return ($a[2] < $b[2]) ? -1 : 1;
}

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = $pid AND enddate IS NULL " .
  "ORDER BY type, begdate");
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Encounter','e'); ?></title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../../library/calendar.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 600, 475);
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0]['issues[]'];
  s.options[s.options.length] = new Option(title, issue, true, true);
 }

 function saveClicked() {
  var f = document.forms[0];
<?php if ($GLOBALS['ippf_specific']) { ?>
  if (f['issues[]'].selectedIndex < 0) {
   if (!confirm('There is no issue selected. If this visit relates to ' +
    'contraception or abortion, click Cancel now and then select or ' +
    'create the appropriate issue. Otherwise you can click OK.'))
   {
    return;
   }
  }
<?php } ?>
  top.restoreSession();
  f.submit();
 }

</script>
</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.reason.focus();">
<?php } ?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'
 <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>>

<?php if ($viewmode) { ?>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<?php echo $_GET["id"] ?>'>
<span class=title><?php xl('Patient Encounter Form','e'); ?></span>
<?php } else { ?>
<input type='hidden' name='mode' value='new'>
<span class='title'><?php xl('New Encounter Form','e'); ?></span>
<?php } ?>

<br>
<center>

<table width='96%'>

 <tr>
  <td width='33%' nowrap class='bold'><?php xl('Consultation Brief Description','e'); ?>:</td>
  <td width='34%' rowspan='2' align='center' valign='top' class='text'>
   <table>

    <tr>
     <td class='bold' nowrap><?php xl('Visit Category:','e'); ?></td>
     <td class='text'>
      <select name='pc_catid'>
<?php
 $cres = sqlStatement("SELECT pc_catid, pc_catname " .
  "FROM openemr_postcalendar_categories ORDER BY pc_catname");
 while ($crow = sqlFetchArray($cres)) {
  $catid = $crow['pc_catid'];
  if ($catid < 9 && $catid != 5) continue;
  echo "       <option value='$catid'";
  if ($viewmode && $crow['pc_catid'] == $result['pc_catid']) echo " selected";
  echo ">" . $crow['pc_catname'] . "</option>\n";
 }
?>
      </select>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php xl('Facility:','e'); ?></td>
     <td class='text'>
      <select name='facility_id'>
<?php

if ($viewmode) {
  $def_facility = $result['facility_id'];
} else {
  $dres = sqlStatement("select facility_id from users where username = '" . $_SESSION['authUser'] . "'");
  $drow = sqlFetchArray($dres);
  $def_facility = $drow['facility_id'];
}
$fres = sqlStatement("select * from facility where service_location != 0 order by name");
if ($fres) {
  $fresult = array();
  for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
    $fresult[$iter] = $frow;
  foreach($fresult as $iter) {
?>
       <option value="<?php echo $iter['id']; ?>" <?php if ($def_facility == $iter['id']) echo "selected";?>><?php echo $iter['name']; ?></option>
<?php
  }
 }
?>
      </select>
     </td>
    </tr>

    <tr>
<?php
 $sensitivities = acl_get_sensitivities();
 if ($sensitivities && count($sensitivities)) {
  usort($sensitivities, "sensitivity_compare");
?>
     <td class='bold' nowrap><?php xl('Sensitivity:','e'); ?></td>
     <td class='text'>
      <select name='form_sensitivity'>
<?php
  foreach ($sensitivities as $value) {
   // Omit sensitivities to which this user does not have access.
   if (acl_check('sensitivities', $value[1])) {
    echo "       <option value='" . $value[1] . "'";
    if ($viewmode && $result['sensitivity'] == $value[1]) echo " selected";
    echo ">" . $value[3] . "</option>\n";
   }
  }
  echo "       <option value=''";
  if ($viewmode && !$result['sensitivity']) echo " selected";
  echo ">" . xl('None'). "</option>\n";
?>
      </select>
     </td>
<?php
 } else {
?>
     <td colspan='2'><!-- sensitivities not used --></td>
<?php
 }
?>
    </tr>

    <tr>
     <td class='bold' nowrap><?php xl('Date of Service:','e'); ?></td>
     <td class='text' nowrap>
      <input type='text' size='10' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $viewmode ? substr($result['date'], 0, 10) : date('Y-m-d'); ?>'
       title='<?php xl('yyyy-mm-dd Date of service','e'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
      <a href="javascript:show_calendar('new_encounter.form_date')"
       title="<?php xl('Click here to choose a date','e'); ?>"
       ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' alt='[?]'></a>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php xl('Onset/hosp. date:','e'); ?></td>
     <td class='text' nowrap>
      <input type='text' size='10' name='form_onset_date'
       value='<?php echo $viewmode ? substr($result['onset_date'], 0, 10) : date('Y-m-d'); ?>'
       title='<?php xl('yyyy-mm-dd Date of onset or hospitalization','e'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
      <a href="javascript:show_calendar('new_encounter.form_onset_date')"
       title="<?php xl('Click here to choose a date','e'); ?>"
       ><img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0' alt='[?]'></a>
     </td>
    </tr>

    <tr>
     <td class='text' colspan='2' style='padding-top:1em'>

<?php if ($GLOBALS['athletic_team']) { ?>
      <p><i>Click [Add Issue] to add a new issue if:<br />
      New injury likely to miss &gt; 1 day<br />
      New significant illness/medical<br />
      New allergy - only if nil exist</i></p>
<?php } ?>

      <p class='bold'>
      <a href="javascript:saveClicked();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
      <?php if ($viewmode || !isset($_GET["autoloaded"]) || $_GET["autoloaded"] != "1") { ?>
      &nbsp; &nbsp;
      <?php if ($GLOBALS['concurrent_layout']) { ?>
      <a href="<?php echo "$rootdir/patient_file/encounter/encounter_top.php"; ?>"
        class="link_submit" onclick="top.restoreSession()">[<?php xl('Cancel','e'); ?>]</a>
      <?php } else { ?>
      <a href="<?php echo "$rootdir/patient_file/encounter/patient_encounter.php"; ?>"
        class="link_submit" target='Main' onclick="top.restoreSession()">
      [<?php xl('Cancel','e'); ?>]</a>
      <?php } // end not concurrent layout ?>
      <?php } // end not autoloading ?>
      &nbsp; &nbsp;
      <a href="" onclick="return newissue()" class="link_submit">[<?php xl('Add Issue','e'); ?>]</a>
      </p>

     </td>
    </tr>

   </table>

  </td>

  <td class='bold' width='33%' nowrap>
   <?php xl('Issues (Injuries/Medical/Allergy):','e'); ?>
  </td>
 </tr>

 <tr>
  <td class='text' valign='top'>
   <textarea name='reason' cols='40' rows='12' wrap='virtual' style='width:96%'
    ><?php echo $viewmode ? htmlspecialchars($result['reason']) : $GLOBALS['default_chief_complaint']; ?></textarea>
  </td>
  <td class='text' valign='top'>
   <select multiple name='issues[]' size='8' style='width:100%'
    title='<?php xl('Hold down [Ctrl] for multiple selections or to unselect','e'); ?>'>
<?php
while ($irow = sqlFetchArray($ires)) {
  $list_id = $irow['id'];
  $tcode = $irow['type'];
  if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];

  if ($viewmode) {
    echo "    <option value='$list_id'";
    $perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
      "pid = '$pid' AND encounter = '$encounter' AND list_id = '$list_id'");
    if ($perow['count']) echo " selected";
    echo ">$tcode: " . $irow['begdate'] . " " .
      htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
  }
  else {
    echo "    <option value='$list_id'>$tcode: ";
    echo $irow['begdate'] . " " . htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
  }
}
?>
   </select>

   <p><i>To link this encounter/consult to an existing issue, click the desired issue
   above to highlight it and then click [Save].  Hold down &lt;Ctrl&gt; to select
   multiple issues.</i></p>

  </td>
 </tr>

</table>

</center>

</form>

</body>
</html>
