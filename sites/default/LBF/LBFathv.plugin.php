<?php
// Copyright (C) 2010-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the LBFathv visit form,
// "Vitals".  It is invoked by interface/forms/LBF/new.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function LBFathv_javascript() {
  global $formid;

  echo "// Compute Body Mass Index.
function athvComputeBMI() {
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
function athv_height_cm_changed() {
 var f = document.forms[0];
 var cm = f.form_height_cm.value;
 if (cm == parseFloat(cm)) {
  inch = cm / 2.54;
  f.form_height_in.value = inch.toFixed(2);
 }
 else {
  f.form_height_in.value = '';
 }
 athvComputeBMI();
}
";

  echo "// Height in inches has changed.
function athv_height_in_changed() {
 var f = document.forms[0];
 var inch = f.form_height_in.value;
 if (inch == parseFloat(inch)) {
  cm = inch * 2.54;
  f.form_height_cm.value = cm.toFixed(2);
 }
 else {
  f.form_height_cm.value = '';
 }
 athvComputeBMI();
}
";

  echo "// Weight in kg has changed.
function athv_weight_kg_changed() {
 var f = document.forms[0];
 var kg = f.form_weight_kg.value;
 if (kg == parseFloat(kg)) {
  lbs = kg / 0.45359237;
  f.form_weight_lbs.value = lbs.toFixed(2);
 }
 else {
  f.form_weight_lbs.value = '';
 }
 athvComputeBMI();
}
";

  echo "// Weight in lbs has changed.
function athv_weight_lbs_changed() {
 var f = document.forms[0];
 var lbs = f.form_weight_lbs.value;
 if (lbs == parseFloat(lbs)) {
  kg = lbs * 0.45359237;
  f.form_weight_kg.value = kg.toFixed(2);
 }
 else {
  f.form_weight_kg.value = '';
 }
 athvComputeBMI();
}
";

  echo "// Temperature in centigrade has changed.
function athv_temperature_c_changed() {
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
function athv_temperature_f_changed() {
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

}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function LBFathv_javascript_onload() {

  echo "
var f = document.forms[0];
if (f.form_weight_lbs && f.form_weight_kg) {
 // Set onchange handlers to convert kg to lbs and vice versa.
 f.form_weight_lbs.onchange = function () { athv_weight_lbs_changed(); };
 f.form_weight_kg.onchange  = function () { athv_weight_kg_changed() ; };
}
if (f.form_height_in && f.form_height_cm) {
 // Set onchange handlers to convert centimeters to inches and vice versa.
 f.form_height_in.onchange = function () { athv_height_in_changed(); };
 f.form_height_cm.onchange = function () { athv_height_cm_changed(); };
}
if (f.form_temperature_f && f.form_temperature_c) {
 // Set onchange handlers to convert centigrade to farenheit and vice versa.
 f.form_temperature_f.onchange = function () { athv_temperature_f_changed(); };
 f.form_temperature_c.onchange = function () { athv_temperature_c_changed(); };
}
// Set computed fields to be readonly.
if (f.form_bmi) {
 f.form_bmi.readOnly = true;
}
if (f.form_bmi_status) {
 f.form_bmi_status.readOnly = true;
}
";

}
?>

