<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This provides the left navigation frame when $GLOBALS['concurrent_layout']
 // is true.  Following are notes as to what else was changed for this feature:
 //
 // * interface/main/main_screen.php: the top-level frameset.
 // * interface/main/finder/patient_select.php: loads stuff when a new patient
 //   is selected.
 // * interface/patient_file/summary/demographics.php: this is the first frame
 //   loaded when a new patient is chosen, and in turn sets the current pid and
 //   then loads the initial bottom frame.
 // * interface/patient_file/summary/summary_bottom.php: new frameset for the
 //   summary, prescriptions and notes for a selected patient, cloned from
 //   patient_summary.php.
 // * interface/patient_file/encounter/encounter_bottom.php: new frameset for
 //   the selected encounter, mosting coding/billing stuff, cloned from
 //   patient_encounter.php.  This will also self-load the superbill pages
 //   as requested.
 // * interface/usergroup/user_info.php: removed Back link.
 // * interface/usergroup/admin_frameset.php: new frameset for Admin pages,
 //   cloned from usergroup.php.
 // * interface/main/onotes/office_comments.php: removed Back link target.
 // * interface/main/onotes/office_comments_full.php: changed Back link.
 // * interface/billing/billing_report.php: removed Back link.
 // * interface/new/new.php: removed Back link and revised form target.
 // * interface/new/new_patient_save.php: modified to load the demographics
 //   page to the current frame instead of loading a new frameset.
 // * interface/patient_file/history/history.php: target change.
 // * interface/patient_file/history/history_full.php: target changes.
 // * interface/patient_file/history/history_save.php: target change.
 // * interface/patient_file/history/encounters.php: various link/target changes.
 // * interface/patient_file/encounter/encounter_top.php: another new frameset
 //   cloned from patient_encounter.php.
 // * interface/patient_file/encounter/forms.php: link target removal.
 // * interface/patient_file/encounter/new_form.php: target change.
 // * interface/forms/newpatient/new.php, view.php, save.php: link/target
 //   changes.
 // * interface/patient_file/summary/immunizations.php: removed back link.
 // * interface/patient_file/summary/pnotes.php: changed link targets.
 // * interface/patient_file/summary/pnotes_full.php: changed back link.
 // * interface/patient_file/transaction/transactions.php: various changes.
 // * interface/patient_file/transaction/add_transaction.php: new return js.
 // * interface/patient_file/encounter/superbill_codes.php: target and link
 //   changes.
 // * interface/patient_file/encounter/superbill_custom_full.php: target and
 //   link changes.
 // * interface/patient_file/encounter/diagnosis.php: target changes.
 // * interface/patient_file/encounter/diagnosis_full.php: target and link
 //   changes.

 // Our find_patient form, when submitted, invokes patient_select.php in the
 // upper frame. When the patient is selected, demographics.php is invoked
 // with the set_pid parameter, which establishes the new session pid and also
 // calls the above setPatient() function.  In this case demographics.php
 // will also load the summary frameset into the bottom frame, invoking the
 // above loadFrame() and setRadio() functions.
 //
 // TBD: We'll want to make sure all this also happens if a new patient or
 // encounter is selected by some other means, such as from a patient note
 // or from the calendar.
 //
 // Similarly, we have the concept of selecting an encounter from the
 // Encounters list, and then having that "stick" until some other encounter
 // or a new encounter is chosen.  We also have a navigation item for creating
 // a new encounter.  interface/patient_file/encounter/encounter_top.php
 // supports set_encounter to establish an encounter.
 //
 // TBD: Finish logic to clear encounters and patients; make sure the right
 // things are done when encounters are selected/cleared by other means; call
 // the sanity check functions (above) in various places.

 include_once("../globals.php");
 include_once("../../library/acl.inc");

 // This array defines the list of primary documents that may be
 // chosen.  Each element value is an array of 3 values:
 //
 // * Name to appear in the navigation table
 // * Usage: 0 = global, 1 = patient-specific, 2 = encounter-specific
 // * The relative URL
 //
 $primary_docs = array(
  'ros' => array('Roster'    , 0, '../reports/players_report.php?embed=1'),
  'cal' => array('Calendar'  , 0, 'main_info.php'),
  'pwd' => array('Password'  , 0, '../usergroup/user_info.php'),
  'adm' => array('Admin'     , 0, '../usergroup/admin_frameset.php'),
  'rep' => array('Reports'   , 0, '../reports/index.php'),
  'ono' => array('Ofc Notes' , 0, 'onotes/office_comments.php'),
  'fax' => array('Fax/Scan'  , 0, '../fax/faxq.php'),
  'adb' => array('Addr Bk'   , 0, '../usergroup/addrbook_list.php'),
  'bil' => array('Billing'   , 0, '../billing/billing_report.php'),
  'sup' => array('Superbill' , 0, '../patient_file/encounter/superbill_custom_full.php'),
  'aun' => array('Auth/notes', 0, 'authorizations/authorizations.php'),
  'new' => array('New Pt'    , 0, '../new/new.php'),
  'dem' => array('Patient'   , 1, '../patient_file/summary/demographics.php'),
  'his' => array('History'   , 1, '../patient_file/history/history.php'),
  'ens' => array('Encounters', 1, '../patient_file/history/encounters.php'),
  'iss' => array('Issues'    , 1, '../patient_file/summary/stats_full.php?active=all'),
  'imm' => array('Immunize'  , 1, '../patient_file/summary/immunizations.php'),
  'doc' => array('Documents' , 1, '../../controller.php?document&list&patient_id='),
  'prp' => array('Pt Report' , 1, '../patient_file/report/patient_report.php'),
  'pno' => array('Pt Notes'  , 1, '../patient_file/summary/pnotes.php'),
  'tra' => array('Transact'  , 1, '../patient_file/transaction/transactions.php'),
  'sum' => array('Summary'   , 1, '../patient_file/summary/summary_bottom.php'),
  'nen' => array('New Enctr' , 1, '../forms/newpatient/new.php?autoloaded=1&calenc='),
  'enc' => array('Encounter' , 2, '../patient_file/encounter/encounter_top.php'),
  'cod' => array('Coding'    , 2, '../patient_file/encounter/encounter_bottom.php'),
 );

 $admin_allowed = acl_check('admin', 'calendar') ||
  acl_check('admin', 'database') || acl_check('admin', 'forms') ||
  acl_check('admin', 'practice') || acl_check('admin', 'users');

 $billing_allowed = acl_check('acct', 'rep') || acl_check('acct', 'eob') ||
  acl_check('acct', 'bill');
?>
<html>
<head>
<title>Navigation</title>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<style type="text/css">
 body {
  font-family:sans-serif;
  font-size:8pt;
  font-weight:normal;
  padding: 5px 3px 5px 3px;
 }
 .smalltext {
  font-family:sans-serif;
  font-size:8pt;
  font-weight:normal;
 }
 a.navitem, a.navitem:visited {
  color:#0000ff;
  font-family:sans-serif;
  font-size:8pt;
  font-weight:bold;
 }
.inputtext {
 font-size:9pt;
 font-weight:normal;
 border-style:solid;
 border-width:1px;
 padding-left:2px;
 padding-right:2px;
 border-color: #000000;
 background-color:transparent;
}
</style>
<script language='JavaScript'>

 // Master values for current pid and encounter.
 var active_pid = 0;
 var active_encounter = 0;

 // Expand and/or collapse frames in response to checkbox clicks.
 function toggleFrame(fnum) {
  var f = document.forms[0];
  var fset = top.document.getElementById('fsright');
  var rows = '';
  if (!f.cb_top.checked && !f.cb_bot.checked) {
   if (fnum == 1) f.cb_bot.checked = true;
   else f.cb_top.checked = true;
  }
  rows += f.cb_top.checked ?  '*' :  '0';
  rows += f.cb_bot.checked ? ',*' : ',0';
  fset.rows = rows;
  fset.rows = rows;
 }

 // Load the specified url into the specified frame (RTop or RBot).
 function loadFrame(frame, url) {
  top.frames[frame].location = url;
 }

 // Select a designated radio button. raname may be either the radio button
 // array name (rb_top or rb_bot), or the frame name (RTop or RBot).
 function setRadio(raname, rbid) {
  var f = document.forms[0];
  if (raname == 'RTop') raname = 'rb_top';
  if (raname == 'RBot') raname = 'rb_bot';
  for (var i = 0; i < f[raname].length; ++i) {
   if (f[raname][i].value.substring(0,3) == rbid) {
    f[raname][i].checked = true;
    return true;
   }
  }
  return false;
 }

 // Set disabled/enabled state of radio buttons and associated labels.
 function syncRadios() {
  var f = document.forms[0];
  for (var i = 0; i < f.rb_top.length; ++i) {
   var da = false;
   var rb1 = f.rb_top[i];
   var rb2 = f.rb_bot[i];
   var rbid = rb1.value.substring(0,3);
   var usage = rb1.value.substring(3);
   if (active_pid == 0 && usage > '0') da = true;
   if (active_encounter == 0 && usage > '1') da = true;
   rb1.disabled = da;
   rb2.disabled = da;
   document.getElementById('lbl_' + rbid).style.color = da ? '#888888' : '#000000';
  }
 }

 // Process the click to find a patient by name, id, ssn or dob.
 function findPatient(findby) {
  var f = document.forms[0];
  if (! f.cb_top.checked) {
   f.cb_top.checked = true;
   toggleFrame(1);
  }
  f.findBy.value = findby;
  setRadio('rb_top', 'dem');
  document.find_patient.submit();
 }

 // Helper function to set the contents of a div.
 function setDivContent(id, content) {
  if (document.getElementById) {
   var x = document.getElementById(id);
   x.innerHTML = '';
   x.innerHTML = content;
  }
  else if (document.all) {
   var x = document.all[id];
   x.innerHTML = content;
  }
 }

 // Call this to tell us that the patient has changed.
 function setPatient(pname, pid) {
  var str = '<b>' + pname + ' (' + pid + ')</b>';
  setDivContent('current_patient', str);
  active_pid = pid;
  syncRadios();

  // TBD: reload any patient-specific frames

 }

 // Call this to tell us that the encounter has changed.
 function setEncounter(edate, eid) {
  // var str = '<b>' + edate + ' (' + eid + ')</b>';
  if (!eid) edate = 'None';
  var str = '<b>' + edate + '</b>';
  setDivContent('current_encounter', str);
  active_encounter = eid;
  syncRadios();

  // TBD: reload any encounter-specific frames

 }

 function clearPatient() {

  // TBD: do whatever is needed when (for example) the current patient is deleted.

  active_pid = 0;
  active_encounter = 0;
 }

 // Call this to make sure the session pid is what we expect.
 function pidSanityCheck(pid) {
  if (pid != active_pid) {
   alert('Session patient ID is ' + pid + ', expecting ' + active_pid +
    '. This session is unstable and should be abandoned. Do not use ' +
    'OpenEMR in multiple browser windows!');
   return false;
  }
  return true;
 }

 // Call this to make sure the session encounter is what we expect.
 function encounterSanityCheck(eid) {
  if (eid != active_encounter) {
   alert('Session encounter ID is ' + eid + ', expecting ' + active_encounter +
    '. This session is unstable and should be abandoned. Do not use ' +
    'OpenEMR in multiple browser windows!');
   return false;
  }
  return true;
 }

</script>
</head>

<body <?echo $nav_bg_line;?> topmargin='0' rightmargin='4' leftmargin='2'
 bottommargin='0' marginheight='0'>

<form method='post' name='find_patient' target='RTop'
 action='<?php echo $rootdir ?>/main/finder/patient_select.php'>

<table cellpadding='0' cellspacing='0' border='0'>
 <tr>
  <td colspan='3'>
   <table cellpadding='0' cellspacing='0' border='0' width='100%'>
    <tr>
     <td class='smalltext' nowrap>
      <input type='checkbox' name='cb_top' onclick='toggleFrame(1)' checked /><b>Top</b>
     </td>
     <td class='smalltext' align='right' nowrap>
      <b>Bot</b><input type='checkbox' name='cb_bot' onclick='toggleFrame(2)' checked />
     </td>
    </tr>
   </table>
  </td>
 </tr>
<?php
 // Builds the table of radio buttons and their labels.  Radio button values
 // are constructed of the 3-character document id and the 1-digit usage type.
 foreach ($primary_docs as $key => $varr) {
  if ($key == 'ros' && !$GLOBALS['athletic_team']) continue;
  if ($key == 'adm' && !$admin_allowed           ) continue;
  if ($key == 'bil' && !$billing_allowed         ) continue;
  $label = $varr[0];
  $usage = $varr[1];
  $url   = $varr[2];
  echo " <tr>\n";
  echo "  <td class='smalltext'><input type='radio' name='rb_top' value='$key$usage' " .
       "onclick=\"loadFrame('RTop','$url')\"";
  if ($key == 'cal') echo " checked";
  echo " /></td>\n";
  echo "  <td class='smalltext' id='lbl_$key'>$label</td>\n";
  echo "  <td class='smalltext'><input type='radio' name='rb_bot' value='$key$usage' " .
       "onclick=\"loadFrame('RBot','$url')\"";
  if ($key == 'aun') echo " checked";
  echo " /></td>\n";
  echo " </tr>\n";
 }
?>
</table>

<br /><hr />

Active Patient:<br />
<div id='current_patient'>
<b>None</b>
</div>

Active Encounter:<br />
<div id='current_encounter'>
<b>None</b>
</div>

<hr />

Find:
<input type="entry" size="7" name="patient" class='inputtext' style='width:65px;' />
<table cellpadding='0' cellspacing='0' border='0'>
 <tr>
  <td class='smalltext'>by:&nbsp;</td>
  <td><a href="javascript:findPatient('Last');" class="navitem">Name</a></td>
  <td align='right'><a href="javascript:findPatient('ID');"   class="navitem">ID</a></td>
 </tr>
 <tr>
  <td class='smalltext'>&nbsp;</td>
  <td><a href="javascript:findPatient('SSN');"  class="navitem">SSN</a></td>
  <td align='right'><a href="javascript:findPatient('DOB');"  class="navitem">DOB</a></td>
 </tr>
</table>

<hr />
<a href="../logout.php?auth=logout" target="_top" class="navitem"><? xl('Logout','e'); ?></a>

<input type='hidden' name='findBy' value='Name' />

</form>

<script language='JavaScript'>
syncRadios();
</script>

</body>
</html>
