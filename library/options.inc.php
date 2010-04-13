<?php
// Copyright (C) 2007-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Functions for managing the lists and layouts
//
// Note: there are translation wrappers for the lists and layout labels
//   at library/translation.inc.php. The functions are titled
//   xl_list_label() and xl_layout_label() and are controlled by the
//   $GLOBALS['translate_lists'] and $GLOBALS['translate_layout']
//   flags in globals.php

require_once("formdata.inc.php");
require_once("formatting.inc.php");

$date_init = "";

function get_pharmacies() {
  return sqlStatement("SELECT d.id, d.name, a.line1, a.city, " .
    "p.area_code, p.prefix, p.number FROM pharmacies AS d " .
    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
    "AND p.type = 2 " .
    "ORDER BY name, area_code, prefix, number");
}

// Function to generate a drop-list.
//
function generate_select_list($tag_name, $list_id, $currvalue, $title,
  $empty_name=' ', $class='', $onchange='')
{
  $s = '';
  $s .= "<select name='$tag_name' id='$tag_name'";
  if ($class) $s .= " class='$class'";
  if ($onchange) $s .= " onchange='$onchange'";
  $s .= " title='$title'>";
  if ($empty_name) $s .= "<option value=''>" . xl($empty_name) . "</option>";
  $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = '$list_id' ORDER BY seq, title");
  $got_selected = FALSE;
  while ($lrow = sqlFetchArray($lres)) {
    $s .= "<option value='" . $lrow['option_id'] . "'";
    if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
        (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
    {
      $s .= " selected";
      $got_selected = TRUE;
    }
    $s .= ">" . xl_list_label($lrow['title']) . "</option>\n";
  }
  if (!$got_selected && strlen($currvalue) > 0) {
    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);
    $s .= "<option value='$currescaped' selected>* $currescaped *</option>";
    $s .= "</select>";
    $s .= " <font color='red' title='" .
      xl('Please choose a valid selection from the list.') . "'>" .
      xl('Fix this') . "!</font>";
  }
  else {
    $s .= "</select>";
  }
  return $s;
}

function generate_form_field($frow, $currvalue) {
  global $rootdir, $date_init;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];

  // Added 5-09 by BM - Translate description if applicable  
  $description = htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES);
      
  // added 5-2009 by BM to allow modification of the 'empty' text title field.
  //  Can pass $frow['empty_title'] with this variable, otherwise
  //  will default to 'Unassigned'.
  // modified 6-2009 by BM to allow complete skipping of the 'empty' text title
  //  if make $frow['empty_title'] equal to 'SKIP'
  $showEmpty = true;
  if (isset($frow['empty_title'])) {
   if ($frow['empty_title'] == "SKIP") {
    //do not display an 'empty' choice
    $showEmpty = false;
    $empty_title = "Unassigned";
   }
   else {     
    $empty_title = $frow['empty_title'];
   }
  }
  else {
   $empty_title = "Unassigned";   
  }
    
  // generic single-selection list
  if ($data_type == 1) {
    echo generate_select_list("form_$field_id", $list_id, $currvalue,
      $description, $showEmpty ? $empty_title : '');
  }

  // simple text field
  else if ($data_type == 2) {
    echo "<input type='text'" .
      " name='form_$field_id'" .
      " id='form_$field_id'" .
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
      " id='form_$field_id'" .
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
    echo "<select name='form_$field_id' id='form_$field_id' title='$description'>";
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
    echo "<select name='form_$field_id' id='form_$field_id' title='$description'>";
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
    echo "<select name='form_$field_id' id='form_$field_id' title='$description'>";
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
    echo "<select name='form_$field_id' id='form_$field_id' title='$description'>";
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

  // Address book, preferring organization name if it exists and is not in
  // parentheses, and excluding local users who are not providers.
  // Supports "referred to" practitioners and facilities.
  // Alternatively the letter O in edit_options means that abook_type
  // must begin with "ord_", indicating types used with the procedure
  // ordering system.
  // Alternatively the letter V in edit_options means that abook_type
  // must be "vendor", indicating the Vendor type.
  else if ($data_type == 14) {
    if (strpos($frow['edit_options'], 'O') !== FALSE)
      $tmp = "abook_type LIKE 'ord\\_%'";
    else if (strpos($frow['edit_options'], 'V') !== FALSE)
      $tmp = "abook_type LIKE 'vendor%'";
    else
      $tmp = "( username = '' OR authorized = 1 )";
    $ures = sqlStatement("SELECT id, fname, lname, organization, username FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND $tmp " .
      "ORDER BY organization, lname, fname");
    echo "<select name='form_$field_id' id='form_$field_id' title='$description'>";
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['organization'];
      if (empty($uname) || substr($uname, 0, 1) == '(') {
        $uname = $urow['lname'];
        if ($urow['fname']) $uname .= ", " . $urow['fname'];
      }
      echo "<option value='" . $urow['id'] . "'";
      $title = $urow['username'] ? xl('Local') : xl('External');
      echo " title='$title'";
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
      "WHERE list_id = '$list_id' ORDER BY seq, title");
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
      echo "<input type='checkbox' name='form_{$field_id}[$option_id]' id='form_{$field_id}[$option_id]' value='1'";
      if (in_array($option_id, $avalue)) echo " checked";

      // Added 5-09 by BM - Translate label if applicable
      echo ">" . xl_list_label($lrow['title']);
	
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
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	
      echo "<td><input type='text'" .
        " name='form_{$field_id}[$option_id]'" .
        " id='form_{$field_id}[$option_id]'" .
        " size='$fldlength'" .
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
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp;</td><td class='bold'>" . xl('N/A') .
      "&nbsp;</td><td class='bold'>" . xl('Nor') . "&nbsp;</td>" .
      "<td class='bold'>" . xl('Abn') . "&nbsp;</td><td class='bold'>" .
      xl('Date/Notes') . "</td></tr>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
	
      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	
      for ($i = 0; $i < 3; ++$i) {
        echo "<td><input type='radio'" .
          " name='radio_{$field_id}[$option_id]'" .
          " id='radio_{$field_id}[$option_id]'" .
          " value='$i'";
        if ($restype === "$i") echo " checked";
        echo " /></td>";
      }
      echo "<td><input type='text'" .
        " name='form_{$field_id}[$option_id]'" .
        " id='form_{$field_id}[$option_id]'" .
        " size='$fldlength'" .
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

  // a set of labeled checkboxes, each with a text field:
  else if ($data_type == 25) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);

      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	
      echo "<td><input type='checkbox' name='check_{$field_id}[$option_id]' id='check_{$field_id}[$option_id]' value='1'";
      if ($restype) echo " checked";
      echo " />&nbsp;</td>";
      echo "<td><input type='text'" .
        " name='form_{$field_id}[$option_id]'" .
        " id='form_{$field_id}[$option_id]'" .
        " size='$fldlength'" .
        " maxlength='$maxlength'" .
        " value='$resnote' /></td>";
      echo "</tr>";
    }
    echo "</table>";
  }
  
  // single-selection list with ability to add to it
  else if ($data_type == 26) {
    echo "<select class='addtolistclass_$list_id' name='form_$field_id' id='form_$field_id' title='$description'>";
    if ($showEmpty) echo "<option value=''>" . xl($empty_title) . "</option>";
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    $got_selected = FALSE;
    while ($lrow = sqlFetchArray($lres)) {
      echo "<option value='" . $lrow['option_id'] . "'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
      {
        echo " selected";
        $got_selected = TRUE;
      }
      // Added 5-09 by BM - Translate label if applicable
      echo ">" . xl_list_label($lrow['title']) . "</option>\n";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='$currescaped' selected>* $currescaped *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xl('Please choose a valid selection from the list.') . "'>" . xl('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
    // show the add button if user has access to correct list
    $outputAddButton = "<input type='button' id='addtolistid_".$list_id."' fieldid='form_".$field_id."' class='addtolist' value='" . xl('Add') . "'>";
    if (aco_exist('lists', $list_id)) {
     // a specific aco exist for this list, so ensure access
     if (acl_check('lists', $list_id)) echo $outputAddButton;
    }
    else {
     // no specific aco exist for this list, so check for access to 'default' list
     if (acl_check('lists', 'default')) echo $outputAddButton;	
    }
  }

  // a set of labeled radio buttons
  else if ($data_type == 27) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $frow['fld_length']);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    $got_selected = FALSE;
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='radio' name='form_{$field_id}' id='form_{$field_id}[$option_id]' value='$option_id'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $option_id == $currvalue))
      {
        echo " checked";
        $got_selected = TRUE;
      }
      echo ">" . xl_list_label($lrow['title']);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of radio buttons.
        echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
      }
    }
    echo "</table>";
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "$currvalue <font color='red' title='" . xl('Please choose a valid selection.') . "'>" . xl('Fix this') . "!</font>";
    }
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  else if ($data_type == 28) {
    $tmp = explode('|', $currvalue);
    switch(count($tmp)) {
      case "3": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = $tmp[2];
      } break;
      case "2": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = "";
      } break;
      case "1": {
        $resnote = $tmp[0];
        $resdate = $restype = "";
      } break;
      default: {
        $restype = $resdate = $resnote = "";
      } break;
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
    
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr>";
	// input text 
    echo "<td><input type='text'" .
      " name='form_$field_id'" .
      " id='form_$field_id'" .
      " size='$fldlength'" .
      " maxlength='$maxlength'" .
      " value='$resnote' />&nbsp;</td>";
	echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;".xl('Status').":&nbsp;</td>";
    // current
    echo "<td><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[current]'" .
      " value='current".$field_id."'";
    if ($restype == "current".$field_id) echo " checked";
    // echo " onclick=\"return clinical_alerts_popup(this.value,'Patient_History')\" />".xl('Current')."&nbsp;</td>";
	echo "/>".xl('Current')."&nbsp;</td>";
    // quit
    echo "<td><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[quit]'" .
      " value='quit".$field_id."'";
    if ($restype == "quit".$field_id) echo " checked";
    echo "/>".xl('Quit')."&nbsp;</td>";
    // quit date
    echo "<td><input type='text' size='6' name='date_$field_id' id='date_$field_id'" .
      " value='$resdate'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . xl('Click here to choose a date') . "' />&nbsp;</td>";
    $date_init .= " Calendar.setup({inputField:'date_$field_id', ifFormat:'%Y-%m-%d', button:'img_$field_id'});\n";
    // never
    echo "<td><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[never]'" .
      " value='never".$field_id."'";
    if ($restype == "never".$field_id) echo " checked";
    echo " />".xl('Never')."&nbsp;</td>";
	// Not Applicable
    echo "<td><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[not_applicable]'" .
      " value='not_applicable".$field_id."'";
    if ($restype == "not_applicable".$field_id) echo " checked";
    echo " />".xl('N/A')."&nbsp;</td>";
    echo "</tr>";
    echo "</table>";
  }

}

function generate_print_field($frow, $currvalue) {
  global $rootdir, $date_init;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];
  $fld_length  = $frow['fld_length'];

  $description = htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES);
      
  // Can pass $frow['empty_title'] with this variable, otherwise
  //  will default to 'Unassigned'.
  // If it is 'SKIP' then an empty text title is completely skipped.
  $showEmpty = true;
  if (isset($frow['empty_title'])) {
    if ($frow['empty_title'] == "SKIP") {
      //do not display an 'empty' choice
      $showEmpty = false;
      $empty_title = "Unassigned";
    }
    else {     
      $empty_title = $frow['empty_title'];
    }
  }
  else {
    $empty_title = "Unassigned";   
  }

  // generic single-selection list
  if ($data_type == 1 || $data_type == 26) {
    if (empty($fld_length)) {
      if ($list_id == 'titles') {
        $fld_length = 3;
      } else {
        $fld_length = 10;
      }
    }
    $tmp = '';
    if ($currvalue) {
      $lrow = sqlQuery("SELECT title FROM list_options " .
        "WHERE list_id = '$list_id' AND option_id = '$currvalue'");
      $tmp = xl_list_label($lrow['title']);
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$tmp'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($tmp === '') $tmp = '&nbsp;';
    echo $tmp;
  }

  // simple text field
  else if ($data_type == 2 || $data_type == 15) {
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$currescaped'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($currescaped === '') $currescaped = '&nbsp;';
    echo $currescaped;
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    echo "<textarea" .
      " cols='$fld_length'" .
      " rows='" . $frow['max_length'] . "'>" .
      $currescaped . "</textarea>";
  }

  // date
  else if ($data_type == 4) {
    /*****************************************************************
    echo "<input type='text' size='10'" .
      " value='$currescaped'" .
      " title='$description'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($currescaped === '') $currescaped = '&nbsp;';
    echo oeFormatShortDate($currescaped);
  }

  // provider list
  else if ($data_type == 10 || $data_type == 11) {
    $tmp = '';
    if ($currvalue) {
      $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = '$currvalue'");
      $tmp = ucwords($urow['fname'] . " " . $urow['lname']);
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$tmp'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($tmp === '') $tmp = '&nbsp;';
    echo $tmp;
  }

  // pharmacy list
  else if ($data_type == 12) {
    $tmp = '';
    if ($currvalue) {
      $pres = get_pharmacies();
      while ($prow = sqlFetchArray($pres)) {
        $key = $prow['id'];
        if ($currvalue == $key) {
          $tmp = $prow['name'] . ' ' . $prow['area_code'] . '-' .
            $prow['prefix'] . '-' . $prow['number'] . ' / ' .
            $prow['line1'] . ' / ' . $prow['city'];
        }
      }
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$tmp'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($tmp === '') $tmp = '&nbsp;';
    echo $tmp;
  }

  // squads
  else if ($data_type == 13) {
    $tmp = '';
    if ($currvalue) {
      $squads = acl_get_squads();
      if ($squads) {
        foreach ($squads as $key => $value) {
          if ($currvalue == $key) {
            $tmp = $value[3];
          }
        }
      }
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$tmp'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($tmp === '') $tmp = '&nbsp;';
    echo $tmp;
  }

  // Address book.
  else if ($data_type == 14) {
    $tmp = '';
    if ($currvalue) {
      $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = '$currvalue'");
      $uname = $urow['lname'];
      if ($urow['fname']) $uname .= ", " . $urow['fname'];
      $tmp = $uname;
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    /*****************************************************************
    echo "<input type='text'" .
      " size='$fld_length'" .
      " value='$tmp'" .
      " class='under'" .
      " />";
    *****************************************************************/
    if ($tmp === '') $tmp = '&nbsp;';
    echo $tmp;
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $fld_length);
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='checkbox'";
      if (in_array($option_id, $avalue)) echo " checked";
      echo ">" . xl_list_label($lrow['title']);
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
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($fld_length) ?  20 : $fld_length;
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	      echo "<td><input type='text'" .
        " size='$fldlength'" .
        " value='" . $avalue[$option_id] . "'" .
        " class='under'" .
        " /></td></tr>";
    }
    echo "</table>";
  }

  // a set of exam results; 3 radio buttons and a text field:
  else if ($data_type == 23) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($fld_length) ?  20 : $fld_length;
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp;</td><td class='bold'>" . xl('N/A') .
      "&nbsp;</td><td class='bold'>" . xl('Nor') . "&nbsp;</td>" .
      "<td class='bold'>" . xl('Abn') . "&nbsp;</td><td class='bold'>" .
      xl('Date/Notes') . "</td></tr>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
      for ($i = 0; $i < 3; ++$i) {
        echo "<td><input type='radio'";
        if ($restype === "$i") echo " checked";
        echo " /></td>";
      }
      echo "<td><input type='text'" .
        " size='$fldlength'" .
        " value='$resnote'" .
        " class='under' /></td>" .
        "</tr>";
    }
    echo "</table>";
  }

  // the list of active allergies for the current patient
  // this is read-only!
  else if ($data_type == 24) {
    $query = "SELECT title, comments FROM lists WHERE " .
      "pid = '" . $GLOBALS['pid'] . "' AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    $lres = sqlStatement($query);
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) echo "<br />";
      echo $lrow['title'];
      if ($lrow['comments']) echo ' (' . $lrow['comments'] . ')';
    }
  }

  // a set of labeled checkboxes, each with a text field:
  else if ($data_type == 25) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($fld_length) ?  20 : $fld_length;
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      echo "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
      echo "<td><input type='checkbox'";
      if ($restype) echo " checked";
      echo " />&nbsp;</td>";
      echo "<td><input type='text'" .
        " size='$fldlength'" .
        " value='$resnote'" .
        " class='under'" .
        " /></td>" .
        "</tr>";
    }
    echo "</table>";
  }

  // a set of labeled radio buttons
  else if ($data_type == 27) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $frow['fld_length']);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='radio'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $option_id == $currvalue))
      {
        echo " checked";
      }
      echo ">" . xl_list_label($lrow['title']);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of radio buttons.
        echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
      }
    }
    echo "</table>";
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  else if ($data_type == 28) {
    $tmp = explode('|', $currvalue);
	switch(count($tmp)) {
      case "3": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = $tmp[2];
      } break;
      case "2": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = "";
      } break;
      case "1": {
        $resnote = $tmp[0];
        $resdate = $restype = "";
      } break;
      default: {
        $restype = $resdate = $resnote = "";
      } break;
    }
    $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
    $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr>";
	
    echo "<td><input type='text'" .
      " size='$fldlength'" .
      " class='under'" .
      " value='$resnote' /></td>";
	echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;".xl('Status').":&nbsp;</td>";  
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo "/>".xl('Current')."&nbsp;</td>";
    
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo "/>".xl('Quit')."&nbsp;</td>";
    
    echo "<td><input type='text' size='6'" .
      " value='$resdate'" .
      " class='under'" .
      " /></td>";
    
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo " />".xl('Never')."</td>";
	
    echo "<td><input type='radio'";
    if ($restype == "not_applicable".$field_id) echo " checked";
    echo " />".xl('N/A')."&nbsp;</td>";
    echo "</tr>";
    echo "</table>";
  }

}

function generate_display_field($frow, $currvalue) {
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $s = '';

  // generic selection list or the generic selection list with add on the fly
  // feature, or radio buttons
  if ($data_type == 1 || $data_type == 26 || $data_type == 27) {
    $lrow = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = '$list_id' AND option_id = '$currvalue'");
      $s = xl_list_label($lrow['title']);
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
    $s = oeFormatShortDate($currvalue);
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
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (in_array($option_id, $avalue)) {
        if ($count++) $s .= "<br />";
	  
	// Added 5-09 by BM - Translate label if applicable
        $s .= xl_list_label($lrow['title']);
	    
      }
    }
  }

  // a set of labeled text input fields
  else if ($data_type == 22) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (empty($avalue[$option_id])) continue;
	
      // Added 5-09 by BM - Translate label if applicable
      $s .= "<tr><td class='bold' valign='top'>" . xl_list_label($lrow['title']) . ":&nbsp;</td>";
	  
      $s .= "<td class='text' valign='top'>" . $avalue[$option_id] . "</td></tr>";
    }
    $s .= "</table>";
  }

  // a set of exam results; 3 radio buttons and a text field:
  else if ($data_type == 23) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
	
      // Added 5-09 by BM - Translate label if applicable
      $s .= "<tr><td class='bold' valign='top'>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	
      $restype = ($restype == '1') ? xl('Normal') : (($restype == '2') ? xl('Abnormal') : xl('N/A'));
      // $s .= "<td class='text' valign='top'>$restype</td></tr>";
      // $s .= "<td class='text' valign='top'>$resnote</td></tr>";
      $s .= "<td class='text' valign='top'>$restype&nbsp;</td>";
      $s .= "<td class='text' valign='top'>$resnote</td>";
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

  // a set of labeled checkboxes, each with a text field:
  else if ($data_type == 25) {
    $tmp = explode('|', $currvalue);
    $avalue = array();
    foreach ($tmp as $value) {
      if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
        $avalue[$matches[1]] = $matches[2];
      }
    }
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = '$list_id' ORDER BY seq, title");
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
	
      // Added 5-09 by BM - Translate label if applicable	
      $s .= "<tr><td class='bold' valign='top'>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
	
      $restype = $restype ? xl('Yes') : xl('No');  
      $s .= "<td class='text' valign='top'>$restype</td></tr>";
      $s .= "<td class='text' valign='top'>$resnote</td></tr>";
      $s .= "</tr>";
    }
    $s .= "</table>";
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  else if ($data_type == 28) {
    $tmp = explode('|', $currvalue);
    switch(count($tmp)) {
      case "3": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = $tmp[2];
      } break;
      case "2": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = "";
      } break;
      case "1": {
        $resnote = $tmp[0];
        $resdate = $restype = "";
      } break;
      default: {
        $restype = $resdate = $resnote = "";
      } break;
    }
    $s .= "<table cellpadding='0' cellspacing='0'>";
      
    $s .= "<tr>";
	$res = "";
    if ($restype == "current".$field_id) $res = xl('Current');
	if ($restype == "quit".$field_id) $res = xl('Quit');
	if ($restype == "never".$field_id) $res = xl('Never');
	if ($restype == "not_applicable".$field_id) $res = xl('N/A');
    // $s .= "<td class='text' valign='top'>$restype</td></tr>";
    // $s .= "<td class='text' valign='top'>$resnote</td></tr>";
    if (!empty($resnote)) $s .= "<td class='text' valign='top'>$resnote&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
	if (!empty($res)) $s .= "<td class='text' valign='top'><b>".xl('Status')."</b>:&nbsp;".$res."&nbsp;</td>";
    if ($restype == "quit".$field_id) $s .= "<td class='text' valign='top'>$resdate&nbsp;</td>";
    $s .= "</tr>";
    $s .= "</table>";
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
        // Skip employer related fields, if it's disabled.
        if ($GLOBALS['omit_employers']) continue;
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
      $group_name = substr($this_group, 1);
      // totally skip generating the employer category, if it's disabled.
      if ($group_name === 'Employer' && $GLOBALS['omit_employers']) continue;
      disp_end_group();
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
	
        // Added 5-09 by BM - Translate label if applicable
        echo (xl_layout_label($group_name));
	  
        $group_name = '';
      } else {
        //echo "<td class='' style='padding-right:5pt' valign='top'>";
        echo "<td valign='top'>&nbsp;";
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

    // Added 5-09 by BM - Translate label if applicable
    if ($frow['title']) echo (xl_layout_label($frow['title']).":"); else echo "&nbsp;";

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

function display_layout_tabs($formtype, $result1, $result2='') {
  global $item_count, $cell_count, $last_group, $CPR;

  $fres = sqlStatement("SELECT distinct group_name FROM layout_options " .
    "WHERE form_id = '$formtype' AND uor > 0 " .
    "ORDER BY group_name, seq");

  $first = true;
  while ($frow = sqlFetchArray($fres)) {
	  $this_group = $frow['group_name'];
      $group_name = substr($this_group, 1);
      ?>
		<li <?php echo $first ? 'class="current"' : '' ?>>
			<a href="/play/javascript-tabbed-navigation/" id="header_tab_<?php echo $group_name?>"><?php echo xl_layout_label($group_name); ?></a>
		</li>
	  <?php
	  $first = false;
  }
}

function display_layout_tabs_data($formtype, $result1, $result2='') {
  global $item_count, $cell_count, $last_group, $CPR;

  $fres = sqlStatement("SELECT distinct group_name FROM layout_options " .
    "WHERE form_id = '$formtype' AND uor > 0 " .
    "ORDER BY group_name, seq");

	$first = true;
	while ($frow = sqlFetchArray($fres)) {
		$this_group = $frow['group_name'];
		$titlecols  = $frow['titlecols'];
		$datacols   = $frow['datacols'];
		$data_type  = $frow['data_type'];
		$field_id   = $frow['field_id'];
		$list_id    = $frow['list_id'];
		$currvalue  = '';

		$group_fields_query = sqlStatement("SELECT * FROM layout_options " .
		"WHERE form_id = '$formtype' AND uor > 0 AND group_name = '$this_group' " .
		"ORDER BY seq");
	?>

		<div class="tab <?php echo $first ? 'current' : '' ?>">
			<table border='0' cellpadding='0'>

			<?php
				while ($group_fields = sqlFetchArray($group_fields_query)) {

					$titlecols  = $group_fields['titlecols'];
					$datacols   = $group_fields['datacols'];
					$data_type  = $group_fields['data_type'];
					$field_id   = $group_fields['field_id'];
					$list_id    = $group_fields['list_id'];
					$currvalue  = '';

					if ($formtype == 'DEM') {
					  if ($GLOBALS['athletic_team']) {
						// Skip fitness level and return-to-play date because those appear
						// in a special display/update form on this page.
						if ($field_id === 'fitness' || $field_id === 'userdate1') continue;
					  }
					  if (strpos($field_id, 'em_') === 0) {
					// Skip employer related fields, if it's disabled.
						if ($GLOBALS['omit_employers']) continue;
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
					  $group_name = substr($this_group, 1);
					  // totally skip generating the employer category, if it's disabled.
					  if ($group_name === 'Employer' && $GLOBALS['omit_employers']) continue;
					  $last_group = $this_group;
					}

					// Handle starting of a new row.
					if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
					  disp_end_row();
					  echo "<tr>";
					}

					if ($item_count == 0 && $titlecols == 0) {
						$titlecols = 1;
					}

					// Handle starting of a new label cell.
					if ($titlecols > 0) {
					  disp_end_cell();
					  echo "<td class='label' colspan='$titlecols' ";
					  echo ">";
					  $cell_count += $titlecols;
					}
					++$item_count;

					// Added 5-09 by BM - Translate label if applicable
					if ($group_fields['title']) echo (xl_layout_label($group_fields['title']).":"); else echo "&nbsp;";

					// Handle starting of a new data cell.
					if ($datacols > 0) {
					  disp_end_cell();
					  echo "<td class='text data' colspan='$datacols'";
					  echo ">";
					  $cell_count += $datacols;
					}

					++$item_count;
					echo generate_display_field($group_fields, $currvalue);
				  }
			?>

			</table>
		</div>

 	 <?php

	$first = false;

	}

}

function display_layout_tabs_data_editable($formtype, $result1, $result2='') {
  global $item_count, $cell_count, $last_group, $CPR;

  $fres = sqlStatement("SELECT distinct group_name FROM layout_options " .
    "WHERE form_id = '$formtype' AND uor > 0 " .
    "ORDER BY group_name, seq");

	$first = true;
	while ($frow = sqlFetchArray($fres)) {
		$this_group = $frow['group_name'];
		$group_name = substr($this_group, 1);
		$titlecols  = $frow['titlecols'];
		$datacols   = $frow['datacols'];
		$data_type  = $frow['data_type'];
		$field_id   = $frow['field_id'];
		$list_id    = $frow['list_id'];
		$currvalue  = '';

		$group_fields_query = sqlStatement("SELECT * FROM layout_options " .
		"WHERE form_id = '$formtype' AND uor > 0 AND group_name = '$this_group' " .
		"ORDER BY seq");
	?>

		<div class="tab <?php echo $first ? 'current' : '' ?>" id="tab_<?php echo $group_name?>" >
			<table border='0' cellpadding='0'>

			<?php
				while ($group_fields = sqlFetchArray($group_fields_query)) {

					$titlecols  = $group_fields['titlecols'];
					$datacols   = $group_fields['datacols'];
					$data_type  = $group_fields['data_type'];
					$field_id   = $group_fields['field_id'];
					$list_id    = $group_fields['list_id'];
					$currvalue  = '';

					if ($formtype == 'DEM') {
					  if ($GLOBALS['athletic_team']) {
						// Skip fitness level and return-to-play date because those appear
						// in a special display/update form on this page.
						if ($field_id === 'fitness' || $field_id === 'userdate1') continue;
					  }
					  if (strpos($field_id, 'em_') === 0) {
					// Skip employer related fields, if it's disabled.
						if ($GLOBALS['omit_employers']) continue;
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
					  $group_name = substr($this_group, 1);
					  // totally skip generating the employer category, if it's disabled.
					  if ($group_name === 'Employer' && $GLOBALS['omit_employers']) continue;
					  $last_group = $this_group;
					}

					// Handle starting of a new row.
					if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
					  disp_end_row();
					  echo "<tr>";
					}

					if ($item_count == 0 && $titlecols == 0) {
						$titlecols = 1;
					}

					// Handle starting of a new label cell.
					if ($titlecols > 0) {
					  disp_end_cell();
					  echo "<td class='label' colspan='$titlecols' ";
					  echo ">";
					  $cell_count += $titlecols;
					}
					++$item_count;

					// Added 5-09 by BM - Translate label if applicable
					if ($group_fields['title']) echo (xl_layout_label($group_fields['title']).":"); else echo "&nbsp;";

					// Handle starting of a new data cell.
					if ($datacols > 0) {
					  disp_end_cell();
					  echo "<td class='text data' colspan='$datacols'";
					  echo ">";
					  $cell_count += $datacols;
					}

					++$item_count;
					echo generate_form_field($group_fields, $currvalue);
				  }
			?>

			</table>
		</div>

 	 <?php

	$first = false;

	}
}

// From the currently posted HTML form, this gets the value of the
// field corresponding to the provided layout_options table row.
//
function get_layout_form_value($frow, $maxlength=255) {
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
    else if ($data_type == 25) {
      // $_POST["form_$field_id"] is an array of text fields with companion
      // checkboxes to be imploded into "key:n:notes|key:n:notes|...".
      foreach ($_POST["form_$field_id"] as $key => $val) {
        $restype = empty($_POST["check_{$field_id}"][$key]) ? '0' : '1';
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$restype:$val";
      }
    }
    else if ($data_type == 28) {
      // $_POST["form_$field_id"] is an date text fields with companion
      // radio buttons to be imploded into "notes|type|date".
      $restype = $_POST["radio_{$field_id}"];
      if (empty($restype)) $restype = '0';
      $resdate = str_replace('|', ' ', $_POST["date_$field_id"]);
      $resnote = str_replace('|', ' ', $_POST["form_$field_id"]);
      $value = "$resnote|$restype|$resdate";
    }
    else {
      $value = $_POST["form_$field_id"];
    }
  }

  // Better to die than to silently truncate data!
  if ($maxlength && $data_type != 3 && strlen($value) > $maxlength)
    die(xl('ERROR: Field') . " '$field_id' " . xl('is too long') .
    ":<br />&nbsp;<br />$value");

  // Make sure the return value is quote-safe.
  return formTrim($value);
}

// Generate JavaScript validation logic for the required fields.
//
function generate_layout_validation($form_id) {
  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = '$form_id' AND uor > 0 AND field_id != '' " .
    "ORDER BY group_name, seq");

  while ($frow = sqlFetchArray($fres)) {
    if ($frow['uor'] < 2) continue;
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    $fldtitle  = $frow['title'];
    if (!$fldtitle) $fldtitle  = $frow['description'];
    $fldname   = "form_$field_id";
    switch($data_type) {
      case  1:
      case 11:
      case 12:
      case 13:
      case 14:
      case 26:
        echo
        " if (f.$fldname.selectedIndex <= 0) {\n" .
        "  if (f.$fldname.focus) f.$fldname.focus();\n" .
        "  		errMsgs[errMsgs.length] = '" . addslashes(xl_layout_label($fldtitle)) . "'; \n" .
        " }\n";
        break;
      case 27: // radio buttons
        echo
        " var i = 0;\n" .
        " for (; i < f.$fldname.length; ++i) if (f.$fldname[i].checked) break;\n" .
        " if (i >= f.$fldname.length) {\n" .
        "  		errMsgs[errMsgs.length] = '" . addslashes(xl_layout_label($fldtitle)) . "'; \n" .
        " }\n";
        break;
      case  2:
      case  3:
      case  4:
      case 15:
        echo
        " if (trimlen(f.$fldname.value) == 0) {\n" .
        "  		if (f.$fldname.focus) f.$fldname.focus();\n" .
		"  		$('#form_" . $field_id . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','red'); } ); " .
		"  		$('#form_" . $field_id . "').attr('style','background:red'); \n" .
        "  		errMsgs[errMsgs.length] = '" . addslashes(xl_layout_label($fldtitle)) . "'; \n" .
        " } else { " .
		" 		$('#form_" . $field_id . "').attr('style',''); " .
		"  		$('#form_" . $field_id . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','');  } ); " .
		" } \n";
        break;
    }
  }
}
?>
