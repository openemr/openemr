<?php
// Copyright (C) 2014 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This is the place to put JavaScript functions that are needed to support
// options.inc.php. Include this in the <head> section of relevant modules.
// It's a .php module so that translation can be supported.
?>
<script type="text/javascript">

// JavaScript support for date types when the A or B edit option is used.
// Called to recompute displayed age dynamically when the corresponding date is
// changed. Must generate the same age formats as the oeFormatAge() function.
//
function updateAgeString(fieldid, asof, format) {
  var datefld = document.getElementById('form_' + fieldid);
  var f = datefld.form;
  var age = '';
  var date1 = new Date(datefld.value);
  var date2 = asof ? new Date(asof) : new Date();
  if (format == 3) {
    // Gestational age.
    var msecs = date2.getTime() - date1.getTime();
    var days  = Math.round(msecs / (24 * 60 * 60 * 1000));
    var weeks = Math.floor(days / 7);
    days = days % 7;
    age = '<?php echo xls('Gest age') ?> ' +
      weeks + (weeks == 1 ? ' <?php echo xls('week') ?>' : ' <?php echo xls('weeks') ?>') + ' ' +
      days  + (days  == 1 ? ' <?php echo xls('day' ) ?>' : ' <?php echo xls('days' ) ?>');
  }
  else {
    // Years or months.
    var dayDiff   = date2.getDate()     - date1.getDate();
    var monthDiff = date2.getMonth()    - date1.getMonth();
    var yearDiff  = date2.getFullYear() - date1.getFullYear();
    var ageInMonths = yearDiff * 12 + monthDiff;
    if (dayDiff < 0) --ageInMonths;
    if (format == 1 || (format == 0 && ageInMonths >= 24)) {
      age = yearDiff;
      if (monthDiff < 0 || (monthDiff == 0 && dayDiff < 0)) --age;
      age = '' + age;
    }
    else {
      age = '' + ageInMonths;
      if (format == 0) {
        age = age + ' ' + (ageInMonths == 1 ? '<?php echo xls('month') ?>' : '<?php echo xls('months') ?>'); 
      }
    }
    if (age != '') age = '<?php echo xls('Age') ?> ' + age;
  }
  document.getElementById('span_' + fieldid).innerHTML = age;
}

// Function to show or hide form fields (and their labels) depending on "skip conditions"
// defined in the layout.
//
var cskerror = false; // to avoid repeating error messages
function checkSkipConditions() {
  var myerror = cskerror;
  var prevandor = '';
  var prevcond = false;
  for (var i = 0; i < skipArray.length; ++i) {
    var target   = skipArray[i].target;
    var id       = skipArray[i].id;
    var itemid   = skipArray[i].itemid;
    var operator = skipArray[i].operator;
    var value    = skipArray[i].value;

    var tofind = id;
    if (itemid) tofind += '[' + itemid + ']';
    // Some different source IDs are possible depending on the data type.
    var srcelem = document.getElementById('check_' + tofind);
    if (srcelem == null) srcelem = document.getElementById('radio_' + tofind);
    if (srcelem == null) srcelem = document.getElementById('form_' + tofind);
    if (srcelem == null) {
      if (!cskerror) alert('Cannot find a skip source field for "' + tofind + '"');
      myerror = true;
      continue;
    }

    var condition = false;
    if (operator == 'eq') condition = srcelem.value == value; else
    if (operator == 'ne') condition = srcelem.value != value; else
    if (operator == 'se') condition = srcelem.checked       ; else
    if (operator == 'ns') condition = !srcelem.checked;

    // Logic to accumulate multiple conditions for the same target.
    // alert('target = ' + target + ' prevandor = ' + prevandor + ' prevcond = ' + prevcond); // debugging
    if (prevandor == 'and') condition = condition && prevcond; else
    if (prevandor == 'or' ) condition = condition || prevcond;
    prevandor = skipArray[i].andor;
    prevcond = condition;
    var j = i + 1;
    if (j < skipArray.length && skipArray[j].target == target) continue;

    // At this point condition indicates if the target should be hidden.

    var trgelem1 = document.getElementById('label_id_' + target);
    var trgelem2 = document.getElementById('value_id_' + target);
    if (trgelem1 == null && trgelem2 == null) {
      if (!cskerror) alert('Cannot find a skip target field for "' + target + '"');
      myerror = true;
      continue;
    }
    // If the item occupies a whole row then undisplay its row, otherwise hide its cells.
    var colspan = 0;
    if (trgelem1) colspan += trgelem1.colSpan;
    if (trgelem2) colspan += trgelem2.colSpan;
    if (colspan < 4) {
      if (trgelem1) trgelem1.style.visibility = condition ? 'hidden' : 'visible';
      if (trgelem2) trgelem2.style.visibility = condition ? 'hidden' : 'visible';
    }
    else {
      if (trgelem1) trgelem1.parentNode.style.display = condition ? 'none' : '';
      else          trgelem2.parentNode.style.display = condition ? 'none' : '';
    }
  }
  // If any errors, all show in the first pass and none in subsequent passes.
  cskerror = cskerror || myerror;
}

</script>
