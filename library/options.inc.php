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

  // generic selection list
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

  // address book
  else if ($data_type == 14) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['lname'];
      if ($urow['fname']) $uname .= ", " . $urow['fname'];
      echo "<option value='" . $urow['id'] . "'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$uname</option>";
    }
    echo "</select>";
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

  return $s;
}
?>
