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
 // * interface/patient_file/summary/demographics_full.php: added support for
 //   setting a new pid, needed for going to demographics from billing.
 // * interface/patient_file/summary/demographics_save.php: redisplay
 //   demographics.php and not the frameset.
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
 // * interface/billing/billing_report.php: removed Back link; added logic
 //   to properly go to demographics or to an encounter when requested.
 // * interface/new/new.php: removed Back link and revised form target.
 // * interface/new/new_patient_save.php: modified to load the demographics
 //   page to the current frame instead of loading a new frameset.
 // * interface/patient_file/history/history.php: target change.
 // * interface/patient_file/history/history_full.php: target changes.
 // * interface/patient_file/history/history_save.php: target change.
 // * interface/patient_file/history/encounters.php: link/target changes.
 // * interface/patient_file/history/encounters_full.php: link/target changes.
 // * interface/patient_file/encounter/encounter_top.php: another new frameset
 //   cloned from patient_encounter.php.
 // * interface/patient_file/encounter/forms.php: link target removal.
 // * interface/patient_file/encounter/new_form.php: target change.
 // * interface/forms/newpatient/new.php, view.php, save.php: link/target
 //   changes.
 // * interface/patient_file/summary/immunizations.php: removed back link.
 // * interface/patient_file/summary/pnotes.php: changed link targets.
 // * interface/patient_file/summary/pnotes_full.php: changed back link and
 //   added set_pid logic.
 // * interface/patient_file/transaction/transactions.php: various changes.
 // * interface/patient_file/transaction/add_transaction.php: new return js.
 // * interface/patient_file/encounter/superbill_codes.php: target and link
 //   changes.
 // * interface/patient_file/encounter/superbill_custom_full.php: target and
 //   link changes.
 // * interface/patient_file/encounter/diagnosis.php: target changes.
 // * interface/patient_file/encounter/diagnosis_full.php: target and link
 //   changes.
 // * interface/main/authorizations/authorizations.php: link and target changes.
 // * library/api.inc: url change.
 // * interface/patient_file/summary/rx_frameset.php: new frameset.
 // * interface/patient_file/summary/rx_left.php: new for prescriptions.
 // * all encounter forms: remove all instances of "target=Main" and change
 //   all instances of "patient_encounter.php" to "encounter_top.php".

 // Our find_patient form, when submitted, invokes patient_select.php in the
 // upper frame. When the patient is selected, demographics.php is invoked
 // with the set_pid parameter, which establishes the new session pid and also
 // calls the setPatient() function (below).  In this case demographics.php
 // will also load the summary frameset into the bottom frame, invoking our
 // loadFrame() and setRadio() functions.
 //
 // Similarly, we have the concept of selecting an encounter from the
 // Encounters list, and then having that "stick" until some other encounter
 // or a new encounter is chosen.  We also have a navigation item for creating
 // a new encounter.  interface/patient_file/encounter/encounter_top.php
 // supports set_encounter to establish an encounter.
 //
 // TBD: Call the sanity check functions in various places.
 // TBD: Fixes to all encounter forms!

 include_once("../globals.php");
 include_once("../../library/acl.inc");

 // This array defines the list of primary documents that may be
 // chosen.  Each element value is an array of 3 values:
 //
 // * Name to appear in the navigation table
 // * Usage: 0 = global, 1 = patient-specific, 2 = encounter-specific
 // * The URL relative to the interface directory
 //
 $primary_docs = array(
  'ros' => array('Roster'    , 0, 'reports/players_report.php?embed=1'),
  'cal' => array('Calendar'  , 0, 'main/main_info.php'),
  'pwd' => array('Password'  , 0, 'usergroup/user_info.php'),
  'adm' => array('Admin'     , 0, 'usergroup/admin_frameset.php'),
  'rep' => array('Reports'   , 0, 'reports/index.php'),
  'ono' => array('Ofc Notes' , 0, 'main/onotes/office_comments.php'),
  'fax' => array('Fax/Scan'  , 0, 'fax/faxq.php'),
  'adb' => array('Addr Bk'   , 0, 'usergroup/addrbook_list.php'),
  'bil' => array('Billing'   , 0, 'billing/billing_report.php'),
  'sup' => array('Superbill' , 0, 'patient_file/encounter/superbill_custom_full.php'),
  'aun' => array('Auth/notes', 0, 'main/authorizations/authorizations.php'),
  'new' => array('New Pt'    , 0, 'new/new.php'),
  'dem' => array('Patient'   , 1, 'patient_file/summary/demographics.php'),
  'his' => array('History'   , 1, 'patient_file/history/history.php'),
  'ens' => array('Encounters', 1, 'patient_file/history/encounters.php'),
  'nen' => array('New Enctr' , 1, 'forms/newpatient/new.php?autoloaded=1&calenc='),
  'pre' => array('Rx'        , 1, 'patient_file/summary/rx_frameset.php'),
  'iss' => array('Issues'    , 1, 'patient_file/summary/stats_full.php?active=all'),
  'imm' => array('Immunize'  , 1, 'patient_file/summary/immunizations.php'),
  'doc' => array('Documents' , 1, '../controller.php?document&list&patient_id='),
  'prp' => array('Pt Report' , 1, 'patient_file/report/patient_report.php'),
  'pno' => array('Pt Notes'  , 1, 'patient_file/summary/pnotes.php'),
  'tra' => array('Transact'  , 1, 'patient_file/transaction/transactions.php'),
  'sum' => array('Summary'   , 1, 'patient_file/summary/summary_bottom.php'),
  'enc' => array('Encounter' , 2, 'patient_file/encounter/encounter_top.php'),
  'cod' => array('Charges'   , 2, 'patient_file/encounter/encounter_bottom.php'),
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
 // fnum indicates which checkbox was clicked (1=left, 2=right).
 function toggleFrame(fnum) {
  var f = document.forms[0];
  var fset = top.document.getElementById('fsright');
  if (!f.cb_top.checked && !f.cb_bot.checked) {
   if (fnum == 1) f.cb_bot.checked = true;
   else f.cb_top.checked = true;
  }
  var rows = f.cb_top.checked ? '*' :  '0';
  rows += f.cb_bot.checked ? ',*' : ',0';
  fset.rows = rows;
  fset.rows = rows;
 }

 // Load the specified url into the specified frame (RTop or RBot).
 // The URL provided must be relative to interface.
 function loadFrame(frame, url) {
  top.frames[frame].location = '<?php echo "$web_root/interface/" ?>' + url;
 }

 // Select a designated radio button. raname may be either the radio button
 // array name (rb_top or rb_bot), or the frame name (RTop or RBot).
 // You should call this if you directly load a document that does not
 // correspond to the current radio button setting.
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

 // Set disabled/enabled state of radio buttons and associated labels
 // depending on whether there is an active patient or encounter.
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
   // daemon_frame can also set special label colors, so don't mess with
   // them unless we have to.
   if (rb1.disabled != da) {
    rb1.disabled = da;
    rb2.disabled = da;
    document.getElementById('lbl_' + rbid).style.color = da ? '#888888' : '#000000';
   }
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

 // This is called automatically when a new patient is set, to make sure
 // there are no patient-specific documents showing stale data.  If a frame
 // was just loaded with data for the correct patient, its name is passed so
 // that it will not be zapped.  At this point the new server-side pid is not
 // assumed to be set, so this function will only load global data.
 function reloadPatient(fname) {
  var f = document.forms[0];
  for (var i = 0; i < f.rb_top.length; ++i) {
   if (f.rb_top[i].value.substring(3) > '0') {
    if (fname != 'RTop' && f.rb_top[i].checked) {
     loadFrame('RTop', '<?php echo $primary_docs['cal'][2]; ?>');
     setRadio('rb_top', 'cal');
    }
    if (fname != 'RBot' && f.rb_bot[i].checked) {
     loadFrame('RBot', '<?php echo $primary_docs['aun'][2]; ?>');
     setRadio('rb_bot', 'aun');
    }
   }
  }
 }

 // Reload encounter-specific frames, excluding a specified frame.  At this
 // point the new server-side encounter ID may not be set and loading the same
 // document for the new encounter will not work, so load patient info instead.
 function reloadEncounter(fname) {
  var f = document.forms[0];
  for (var i = 0; i < f.rb_top.length; ++i) {
   if (f.rb_top[i].value.substring(3) > '1') {
    if (fname != 'RTop' && f.rb_top[i].checked) {
     loadFrame('RTop', '<?php echo $primary_docs['dem'][2]; ?>');
     setRadio('rb_top', 'dem');
    }
    if (fname != 'RBot' && f.rb_bot[i].checked) {
     loadFrame('RBot', '<?php echo $primary_docs['ens'][2]; ?>');
     setRadio('rb_bot', 'ens');
    }
   }
  }
 }

 // Call this to announce that the patient has changed.  You must call this
 // if you change the session PID, so that the navigation frame will show the
 // correct patient and so that the other frame will be reloaded if it contains
 // patient-specific information from the previous patient.  fname is the name
 // of the frame that the call came from, so we know to only reload content
 // from the *other* frame if it is patient-specific.
 function setPatient(pname, pid, fname) {
  if (pid == active_pid) return;
  var str = '<b>' + pname + ' (' + pid + ')</b>';
  setDivContent('current_patient', str);
  setDivContent('current_encounter', '<b>None</b>');
  active_pid = pid;
  active_encounter = 0;
  if (fname) reloadPatient(fname);
  syncRadios();
 }

 // Call this to announce that the encounter has changed.  You must call this
 // if you change the session encounter, so that the navigation frame will
 // show the correct encounter and so that the other frame will be reloaded if
 // it contains encounter-specific information from the previous encounter.
 // fname is the name of the frame that the call came from, so we know to only
 // reload encounter-specific content from the *other* frame.
 function setEncounter(edate, eid, fname) {
  if (eid == active_encounter) return;
  if (!eid) edate = 'None';
  var str = '<b>' + edate + '</b>';
  setDivContent('current_encounter', str);
  active_encounter = eid;
  reloadEncounter(fname);
  syncRadios();
 }

 // You must call this if you delete the active patient (or if for any other
 // reason you "close" the active patient without opening a new one), so that
 // the appearance of the navigation frame will be correct and so that any
 // stale content will be reloaded.
 function clearPatient() {
  if (active_pid == 0) return;
  var f = document.forms[0];
  active_pid = 0;
  active_encounter = 0;
  setDivContent('current_encounter', '<b>None</b>');
  setDivContent('current_patient', '<b>None</b>');
  reloadPatient('');
  syncRadios();
 }

 // You must call this if you delete the active encounter (or if for any other
 // reason you "close" the active encounter without opening a new one), so that
 // the appearance of the navigation frame will be correct and so that any
 // stale content will be reloaded.
 function clearEncounter() {
  if (active_encounter == 0) return;
  setDivContent('current_encounter', '<b>None</b>');
  active_encounter = 0;
  reloadEncounter('');
  syncRadios();
 }

 // You can call this to make sure the session pid is what we expect.
 function pidSanityCheck(pid) {
  if (pid != active_pid) {
   alert('Session patient ID is ' + pid + ', expecting ' + active_pid +
    '. This session is unstable and should be abandoned. Do not use ' +
    'OpenEMR in multiple browser windows!');
   return false;
  }
  return true;
 }

 // You can call this to make sure the session encounter is what we expect.
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
 // are comprised of the 3-character document id and the 1-digit usage type,
 // so that JavaScript can easily access this information.
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

<table cellpadding='0' cellspacing='0' border='0'>
 <tr>
  <td class='smalltext'>Find:&nbsp;</td>
  <td class='smalltext' colspan='2'>
   <input type="entry" size="7" name="patient" class='inputtext' style='width:65px;' />
  </td>
 </tr>
 <tr>
  <td class='smalltext'>by:</td>
  <td class='smalltext'>
   <a href="javascript:findPatient('Last');" class="navitem">Name</a>
  </td>
  <td class='smalltext' align='right'>
   <a href="javascript:findPatient('ID');"   class="navitem">ID</a>
  </td>
 </tr>
 <tr>
  <td class='smalltext'>&nbsp;</td>
  <td class='smalltext'>
   <a href="javascript:findPatient('SSN');"  class="navitem">SSN</a>
  </td>
  <td class='smalltext' align='right'>
   <a href="javascript:findPatient('DOB');"  class="navitem">DOB</a>
  </td>
 </tr>
</table>

<hr />
<a href="../logout.php?auth=logout" target="_top" class="navitem" id="logout_link">
<? xl('Logout','e'); ?></a>

<input type='hidden' name='findBy' value='Name' />

</form>

<script language='JavaScript'>
syncRadios();
</script>

</body>
</html>
