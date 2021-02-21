<?php
/* Copyright (C) 2016-2021 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

require_once('../globals.php');
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php Header::setupHeader(['common', 'datetime-picker', 'opener']); ?>

<title><?php echo xlt('Void Dialog'); ?></title>

<script>

function DoSubmit() {
  var f = document.forms[0];
  if (opener.closed || !opener.voidwrap) {
    alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
  }
  else if (f.form_reason.selectedIndex == 0) {
    alert(<?php echo xlj('Please specify Void Reason.'); ?>);
    return false;
  }
  else if (f.form_notes.value.trim() == '') {
    alert(<?php echo xlj('Please specify Void Notes.'); ?>);
    return false;
  }
  else {
    opener.voidwrap(f.form_reason.value, f.form_notes.value);
  }
  window.close();
  return false;
};

</script>

</head>

<body>

<div class="container-fluid">

<form method='post'>

<center>

<table border='0'>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Void Reason'); ?>:</b></td>
  <td>
<?php
generate_form_field(
    array(
        'data_type' => 1,
        'field_id' => 'reason',
        'list_id' => 'void_reasons',
        'empty_title' => 'Select Reason'
    ),
    ''
);
?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Void Notes'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_notes' maxlength='80' value='' style='width:100%' />
  </td>
 </tr>

</table>

<?php
echo "<p>\n";
echo "<input type='button' value='" . xla('Submit') . "' onclick='DoSubmit()' />&nbsp;\n";
echo "<input type='button' value='" . xla('Cancel') . "' onclick='window.close()' />\n";
echo "</p>\n";
?>

</center>

</form>
</div>
</body>
</html>
