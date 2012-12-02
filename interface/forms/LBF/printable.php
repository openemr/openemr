<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");

$CPR = 4; // cells per row

// The form name is passed to us as a GET parameter.
$formname = isset($_GET['formname']) ? $_GET['formname'] : '';

$tmp = sqlQuery("SELECT title FROM list_options WHERE " .
  "list_id = 'lbfnames' AND option_id = ? LIMIT 1", array($formname) );
$formtitle = $tmp['title'];

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = ? AND uor > 0 " .
  "ORDER BY group_name, seq", array($formname) );
?>
<html>
<head>
<?php html_header_show();?>

<style>
body, td {
 font-family: Arial, Helvetica, sans-serif;
 font-weight: normal;
 font-size: 9pt;
}

body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border-style: solid;
 border-width: 1px;
 border-color: #000000;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

.mainhead {
 font-weight: bold;
 font-size: 14pt;
 text-align: center;
}

.subhead {
 font-weight: bold;
 font-size: 8pt;
}

.under {
 border-style: solid;
 border-width: 0 0 1px 0;
 border-color: #999999;
}

.ftitletable {
 width: 100%;
 margin: 0 0 8pt 0;
}
.ftitlecell1 {
 vertical-align: top;
 text-align: left;
 font-size: 14pt;
 font-weight: bold;
}
.ftitlecell2 {
 vertical-align: top;
 text-align: right;
 font-size: 9pt;
}
</style>
</head>

<body bgcolor='#ffffff'>
<form>

<?php echo genFacilityTitle($formtitle, -1); ?>

<span class='subhead'>
 <?php echo xlt('Patient') ?>: ________________________________________ &nbsp;
 <?php echo xlt('Clinic') ?>: ____________________ &nbsp;
 <?php echo xlt('Date') ?>: ____________________<br />&nbsp;<br />
</span>

<?php

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo " </table>\n";
    echo "</div>\n";
  }
}

$last_group = '';
$cell_count = 0;
$item_count = 0;

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = '';

  // if (isset($result[$field_id])) $currvalue = $result[$field_id];

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    if (strlen($last_group) > 0) echo "<br />\n";
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
    echo "<b>" . text(xl_layout_label($group_name)) . "</b>\n";
      
    echo "<div class='section'>\n";
    echo " <table border='0' cellpadding='0'>\n";
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr style='height:30pt'>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td colspan='" . attr($titlecols) . "' width='10%'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  echo "<b>";
    
  if ($frow['title']) echo (text(xl_layout_label($frow['title'])) . ":"); else echo "&nbsp;";

  echo "</b>";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='" . attr($datacols) . "' width='40%'";
    if ($data_type < 21 || $data_type > 25) echo " class='under'";
    if ($cell_count > 0) echo " style='padding-left:5pt;'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_print_field($frow, $currvalue);
}

end_group();
?>

</form>

<!-- This should really be in the onload handler but that seems to be unreliable and can crash Firefox 3. -->
<script language='JavaScript'>
window.print();
</script>

</body>
</html>
