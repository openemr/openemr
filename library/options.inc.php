<?php

$date_init = "";

function get_pharmacies() {
  return sqlStatement("SELECT d.id, d.name, a.line1, a.city, " .
    "p.area_code, p.prefix, p.number FROM pharmacies AS d " .
    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
    "AND p.type = 2 " .
    "ORDER BY name, area_code, prefix, number");
}

function generate_form_field($frow, $currvalue) {
  global $rootdir, $date_init;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];
  $description = htmlspecialchars($frow['description'], ENT_QUOTES);

  // generic single-selection list
  if ($data_type == 1) {
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    $got_selected = FALSE;
    while ($lrow = sqlFetchArray($lres)) {
      echo "<option value='" . $lrow['option_id'] . "'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
      {
        echo " selected";
        $got_selected = TRUE;
      }
      echo ">" . $lrow['title'] . "</option>\n";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='$currescaped' selected>* $currescaped *</option>";
      echo "</select>";
      echo " <font color='red' title='Please choose a valid selection " .
        "from the list'>Fix this!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // simple text field
  else if ($data_type == 2) {
    echo "<input type='text'" .
      " name='form_$field_id'" .
      " size='" . $frow['fld_length'] . "'" .
      " maxlength='" . $frow['max_length'] . "'" .
      " title='$description'" .
      " value='$currescaped'";
    if (strpos($frow['edit_options'], 'C') !== FALSE)
      echo " onchange='capitalizeMe(this)'";
    echo " />";
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    echo "<textarea" .
      " name='form_$field_id'" .
      " title='$description'" .
      " cols='" . $frow['fld_length'] . "'" .
      " rows='" . $frow['max_length'] . "'>" .
      $currescaped . "</textarea>";
  }

  // date
  else if ($data_type == 4) {
    echo "<input type='text' size='10' name='form_$field_id' id='form_$field_id'" .
      " value='$currescaped'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . xl('Click here to choose a date') . "' />";
    $date_init .= " Calendar.setup({inputField:'form_$field_id', ifFormat:'%Y-%m-%d', button:'img_$field_id'});\n";
  }

  // provider list, local providers only
  else if ($data_type == 10) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND authorized = 1 " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['fname'] . ' ' . $urow['lname'];
      echo "<option value='" . $urow['id'] . "'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$uname</option>";
    }
    echo "</select>";
  }

  // provider list, including address book entries with an NPI number
  else if ($data_type == 11) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND ( authorized = 1 OR ( username = '' AND npi != '' ) ) " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['fname'] . ' ' . $urow['lname'];
      echo "<option value='" . $urow['id'] . "'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$uname</option>";
    }
    echo "</select>";
  }

  // pharmacy list
  else if ($data_type == 12) {
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value='0'></option>";
    $pres = get_pharmacies();
    while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      echo "<option value='$key'";
      if ($currvalue == $key) echo " selected";
      echo '>' . $prow['name'] . ' ' . $prow['area_code'] . '-' .
        $prow['prefix'] . '-' . $prow['number'] . ' / ' .
        $prow['line1'] . ' / ' . $prow['city'] . "</option>";
    }
    echo "</select>";
  }

  // squads
  else if ($data_type == 13) {
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>&nbsp;</option>";
    $squads = acl_get_squads();
    if ($squads) {
      foreach ($squads as $key => $value) {
        echo "<option value='$key'";
        if ($currvalue == $key) echo " selected";
        echo ">" . $value[3] . "</option>\n";
      }
    }
    echo "</select>";
  }

  // address book, preferring organization name if it exists and is not in parentheses
  else if ($data_type == 14) {
    $ures = sqlStatement("SELECT id, fname, lname, organization FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "ORDER BY organization, lname, fname");
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['organization'];
      if (empty($uname) || substr($uname, 0, 1) == '(') {
        $uname = $urow['lname'];
        if ($urow['fname']) $uname .= ", " . $urow['fname'];
      }
      echo "<option value='" . $urow['id'] . "'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$uname</option>";
    }
    echo "</select>";
  }

  // a billing code (only one of these allowed!)
  else if ($data_type == 15) {
    echo "<input type='text'" .
      " name='form_$field_id'" .
      " id='form_related_code'" .
      " size='" . $frow['fld_length'] . "'" .
      " maxlength='" . $frow['max_length'] . "'" .
      " title='$description'" .
      " value='$currescaped'" .
      " onclick='sel_related()' readonly" .
      " />";
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $frow['fld_length']);
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      // if ($count) echo "<br />";
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='checkbox' name='form_{$field_id}[$option_id]' value='1'";
      if (in_array($option_id, $avalue)) echo " checked";
      echo ">" . $lrow['title'];
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of checkboxes.
        echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
      }
    }
    echo "</table>";
  }

  // a set of labeled text input fields
  else if ($data_type == 22) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^(\w+?):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
      echo "<tr><td>" . $lrow['title'] . "&nbsp;</td>";
      echo "<td><input type='text'" .
        " name='form_{$field_id}[$option_id]'" .
        " size='" . $frow['fld_length'] . "'" .
        " maxlength='$maxlength'" .
        " value='" . $avalue[$option_id] . "'";
      echo " /></td></tr>";
    }
    echo "</table>";
  }

  // a set of exam results; 3 radio buttons and a text field:
  else if ($data_type == 23) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^(\w+?):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp;</td><td class='bold'>N/A&nbsp;</td><td class='bold'>Nor&nbsp;</td>" .
      "<td class='bold'>Abn&nbsp;</td><td class='bold'>Date/Notes</td></tr>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      echo "<tr><td>" . $lrow['title'] . "&nbsp;</td>";
      for ($i = 0; $i < 3; ++$i) {
        echo "<td><input type='radio'" .
          " name='radio_{$field_id}[$option_id]'" .
          " value='$i'";
        if ($restype === "$i") echo " checked";
        echo " /></td>";
      }
      echo "<td><input type='text'" .
        " name='form_{$field_id}[$option_id]'" .
        " size='" . $frow['fld_length'] . "'" .
        " maxlength='$maxlength'" .
        " value='$resnote' /></td>";
      echo "</tr>";
    }
    echo "</table>";
  }

  // the list of active allergies for the current patient
  // this is read-only!
  else if ($data_type == 24) {
    $query = "SELECT title, comments FROM lists WHERE " .
      "pid = '" . $GLOBALS['pid'] . "' AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    // echo "<!-- $query -->\n"; // debugging
    $lres = sqlStatement($query);
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) echo "<br />";
      echo $lrow['title'];
      if ($lrow['comments']) echo ' (' . $lrow['comments'] . ')';
    }
  }

}

function generate_display_field($frow, $currvalue) {
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $s = '';

  // generic selection list
  if ($data_type == 1) {
    $lrow = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = '$list_id' AND option_id = '$currvalue'");
    $s = $lrow['title'];
  }

  // simple text field
  else if ($data_type == 2) {
    $s = $currvalue;
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    $s = nl2br($currvalue);
  }

  // date
  else if ($data_type == 4) {
    $s = $currvalue;
  }

  // provider
  else if ($data_type == 10 || $data_type == 11) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = '$currvalue'");
    $s = ucwords($urow['fname'] . " " . $urow['lname']);
  }

  // pharmacy list
  else if ($data_type == 12) {
    $pres = get_pharmacies();
    while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      if ($currvalue == $key) {
        $s .= $prow['name'] . ' ' . $prow['area_code'] . '-' .
          $prow['prefix'] . '-' . $prow['number'] . ' / ' .
          $prow['line1'] . ' / ' . $prow['city'];
      }
    }
  }

  // squads
  else if ($data_type == 13) {
    $squads = acl_get_squads();
    if ($squads) {
      foreach ($squads as $key => $value) {
        if ($currvalue == $key) {
          $s .= $value[3];
        }
      }
    }
  }

  // address book
  else if ($data_type == 14) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = '$currvalue'");
    $uname = $urow['lname'];
    if ($urow['fname']) $uname .= ", " . $urow['fname'];
    $s = $uname;
  }

  // billing code
  else if ($data_type == 15) {
    $s = $currvalue;
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (in_array($option_id, $avalue)) {
        if ($count++) $s .= "<br />";
        $s .= $lrow['title'];
      }
    }
  }

  // a set of labeled text input fields
  else if ($data_type == 22) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^(\w+?):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (empty($avalue[$option_id])) continue;
      $s .= "<tr><td class='bold' valign='top'>" . $lrow['title'] . ":&nbsp;</td>";
      $s .= "<td class='text' valign='top'>" . $avalue[$option_id] . "</td></tr>";
    }
    $s .= "</table>";
  }

  // a set of exam results; 3 radio buttons and a text field:
  else if ($data_type == 23) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^(\w+?):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq");
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
      $s .= "<tr><td class='bold' valign='top'>" . $lrow['title'] . "&nbsp;</td>";
      $restype = ($restype == '1') ? 'Normal' : (($restype == '2') ? 'Abnormal' : 'N/A');
      $s .= "<td class='text' valign='top'>$restype</td></tr>";
      $s .= "<td class='text' valign='top'>$resnote</td></tr>";
      $s .= "</tr>";
    }
    $s .= "</table>";
  }

  // the list of active allergies for the current patient
  else if ($data_type == 24) {
    $query = "SELECT title, comments FROM lists WHERE " .
      "pid = '" . $GLOBALS['pid'] . "' AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    // echo "<!-- $query -->\n"; // debugging
    $lres = sqlStatement($query);
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) $s .= "<br />";
      $s .= $lrow['title'];
      if ($lrow['comments']) $s .= ' (' . $lrow['comments'] . ')';
    }
  }

  return $s;
}

$CPR = 4; // cells per row of generic data
$last_group = '';
$cell_count = 0;
$item_count = 0;

function disp_end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function disp_end_row() {
  global $cell_count, $CPR;
  disp_end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function disp_end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    disp_end_row();
  }
}

function display_layout_rows($formtype, $result1, $result2='') {
  global $item_count, $cell_count, $last_group, $CPR;

  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = '$formtype' AND uor > 0 " .
    "ORDER BY group_name, seq");

  while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_name'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];
    $currvalue  = '';

    if ($formtype == 'DEM') {
      if ($GLOBALS['athletic_team']) {
        // Skip fitness level and return-to-play date because those appear
        // in a special display/update form on this page.
        if ($field_id === 'fitness' || $field_id === 'userdate1') continue;
      }
      if (strpos($field_id, 'em_') === 0) {
        $tmp = substr($field_id, 3);
        if (isset($result2[$tmp])) $currvalue = $result2[$tmp];
      }
      else {
        if (isset($result1[$field_id])) $currvalue = $result1[$field_id];
      }
    }
    else {
      if (isset($result1[$field_id])) $currvalue = $result1[$field_id];
    }

    // Handle a data category (group) change.
    if (strcmp($this_group, $last_group) != 0) {
      disp_end_group();
      $group_name = substr($this_group, 1);
      $last_group = $this_group;
    }

    // Handle starting of a new row.
    if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
      disp_end_row();
      echo "<tr>";
      if ($group_name) {
        echo "<td class='groupname'>";
        //echo "<td class='groupname' style='padding-right:5pt' valign='top'>";
        //echo "<font color='#008800'>$group_name</font>";
        echo $group_name;
        $group_name = '';
      } else {
        //echo "<td class='' style='padding-right:5pt' valign='top'>";
        echo '<td>&nbsp;';
      }
      echo "</td>";
    }

    if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

    // Handle starting of a new label cell.
    if ($titlecols > 0) {
      disp_end_cell();
      //echo "<td class='label' colspan='$titlecols' valign='top'";
      echo "<td class='label' colspan='$titlecols' ";
      //if ($cell_count == 2) echo " style='padding-left:10pt'";
      echo ">";
      $cell_count += $titlecols;
    }
    ++$item_count;

    if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
      disp_end_cell();
      //echo "<td class='text data' colspan='$datacols' valign='top'";
      echo "<td class='text data' colspan='$datacols'";
      //if ($cell_count > 0) echo " style='padding-left:5pt'";
      echo ">";
      $cell_count += $datacols;
    }

    ++$item_count;
    echo generate_display_field($frow, $currvalue);
  }

  disp_end_group();
}

// From the currently posted HTML form, this gets the value of the
// field corresponding to the provided layout_options table row.
//
function get_layout_form_value($frow) {
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  $value  = '';
  if (isset($_POST["form_$field_id"])) {
    if ($data_type == 21) {
      // $_POST["form_$field_id"] is an array of checkboxes and its keys
      // must be concatenated into a |-separated string.
      foreach ($_POST["form_$field_id"] as $key => $val) {
        if (strlen($value)) $value .= '|';
        $value .= $key;
      }
    }
    else if ($data_type == 22) {
      // $_POST["form_$field_id"] is an array of text fields to be imploded
      // into "key:value|key:value|...".
      foreach ($_POST["form_$field_id"] as $key => $val) {
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$val";
      }
    }
    else if ($data_type == 23) {
      // $_POST["form_$field_id"] is an array of text fields with companion
      // radio buttons to be imploded into "key:n:notes|key:n:notes|...".
      foreach ($_POST["form_$field_id"] as $key => $val) {
        $restype = $_POST["radio_{$field_id}"][$key];
        if (empty($restype)) $restype = '0';
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$restype:$val";
      }
    }
    else {
      $value = $_POST["form_$field_id"];
    }
  }
  return $value;
}
?>
