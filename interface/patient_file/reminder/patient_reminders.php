<?php
// Copyright (C) 2011 by following authors:
//   -Brady Miller <brady@sparmy.com>
//   -Ensofttek, LLC
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/reminders.php");
require_once("$srcdir/clinical_rules.php");

//To improve performance and not freeze the session when running this
// report, turn off session writing. Note that php session variables
// can not be modified after the line below. So, if need to do any php
// session work in the future, then will need to remove this line.
session_write_close();

//Remove time limit, since script can take many minutes
set_time_limit(0);
?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>

<script LANGUAGE="JavaScript">
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<?php
$patient_id = ($_GET['patient_id']) ? $_GET['patient_id'] : "";
$mode = ($_GET['mode']) ? $_GET['mode'] : "simple";
$sortby = $_GET['sortby'];
$sortorder = $_GET['sortorder'];
$begin = $_GET['begin'];

// Update the reminders and show debugging data
if (empty($patient_id)) {
  //Update all patients
  $update_rem_log = update_reminders_batch_method();
}
else {
  //Only update one patient
  $update_rem_log = update_reminders('', $patient_id);
}

if ($mode == "simple") {
  // Collect the rules for the per patient rules selection tab
  $rules_default = resolve_rules_sql('','0',TRUE);
}

?>

<script language="javascript">
  // This is for callback by the find-patient popup.
  function setpatient(pid, lname, fname, dob) {
    var f = document.forms[0];
    f.form_patient.value = lname + ', ' + fname;
    f.patient_id.value = pid;
  }

  // This invokes the find-patient popup.
  function sel_patient() {
    dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 500, 400);
  }
</script>

<body class='body_top'>
<div>
  <span class='title'><?php echo htmlspecialchars( xl('Patient Reminders'), ENT_NOQUOTES); ?></span>
</div>
<?php if ($mode == "simple") { ?> 
  <div style='float:left;margin-right:10px'>
    <?php echo htmlspecialchars( xl('for'), ENT_NOQUOTES);?>&nbsp;
    <span class="title">
      <a href="../summary/demographics.php" onclick="top.restoreSession()"><?php echo htmlspecialchars( getPatientName($pid), ENT_NOQUOTES); ?></a>
    </span>
  </div>
  <div>
    <a href="../summary/demographics.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onclick="top.restoreSession()">
      <span><?php echo htmlspecialchars( xl('Back To Patient'), ENT_NOQUOTES);?></span>
    </a>
  </div>
<?php } ?>
<div>
 &nbsp;
</div>

<?php
// This is for sorting the records.
$sort = array("category, item", "lname, fname", "due_status", "date_created", "hipaa_allowemail", "hipaa_allowsms", "date_sent", "voice_status", "email_status", "sms_status", "mail_status");
if($sortby == "") {
  $sortby = $sort[0];
}
if($sortorder == "") { 
  $sortorder = "asc";
}
for($i = 0; $i < count($sort); $i++) {
  $sortlink[$i] = "<a href=\"patient_reminders.php?patient_id=$patient_id&mode=$mode&sortby=$sort[$i]&sortorder=asc\" onclick=\"top.restoreSession()\">" .
    "<img src=\"../../../images/sortdown.gif\" border=0 alt=\"".htmlspecialchars(xl('Sort Up'), ENT_QUOTES)."\"></a>";
}
for($i = 0; $i < count($sort); $i++) {
  if($sortby == $sort[$i]) {
    switch($sortorder) {
      case "asc"      : $sortlink[$i] = "<a href=\"patient_reminders.php?patient_id=$patient_id&mode=$mode&sortby=$sortby&sortorder=desc\" onclick=\"top.restoreSession()\">" .
                          "<img src=\"../../../images/sortup.gif\" border=0 alt=\"".htmlspecialchars(xl('Sort Up'), ENT_QUOTES)."\"></a>";
                        break;
      case "desc"     : $sortlink[$i] = "<a href=\"patient_reminders.php?patient_id=$patient_id&mode=$mode&sortby=$sortby&sortorder=asc\" onclick=\"top.restoreSession()\">" .
                          "<img src=\"../../../images/sortdown.gif\" border=0 alt=\"".htmlspecialchars(xl('Sort Down'), ENT_QUOTES)."\"></a>";
                        break;
    } break;
  }
}
// This is for managing page numbering and display beneath the Patient Reminders table.
$listnumber = 25;
$sqlBindArray = array();
if (!empty($patient_id)) {
  $add_sql = "AND a.pid=? ";
  array_push($sqlBindArray,$patient_id);
}
$sql = "SELECT a.id, a.due_status, a.category, a.item, a.date_created, a.date_sent, b.fname, b.lname " .
  "FROM `patient_reminders` as a, `patient_data` as b " .
  "WHERE a.active='1' AND a.pid=b.pid ".$add_sql;
$result = sqlStatement($sql, $sqlBindArray);
if(sqlNumRows($result) != 0) {
  $total = sqlNumRows($result);
}
else {
  $total = 0;
}
if($begin == "" or $begin == 0) {
  $begin = 0;
}
$prev = $begin - $listnumber;
$next = $begin + $listnumber;
$start = $begin + 1;
$end = $listnumber + $start - 1;
if($end >= $total) {
 $end = $total;
}
if($end < $start) {
  $start = 0;
}
if($prev >= 0) {
  $prevlink = "<a href=\"patient_reminders.php?patient_id=$patient_id&mode=$mode&sortby=$sortby&sortorder=$sortorder&begin=$prev\" onclick=\"top.restoreSession()\"><<</a>";
}
else { 
  $prevlink = "<<";
}

if($next < $total) {
  $nextlink = "<a href=\"patient_reminders.php?patient_id=$patient_id&mode=$mode&sortby=$sortby&sortorder=$sortorder&begin=$next\" onclick=\"top.restoreSession()\">>></a>";
}
else {
  $nextlink = ">>";
}
?>


<br>
<br>

<?php if ($mode == "simple") { // show the per patient rule setting option ?>
  <ul class="tabNav">
    <li class='current'><a href='/play/javascript-tabbed-navigation/'><?php echo htmlspecialchars( xl('Main'), ENT_NOQUOTES); ?></a></li>
    <li ><a href='/play/javascript-tabbed-navigation/' onclick='top.restoreSession()'><?php echo htmlspecialchars( xl('Rules'), ENT_NOQUOTES); ?></a></li>
  </ul>
  <div class="tabContainer">
  <div class="tab current" style="height:auto;width:97%;">
<?php } ?>

<div id='report_parameters'>
  <table>
    <tr>
      <td width='410px'>
        <div style='float:left'>
          <table class='text'>
            <tr>
              <td class='label'>
                <?php echo " "; ?>
              </td>
            </tr>
          </table>
        </div>
      </td>
      <td align='left' valign='middle' height="100%">
        <table style='border-left:1px solid; width:100%; height:100%' >
          <tr>
            <td>
              <div style='margin-left:15px'>
                <?php if ($mode == "admin") { ?>
                 <a href='#' class='css_button' onclick='return ReminderBatch()'>
                   <span><?php echo htmlspecialchars( xl('Send Reminders Batch'), ENT_NOQUOTES); ?></span>
                 </a>
                <?php } ?>
                <a href='patient_reminders.php?patient_id=<?php echo $patient_id; ?>&mode=<?php echo $mode; ?>' class='css_button' onclick='top.restoreSession()'>
                  <span><?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?></span>
                </a>
              </div>
            </td>
            <td align=right class='text'><?php echo $prevlink." ".$end." of ".$total." ".$nextlink; ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>

<div id='report_results'>
    <table>
      <thead>
        <th><?php echo htmlspecialchars( xl('Item'), ENT_NOQUOTES) . " " . $sortlink[0]; ?></th>
        <th><?php echo htmlspecialchars( xl('Patient'), ENT_NOQUOTES) . " " . $sortlink[1]; ?></th>
        <th><?php echo htmlspecialchars( xl('Due Status'), ENT_NOQUOTES) . " " . $sortlink[2]; ?></th>
        <th><?php echo htmlspecialchars( xl('Date Created'), ENT_NOQUOTES) . " " . $sortlink[3]; ?></th>
        <th><?php echo htmlspecialchars( xl('Email Auth'), ENT_NOQUOTES) . " " . $sortlink[4]; ?></th>
        <th><?php echo htmlspecialchars( xl('SMS Auth'), ENT_NOQUOTES) . " " . $sortlink[5]; ?></th>
        <th><?php echo htmlspecialchars( xl('Date Sent'), ENT_NOQUOTES) . " " . $sortlink[6]; ?></th>
        <th><?php echo htmlspecialchars( xl('Voice Sent'), ENT_NOQUOTES) . " " . $sortlink[7]; ?></th>
        <th><?php echo htmlspecialchars( xl('Email Sent'), ENT_NOQUOTES) . " " . $sortlink[8]; ?></th>
        <th><?php echo htmlspecialchars( xl('SMS Sent'), ENT_NOQUOTES) . " " . $sortlink[9]; ?></th>
        <th><?php echo htmlspecialchars( xl('Mail Sent'), ENT_NOQUOTES) . " " . $sortlink[10]; ?></th>
      </thead>
      <tbody>
<?php
      $sql = "SELECT a.id, a.due_status, a.category, a.item, a.date_created, a.date_sent, a.voice_status, " .
      				"a.sms_status, a.email_status, a.mail_status, b.fname, b.lname, b.hipaa_allowemail, b.hipaa_allowsms " .
        "FROM `patient_reminders` as a, `patient_data` as b " .
        "WHERE a.active='1' AND a.pid=b.pid " . $add_sql .
        "ORDER BY " . add_escape_custom($sortby) . " " .
        add_escape_custom($sortorder) . " " .
        "LIMIT " . add_escape_custom($begin) . ", " .
        add_escape_custom($listnumber);
      $result = sqlStatement($sql,$sqlBindArray);
      while ($myrow = sqlFetchArray($result)) { ?>
        <tr>
          <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$myrow['category']) . " : " .
                generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$myrow['item']); ?></td>
          <td><?php echo htmlspecialchars($myrow['lname'].", ".$myrow['fname'], ENT_NOQUOTES); ?></td>
          <td><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_reminder_due_opt'),$myrow['due_status']); ?></td>
          <td><?php echo ($myrow['date_created']) ? htmlspecialchars($myrow['date_created'], ENT_NOQUOTES) : " "; ?></td>
          <td><?php echo ($myrow['hipaa_allowemail']=='YES') ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['hipaa_allowsms']=='YES') ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['date_sent']) ? htmlspecialchars($myrow['date_sent'], ENT_NOQUOTES) : htmlspecialchars( xl("Not Sent Yet") , ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['voice_status']==1) ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['email_status']==1) ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['sms_status']==1) ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
          <td><?php echo ($myrow['mail_status']==1) ? htmlspecialchars( xl("YES"), ENT_NOQUOTES) : htmlspecialchars( xl("NO"), ENT_NOQUOTES); ?></td>
        </tr>
<?php } ?>
      </tbody>
    </table>
</div>

<?php if ($mode == "simple") { // show the per patient rule setting option ?>
  </div>
  <div class="tab" style="height:auto;width:97%;">
    <div id='report_results'>
      <table>
        <tr>
          <th rowspan="2"><?php echo htmlspecialchars( xl('Rule'), ENT_NOQUOTES); ?></th>
          <th colspan="2"><?php echo htmlspecialchars( xl('Patient Reminder'), ENT_NOQUOTES); ?></th>
        </tr>
        <tr>
          <th><?php echo htmlspecialchars( xl('Patient Setting'), ENT_NOQUOTES); ?></th>
          <th style="left-margin:1em;"><?php echo htmlspecialchars( xl('Practice Default Setting'), ENT_NOQUOTES); ?></th>
        </tr>
        <?php foreach ($rules_default as $rule) { ?>
          <tr>
            <td style="border-right:1px solid black;"><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'clinical_rules'), $rule['id']); ?></td>
            <td align="center">
              <?php
              $patient_rule = collect_rule($rule['id'],$patient_id);
              // Set the patient specific setting for gui
              if (empty($patient_rule)) {
                $select = "default";
              }
              else {
                if ($patient_rule['patient_reminder_flag'] == "1") {
                  $select = "on";
                }
                else if ($patient_rule['patient_reminder_flag'] == "0"){
                  $select = "off";
                }
                else { // $patient_rule['patient_reminder_flag'] == NULL
                  $select = "default";
                }
              } ?>
              <select class="patient_reminder" name="<?php echo htmlspecialchars( $rule['id'], ENT_NOQUOTES); ?>">
                <option value="default" <?php if ($select == "default") echo "selected"; ?>><?php echo htmlspecialchars( xl('Default'), ENT_NOQUOTES); ?></option>
                <option value="on" <?php if ($select == "on") echo "selected"; ?>><?php echo htmlspecialchars( xl('On'), ENT_NOQUOTES); ?></option>
                <option value="off" <?php if ($select == "off") echo "selected"; ?>><?php echo htmlspecialchars( xl('Off'), ENT_NOQUOTES); ?></option>
              </select>
            </td>
            <td align="center" style="border-right:1px solid black;">
              <?php if ($rule['patient_reminder_flag'] == "1") {
                echo htmlspecialchars( xl('On'), ENT_NOQUOTES);
              }
              else {
                echo htmlspecialchars( xl('Off'), ENT_NOQUOTES);
              } ?>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
  </div>
<?php } ?>

<script language="javascript">

$(document).ready(function(){

  tabbify();

  $(".patient_reminder").change(function() {
    top.restoreSession();
    $.post( "../../../library/ajax/rule_setting.php", {
      rule: this.name,
      type: 'patient_reminder',
      setting: this.value,
      patient_id: '<?php echo htmlspecialchars($patient_id, ENT_QUOTES); ?>'
    });
  });

});

// Show a template popup of patient reminders batch sending tool.
function ReminderBatch() {
  top.restoreSession();
  dlgopen('../../batchcom/batch_reminders.php', '_blank', 600, 500);
  return false;
}
</script>
</body>
</html>

