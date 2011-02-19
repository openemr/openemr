<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the LBFvbf visit form,
// "Vitals and Body Fat".  It is invoked by interface/forms/LBF/new.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function LBFvbf_javascript() {
  global $formid;

  echo "// Compute Body Mass Index.
function vbfComputeBMI() {
 var f = document.forms[0];
 var bmi = 0;
 var stat = '';
 var height = parseFloat(f.form_height_in.value);
 var weight = parseFloat(f.form_weight_lbs.value);
 if(isNaN(height) || isNaN(weight) || height <= 0 || weight <= 0) {
  bmi = '';
 }
 else {
  bmi = weight / height / height * 703;
  bmi = bmi.toFixed(1);
  if      (bmi > 42  ) stat = '" . xl('Obesity III') . "';
  else if (bmi > 34  ) stat = '" . xl('Obesity II' ) . "';
  else if (bmi > 30  ) stat = '" . xl('Obesity I'  ) . "';
  else if (bmi > 27  ) stat = '" . xl('Overweight' ) . "';
  else if (bmi > 18.5) stat = '" . xl('Normal'     ) . "';
  else                 stat = '" . xl('Underweight') . "';
 }
 if (f.form_bmi) f.form_bmi.value = bmi;
 if (f.form_bmi_status) f.form_bmi_status.value = stat;
}
";

  echo "// Height in cm has changed.
function vbf_height_cm_changed() {
 var f = document.forms[0];
 var cm = f.form_height_cm.value;
 if (cm == parseFloat(cm)) {
  inch = cm / 2.54;
  f.form_height_in.value = inch.toFixed(2);
 }
 else {
  f.form_height_in.value = '';
 }
 vbfComputeBMI();
}
";

  echo "// Height in inches has changed.
function vbf_height_in_changed() {
 var f = document.forms[0];
 var inch = f.form_height_in.value;
 if (inch == parseFloat(inch)) {
  cm = inch * 2.54;
  f.form_height_cm.value = cm.toFixed(2);
 }
 else {
  f.form_height_cm.value = '';
 }
 vbfComputeBMI();
}
";

  echo "// Weight in kg has changed.
function vbf_weight_kg_changed() {
 var f = document.forms[0];
 var kg = f.form_weight_kg.value;
 if (kg == parseFloat(kg)) {
  lbs = kg / 0.45359237;
  f.form_weight_lbs.value = lbs.toFixed(2);
 }
 else {
  f.form_weight_lbs.value = '';
 }
 vbfComputeBMI();
}
";

  echo "// Weight in lbs has changed.
function vbf_weight_lbs_changed() {
 var f = document.forms[0];
 var lbs = f.form_weight_lbs.value;
 if (lbs == parseFloat(lbs)) {
  kg = lbs * 0.45359237;
  f.form_weight_kg.value = kg.toFixed(2);
 }
 else {
  f.form_weight_kg.value = '';
 }
 vbfComputeBMI();
}
";

  echo "// Temperature in centigrade has changed.
function vbf_temperature_c_changed() {
 var f = document.forms[0];
 var tc = f.form_temperature_c.value;
 if (tc == parseFloat(tc)) {
  tf = tc * 9 / 5 + 32;
  f.form_temperature_f.value = tf.toFixed(2);
 }
 else {
  f.form_temperature_f.value = '';
 }
}
";

  echo "// Temperature in farenheit has changed.
function vbf_temperature_f_changed() {
 var f = document.forms[0];
 var tf = f.form_temperature_f.value;
 if (tf == parseFloat(tf)) {
  tc = (tf - 32) * 5 / 9;
  f.form_temperature_c.value = tc.toFixed(2);
 }
 else {
  f.form_temperature_c.value = '';
 }
}
";

  // Compute patient age and sex.
  $ptrow = sqlQuery("SELECT DOB, sex FROM patient_data WHERE " .
    "pid = '$pid' LIMIT 1");
  $pt_age = 0 + getpatientAge($ptrow['DOB']);
  $pt_sex = strtoupper(substr($ptrow['sex'], 0, 1)) == 'F' ? 1 : 0;

  echo "// Compute Body Fat Percentage.
function vbfComputeBF() {
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
function vbfSFChanged() {
 var f = document.forms[0];
 var sum = 0;
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name.substring(0,8) == 'form_sf_' && e.name != 'form_sf_sum') {
   if (e.value == parseFloat(e.value)) sum += parseFloat(e.value);
  }
 }
 f.form_sf_sum.value = sum.toFixed(2);
 vbfComputeBF();
}
";

}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function LBFvbf_javascript_onload() {

  echo "
var f = document.forms[0];
if (f.form_weight_lbs && f.form_weight_kg) {
 // Set onchange handlers to convert kg to lbs and vice versa.
 f.form_weight_lbs.onchange = function () { vbf_weight_lbs_changed(); };
 f.form_weight_kg.onchange  = function () { vbf_weight_kg_changed() ; };
}
if (f.form_height_in && f.form_height_cm) {
 // Set onchange handlers to convert centimeters to inches and vice versa.
 f.form_height_in.onchange = function () { vbf_height_in_changed(); };
 f.form_height_cm.onchange = function () { vbf_height_cm_changed(); };
}
if (f.form_temperature_f && f.form_temperature_c) {
 // Set onchange handlers to convert centigrade to farenheit and vice versa.
 f.form_temperature_f.onchange = function () { vbf_temperature_f_changed(); };
 f.form_temperature_c.onchange = function () { vbf_temperature_c_changed(); };
}
// Set computed fields to be readonly.
if (f.form_bmi) {
 f.form_bmi.readOnly = true;
}
if (f.form_bmi_status) {
 f.form_bmi_status.readOnly = true;
}
if (f.form_body_fat) {
 f.form_body_fat.readOnly = true;
}
// More of the same, for skin folds.
if (f.form_sf_sum) {
 f.form_sf_sum.readOnly = true;
 for (var i = 0; i < f.elements.length; ++i) {
  var e = f.elements[i];
  if (e.name.substring(0,8) == 'form_sf_' && e.name != 'form_sf_sum') {
   e.onchange = function () { vbfSFChanged(); };
  }
 }
}
";

}
?>

