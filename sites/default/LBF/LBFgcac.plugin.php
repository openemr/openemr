<?php
// Copyright (C) 2009-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the LBFgcac visit form.
// It is invoked by interface/forms/LBF/new.php.

// Private function.  Constructs a query to find a given lbf_data field's
// values from visits within the past 2 weeks.
//
function _LBFgcac_query_recent($more) {
  global $pid, $encounter, $formname, $formid;

  // Get the date of this visit.
  $encrow = sqlQuery("SELECT date FROM form_encounter WHERE " .
    "pid = '$pid' AND encounter = '$encounter'");
  $encdate = $encrow['date'];

  // Query complications from the two weeks prior to this visit.
  $query = "SELECT d.field_value " .
    "FROM forms AS f, form_encounter AS fe, lbf_data AS d " .
    "WHERE f.pid = '$pid' AND " .
    "f.formdir = '$formname' AND " .
    "f.deleted = 0 AND " .
    "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
    "fe.date <= '$encdate' AND " .
    "DATE_ADD(fe.date, INTERVAL 14 DAY) > '$encdate' AND " .
    "d.form_id = f.form_id AND $more";

  return $query;
}

// Private function.  Given a field name, gets its value from the most
// recent instance of this form type that is not more than 2 weeks old.
//
function _LBFgcac_recent_default($name) {
  global $formid;

  // This logic only makes sense for a new form.
  if ($formid) return '';

  $query = _LBFgcac_query_recent(
    "d.field_id = '$name' " .
    "ORDER BY f.form_id DESC LIMIT 1");
  $row = sqlQuery($query);

  if (empty($row['field_value'])) return '';
  return $row['field_value'];
}

// Private function.  Query services within 2 weeks of this encounter.
//
function _LBFgcac_query_recent_services() {
  global $pid, $encounter;

  // Get the date of this visit.
  $encrow = sqlQuery("SELECT date FROM form_encounter WHERE " .
    "pid = '$pid' AND encounter = '$encounter'");
  $encdate = $encrow['date'];

  // Query services from the two weeks prior to this visit.
  $query = "SELECT c.related_code " .
    "FROM form_encounter AS fe, billing AS b, codes AS c " .
    "WHERE fe.pid = '$pid' AND fe.date <= '$encdate' AND " .
    "DATE_ADD(fe.date, INTERVAL 14 DAY) > '$encdate' AND " .
    "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 AND " .
    "b.code_type = 'MA' AND c.code_type = '12' AND " .
    "c.code = b.code AND c.modifier = b.modifier " .
    "ORDER BY fe.date DESC, b.id DESC";

  return $query;
}

// Private function.  Query services from this encounter.
//
function _LBFgcac_query_current_services() {
  global $pid, $encounter;

  $query = "SELECT c.related_code " .
    "FROM billing AS b, codes AS c WHERE " .
    "b.pid = '$pid' AND b.encounter = '$encounter' AND b.activity = 1 AND " .
    "b.code_type = 'MA' AND c.code_type = '12' AND " .
    "c.code = b.code AND c.modifier = b.modifier " .
    "ORDER BY b.id DESC";

  return $query;
}

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function LBFgcac_javascript() {
  global $formid;

  // Query complications from the two weeks prior to this visit.
  $res = sqlStatement(_LBFgcac_query_recent(
    "f.form_id != '$formid' AND " .
    "d.field_id = 'complications'"));

  // This JavaScript function is to enable items in the "Main complications"
  // list that have been selected, and to disable all others.
  // Option.disabled seems to work for Firefox 3 and IE8 but not IE6.
  echo "// Enable recent complications and disable all others.
function set_main_compl_list() {
 var f = document.forms[0];
 var sel = f.form_main_compl;
 var n = '';
";
  // We use the checkbox object values as a scratch area to note which
  // complications were already selected from other forms.
  while ($row = sqlFetchArray($res)) {
    $a = explode('|', $row['field_value']);
    foreach ($a as $complid) {
      if (empty($complid)) continue;
      echo " n = 'form_complications[$complid]'; if (f[n]) f[n].value = 2;\n";
    }
  }
  echo " // Scan the list items and set their disabled flags.
 for (var i = 1; i < sel.options.length; ++i) {
  n = 'form_complications[' + sel.options[i].value + ']';
  sel.options[i].disabled = (f[n] && (f[n].checked || f[n].value == '2')) ? false : true;
 }
}
";

  echo "
// Disable most form fields if refusing abortion.
function client_status_changed() {
 var f = document.forms[0];
 var dis1 = false; // true to disable complications
 var dis2 = false; // true to disable procedures
 var cs = f.form_client_status;
 var csval = '';
 if (cs.type) { // cs = select list
  if (cs.selectedIndex >= 0) {
   csval = cs.options[cs.selectedIndex].value;
  }
 }
 else { // cs = array of radio buttons
  for (var i = 0; i < cs.length; ++i) {
   if (cs[i].checked) {
    csval = cs[i].value;
    break;
   }
  }
 }
 if (csval == 'mara' || csval == 'defer' || csval == 'refin') {
  dis1 = true;
  dis2 = true;
 }
 else if (csval == 'maaa') {
  dis2 = true;
 }
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name.substring(0,18) == 'form_complications' || e.name == 'form_main_compl') {
   e.disabled = dis1;
  }
  else if (e.name == 'form_in_ab_proc') {
   e.disabled = dis2;
  }
  else if (e.name == 'form_ab_location') {
   if (csval == 'maaa') {
    e.disabled = (e.value == 'part' || e.value == 'oth' || e.value == 'na');
   }
   else if (csval == 'mara' || csval == 'defer' || csval == 'self') {
    e.disabled = true; // (e.value != 'na');
   }
   // else if (csval == 'refout') {
   //  e.disabled = (e.value == 'proc' || e.value == 'ma');
   // }
   else { // inbound referral
    e.disabled = (e.value == 'na' || e.value == 'proc' || e.value == 'ma');
   }
  }
  else if (e.name == 'form_gc_rreason') {
   e.disabled = (csval != 'mara' && csval != 'refout');
  }
 }
}
";

  echo "
// Enable some form fields before submitting.
// This is because disabled fields do not submit their data, however
// we do want to save the default values that were set for them.
function mysubmit() {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name == 'form_in_ab_proc') {
   e.disabled = false;
  }
 }
 top.restoreSession();
 return true;
}
";

}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function LBFgcac_javascript_onload() {
  echo "
set_main_compl_list();
client_status_changed();
var f = document.forms[0];
for (var i = 0; i < f.elements.length; ++i) {
 var e = f.elements[i];
 if (e.name.substring(0,18) == 'form_complications')
  e.onclick = function () { set_main_compl_list(); };
}
var cs = f.form_client_status;
if (cs.type) { // cs = select list
 cs.onchange = function () { client_status_changed(); };
}
else { // cs = array of radio buttons
 for (var i = 0; i < cs.length; ++i) {
  cs[i].onclick = function () { client_status_changed(); };
 }
}
f.onsubmit = function () { return mysubmit(); };
";
}

// Generate default for client status.
//
function LBFgcac_default_client_status() {
  return _LBFgcac_recent_default('client_status');
}

// Generate default for visit type.  If there are no recent prior visits,
// then default to new procedure.
//
function LBFgcac_default_ab_location() {
  global $formid;
  if ($formid) return '';
  $vt = _LBFgcac_recent_default('ab_location');
  if (empty($vt)) return 'proc';
  return $vt;
}

// Generate default for the induced procedure type.
//
function LBFgcac_default_in_ab_proc() {

  // Check previous GCAC visit forms for this setting.
  $default = _LBFgcac_recent_default('in_ab_proc');
  if ($default !== '') return $default;

  // If none, query services from recent visits to see if an IPPF code
  // matches that of a procedure type in the list.
  $res = sqlStatement(_LBFgcac_query_recent_services());
  while ($row = sqlFetchArray($res)) {
    if (empty($row['related_code'])) continue;
    $relcodes = explode(';', $row['related_code']);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      if ($codetype !== 'IPPF') continue;
      $lres = sqlStatement("SELECT option_id, mapping FROM list_options " .
        "WHERE list_id = 'in_ab_proc'");
      while ($lrow = sqlFetchArray($lres)) {
        $maparr = explode(':', $lrow['mapping']);
        if (empty($maparr[1])) continue;
        if (preg_match('/^' . $maparr[1] . '/', $code)) {
          return $lrow['option_id'];
        }
      }
    } // end foreach
  }

  return '';
}

// Generate default for the main complication.
//
function LBFgcac_default_main_compl() {
  return _LBFgcac_recent_default('main_compl');
}
?>
