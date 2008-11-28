<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module is invoked by add_edit_issue.php to add support
// for issue types that are specific to IPPF.

require_once("$srcdir/options.inc.php");

$CPR = 4; // cells per row

$pprow = array();

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

function issue_ippf_gcac_newtype() {
  echo "  var gcadisp = (aitypes[index] == 3) ? '' : 'none';\n";
  echo "  document.getElementById('ippf_gcac').style.display = gcadisp;\n";
}

function issue_ippf_gcac_save($issue) {
  $sets = "id = '$issue'";
  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'GCA' AND uor > 0 AND field_id != '' " .
    "ORDER BY group_name, seq");
  while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
    $value = get_layout_form_value($frow);
    $sets .= ", $field_id = '$value'";
  }
  // This replaces the row if its id exists, otherwise inserts it.
  sqlStatement("REPLACE INTO lists_ippf_gcac SET $sets");
}

function issue_ippf_gcac_form($issue) {
  global $pprow, $item_count, $cell_count, $last_group;

  if ($issue) {
    $pprow = sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = '$issue'");
  } else {
    $pprow = array();
  }

  echo "<div id='ippf_gcac' style='display:none'>\n";

  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'GCA' AND uor > 0 " .
    "ORDER BY group_name, seq");
  $last_group = '';
  $cell_count = 0;
  $item_count = 0;
  $display_style = 'block';

  while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_name'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];

    $currvalue  = '';
    if (isset($pprow[$field_id])) $currvalue = $pprow[$field_id];

    // Handle a data category (group) change.
    if (strcmp($this_group, $last_group) != 0) {
      end_group();
      $group_seq  = 'gca' . substr($this_group, 0, 1);
      $group_name = substr($this_group, 1);
      $last_group = $this_group;
      echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
        "onclick='return divclick(this,\"div_$group_seq\");'";
      if ($display_style == 'block') echo " checked";
      echo " /><b>$group_name</b></span>\n";
      echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
      echo " <table border='0' cellpadding='0' width='100%'>\n";
      $display_style = 'none';
    }

    // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
      end_row();
      echo " <tr>";
    }

    if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

    // Handle starting of a new label cell.
    if ($titlecols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$titlecols' width='1%' nowrap";
      echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
      if ($cell_count == 2) echo " style='padding-left:10pt'";
      echo ">";
      $cell_count += $titlecols;
    }
    ++$item_count;

    echo "<b>";
    if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";
    echo "</b>";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$datacols' class='text'";
      if ($cell_count > 0) echo " style='padding-left:5pt'";
      echo ">";
      $cell_count += $datacols;
    }

    ++$item_count;
    generate_form_field($frow, $currvalue);
  }

  end_group();
  echo "</div>\n";
}

function issue_ippf_con_newtype() {
  echo "  var condisp = (aitypes[index] == 4) ? '' : 'none';\n";
  echo "  document.getElementById('ippf_con').style.display = condisp;\n";
}

function issue_ippf_con_save($issue) {
  $sets = "id = '$issue'";
  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'CON' AND uor > 0 AND field_id != '' " .
    "ORDER BY group_name, seq");
  while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
    $value = get_layout_form_value($frow);
    $sets .= ", $field_id = '$value'";
  }
  // This replaces the row if its id exists, otherwise inserts it.
  sqlStatement("REPLACE INTO lists_ippf_con SET $sets");
}

function issue_ippf_con_form($issue) {
  global $pprow, $item_count, $cell_count, $last_group;

  if ($issue) {
    $pprow = sqlQuery("SELECT * FROM lists_ippf_con WHERE id = '$issue'");
  } else {
    $pprow = array();
  }

  echo "<div id='ippf_con' style='display:none'>\n";

  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'CON' AND uor > 0 " .
    "ORDER BY group_name, seq");
  $last_group = '';
  $cell_count = 0;
  $item_count = 0;
  $display_style = 'block';

  while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_name'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];

    $currvalue  = '';
    if (isset($pprow[$field_id])) $currvalue = $pprow[$field_id];

    // Handle a data category (group) change.
    if (strcmp($this_group, $last_group) != 0) {
      end_group();
      $group_seq  = 'con' . substr($this_group, 0, 1);
      $group_name = substr($this_group, 1);
      $last_group = $this_group;
      echo "<br /><span class='bold'><input type='checkbox' name='form_cb_$group_seq' value='1' " .
        "onclick='return divclick(this,\"div_$group_seq\");'";
      if ($display_style == 'block') echo " checked";
      echo " /><b>$group_name</b></span>\n";
      echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
      echo " <table border='0' cellpadding='0' width='100%'>\n";
      $display_style = 'none';
    }

    // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
      end_row();
      echo " <tr>";
    }

    if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

    // Handle starting of a new label cell.
    if ($titlecols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$titlecols' width='1%' nowrap";
      echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
      if ($cell_count == 2) echo " style='padding-left:10pt'";
      echo ">";
      $cell_count += $titlecols;
    }
    ++$item_count;

    echo "<b>";
    if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";
    echo "</b>";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
      end_cell();
      echo "<td valign='top' colspan='$datacols' class='text'";
      if ($cell_count > 0) echo " style='padding-left:5pt'";
      echo ">";
      $cell_count += $datacols;
    }

    ++$item_count;
    generate_form_field($frow, $currvalue);
  }

  end_group();
  echo "</div>\n";
}
?>