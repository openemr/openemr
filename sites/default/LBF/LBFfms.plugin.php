<?php

// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the LBFfms visit form,
// "Functional Movement Screening".  It is invoked by
// interface/forms/LBF/new.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function LBFfms_javascript()
{
    global $formid;

    echo "// Array identifying the numeric '0-3' fields.
var fms_numeric = [
 'squat',
 'hurdle_l',
 'lunge_l',
 'sho_l',
 'actslr_l',
 'tspu',
 'spine',
 'rotary_l'];
";

    echo "// A numeric '0-3' field has changed.
function fms_numeric_changed(e) {
 var f = document.forms[0];
 // Check if the entry is a valid digit.
 var val = parseInt(e.value);
 if (isNaN(val) || val < 0 || val > 3) {
  e.value = '';
  return;
 }
 // Propagate values to the read-only second column.
 var name = e.name;
 var namepref = name.substring(0, name.length - 2); // removing the '_1'.
 var namelr = namepref.substring(namepref.length - 2, namepref.length);
 var name2 = namepref; // this will be the target
 // If this is a Left or Right field, we propagate the lower of the two
 // to the Left row in column 2.
 if (namelr == '_l') {
  var tmp = parseInt(f[namepref.substring(0,namepref.length-1) + 'r_1'].value);
  if (!isNaN(tmp) && tmp < val) val = tmp;
 }
 else if (namelr == '_r') {
  name2 = namepref.substring(0,namepref.length-1) + 'l';
  var tmp = parseInt(f[namepref.substring(0,namepref.length-1) + 'l_1'].value);
  if (!isNaN(tmp) && tmp < val) val = tmp;
 }
 f[name2 + '_2'].value = val;
 // Tally up the propagated values.
 var sum = 0;
 for (var i = 0; i < fms_numeric.length; ++i) {
  var val = parseInt(f['form_' + fms_numeric[i] + '_2'].value);
  if (!isNaN(val)) sum += val;
 }
 f.form_total.value = sum;
}
";
}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function LBFfms_javascript_onload()
{
    echo "
var f = document.forms[0];
for (var i = 0; i < fms_numeric.length; ++i) {
 var namepref = 'form_' + fms_numeric[i];
 f[namepref + '_2'].readOnly = true;
 var ename = namepref + '_1';
 f[ename].onchange = function () { fms_numeric_changed(this); };
  var namelr = namepref.substring(namepref.length - 2, namepref.length);
 if (namelr == '_l') {
  ename = namepref.substring(0,namepref.length-1) + 'r_1';
  f[ename].onchange = function () { fms_numeric_changed(this); };
 }
}
f.form_total.readOnly = true;
";
}
