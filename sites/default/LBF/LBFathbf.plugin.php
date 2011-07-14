<?php
// Copyright (C) 2010-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the LBFathbf visit form,
// "Body Fat".  It is invoked by interface/forms/LBF/new.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function LBFathbf_javascript() {
  global $formid;

  // Compute patient age and sex.
  $ptrow = sqlQuery("SELECT DOB, sex FROM patient_data WHERE " .
    "pid = '$pid' LIMIT 1");
  $pt_age = 0 + getpatientAge($ptrow['DOB']);
  $pt_sex = strtoupper(substr($ptrow['sex'], 0, 1)) == 'F' ? 1 : 0;

  echo "// Compute Body Fat Percentage.
function athbfComputeBF() {
 var f = document.forms[0];
 var age = $pt_age; // Patient age in years
 var sex = $pt_sex; // 0 = Male, 1 = Female
 if (!f.form_sf_sum || !f.form_body_fat) return;
 var sfsum = f.form_sf_sum.value;
 if (sfsum != parseFloat(sfsum) || sfsum <= 0) {
  f.form_body_fat.value = '';
  return;
 }
 var d = 0;
 var sflog = Math.LOG10E * Math.log(sfsum);
 if (sex == 0) {
  if      (age < 17) d = 1.1533 - 0.0643 * sflog;
  else if (age < 20) d = 1.1620 - 0.0630 * sflog;
  else if (age < 30) d = 1.1631 - 0.0632 * sflog;
  else if (age < 40) d = 1.1422 - 0.0544 * sflog;
  else if (age < 50) d = 1.1620 - 0.0700 * sflog;
  else               d = 1.1715 - 0.0779 * sflog;
 }
 else {
  if      (age < 17) d = 1.1369 - 0.0598 * sflog;
  else if (age < 20) d = 1.1549 - 0.0678 * sflog;
  else if (age < 30) d = 1.1599 - 0.0717 * sflog;
  else if (age < 40) d = 1.1423 - 0.0632 * sflog;
  else if (age < 50) d = 1.1333 - 0.0612 * sflog;
  else               d = 1.1339 - 0.0645 * sflog;
 }
 var bf = 495 / d - 450;
 f.form_body_fat.value = bf.toFixed(2);
}
";

  echo "// Tally skin fold measurements.
function athbfSFChanged() {
 var f = document.forms[0];
 var sum = 0;
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name.substring(0,8) == 'form_sf_' && e.name != 'form_sf_sum') {
   if (e.value == parseFloat(e.value)) sum += parseFloat(e.value);
  }
 }
 f.form_sf_sum.value = sum.toFixed(2);
 athbfComputeBF();
}
";

}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function LBFathbf_javascript_onload() {

  echo "
var f = document.forms[0];
if (f.form_body_fat) {
 f.form_body_fat.readOnly = true;
}
// More of the same, for skin folds.
if (f.form_sf_sum) {
 f.form_sf_sum.readOnly = true;
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name.substring(0,8) == 'form_sf_' && e.name != 'form_sf_sum') {
   e.onchange = function () { athbfSFChanged(); };
  }
 }
}
";

}
?>

