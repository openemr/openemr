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

</script>
