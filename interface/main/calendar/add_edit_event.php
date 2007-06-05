<?
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // The event editor looks something like this:

 //------------------------------------------------------------//
 // Category __________________V   O All day event             //
 // Date     _____________ [?]     O Time     ___:___ __V      //
 // Title    ___________________     duration ____ minutes     //
 // Patient  _(Click_to_select)_                               //
 // Provider __________________V   X Repeats  ______V ______V  //
 // Status   __________________V     until    __________ [?]   //
 // Comments ________________________________________________  //
 //                                                            //
 //       [Save]  [Find Available]  [Delete]  [Cancel]         //
 //------------------------------------------------------------//

 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/forms.inc");

 // Things that might be passed by our opener.
 //
 $eid           = $_GET['eid'];         // only for existing events
 $date          = $_GET['date'];        // this and below only for new events
 $userid        = $_GET['userid'];
 $default_catid = $_GET['catid'] ? $_GET['catid'] : '5';
 //
 if ($date)
  $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6);
 else
  $date = date("Y-m-d");
 //
 $starttimem = '00';
 if (isset($_GET['starttimem']))
  $starttimem = substr('00' . $_GET['starttimem'], -2);
 //
 if (isset($_GET['starttimeh'])) {
  $starttimeh = $_GET['starttimeh'];
  if (isset($_GET['startampm'])) {
   if ($_GET['startampm'] == '2' && $starttimeh < 12)
    $starttimeh += 12;
  }
 } else {
  $starttimeh = date("G");
 }
 $startampm = '';

 $info_msg = "";

 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {

  $event_date = fixDate($_POST['form_date']);

  // Compute start and end time strings to be saved.
  if ($_POST['form_allday']) {
   $tmph = 0;
   $tmpm = 0;
   $duration = 24 * 60;
  } else {
   $tmph = $_POST['form_hour'] + 0;
   $tmpm = $_POST['form_minute'] + 0;
   if ($_POST['form_ampm'] == '2' && $tmph < 12) $tmph += 12;
   $duration = $_POST['form_duration'];
  }
  $starttime = "$tmph:$tmpm:00";
  //
  $tmpm += $duration;
  while ($tmpm >= 60) {
   $tmpm -= 60;
   ++$tmph;
  }
  $endtime = "$tmph:$tmpm:00";

  // Useless garbage that we must save.
  $locationspec = 'a:6:{s:14:"event_location";N;s:13:"event_street1";N;' .
   's:13:"event_street2";N;s:10:"event_city";N;s:11:"event_state";N;s:12:"event_postal";N;}';

  // More garbage, but this time 1 character of it is used to save the
  // repeat type.
  if ($_POST['form_repeat']) {
   $recurrspec = 'a:5:{' .
    's:17:"event_repeat_freq";s:1:"' . $_POST['form_repeat_freq'] . '";' .
    's:22:"event_repeat_freq_type";s:1:"' . $_POST['form_repeat_type'] . '";' .
    's:19:"event_repeat_on_num";s:1:"1";' .
    's:19:"event_repeat_on_day";s:1:"0";' .
    's:20:"event_repeat_on_freq";s:1:"0";}';
  } else {
   $recurrspec = 'a:5:{' .
    's:17:"event_repeat_freq";N;' .
    's:22:"event_repeat_freq_type";s:1:"0";' .
    's:19:"event_repeat_on_num";s:1:"1";' .
    's:19:"event_repeat_on_day";s:1:"0";' .
    's:20:"event_repeat_on_freq";s:1:"1";}';
  }

/* =======================================================
//                                  UPDATE EVENTS
========================================================*/
  if ($eid) {
    if ($GLOBALS['select_multi_providers']) {
    /* ==========================================
    // multi providers BOS
    ==========================================*/

    // what is multiple key around this $eid?
    $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = $eid");

    // obtain current list of providers regarding the multiple key
    $up = sqlStatement("SELECT pc_aid FROM openemr_postcalendar_events WHERE pc_multiple={$row['pc_multiple']}");
    while ($current = sqlFetchArray($up)) {
        $providers_current[] = $current['pc_aid'];
    } 

    $providers_new = $_POST['form_provider'];

    // this difference means that some providers from current was UNCHECKED
    // so we must delete this event for them
    $r1 = array_diff ($providers_current, $providers_new);
    if (count ($r1)) {
        foreach ($r1 as $to_be_removed) {
           sqlQuery("DELETE FROM openemr_postcalendar_events WHERE pc_aid='$to_be_removed' AND pc_multiple={$row['pc_multiple']}");
        }
    }

    // this difference means that some providers was added 
    // so we must insert this event for them 
   $r2 = array_diff ($providers_new, $providers_current);
    if (count ($r2)) {
        foreach ($r2 as $to_be_inserted) {
            sqlInsert("INSERT INTO openemr_postcalendar_events ( pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing) 
            VALUES ( " .
                "'" . $_POST['form_category']             . "', " .
                "'" . $row['pc_multiple']             . "', " .
                "'" . $to_be_inserted            . "', " .
                "'" . $_POST['form_pid']                  . "', " .
                "'" . $_POST['form_title']                . "', " .
                "NOW(), "                                         .
                "'" . $_POST['form_comments']             . "', " .
                "'" . $_SESSION['authUserID']             . "', " .
                "'" . $event_date                         . "', " .
                "'" . fixDate($_POST['form_enddate'])     . "', " .
                "'" . ($duration * 60)                    . "', " .
                "'" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
                "'$recurrspec', "                                 .
                "'$starttime', "                                  .
                "'$endtime', "                                    .
                "'" . $_POST['form_allday']               . "', " .
                "'" . $_POST['form_apptstatus']           . "', " .
                "'" . $_POST['form_prefcat']              . "', " .
                "'$locationspec', "                               .
                "1, " .
                "1 )");
        } // foreach
    }


    // after the two diffs above, we must update for remaining providers
   // those who are intersected in $providers_current and $providers_new
   foreach ($_POST['form_provider'] as $provider) {
            sqlStatement("UPDATE openemr_postcalendar_events SET " .
            "pc_catid = '"       . $_POST['form_category']             . "', " .
            "pc_pid = '"         . $_POST['form_pid']                  . "', " .
            "pc_title = '"       . $_POST['form_title']                . "', " .
            "pc_time = NOW(), "                                                .
            "pc_hometext = '"    . $_POST['form_comments']             . "', " .
            "pc_informant = '"   . $_SESSION['authUserID']             . "', " .
            "pc_eventDate = '"   . $event_date                         . "', " .
            "pc_endDate = '"     . fixDate($_POST['form_enddate'])     . "', " .
            "pc_duration = '"    . ($duration * 60)                    . "', " .
            "pc_recurrtype = '"  . ($_POST['form_repeat'] ? '1' : '0') . "', " .
            "pc_recurrspec = '$recurrspec', "                                  .
            "pc_startTime = '$starttime', "                                    .
            "pc_endTime = '$endtime', "                                        .
            "pc_alldayevent = '" . $_POST['form_allday']               . "', " .
            "pc_apptstatus = '"  . $_POST['form_apptstatus']           . "', "  .
            "pc_prefcatid = '"   . $_POST['form_prefcat']              . "' "  .
              "WHERE pc_aid = '$provider' AND pc_multiple={$row['pc_multiple']}");
        } // foreach
 
/* ==========================================
// multi providers EOS
==========================================*/

    } else {
    // simple provider case
    sqlStatement("UPDATE openemr_postcalendar_events SET " .
    "pc_catid = '"       . $_POST['form_category']             . "', " .
    "pc_aid = '"         . $_POST['form_provider']             . "', " .
    "pc_pid = '"         . $_POST['form_pid']                  . "', " .
    "pc_title = '"       . $_POST['form_title']                . "', " .
    "pc_time = NOW(), "                                                .
    "pc_hometext = '"    . $_POST['form_comments']             . "', " .
    "pc_informant = '"   . $_SESSION['authUserID']             . "', " .
    "pc_eventDate = '"   . $event_date                         . "', " .
    "pc_endDate = '"     . fixDate($_POST['form_enddate'])     . "', " .
    "pc_duration = '"    . ($duration * 60)                    . "', " .
    "pc_recurrtype = '"  . ($_POST['form_repeat'] ? '1' : '0') . "', " .
    "pc_recurrspec = '$recurrspec', "                                  .
    "pc_startTime = '$starttime', "                                    .
    "pc_endTime = '$endtime', "                                        .
    "pc_alldayevent = '" . $_POST['form_allday']               . "', " .
    "pc_apptstatus = '"  . $_POST['form_apptstatus']           . "', "  .
    "pc_prefcatid = '"   . $_POST['form_prefcat']              . "' "  .
    "WHERE pc_eid = '$eid'");
    }

  } else {

// =======================================
// multi providers case
// =======================================

if (is_array($_POST['form_provider'])) {

    // obtain the next available unique key to group multiple providers around some event
    $q = sqlStatement ("SELECT MAX(pc_multiple) as max FROM openemr_postcalendar_events");
    $max = sqlFetchArray($q);
    $new_multiple_value = $max['max'] + 1;

    foreach ($_POST['form_provider'] as $provider) {
    sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
    "pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
    "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
    "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
    "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing " .
    ") VALUES ( " .
    "'" . $_POST['form_category']             . "', " .
    "'" . $new_multiple_value             . "', " .
    "'" . $provider                           . "', " .
    "'" . $_POST['form_pid']                  . "', " .
    "'" . $_POST['form_title']                . "', " .
    "NOW(), "                                         .
    "'" . $_POST['form_comments']             . "', " .
    "'" . $_SESSION['authUserID']             . "', " .
    "'" . $event_date                         . "', " .
    "'" . fixDate($_POST['form_enddate'])     . "', " .
    "'" . ($duration * 60)                    . "', " .
    "'" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
    "'$recurrspec', "                                 .
    "'$starttime', "                                  .
    "'$endtime', "                                    .
    "'" . $_POST['form_allday']               . "', " .
    "'" . $_POST['form_apptstatus']           . "', " .
    "'" . $_POST['form_prefcat']              . "', " .
    "'$locationspec', "                               .
    "1, " .
    "1 )");

    } // foreach

// =======================================
// EOS multi providers case
// =======================================

} else {
sqlInsert("INSERT INTO openemr_postcalendar_events ( " .
    "pc_catid, pc_aid, pc_pid, pc_title, pc_time, pc_hometext, " .
    "pc_informant, pc_eventDate, pc_endDate, pc_duration, pc_recurrtype, " .
    "pc_recurrspec, pc_startTime, pc_endTime, pc_alldayevent, " .
    "pc_apptstatus, pc_prefcatid, pc_location, pc_eventstatus, pc_sharing " .
    ") VALUES ( " .
    "'" . $_POST['form_category']             . "', " .
    "'" . $_POST['form_provider']             . "', " .
    "'" . $_POST['form_pid']                  . "', " .
    "'" . $_POST['form_title']                . "', " .
    "NOW(), "                                         .
    "'" . $_POST['form_comments']             . "', " .
    "'" . $_SESSION['authUserID']             . "', " .
    "'" . $event_date                         . "', " .
    "'" . fixDate($_POST['form_enddate'])     . "', " .
    "'" . ($duration * 60)                    . "', " .
    "'" . ($_POST['form_repeat'] ? '1' : '0') . "', " .
    "'$recurrspec', "                                 .
    "'$starttime', "                                  .
    "'$endtime', "                                    .
    "'" . $_POST['form_allday']               . "', " .
    "'" . $_POST['form_apptstatus']           . "', " .
    "'" . $_POST['form_prefcat']              . "', " .
    "'$locationspec', "                               .
    "1, " .
    "1 )");
  }
 }


  // Save new DOB if it's there.
  $patient_dob = trim($_POST['form_dob']);
  if ($patient_dob && $_POST['form_pid']) {
   sqlStatement("UPDATE patient_data SET DOB = '$patient_dob' WHERE " .
    "pid = '" . $_POST['form_pid'] . "'");
  }

  // Auto-create a new encounter if appropriate.
  //
  if ($GLOBALS['auto_create_new_encounters'] &&
    $_POST['form_apptstatus'] == '@' && $event_date == date('Y-m-d'))
  {
    $tmprow = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
      "pid = '" . $_POST['form_pid'] . "' AND date = '$event_date 00:00:00'");
    if ($tmprow['count'] == 0) {
      $tmprow = sqlQuery("SELECT username, facility FROM users WHERE id = '" .
        $_POST['form_provider'] . "'");
      $username = $tmprow['username'];
      $facility = $tmprow['facility'];
      $conn = $GLOBALS['adodb']['db'];
      $encounter = $conn->GenID("sequences");
      addForm($encounter, "New Patient Encounter",
        sqlInsert("INSERT INTO form_encounter SET " .
          "date = '$event_date', " .
          "onset_date = '$event_date', " .
          "reason = '" . $_POST['form_comments'] . "', " .
          "facility = '$facility', " .
          "pid = '" . $_POST['form_pid'] . "', " .
          "encounter = '$encounter'"
        ),
        "newpatient", $_POST['form_pid'], "1", "NOW()", $username
      );
      $info_msg .= "New encounter $encounter was created. ";
    }
  }

 }
 else if ($_POST['form_delete']) {
        // =======================================
        //  multi providers case
        // =======================================
        if ($GLOBALS['select_multi_providers']) {
             // what is multiple key around this $eid?
            $row = sqlQuery("SELECT pc_multiple FROM openemr_postcalendar_events WHERE pc_eid = $eid");
            sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_multiple = {$row['pc_multiple']}");
        // =======================================
        //  EOS multi providers case
        // =======================================
        } else {
            sqlStatement("DELETE FROM openemr_postcalendar_events WHERE pc_eid = '$eid'");
        }
 }

 if ($_POST['form_save'] || $_POST['form_delete']) {
  // Close this window and refresh the calendar display.
  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
  echo " window.close();\n";
  echo "</script>\n</body>\n</html>\n";
  exit();
 }

 // If we get this far then we are displaying the form.

 $statuses = array(
  '-' => '',
  '*' => xl('* Reminder done'),
  '+' => xl('+ Chart pulled'),
  '?' => xl('? No show'),
  '@' => xl('@ Arrived'),
  '~' => xl('~ Arrived late'),
  '!' => xl('! Left w/o visit'),
  '#' => xl('# Ins/fin issue'),
  '<' => xl('< In exam room'),
  '>' => xl('> Checked out'),
  '$' => xl('$ Coding done'),
 );

 $repeats = 0; // if the event repeats
 $repeattype = '0';
 $repeatfreq = '0';
 $patientid = '';
 if ($_REQUEST['patientid']) $patientid = $_REQUEST['patientid'];
 $patientname = xl('Click to select');
 $patienttitle = "";
 $hometext = "";
 $row = array();

 // If we are editing an existing event, then get its data.
 if ($eid) {
  $row = sqlQuery("SELECT * FROM openemr_postcalendar_events WHERE pc_eid = $eid");
  $date = $row['pc_eventDate'];
  $userid = $row['pc_aid'];
  $patientid = $row['pc_pid'];
  $starttimeh = substr($row['pc_startTime'], 0, 2) + 0;
  $starttimem = substr($row['pc_startTime'], 3, 2);
  $repeats = $row['pc_recurrtype'];
  $multiple_value = $row['pc_multiple'];
 
  if (preg_match('/"event_repeat_freq_type";s:1:"(\d)"/', $row['pc_recurrspec'], $matches)) {
   $repeattype = $matches[1];
  }
  if (preg_match('/"event_repeat_freq";s:1:"(\d)"/', $row['pc_recurrspec'], $matches)) {
   $repeatfreq = $matches[1];
  }
  $hometext = $row['pc_hometext'];
  if (substr($hometext, 0, 6) == ':text:') $hometext = substr($hometext, 6);
 }

 // If we have a patient ID, get the name and phone numbers to display.
 if ($patientid) {
  $prow = sqlQuery("SELECT lname, fname, phone_home, phone_biz, DOB " .
   "FROM patient_data WHERE pid = '" . $patientid . "'");
  $patientname = $prow['lname'] . ", " . $prow['fname'];
  if ($prow['phone_home']) $patienttitle .= " H=" . $prow['phone_home'];
  if ($prow['phone_biz']) $patienttitle  .= " W=" . $prow['phone_biz'];
 }

 // Get the providers list.
 $ures = sqlStatement("SELECT id, username, fname, lname FROM users WHERE " .
  "authorized != 0 AND active = 1 ORDER BY lname, fname");

 // Get event categories.
 $cres = sqlStatement("SELECT pc_catid, pc_catname, pc_recurrtype, pc_duration, pc_end_all_day " .
  "FROM openemr_postcalendar_categories ORDER BY pc_catname");

 // Fix up the time format for AM/PM.
 $startampm = '1';
 if ($starttimeh >= 12) { // p.m. starts at noon and not 12:01
  $startampm = '2';
  if ($starttimeh > 12) $starttimeh -= 12;
 }
?>
<html>
<head>
<title><? echo $eid ? "Edit" : "Add New" ?> <?php xl('Event','e');?></title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/topdialog.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 var durations = new Array();
 // var rectypes  = new Array();
<?
 // Read the event categories, generate their options list, and get
 // the default event duration from them if this is a new event.
 $catoptions = "";
 $prefcat_options = "    <option value='0'>-- None --</option>\n";
 $thisduration = 0;
 if ($eid) {
  $thisduration = $row['pc_alldayevent'] ? 1440 : round($row['pc_duration'] / 60);
 }
 while ($crow = sqlFetchArray($cres)) {
  $duration = round($crow['pc_duration'] / 60);
  if ($crow['pc_end_all_day']) $duration = 1440;
  echo " durations[" . $crow['pc_catid'] . "] = $duration\n";
  // echo " rectypes[" . $crow['pc_catid'] . "] = " . $crow['pc_recurrtype'] . "\n";
  $catoptions .= "    <option value='" . $crow['pc_catid'] . "'";
  if ($eid) {
   if ($crow['pc_catid'] == $row['pc_catid']) $catoptions .= " selected";
  } else {
   if ($crow['pc_catid'] == $default_catid) {
    $catoptions .= " selected";
    $thisduration = $duration;
   }
  }
  $catoptions .= ">" . $crow['pc_catname'] . "</option>\n";

  // This section is to build the list of preferred categories:
  if ($duration) {
   $prefcat_options .= "    <option value='" . $crow['pc_catid'] . "'";
   if ($eid) {
    if ($crow['pc_catid'] == $row['pc_prefcatid']) $prefcat_options .= " selected";
   }
   $prefcat_options .= ">" . $crow['pc_catname'] . "</option>\n";
  }

 }
?>

 // This is for callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.forms[0];
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;
  dobstyle = (dob == '' || dob.substr(5, 10) == '00-00') ? '' : 'none';
  document.getElementById('dob_row').style.display = dobstyle;
 }

 // This invokes the find-patient popup.
 function sel_patient() {
  dlgopen('find_patient_popup.php', '_blank', 500, 400);
 }

 // Do whatever is needed when a new event category is selected.
 // For now this means changing the event title and duration.
 function set_display() {
  var f = document.forms[0];
  var s = f.form_category;
  if (s.selectedIndex >= 0) {
   var catid = s.options[s.selectedIndex].value;
   var style_apptstatus = document.getElementById('title_apptstatus').style;
   var style_prefcat = document.getElementById('title_prefcat').style;
   if (catid == '2') { // In Office
    style_apptstatus.display = 'none';
    style_prefcat.display = '';
    f.form_apptstatus.style.display = 'none';
    f.form_prefcat.style.display = '';
   } else {
    style_prefcat.display = 'none';
    style_apptstatus.display = '';
    f.form_prefcat.style.display = 'none';
    f.form_apptstatus.style.display = '';
   }
  }
 }

 // Do whatever is needed when a new event category is selected.
 // For now this means changing the event title and duration.
 function set_category() {
  var f = document.forms[0];
  var s = f.form_category;
  if (s.selectedIndex >= 0) {
   var catid = s.options[s.selectedIndex].value;
   f.form_title.value = s.options[s.selectedIndex].text;
   f.form_duration.value = durations[catid];
   set_display();
  }
 }

 // Modify some visual attributes when the all-day or timed-event
 // radio buttons are clicked.
 function set_allday() {
  var f = document.forms[0];
  var color1 = '#777777';
  var color2 = '#777777';
  var disabled2 = true;
  if (document.getElementById('rballday1').checked) {
   color1 = '#000000';
  }
  if (document.getElementById('rballday2').checked) {
   color2 = '#000000';
   disabled2 = false;
  }
  document.getElementById('tdallday1').style.color = color1;
  document.getElementById('tdallday2').style.color = color2;
  document.getElementById('tdallday3').style.color = color2;
  document.getElementById('tdallday4').style.color = color2;
  document.getElementById('tdallday5').style.color = color2;
  f.form_hour.disabled     = disabled2;
  f.form_minute.disabled   = disabled2;
  f.form_ampm.disabled     = disabled2;
  f.form_duration.disabled = disabled2;
 }

 // Modify some visual attributes when the Repeat checkbox is clicked.
 function set_repeat() {
  var f = document.forms[0];
  var isdisabled = true;
  var mycolor = '#777777';
  var myvisibility = 'hidden';
  if (f.form_repeat.checked) {
   isdisabled = false;
   mycolor = '#000000';
   myvisibility = 'visible';
  }
  f.form_repeat_type.disabled = isdisabled;
  f.form_repeat_freq.disabled = isdisabled;
  f.form_enddate.disabled = isdisabled;
  document.getElementById('tdrepeat1').style.color = mycolor;
  document.getElementById('tdrepeat2').style.color = mycolor;
  document.getElementById('img_enddate').style.visibility = myvisibility;
 }

 // This is for callback by the find-available popup.
 function setappt(year,mon,mday,hours,minutes) {
  var f = document.forms[0];
  f.form_date.value = '' + year + '-' +
   ('' + (mon  + 100)).substring(1) + '-' +
   ('' + (mday + 100)).substring(1);
  f.form_ampm.selectedIndex = (hours >= 12) ? 1 : 0;
  f.form_hour.value = (hours > 12) ? hours - 12 : hours;
  f.form_minute.value = ('' + (minutes + 100)).substring(1);
 }

 // Invoke the find-available popup.
 function find_available() {
  var s = document.forms[0].form_provider;
  var c = document.forms[0].form_category;
  dlgopen('find_appt_popup.php?providerid=' + s.options[s.selectedIndex].value +
   '&catid=' + c.options[c.selectedIndex].value, '_blank', 500, 400);
 }

 // Check for errors when the form is submitted.
 function validate() {
  var f = document.forms[0];
  if (f.form_repeat.checked &&
      (! f.form_enddate.value || f.form_enddate.value < f.form_date.value)) {
   alert('An end date later than the start date is required for repeated events!');
   return false;
  }
  return true;
 }

</script>

</head>

<body <?echo $top_bg_line;?> onunload='imclosing()'>

<form method='post' name='theform' action='add_edit_event.php?eid=<? echo $eid ?>'
 onsubmit='return validate()'>
<center>

<table border='0' width='100%'>

 <tr>
  <td width='1%' nowrap>
   <b><? xl('Category','e'); ?>:</b>
  </td>
  <td nowrap>
   <select name='form_category' onchange='set_category()' style='width:100%'>
<? echo $catoptions ?>
   </select>
  </td>
  <td width='1%' nowrap>
   &nbsp;&nbsp;
   <input type='radio' name='form_allday' onclick='set_allday()' value='1' id='rballday1'
    <? if ($thisduration == 1440) echo "checked " ?>/>
  </td>
  <td colspan='2' nowrap id='tdallday1'>
   <? xl('All day event','e'); ?>
  </td>
 </tr>

 <tr>
  <td nowrap>
   <b><? xl('Date','e'); ?>:</b>
  </td>
  <td nowrap>
   <input type='text' size='10' name='form_date' id='form_date'
    value='<? echo $eid ? $row['pc_eventDate'] : $date ?>'
    title='<?php xl('yyyy-mm-dd event date or starting date','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
  </td>
  <td nowrap>
   &nbsp;&nbsp;
   <input type='radio' name='form_allday' onclick='set_allday()' value='0' id='rballday2'
    <? if ($thisduration != 1440) echo "checked " ?>/>
  </td>
  <td width='1%' nowrap id='tdallday2'>
   <? xl('Time','e'); ?>
  </td>
  <td width='1%' nowrap id='tdallday3'>
   <input type='text' size='2' name='form_hour'
    value='<? echo $starttimeh ?>'
    title='<?php xl('Event start time','e'); ?>' /> :
   <input type='text' size='2' name='form_minute'
    value='<? echo $starttimem ?>'
    title='<?php xl('Event start time','e'); ?>' />&nbsp;
   <select name='form_ampm' title='Note: 12:00 noon is PM, not AM'>
    <option value='1'><?php xl('AM','e'); ?></option>
    <option value='2'<? if ($startampm == '2') echo " selected" ?>><?php xl('PM','e'); ?></option>
   </select>
  </td>
 </tr>

 <tr>
  <td nowrap>
   <b><? xl('Title','e'); ?>:</b>
  </td>
  <td nowrap>
   <input type='text' size='10' name='form_title'
    value='<? echo addslashes($row['pc_title']) ?>'
    style='width:100%'
    title='<?php xl('Event title','e'); ?>' />
  </td>
  <td nowrap>
   &nbsp;
  </td>
  <td nowrap id='tdallday4'><? xl('duration','e'); ?>
  </td>
  <td nowrap id='tdallday5'>
   <input type='text' size='4' name='form_duration' value='<? echo $thisduration ?>'
    title='<?php xl('Event duration in minutes','e'); ?>' /> <? xl('minutes','e'); ?>
  </td>
 </tr>

 <tr>
  <td nowrap>
   <b><? xl('Patient','e'); ?>:</b>
  </td>
  <td nowrap>
   <input type='text' size='10' name='form_patient' style='width:100%'
    value='<? echo $patientname ?>' onclick='sel_patient()'
    title='<?php xl('Click to select patient','e'); ?>' readonly />
   <input type='hidden' name='form_pid' value='<? echo $patientid ?>' />
  </td>
  <td colspan='3' nowrap style='font-size:8pt'>
   &nbsp;<? echo $patienttitle ?>
  </td>
 </tr>

 <tr>
  <td nowrap>
   <b><? xl('Provider','e'); ?>:</b>
  </td>
  <td nowrap>

<?php

// =======================================
// multi providers case
// =======================================
if  ($GLOBALS['select_multi_providers']) {

//  there are two posible situations: edit and new record

// this is executed only on edit ($eid)
if ($eid) {
    // find all the providers around multiple key
    $qall = sqlStatement ("SELECT pc_aid AS providers FROM openemr_postcalendar_events WHERE pc_multiple = $multiple_value");

    while ($r = sqlFetchArray($qall)) {
        $providers_array[] = $r['providers'];
    }
}

// build the selection tool
echo "<select name='form_provider[]' style='width:100%' multiple='multiple' size='5'>";

while ($urow = sqlFetchArray($ures)) {
    echo "    <option value='" . $urow['id'] . "'";

    if ($userid) {
        if ( in_array($urow['id'], $providers_array) ) echo " selected";
    }

    echo ">" . $urow['lname'];
    if ($urow['fname']) echo ", " . $urow['fname'];
    echo "</option>\n";
 }

echo '</select>';

// =======================================
// EOS  multi providers case
// =======================================
} else {
?>
<select name='form_provider' style='width:100%'>
<?
 while ($urow = sqlFetchArray($ures)) {
  echo "    <option value='" . $urow['id'] . "'";
  if ($userid) {
   if ($urow['id'] == $userid) echo " selected";
  } else {
   if ($urow['id'] == $_SESSION['authUserID']) echo " selected";
  }
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
 }
?>
   </select>
<?php } ?>


  </td>
  <td nowrap>
   &nbsp;&nbsp;
   <input type='checkbox' name='form_repeat' onclick='set_repeat(this)'
    value='1'<? if ($repeats) echo " checked" ?>/>
  </td>
  <td nowrap id='tdrepeat1'><? xl('Repeats','e'); ?>
  </td>
  <td nowrap>

   <select name='form_repeat_freq' title='Every, every other, every 3rd, etc.'>
<?
 foreach (array(1 => 'every', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th', 6 => '6th')
  as $key => $value)
 {
  echo "    <option value='$key'";
  if ($key == $repeatfreq) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>

   <select name='form_repeat_type'>
<?
 // See common.api.php for these:
 foreach (array(0 => 'day' , 4 => 'workday', 1 => 'week', 2 => 'month', 3 => 'year')
  as $key => $value)
 {
  echo "    <option value='$key'";
  if ($key == $repeattype) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>

  </td>
 </tr>

 <tr>
  <td nowrap>
   <span id='title_apptstatus'><b><? xl('Status','e'); ?>:</b></span>
   <span id='title_prefcat' style='display:none'><b><? xl('Pref Cat','e'); ?>:</b></span>
  </td>
  <td nowrap>

   <select name='form_apptstatus' style='width:100%' title='<?php xl('Appointment status','e'); ?>'>
<?
 foreach ($statuses as $key => $value) {
  echo "    <option value='$key'";
  if ($key == $row['pc_apptstatus']) echo " selected";
  echo ">" . htmlspecialchars($value) . "</option>\n";
 }
?>
   </select>
   <!--
    The following list will be invisible unless this is an In Office
    event, in which case form_apptstatus (above) is to be invisible.
   -->
   <select name='form_prefcat' style='width:100%;display:none' title='<?php xl('Preferred Event Category','e');?>'>
<? echo $prefcat_options ?>
   </select>

  </td>
  <td nowrap>
   &nbsp;
  </td>
  <td nowrap id='tdrepeat2'><? xl('until','e'); ?>
  </td>
  <td nowrap>
   <input type='text' size='10' name='form_enddate' id='form_enddate'
    value='<? echo $row['pc_endDate'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php xl('yyyy-mm-dd last date of this event','e');?>' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_enddate' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e');?>'>
  </td>
 </tr>

 <tr>
  <td nowrap>
   <b><? xl('Comments','e'); ?>:</b>
  </td>
  <td colspan='4' nowrap>
   <input type='text' size='40' name='form_comments' style='width:100%'
    value='<? echo $hometext ?>'
    title='<?php xl('Optional information about this event','e');?>' />
  </td>
 </tr>

<?php
 // DOB is important for the clinic, so if it's missing give them a chance
 // to enter it right here.  We must display or hide this row dynamically
 // in case the patient-select popup is used.
 $patient_dob = trim($prow['DOB']);
 $dobstyle = ($prow && (!$patient_dob || substr($patient_dob, 5) == '00-00')) ?
  '' : 'none';
?>
 <tr id='dob_row' style='display:<?php echo $dobstyle ?>'>
  <td colspan='4' nowrap>
   <b><font color='red'><? xl('DOB is missing, please enter if possible','e'); ?>:</font></b>
  </td>
  <td nowrap>
   <input type='text' size='10' name='form_dob' id='form_dob'
    title='<?php xl('yyyy-mm-dd date of birth','e');?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_dob' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e');?>'>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e');?>' />
&nbsp;
<input type='button' value='<?php xl('Find Available','e');?>' onclick='find_available()' />
&nbsp;
<input type='submit' name='form_delete' value='<?php xl('Delete','e');?>'<? if (!$eid) echo " disabled" ?> />
&nbsp;
<input type='button' value='<?php xl('Cancel','e');?>' onclick='window.close()' />
</p>
</center>
</form>

<script language='JavaScript'>
<? if ($eid) { ?>
 set_display();
<? } else { ?>
 set_category();
<? } ?>
 set_allday();
 set_repeat();

 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});
 Calendar.setup({inputField:"form_enddate", ifFormat:"%Y-%m-%d", button:"img_enddate"});
 Calendar.setup({inputField:"form_dob", ifFormat:"%Y-%m-%d", button:"img_dob"});
</script>

</body>
</html>
