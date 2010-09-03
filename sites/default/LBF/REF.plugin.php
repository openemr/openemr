<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This provides enhancement functions for the referral (REF) form.
// It is invoked by interface/patient_file/transaction/add_transaction.php.

// The purpose of this function is to create JavaScript for the <head>
// section of the page.  This in turn defines desired javaScript
// functions.
//
function REF_javascript() {
  // This JavaScript function is to reload the "Refer To" options when
  // the "External Referral" selection changes.
  echo "// onChange handler for form_refer_external.
var poptions = new Array();
function external_changed() {
 var f = document.forms[0];
 var eix = f.form_refer_external.selectedIndex;
 var psel = f.form_refer_to;
 var i = psel.selectedIndex < 0 ? 0 : psel.selectedIndex;
 var pvalue = psel.options[i].value;
 if (poptions.length == 0) {
  for (i = 0; i < psel.options.length; ++i) {
   poptions[i] = psel.options[i];
  }
 }
 psel.options.length = 1;
 var selindex = 0;
 for (i = 1; i < poptions.length; ++i) {
  var local = poptions[i].title == 'Local';
  if (eix == 1 && !local) continue;
  if (eix == 2 &&  local) continue;
  if (poptions[i].value == pvalue) selindex = psel.options.length;
  psel.options[psel.options.length] = poptions[i];
 }
 psel.selectedIndex = selindex;
}
";
}

// The purpose of this function is to create JavaScript that is run
// once when the page is loaded.
//
function REF_javascript_onload() {
  echo "
external_changed();
var f = document.forms[0];
f.form_refer_external.onchange = function () { external_changed(); };
";
}

?>
