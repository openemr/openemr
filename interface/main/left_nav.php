<?php
 // Copyright (C) 2006, 2008 Rod Roark <rod@sunsetsystems.com>
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
 // TBD: Include active_pid and/or active_encounter in relevant submitted
 // form data, and add logic to the save routines to make sure they match
 // the corresponding session values.

 include_once("../globals.php");
 include_once("../../library/acl.inc");
 include_once("../../custom/code_types.inc.php");

 // This array defines the list of primary documents that may be
 // chosen.  Each element value is an array of 3 values:
 //
 // * Name to appear in the navigation table
 // * Usage: 0 = global, 1 = patient-specific, 2 = encounter-specific
 // * The URL relative to the interface directory
 //

 // larry :: dbc insert 
 $demovarbase = ( $GLOBALS['dutchpc'] ) ? 'demographics_dutch.php' : 'demographics.php';
 // larry :: end of dbc insert

 $primary_docs = array(
  'ros' => array('Roster'    , 0, 'reports/players_report.php?embed=1'),
  'cal' => array('Calendar'  , 0, 'main/main_info.php'),
  'pwd' => array('Password'  , 0, 'usergroup/user_info.php'),
  'adm' => array('Admin'     , 0, 'usergroup/admin_frameset.php'),
  'rep' => array('Reports'   , 0, 'reports/index.php'),
  'ono' => array('Ofc Notes' , 0, 'main/onotes/office_comments.php'),
  'fax' => array('Fax/Scan'  , 0, 'fax/faxq.php'),
  'adb' => array('Addr Bk'   , 0, 'usergroup/addrbook_list.php'),
  'imp' => array('Import'    , 0, '../custom/import.php'),
  'bil' => array('Billing'   , 0, 'billing/billing_report.php'),
  'sup' => array('Superbill' , 0, 'patient_file/encounter/superbill_custom_full.php'),
  'aun' => array('Auth/notes', 0, 'main/authorizations/authorizations.php'),
  'new' => array('New Pt'    , 0, 'new/new.php'),
  'dem' => array('Patient'   , 1,  "patient_file/summary/$demovarbase"),
  'his' => array('History'   , 1, 'patient_file/history/history.php'),
  'ens' => array('Encounters', 1, 'patient_file/history/encounters.php'),
  'nen' => array('New Enctr' , 1, 'forms/newpatient/new.php?autoloaded=1&calenc='),
  'pre' => array('Rx'        , 1, 'patient_file/summary/rx_frameset.php'),
  'iss' => array('Issues'    , 1, 'patient_file/summary/stats_full.php?active=all'),
  'imm' => array('Immunize'  , 1, 'patient_file/summary/immunizations.php'),
  'doc' => array('Documents' , 1, '../controller.php?document&list&patient_id={PID}'),
  'prp' => array('Pt Report' , 1, 'patient_file/report/patient_report.php'),
  'pno' => array('Pt Notes'  , 1, 'patient_file/summary/pnotes.php'),
  'tra' => array('Transact'  , 1, 'patient_file/transaction/transactions.php'),
  'sum' => array('Summary'   , 1, 'patient_file/summary/summary_bottom.php'),
  'enc' => array('Encounter' , 2, 'patient_file/encounter/encounter_top.php'),
  'cod' => array('Charges'   , 2, 'patient_file/encounter/encounter_bottom.php'),
 );

 // This section decides which navigation items will not appear.

 $disallowed = array();

 $disallowed['adm'] = !(acl_check('admin', 'calendar') ||
  acl_check('admin', 'database') || acl_check('admin', 'forms') ||
  acl_check('admin', 'practice') || acl_check('admin', 'users'));

 $disallowed['bil'] = !(acl_check('acct', 'rep') || acl_check('acct', 'eob') ||
  acl_check('acct', 'bill'));

 $tmp = acl_check('patients', 'demo');
 $disallowed['new'] = !($tmp == 'write' || $tmp == 'addonly');

if ( isset ($GLOBALS['hylafax_server']) && isset ($GLOBALS['scanner_output_directory']) ) {
    $disallowed['fax'] = !($GLOBALS['hylafax_server'] || $GLOBALS['scanner_output_directory']);
}

 $disallowed['ros'] = !$GLOBALS['athletic_team'];

 $disallowed['iss'] = !((acl_check('encounters', 'notes') == 'write' ||
  acl_check('encounters', 'notes_a') == 'write') &&
  acl_check('patients', 'med') == 'write');

 $disallowed['imp'] = $disallowed['new'] ||
  !is_readable("$webserver_root/custom/import.php");

 // Helper functions for treeview generation.
 function genTreeLink($frame, $name, $title) {
  global $primary_docs, $disallowed;
  if (empty($disallowed[$name])) {
   $id = $name . $primary_docs[$name][1];
   echo "<li><a href='' id='$id' " .
        "onclick=\"return loadFrame2('$id','$frame','" .
        $primary_docs[$name][2] . "')\">" . xl($title) . "</a></li>";
  }
 }
 function genMiscLink($frame, $name, $level, $title, $url) {
  global $primary_docs, $disallowed;
  if (empty($disallowed[$name])) {
   $id = $name . $level;
   echo "<li><a href='' id='$id' " .
        "onclick=\"return loadFrame2('$id','$frame','" .
        $url . "')\">" . xl($title) . "</a></li>";
  }
 }
 function genPopLink($title, $url) {
  echo "<li><a href='' " .
       "onclick=\"return repPopup('$url')\"" .
       ">" . xl($title) . "</a></li>";
 }
?>
<html>
<head>
<title>Navigation</title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<style type="text/css">
 body {
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

#navigation ul {
 background-color:transparent;
}
</style>

<link rel="stylesheet" href="../../library/js/jquery.treeview-1.3/jquery.treeview.css" />
<script src="../../library/js/jquery-1.2.2.min.js" type="text/javascript"></script>
<script src="../../library/js/jquery.treeview-1.3/jquery.treeview.min.js" type="text/javascript"></script>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language='JavaScript'>

 // Master values for current pid and encounter.
 var active_pid = 0;
 var active_encounter = 0;

 // Current selections in the top and bottom frames.
 var topName = '';
 var botName = '';

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
 function loadFrame(fname, frame, url) {
  top.restoreSession();
  var i = url.indexOf('{PID}');
  if (i >= 0) url = url.substring(0,i) + active_pid + url.substring(i+5);
  top.frames[frame].location = '<?php echo "$web_root/interface/" ?>' + url;
  if (frame == 'RTop') topName = fname; else botName = fname;
 }

 // Load the specified url into a frame to be determined, with the specified
 // frame as the default; the url must be relative to interface.
 function loadFrame2(fname, frame, url) {
  var usage = fname.substring(3);
  if (active_pid == 0 && usage > '0') {
   alert('<?php xl('You must first select or add a patient.','e') ?>');
   return false;
  }
  if (active_encounter == 0 && usage > '1') {
   alert('<?php xl('You must first select or create an encounter.','e') ?>');
   return false;
  }
  var f = document.forms[0];
  top.restoreSession();
  var i = url.indexOf('{PID}');
  if (i >= 0) url = url.substring(0,i) + active_pid + url.substring(i+5);
  var fi = f.sel_frame.selectedIndex;
  if (fi == 1) frame = 'RTop'; else if (fi == 2) frame = 'RBot';
  if (!f.cb_bot.checked) frame = 'RTop'; else if (!f.cb_top.checked) frame = 'RBot';
  top.frames[frame].location = '<?php echo "$web_root/interface/" ?>' + url;
  if (frame == 'RTop') topName = fname; else botName = fname;
  return false;
 }

 // Select a designated radio button. raname may be either the radio button
 // array name (rb_top or rb_bot), or the frame name (RTop or RBot).
 // You should call this if you directly load a document that does not
 // correspond to the current radio button setting.
 function setRadio(raname, rbid) {
<?php if ($GLOBALS['concurrent_layout'] != 2) { ?>
  var f = document.forms[0];
  if (raname == 'RTop') raname = 'rb_top';
  if (raname == 'RBot') raname = 'rb_bot';
  for (var i = 0; i < f[raname].length; ++i) {
   if (f[raname][i].value.substring(0,3) == rbid) {
    f[raname][i].checked = true;
    return true;
   }
  }
<?php } ?>
  return false;
 }

 // Set disabled/enabled state of radio buttons and associated labels
 // depending on whether there is an active patient or encounter.
 function syncRadios() {
  var f = document.forms[0];
<?php if ($GLOBALS['concurrent_layout'] == 2) { ?>
  var nlinks = document.links.length;
  for (var i = 0; i < nlinks; ++i) {
   var lnk = document.links[i];
   if (lnk.id.length != 4) continue;
   var usage = lnk.id.substring(3);
   if (usage == '1' || usage == '2') {
    var da = false;
    if (active_pid == 0) da = true;
    if (active_encounter == 0 && usage > '1') da = true;
    lnk.style.color = da ? '#888888' : '#0000ff';
   }
  }
<?php } else { ?>
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
<?php } ?>
  f.popups.disabled = (active_pid == 0);
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
  top.restoreSession();
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
 function reloadPatient(frname) {
  var f = document.forms[0];
  if (topName.length > 3 && topName.substring(3) > '0' && frname != 'RTop') {
   loadFrame('cal0','RTop', '<?php echo $primary_docs['cal'][2]; ?>');
   setRadio('rb_top', 'cal');
  }
  if (botName.length > 3 && botName.substring(3) > '0' && frname != 'RBot') {
   loadFrame('aun0','RBot', '<?php echo $primary_docs['aun'][2]; ?>');
   setRadio('rb_bot', 'aun');
  }
 }

 // Reload encounter-specific frames, excluding a specified frame.  At this
 // point the new server-side encounter ID may not be set and loading the same
 // document for the new encounter will not work, so load patient info instead.
 function reloadEncounter(frname) {
  var f = document.forms[0];
  if (topName.length > 3 && topName.substring(3) > '1' && frname != 'RTop') {
   loadFrame('dem1','RTop', '<?php echo $primary_docs['dem'][2]; ?>');
   setRadio('rb_top', 'dem');
  }
  if (botName.length > 3 && botName.substring(3) > '1' && frname != 'RBot') {
   loadFrame('ens1','RBot', '<?php echo $primary_docs['ens'][2]; ?>');
   setRadio('rb_bot', 'ens');
  }
 }

 // Call this to announce that the patient has changed.  You must call this
 // if you change the session PID, so that the navigation frame will show the
 // correct patient and so that the other frame will be reloaded if it contains
 // patient-specific information from the previous patient.  frname is the name
 // of the frame that the call came from, so we know to only reload content
 // from the *other* frame if it is patient-specific.
 function setPatient(pname, pid, frname) {
  if (pid == active_pid) return;
  var str = '<b>' + pname + ' (' + pid + ')</b>';
  setDivContent('current_patient', str);
  setDivContent('current_encounter', '<b>None</b>');
  active_pid = pid;
  active_encounter = 0;
  if (frname) reloadPatient(frname);
  syncRadios();
 }

 // Call this to announce that the encounter has changed.  You must call this
 // if you change the session encounter, so that the navigation frame will
 // show the correct encounter and so that the other frame will be reloaded if
 // it contains encounter-specific information from the previous encounter.
 // frname is the name of the frame that the call came from, so we know to only
 // reload encounter-specific content from the *other* frame.
 function setEncounter(edate, eid, frname) {
  if (eid == active_encounter) return;
  if (!eid) edate = 'None';
  var str = '<b>' + edate + '</b>';
  setDivContent('current_encounter', str);
  active_encounter = eid;
  reloadEncounter(frname);
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

 // Pop up a report.
 function repPopup(aurl) {
  top.restoreSession();
  window.open('<?php echo "$web_root/interface/reports/" ?>' + aurl, '_blank', 'width=750,height=550,resizable=1,scrollbars=1');
  return false;
 }

 // This is invoked to pop up some window when a popup item is selected.
 function selpopup(selobj) {
  var i = selobj.selectedIndex;
  var opt = selobj.options[i];
  if (i > 0) {
   var width  = 750;
   var height = 550;
   if (opt.text == 'Export' || opt.text == 'Import') {
    width  = 500;
    height = 400;
   }
   else if (opt.text == 'Refer') {
    width  = 700;
    height = 500;
   }
   dlgopen(opt.value, '_blank', width, height);
  }
  selobj.selectedIndex = 0;
 }

 // Treeview activation stuff:
 $(document).ready(function(){
  $("#navigation").treeview({
   animated: "fast",
   collapsed: true,
   unique: true,
   toggle: function() {
    window.console && console.log("%o was toggled", this);
   }
  });
 });

</script>

</head>

<body class="body_nav">

<form method='post' name='find_patient' target='RTop'
 action='<?php echo $rootdir ?>/main/finder/patient_select.php'>

<?php if ($GLOBALS['concurrent_layout'] == 2) { ?>

<select name='sel_frame' style='background-color:transparent;font-size:9pt;width:100%;'>
 <option value='0'><?php xl('Default','e'); ?></option>
 <option value='1'><?php xl('Top','e'); ?></option>
 <option value='2'><?php xl('Bottom','e'); ?></option>
</select>

<table cellpadding='0' cellspacing='0' border='0' width='100%'>
 <tr>
  <td class='smalltext' nowrap>
   <input type='checkbox' name='cb_top' onclick='toggleFrame(1)' checked /><b><?php xl('Top','e') ?></b>
  </td>
  <td class='smalltext' align='right' nowrap>
   <b><?php xl('Bot','e') ?></b><input type='checkbox' name='cb_bot' onclick='toggleFrame(2)' checked />
  </td>
 </tr>
</table>

<ul id="navigation">
  <li class="open"><span><?php xl('Patient/Client','e') ?></span>
    <ul>
      <li><span><?php xl('Management','e') ?></span>
        <ul>
          <?php genTreeLink('RTop','new','New'); ?>
          <?php genTreeLink('RTop','dem','Current'); ?>
          <?php genTreeLink('RBot','sum','Summary'); ?>
        </ul>
      </li>
      <li class="open"><span><?php xl('Visits','e') ?></span>
        <ul>
          <?php genTreeLink('RTop','cal','Calendar'); ?>
          <?php if ($GLOBALS['athletic_team']) genTreeLink('RTop','ros','Roster'); ?>
          <?php genTreeLink('RBot','nen','New Visit'); ?>
          <?php genTreeLink('RBot','enc','Current'); ?>
          <?php genTreeLink('RBot','ens','List'); ?>
          <?php genTreeLink('RBot','tra','Transact'); ?>
        </ul>
      </li>
      <li><span><?php xl('Medical Record','e') ?></span>
        <ul>
          <?php genTreeLink('RBot','pre','Rx'); ?>
          <?php genTreeLink('RTop','his','History'); ?>
          <?php genTreeLink('RTop','iss','Issues'); ?>
          <?php genTreeLink('RBot','imm','Immunize'); ?>
          <?php genTreeLink('RTop','doc','Documents'); ?>
          <?php genTreeLink('RBot','pno','Notes'); ?>
          <?php genTreeLink('RTop','prp','Report'); ?>
        </ul>
      </li>
    </ul>
  </li>
  <li><span><?php xl('Fees','e') ?></span>
    <ul>
      <?php genMiscLink('RBot','cod','2','Fee Sheet','patient_file/encounter/load_form.php?formname=fee_sheet'); ?>
      <?php if (false) genTreeLink('RBot','cod','Charges'); ?>
      <?php genMiscLink('RBot','bil','1','Checkout','patient_file/pos_checkout.php?framed=1'); ?>
      <?php if (! $GLOBALS['simplified_demographics']) genTreeLink('RTop','bil','Billing'); ?>
    </ul>
  </li>
  <?php if ($GLOBALS['inhouse_pharmacy'] && acl_check('admin', 'drugs')) genMiscLink('RTop','adm','0','Inventory','drugs/drug_inventory.php'); ?>
  <li><span><?php xl('Administration','e') ?></span>
    <ul>
      <?php if (acl_check('admin', 'users'    )) genMiscLink('RTop','adm','0','Users','usergroup/usergroup_admin.php'); ?>
      <?php if (acl_check('admin', 'practice' )) genMiscLink('RTop','adm','0','Practice','../controller.php?practice_settings'); ?>
      <?php if (acl_check('admin', 'superbill')) genTreeLink('RTop','sup','Services'); ?>
      <?php if (acl_check('admin', 'super'    )) genMiscLink('RTop','adm','0','Layouts','super/edit_layout.php'); ?>
      <?php if (acl_check('admin', 'super'    )) genMiscLink('RTop','adm','0','Lists','super/edit_list.php'); ?>
      <?php if (acl_check('admin', 'acl'      )) genMiscLink('RTop','adm','0','ACL','usergroup/adminacl.php'); ?>
      <li><span><?php xl('Other','e') ?></span>
        <ul>
          <?php if (acl_check('admin', 'language')) genMiscLink('RTop','adm','0','Language','language/language.php'); ?>
          <?php if (acl_check('admin', 'forms'   )) genMiscLink('RTop','adm','0','Forms','forms_admin/forms_admin.php'); ?>
          <?php if (acl_check('admin', 'calendar')) genMiscLink('RTop','adm','0','Calendar','main/calendar/index.php?module=PostCalendar&type=admin&func=modifyconfig'); ?>
          <?php if (acl_check('admin', 'users'   )) genMiscLink('RTop','adm','0','Logs','logview/logview.php'); ?>
          <?php if (acl_check('admin', 'database')) genMiscLink('RTop','adm','0','Database','main/myadmin/index.php'); ?>
        </ul>
      </li>
    </ul>
  </li>
  <li><span><?php xl('Reports','e') ?></span>
    <ul>
      <li><span><?php xl('Clients','e') ?></span>
        <ul>
          <?php genPopLink('List','patient_list.php'); ?>
          <?php genPopLink('Rx','prescriptions_report.php'); ?>
          <?php genPopLink('Referrals','referrals_report.php'); ?>
          <?php if (!empty($GLOBALS['code_types']['IPPF'])) genPopLink('IPPF Stats','ippf_statistics.php?t=i'); ?>
          <?php if (!empty($GLOBALS['code_types']['IPPF'])) genPopLink('MA Stats','ippf_statistics.php?t=m'); ?>
        </ul>
      </li>
      <li class="open"><span><?php xl('Visits','e') ?></span>
        <ul>
          <?php genPopLink('Appointments','appointments_report.php'); ?>
          <?php genPopLink('Encounters','encounters_report.php'); ?>
          <?php genPopLink('Appt-Enc','appt_encounter_report.php'); ?>
        </ul>
      </li>
<?php if (acl_check('acct', 'rep_a')) { ?>
      <li><span><?php xl('Financial','e') ?></span>
        <ul>
          <?php genPopLink('Sales','sales_by_item.php'); ?>
          <?php genPopLink('Cash Rec','../billing/sl_receipts_report.php'); ?>
          <?php genPopLink('Front Rec','front_receipts_report.php'); ?>
          <?php genPopLink('Pmt Method','receipts_by_method_report.php'); ?>
          <?php genPopLink('Collections','collections_report.php'); ?>
        </ul>
      </li>
<?php } ?>
      <li><span><?php xl('General','e') ?></span>
        <ul>
          <?php genPopLink('Services','services_by_category.php'); ?>
          <?php if ($GLOBALS['inhouse_pharmacy']) genPopLink('Inventory','inventory_list.php'); ?>
          <?php if ($GLOBALS['inhouse_pharmacy']) genPopLink('Destroyed','destroyed_drugs_report.php'); ?>
        </ul>
      </li>
<?php if (! $GLOBALS['simplified_demographics']) { ?>
      <li><span><?php xl('Insurance','e') ?></span>
        <ul>
          <?php genPopLink('Distribution','insurance_allocation_report.php'); ?>
          <?php genPopLink('Indigents','../billing/indigent_patients_report.php'); ?>
          <?php genPopLink('Unique SP','unique_seen_patients_report.php'); ?>
        </ul>
      </li>
<?php } ?>
<?php if ($GLOBALS['athletic_team']) { ?>
      <li><span><?php xl('Athletic','e') ?></span>
        <ul>
          <?php genPopLink('Roster','players_report.php'); ?>
          <?php genPopLink('Missed','absences_report.php'); ?>
          <?php genPopLink('Injuries','football_injury_report.php'); ?>
          <?php genPopLink('Inj Ov','injury_overview_report.php'); ?>
        </ul>
      </li>
<?php } ?>
      <?php // genTreeLink('RTop','rep','Other'); ?>
    </ul>
  </li>
  <li><span><?php xl('Miscellaneous','e') ?></span>
    <ul>
      <?php genTreeLink('RBot','aun','Pt Notes/Auth'); ?>
      <?php genTreeLink('RTop','fax','Fax/Scan'); ?>
      <?php genTreeLink('RTop','adb','Addr Book'); ?>
      <?php genTreeLink('RTop','ono','Ofc Notes'); ?>
      <?php genMiscLink('RTop','adm','0','BatchCom','batchcom/batchcom.php'); ?>
      <?php genTreeLink('RTop','pwd','Password'); ?>
    </ul>
  </li>
</ul>

<?php } else { ?>

<table cellpadding='0' cellspacing='0' border='0'>
 <tr>
  <td colspan='3'>
   <table cellpadding='0' cellspacing='0' border='0' width='100%'>
    <tr>
     <td class='smalltext' nowrap>
      <input type='checkbox' name='cb_top' onclick='toggleFrame(1)' checked /><b><?php xl('Top','e') ?></b>
     </td>
     <td class='smalltext' align='right' nowrap>
      <b><?php xl('Bot','e') ?></b><input type='checkbox' name='cb_bot' onclick='toggleFrame(2)' checked />
     </td>
    </tr>
   </table>
  </td>
 </tr>
<?php
 // Builds the table of radio buttons and their labels.  Radio button values
 // are comprised of the 3-character document id and the 1-digit usage type,
 // so that JavaScript can easily access this information.
 $default_top_rbid = $GLOBALS['athletic_team'] ? 'ros' : 'cal';
 foreach ($primary_docs as $key => $varr) {
  if (!empty($disallowed[$key])) continue;
  $label = $varr[0];
  $usage = $varr[1];
  $url   = $varr[2];
  echo " <tr>\n";
  echo "  <td class='smalltext'><input type='radio' name='rb_top' value='$key$usage' " .
       "onclick=\"loadFrame('$key$usage','RTop','$url')\"";
  if ($key == $default_top_rbid) echo " checked";
  echo " /></td>\n";
  echo "  <td class='smalltext' id='lbl_$key'>$label</td>\n";
  echo "  <td class='smalltext'><input type='radio' name='rb_bot' value='$key$usage' " .
       "onclick=\"loadFrame('$key$usage','RBot','$url')\"";
  if ($key == 'aun') echo " checked";
  echo " /></td>\n";
  echo " </tr>\n";
 }
?>
</table>

<?php } ?>

<br /><hr />

<?php xl('Active Patient','e') ?>:<br />
<div id='current_patient'>
<b>None</b>
</div>

<?php xl('Active Encounter','e') ?>:<br />
<div id='current_encounter'>
<b>None</b>
</div>

<select name='popups' onchange='selpopup(this)' style='background-color:transparent;font-size:9pt;'>
 <option value=''><?php xl('Popups','e'); ?></option>
<?php if (!$disallowed['iss']) { ?>
 <option value='../patient_file/problem_encounter.php'><?php xl('Issues','e'); ?></option>
<?php } ?>
 <option value='../../custom/export_xml.php'><?php xl('Export','e'); ?></option>
 <option value='../../custom/import_xml.php'><?php xl('Import','e'); ?></option>
<?php if ($GLOBALS['athletic_team']) { ?>
 <option value='../reports/players_report.php'><?php xl('Roster','e'); ?></option>
<?php } ?>
 <option value='../reports/appointments_report.php?patient=<?php echo $pid ?>'><?php xl('Appts','e'); ?></option>
<?php if (file_exists("$webserver_root/custom/refer.php")) { ?>
 <option value='../../custom/refer.php'><?php xl('Refer','e'); ?></option>
<?php } ?>
<?php if (file_exists("$webserver_root/custom/fee_sheet_codes.php")) { ?>
 <option value='../patient_file/printed_fee_sheet.php'><?php xl('Superbill','e'); ?></option>
<?php } ?>
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
 <option value='../patient_file/front_payment.php'><?php xl('Prepay','e'); ?></option>
 <option value='../patient_file/pos_checkout.php'><?php xl('Checkout','e'); ?></option>
<?php } else { ?>
 <option value='../patient_file/front_payment.php'><?php xl('Payment','e'); ?></option>
<?php } ?>
</select>

<hr />

<table cellpadding='0' cellspacing='0' border='0'>
 <tr>
  <td class='smalltext'><?php xl('Find','e') ?>:&nbsp;</td>
  <td class='smalltext' colspan='2'>
   <input type="entry" size="7" name="patient" class='inputtext' style='width:65px;' />
  </td>
 </tr>
 <tr>
  <td class='smalltext'><?php xl('by','e') ?>:</td>
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
<a href="../logout.php?auth=logout" target="_top" class="navitem" id="logout_link"
 onclick="top.restoreSession()">
<?php xl('Logout','e'); ?></a>

<input type='hidden' name='findBy' value='Last' />

</form>

<script language='JavaScript'>
syncRadios();
</script>

</body>
</html>
