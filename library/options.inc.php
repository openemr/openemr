<?php
// Copyright (C) 2007-2010 Rod Roark <rod@sunsetsystems.com>
// Copyright © 2010 by Andrew Moore <amoore@cpan.org>
// Copyright © 2010 by "Boyd Stephen Smith Jr." <bss@iguanasuicide.net>
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

// Documentation for layout_options.edit_options:
//
// C = Capitalize first letter of each word (text fields)
// D = Check for duplicates in New Patient form
// G = Graphable (for numeric fields in forms supporting historical data)
// H = Read-only field copied from static history
// L = Lab Order ("ord_lab") types only (address book)
// N = Show in New Patient form
// O = Procedure Order ("ord_*") types only (address book)
// R = Distributor types only (address book)
// U = Capitalize all letters (text fields)
// V = Vendor types only (address book)
// 1 = Write Once (not editable when not empty) (text fields)

require_once("formdata.inc.php");
require_once("formatting.inc.php");
require_once("user.inc");

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
  $tag_name_esc = htmlspecialchars( $tag_name, ENT_QUOTES);
  $s .= "<select name='$tag_name_esc' id='$tag_name_esc'";
  if ($class) $s .= " class='$class'";
  if ($onchange) $s .= " onchange='$onchange'";
  $selectTitle = htmlspecialchars( $title, ENT_QUOTES);
  $s .= " title='$selectTitle'>";
  $selectEmptyName = htmlspecialchars( xl($empty_name), ENT_NOQUOTES);
  if ($empty_name) $s .= "<option value=''>" . $selectEmptyName . "</option>";
  $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
  $got_selected = FALSE;
  while ($lrow = sqlFetchArray($lres)) {
    $optionValue = htmlspecialchars( $lrow['option_id'], ENT_QUOTES);
    $s .= "<option value='$optionValue'";
    if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
        (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
    {
      $s .= " selected";
      $got_selected = TRUE;
    }
    $optionLabel = htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
    $s .= ">$optionLabel</option>\n";
  }
  if (!$got_selected && strlen($currvalue) > 0) {
    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);
    $s .= "<option value='$currescaped' selected>* $currescaped *</option>";
    $s .= "</select>";
    $fontTitle = htmlspecialchars( xl('Please choose a valid selection from the list.'), ENT_QUOTES);
    $fontText = htmlspecialchars( xl('Fix this'), ENT_NOQUOTES);
    $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
  }
  else {
    $s .= "</select>";
  }
  return $s;
}

// $frow is a row from the layout_options table.
// $currvalue is the current value, if any, of the associated item.
//
function generate_form_field($frow, $currvalue) {
  global $rootdir, $date_init;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];
  // escaped variables to use in html
  $field_id_esc= htmlspecialchars( $field_id, ENT_QUOTES);
  $list_id_esc = htmlspecialchars( $list_id, ENT_QUOTES);

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
    $fldlength = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $maxlength = htmlspecialchars( $frow['max_length'], ENT_QUOTES);
    echo "<input type='text'" .
      " name='form_$field_id_esc'" .
      " id='form_$field_id_esc'" .
      " size='$fldlength'" .
      " maxlength='$maxlength'" .
      " title='$description'" .
      " value='$currescaped'";
    if (strpos($frow['edit_options'], 'C') !== FALSE)
      echo " onchange='capitalizeMe(this)'";
    else if (strpos($frow['edit_options'], 'U') !== FALSE)
      echo " onchange='this.value = this.value.toUpperCase()'";
    $tmp = htmlspecialchars( $GLOBALS['gbl_mask_patient_id'], ENT_QUOTES);
    if ($field_id == 'pubpid' && strlen($tmp) > 0) {
      echo " onkeyup='maskkeyup(this,\"$tmp\")'";
      echo " onblur='maskblur(this,\"$tmp\")'";
    }
    if (strpos($frow['edit_options'], '1') !== FALSE && strlen($currescaped) > 0)
      echo " readonly";
    echo " />";
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    $textCols = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $textRows = htmlspecialchars( $frow['max_length'], ENT_QUOTES);
    echo "<textarea" .
      " name='form_$field_id_esc'" .
      " id='form_$field_id_esc'" .
      " title='$description'" .
      " cols='$textCols'" .
      " rows='$textRows'>" .
      $currescaped . "</textarea>";
  }

  // date
  else if ($data_type == 4) {
    echo "<input type='text' size='10' name='form_$field_id_esc' id='form_$field_id_esc'" .
      " value='$currescaped'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id_esc' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />";
    $date_init .= " Calendar.setup({inputField:'form_$field_id', ifFormat:'%Y-%m-%d', button:'img_$field_id'});\n";
  }

  // provider list, local providers only
  else if ($data_type == 10) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND authorized = 1 " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value=''>" . htmlspecialchars( xl('Unassigned'), ENT_NOQUOTES) . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = htmlspecialchars( $urow['fname'] . ' ' . $urow['lname'], ENT_NOQUOTES);
      $optionId = htmlspecialchars( $urow['id'], ENT_QUOTES);
      echo "<option value='$optionId'";
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
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value=''>" . htmlspecialchars( xl('Unassigned'), ENT_NOQUOTES) . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = htmlspecialchars( $urow['fname'] . ' ' . $urow['lname'], ENT_NOQUOTES);
      $optionId = htmlspecialchars( $urow['id'], ENT_QUOTES);
      echo "<option value='$optionId'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$uname</option>";
    }
    echo "</select>";
  }

  // pharmacy list
  else if ($data_type == 12) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value='0'></option>";
    $pres = get_pharmacies();
    while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      $optionValue = htmlspecialchars( $key, ENT_QUOTES);
      $optionLabel = htmlspecialchars( $prow['name'] . ' ' . $prow['area_code'] . '-' .
        $prow['prefix'] . '-' . $prow['number'] . ' / ' .
	$prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
      echo "<option value='$optionValue'";
      if ($currvalue == $key) echo " selected";
      echo ">$optionLabel</option>";
    }
    echo "</select>";
  }

  // squads
  else if ($data_type == 13) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value=''>&nbsp;</option>";
    $squads = acl_get_squads();
    if ($squads) {
      foreach ($squads as $key => $value) {
	$optionValue = htmlspecialchars( $key, ENT_QUOTES);
	$optionLabel = htmlspecialchars( $value[3], ENT_NOQUOTES);
        echo "<option value='$optionValue'";
        if ($currvalue == $key) echo " selected";
        echo ">$optionLabel</option>\n";
      }
    }
    echo "</select>";
  }

  // Address book, preferring organization name if it exists and is not in
  // parentheses, and excluding local users who are not providers.
  // Supports "referred to" practitioners and facilities.
  // Alternatively the letter L in edit_options means that abook_type
  // must be "ord_lab", indicating types used with the procedure
  // lab ordering system.
  // Alternatively the letter O in edit_options means that abook_type
  // must begin with "ord_", indicating types used with the procedure
  // ordering system.
  // Alternatively the letter V in edit_options means that abook_type
  // must be "vendor", indicating the Vendor type.
  // Alternatively the letter R in edit_options means that abook_type
  // must be "dist", indicating the Distributor type.
  else if ($data_type == 14) {
    if (strpos($frow['edit_options'], 'L') !== FALSE)
      $tmp = "abook_type = 'ord_lab'";
    else if (strpos($frow['edit_options'], 'O') !== FALSE)
      $tmp = "abook_type LIKE 'ord\\_%'";
    else if (strpos($frow['edit_options'], 'V') !== FALSE)
      $tmp = "abook_type LIKE 'vendor%'";
    else if (strpos($frow['edit_options'], 'R') !== FALSE)
      $tmp = "abook_type LIKE 'dist'";
    else
      $tmp = "( username = '' OR authorized = 1 )";
    $ures = sqlStatement("SELECT id, fname, lname, organization, username FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND $tmp " .
      "ORDER BY organization, lname, fname");
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value=''>" . htmlspecialchars( xl('Unassigned'), ENT_NOQUOTES) . "</option>";
    while ($urow = sqlFetchArray($ures)) {
      $uname = $urow['organization'];
      if (empty($uname) || substr($uname, 0, 1) == '(') {
        $uname = $urow['lname'];
        if ($urow['fname']) $uname .= ", " . $urow['fname'];
      }
      $optionValue = htmlspecialchars( $urow['id'], ENT_QUOTES);
      $optionLabel = htmlspecialchars( $uname, ENT_NOQUOTES);
      echo "<option value='$optionValue'";
      $title = $urow['username'] ? xl('Local') : xl('External');
      $optionTitle = htmlspecialchars( $title, ENT_QUOTES);
      echo " title='$optionTitle'";
      if ($urow['id'] == $currvalue) echo " selected";
      echo ">$optionLabel</option>";
    }
    echo "</select>";
  }

  // a billing code
  else if ($data_type == 15) {
    $fldlength = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $maxlength = htmlspecialchars( $frow['max_length'], ENT_QUOTES);
    echo "<input type='text'" .
      " name='form_$field_id_esc'" .
      " id='form_related_code'" .
      " size='$fldlength'" .
      " maxlength='$maxlength'" .
      " title='$description'" .
      " value='$currescaped'" .
      " onclick='sel_related(this)' readonly" .
      " />";
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $frow['fld_length']);
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      $option_id_esc = htmlspecialchars( $option_id, ENT_QUOTES);
      // if ($count) echo "<br />";
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='checkbox' name='form_{$field_id_esc}[$option_id_esc]' id='form_{$field_id_esc}[$option_id_esc]' value='1'";
      if (in_array($option_id, $avalue)) echo " checked";

      // Added 5-09 by BM - Translate label if applicable
      echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
	
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of checkboxes.
	$cols = htmlspecialchars( $cols, ENT_QUOTES);
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $option_id_esc = htmlspecialchars( $option_id, ENT_QUOTES);
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $maxlength = htmlspecialchars( $maxlength, ENT_QUOTES);
      $optionValue = htmlspecialchars( $avalue[$option_id], ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
        " size='$fldlength'" .
        " maxlength='$maxlength'" .
        " value='$optionValue'";
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('N/A'), ENT_NOQUOTES) .
      "&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
      "<td class='bold'>" .
      htmlspecialchars( xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $option_id_esc = htmlspecialchars( $option_id, ENT_QUOTES);
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
	
      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
	
      for ($i = 0; $i < 3; ++$i) {
	$inputValue = htmlspecialchars( $i, ENT_QUOTES);
        echo "<td><input type='radio'" .
          " name='radio_{$field_id_esc}[$option_id_esc]'" .
          " id='radio_{$field_id_esc}[$option_id_esc]'" .
          " value='$inputValue'";
        if ($restype === "$i") echo " checked";
        echo " /></td>";
      }
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $maxlength = htmlspecialchars( $maxlength, ENT_QUOTES);
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
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
      "pid = ? AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    // echo "<!-- $query -->\n"; // debugging
    $lres = sqlStatement($query, array($GLOBALS['pid']));
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) echo "<br />";
      echo htmlspecialchars( $lrow['title'], ENT_NOQUOTES);
      if ($lrow['comments']) echo ' (' . htmlspecialchars( $lrow['comments'], ENT_NOQUOTES) . ')';
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $option_id_esc = htmlspecialchars( $option_id, ENT_QUOTES);
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);

      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
	
      $option_id = htmlspecialchars( $option_id, ENT_QUOTES);
      echo "<td><input type='checkbox' name='check_{$field_id_esc}[$option_id_esc]' id='check_{$field_id_esc}[$option_id_esc]' value='1'";
      if ($restype) echo " checked";
      echo " />&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $maxlength = htmlspecialchars( $maxlength, ENT_QUOTES);
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
        " size='$fldlength'" .
        " maxlength='$maxlength'" .
        " value='$resnote' /></td>";
      echo "</tr>";
    }
    echo "</table>";
  }
  
  // single-selection list with ability to add to it
  else if ($data_type == 26) {
    echo "<select class='addtolistclass_$list_id_esc' name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    if ($showEmpty) echo "<option value=''>" . htmlspecialchars( xl($empty_title), ENT_QUOTES) . "</option>";
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    $got_selected = FALSE;
    while ($lrow = sqlFetchArray($lres)) {
      $optionValue = htmlspecialchars( $lrow['option_id'], ENT_QUOTES);
      echo "<option value='$optionValue'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
      {
        echo " selected";
        $got_selected = TRUE;
      }
      // Added 5-09 by BM - Translate label if applicable
      echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "</option>\n";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='$currescaped' selected>* $currescaped *</option>";
      echo "</select>";
      $fontTitle = htmlspecialchars( xl('Please choose a valid selection from the list.'), ENT_NOQUOTES);
      $fontText = htmlspecialchars( xl('Fix this'), ENT_NOQUOTES);
      echo " <font color='red' title='$fontTitle'>$fontText!</font>";
    }
    else {
      echo "</select>";
    }
    // show the add button if user has access to correct list
    $inputValue = htmlspecialchars( xl('Add'), ENT_QUOTES);
    $outputAddButton = "<input type='button' id='addtolistid_".$list_id_esc."' fieldid='form_".$field_id_esc."' class='addtolist' value='$inputValue'>";
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0' width='100%'>";
    $tdpct = (int) (100 / $cols);
    $got_selected = FALSE;
    for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
      $option_id = $lrow['option_id'];
      $option_id_esc = htmlspecialchars( $option_id, ENT_QUOTES);
      if ($count % $cols == 0) {
        if ($count) echo "</tr>";
        echo "<tr>";
      }
      echo "<td width='$tdpct%'>";
      echo "<input type='radio' name='form_{$field_id_esc}' id='form_{$field_id_esc}[$option_id_esc]' value='$option_id_esc'";
      if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $option_id == $currvalue))
      {
        echo " checked";
        $got_selected = TRUE;
      }
      echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of radio buttons.
	$cols = htmlspecialchars( $cols, ENT_QUOTES);
        echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
      }
    }
    echo "</table>";
    if (!$got_selected && strlen($currvalue) > 0) {
      $fontTitle = htmlspecialchars( xl('Please choose a valid selection.'), ENT_QUOTES);
      $fontText = htmlspecialchars( xl('Fix this'), ENT_NOQUOTES);
      echo "$currescaped <font color='red' title='$fontTitle'>$fontText!</font>";
    }
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list box for smoking status:
  else if ($data_type == 28 || $data_type == 32) {
    $tmp = explode('|', $currvalue);
    switch(count($tmp)) {
      case "4": {
        $resnote = $tmp[0]; 
        $restype = $tmp[1];
        $resdate = $tmp[2];
        $reslist = $tmp[3];
      } break;
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

    $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
    $maxlength = htmlspecialchars( $maxlength, ENT_QUOTES);
    $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
    $resdate = htmlspecialchars( $resdate, ENT_QUOTES);
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr>";
    if ($data_type == 28)
    {
	// input text 
    echo "<td><input type='text'" .
      " name='form_$field_id_esc'" .
      " id='form_$field_id_esc'" .
      " size='$fldlength'" .
      " maxlength='$maxlength'" .
      " value='$resnote' />&nbsp;</td>";
   echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
      "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
      htmlspecialchars( xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
    }
    else if($data_type == 32)
    {
    // input text
    echo "<tr><td><input type='text'" .
      " name='form_text_$field_id_esc'" .
      " id='form_text_$field_id_esc'" .
      " size='$fldlength'" .
      " maxlength='$maxlength'" .
      " value='$resnote' />&nbsp;</td></tr>";
    echo "<td>";
    //Selection list for smoking status
    $onchange = 'radioChange(this.options[this.selectedIndex].value)';//VicarePlus :: The javascript function for selection list.
    echo generate_select_list("form_$field_id", $list_id, $reslist,
      $description, $showEmpty ? $empty_title : '', '', $onchange)."</td>";
    echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".htmlspecialchars( xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
    }
    // current
    echo "<td><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[current]'" .
      " value='current".$field_id_esc."'";
    if ($restype == "current".$field_id) echo " checked";
      echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Current'), ENT_NOQUOTES)."&nbsp;</td>";
    // quit
    echo "<td><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[quit]'" .
      " value='quit".$field_id_esc."'";
    if ($restype == "quit".$field_id) echo " checked";
    echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Quit'), ENT_NOQUOTES)."&nbsp;</td>";
    // quit date
    echo "<td><input type='text' size='6' name='date_$field_id_esc' id='date_$field_id_esc'" .
      " value='$resdate'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id_esc' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />&nbsp;</td>";
    $date_init .= " Calendar.setup({inputField:'date_$field_id', ifFormat:'%Y-%m-%d', button:'img_$field_id'});\n";
    // never
    echo "<td><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[never]'" .
      " value='never".$field_id_esc."'";
    if ($restype == "never".$field_id) echo " checked";
    echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Never'), ENT_NOQUOTES)."&nbsp;</td>";
	// Not Applicable
    echo "<td><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[not_applicable]'" .
      " value='not_applicable".$field_id."'";
    if ($restype == "not_applicable".$field_id) echo " checked";
    echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('N/A'), ENT_QUOTES)."&nbsp;</td>";
    echo "</tr>";
    echo "</table>";
  }

  // static text.  read-only, of course.
  else if ($data_type == 31) {
    echo nl2br($frow['description']);
  }

  //VicarePlus :: A single selection list for Race and Ethnicity, which is specialized to check the 'ethrace' list if the entry does not exist in the list_id of the given list. At some point in the future (when able to input two lists via the layouts engine), this function could be expanded to allow using any list as a backup entry.
  else if ($data_type == 33) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
        if ($showEmpty) echo "<option value=''>" . htmlspecialchars( xl($empty_title), ENT_QUOTES) . "</option>";
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
        $got_selected = FALSE;
        while ($lrow = sqlFetchArray($lres)) {
         $optionValue = htmlspecialchars( $lrow['option_id'], ENT_QUOTES);
         echo "<option value='$optionValue'";
         if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
          (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue))
          {
          echo " selected";
          $got_selected = TRUE;
          }
         
         echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "</option>\n";
         }
        if (!$got_selected && strlen($currvalue) > 0)
        {
        //Check 'ethrace' list if the entry does not exist in the list_id of the given list(Race or Ethnicity).
         $list_id='ethrace';
         $lrow = sqlQuery("SELECT title FROM list_options " .
         "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue) );
         if ($lrow > 0)
                {
                $s = htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
                echo "<option value='$currvalue' selected> $s </option>";
                echo "</select>";
                }
         else
                {
                echo "<option value='$currescaped' selected>* $currescaped *</option>";
                echo "</select>";
                $fontTitle = htmlspecialchars( xl('Please choose a valid selection from the list.'), ENT_NOQUOTES);
                $fontText = htmlspecialchars( xl('Fix this'), ENT_NOQUOTES);
                echo " <font color='red' title='$fontTitle'>$fontText!</font>";
                }
        }
        else {
        echo "</select>";
        }
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
  if ($data_type == 1 || $data_type == 26 || $data_type == 33) {
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
        "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue));
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
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars( $tmp, ENT_QUOTES); }
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
    $fldlength = htmlspecialchars( $fld_length, ENT_QUOTES);
    $maxlength = htmlspecialchars( $frow['max_length'], ENT_QUOTES);
    echo "<textarea" .
      " cols='$fldlength'" .
      " rows='$maxlength'>" .
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
    if ($currvalue === '') { $tmp = oeFormatShortDate('&nbsp;'); }
    else { $tmp = htmlspecialchars( oeFormatShortDate($currvalue), ENT_QUOTES); }
    echo $tmp;
  }

  // provider list
  else if ($data_type == 10 || $data_type == 11) {
    $tmp = '';
    if ($currvalue) {
      $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue) );
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
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars( $tmp, ENT_QUOTES); }
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
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars( $tmp, ENT_QUOTES); }
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
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars( $tmp, ENT_QUOTES); }
    echo $tmp;
  }

  // Address book.
  else if ($data_type == 14) {
    $tmp = '';
    if ($currvalue) {
      $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue) );
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
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars( $tmp, ENT_QUOTES); }
    echo $tmp;
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    // In this special case, fld_length is the number of columns generated.
    $cols = max(1, $fld_length);
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
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
      echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of checkboxes.
	$cols = htmlspecialchars( $cols, ENT_QUOTES);
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
      $fldlength = empty($fld_length) ?  20 : $fld_length;
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $inputValue = htmlspecialchars( $avalue[$option_id], ENT_QUOTES);
      echo "<td><input type='text'" .
        " size='$fldlength'" .
        " value='$inputValue'" .
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    echo "<tr><td>&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('N/A'), ENT_NOQUOTES) .
      "&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
      "<td class='bold'>" .
      htmlspecialchars( xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='bold'>" .
      htmlspecialchars( xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
      for ($i = 0; $i < 3; ++$i) {
        echo "<td><input type='radio'";
        if ($restype === "$i") echo " checked";
        echo " /></td>";
      }
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
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
      "pid = ? AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    $lres = sqlStatement($query, array($GLOBALS['pid']) );
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) echo "<br />";
      echo htmlspecialchars( $lrow['title'], ENT_QUOTES);
      if ($lrow['comments']) echo htmlspecialchars( ' (' . $lrow['comments'] . ')', ENT_QUOTES);
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    echo "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
      echo "<td><input type='checkbox'";
      if ($restype) echo " checked";
      echo " />&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
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
      echo ">" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of radio buttons.
	$cols = htmlspecialchars( $cols, ENT_QUOTES);
        echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
      }
    }
    echo "</table>";
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  else if ($data_type == 28 || $data_type == 32) {
    $tmp = explode('|', $currvalue);
	switch(count($tmp)) {
      case "4": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = $tmp[2];
        $reslist = $tmp[3];
      } break;
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
    $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
    $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
    $resdate = htmlspecialchars( $resdate, ENT_QUOTES);
    if($data_type == 28)
    {
    echo "<td><input type='text'" .
      " size='$fldlength'" .
      " class='under'" .
      " value='$resnote' /></td>";
    echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
      "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
      htmlspecialchars( xl('Status'), ENT_NOQUOTES).":&nbsp;</td>";  
    } 
    else if($data_type == 32)
    {
    echo "<tr><td><input type='text'" .
      " size='$fldlength'" .
      " class='under'" .
      " value='$resnote' /></td></tr>"; 
    $fldlength = 30;
    $smoking_status_title = generate_display_field(array('data_type'=>'1','list_id'=>$list_id),$reslist);
    echo "<td><input type='text'" .
      " size='$fldlength'" .
      " class='under'" .
      " value='$smoking_status_title' /></td>";
    echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".htmlspecialchars( xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
    }
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo "/>".htmlspecialchars( xl('Current'), ENT_NOQUOTES)."&nbsp;</td>";
    
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo "/>".htmlspecialchars( xl('Quit'), ENT_NOQUOTES)."&nbsp;</td>";
    
    echo "<td><input type='text' size='6'" .
      " value='$resdate'" .
      " class='under'" .
      " /></td>";
    
    echo "<td><input type='radio'";
    if ($restype == "current".$field_id) echo " checked";
    echo " />".htmlspecialchars( xl('Never'), ENT_NOQUOTES)."</td>";
	
    echo "<td><input type='radio'";
    if ($restype == "not_applicable".$field_id) echo " checked";
    echo " />".htmlspecialchars( xl('N/A'), ENT_NOQUOTES)."&nbsp;</td>";
    echo "</tr>";
    echo "</table>";
  }

  // static text.  read-only, of course.
  else if ($data_type == 31) {
    echo nl2br($frow['description']);
  }

}

function generate_display_field($frow, $currvalue) {
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $s = '';

  // generic selection list or the generic selection list with add on the fly
  // feature, or radio buttons
  if ($data_type == 1 || $data_type == 26 || $data_type == 27 || $data_type == 33) {
    $lrow = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue) );
      $s = htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
    //For lists Race and Ethnicity if there is no matching value in the corresponding lists check ethrace list
    if ($lrow == 0 && $data_type == 33)
    {
    $list_id='ethrace';
    $lrow_ethrace = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue) );
    $s = htmlspecialchars(xl_list_label($lrow_ethrace['title']),ENT_NOQUOTES);
    }
  }

  // simple text field
  else if ($data_type == 2) {
    $s = htmlspecialchars($currvalue,ENT_NOQUOTES);
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    $s = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
  }

  // date
  else if ($data_type == 4) {
    $s = htmlspecialchars(oeFormatShortDate($currvalue),ENT_NOQUOTES);
  }

  // provider
  else if ($data_type == 10 || $data_type == 11) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = ?", array($currvalue) );
    $s = htmlspecialchars(ucwords($urow['fname'] . " " . $urow['lname']),ENT_NOQUOTES);
  }

  // pharmacy list
  else if ($data_type == 12) {
    $pres = get_pharmacies();
    while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      if ($currvalue == $key) {
        $s .= htmlspecialchars($prow['name'] . ' ' . $prow['area_code'] . '-' .
          $prow['prefix'] . '-' . $prow['number'] . ' / ' .
          $prow['line1'] . ' / ' . $prow['city'],ENT_NOQUOTES);
      }
    }
  }

  // squads
  else if ($data_type == 13) {
    $squads = acl_get_squads();
    if ($squads) {
      foreach ($squads as $key => $value) {
        if ($currvalue == $key) {
          $s .= htmlspecialchars($value[3],ENT_NOQUOTES);
        }
      }
    }
  }

  // address book
  else if ($data_type == 14) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = ?", array($currvalue));
    $uname = $urow['lname'];
    if ($urow['fname']) $uname .= ", " . $urow['fname'];
    $s = htmlspecialchars($uname,ENT_NOQUOTES);
  }

  // billing code
  else if ($data_type == 15) {
    $s = htmlspecialchars($currvalue,ENT_NOQUOTES);
  }

  // a set of labeled checkboxes
  else if ($data_type == 21) {
    $avalue = explode('|', $currvalue);
    $lres = sqlStatement("SELECT * FROM list_options " .
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (in_array($option_id, $avalue)) {
        if ($count++) $s .= "<br />";
	  
	// Added 5-09 by BM - Translate label if applicable
        $s .= htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
	    
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (empty($avalue[$option_id])) continue;
	
      // Added 5-09 by BM - Translate label if applicable
      $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES) . ":&nbsp;</td>";
	  
      $s .= "<td class='text' valign='top'>" . htmlspecialchars($avalue[$option_id],ENT_NOQUOTES) . "</td></tr>";
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
	
      // Added 5-09 by BM - Translate label if applicable
      $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES) . "&nbsp;</td>";
	
      $restype = ($restype == '1') ? xl('Normal') : (($restype == '2') ? xl('Abnormal') : xl('N/A'));
      // $s .= "<td class='text' valign='top'>$restype</td></tr>";
      // $s .= "<td class='text' valign='top'>$resnote</td></tr>";
      $s .= "<td class='text' valign='top'>" . htmlspecialchars($restype,ENT_NOQUOTES) . "&nbsp;</td>";
      $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote,ENT_NOQUOTES) . "</td>";
      $s .= "</tr>";
    }
    $s .= "</table>";
  }

  // the list of active allergies for the current patient
  else if ($data_type == 24) {
    $query = "SELECT title, comments FROM lists WHERE " .
      "pid = ? AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    // echo "<!-- $query -->\n"; // debugging
    $lres = sqlStatement($query, array($GLOBALS['pid']) );
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) $s .= "<br />";
      $s .= htmlspecialchars($lrow['title'],ENT_NOQUOTES);
      if ($lrow['comments']) $s .= ' (' . htmlspecialchars($lrow['comments'],ENT_NOQUOTES) . ')';
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    $s .= "<table cellpadding='0' cellspacing='0'>";
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
	
      // Added 5-09 by BM - Translate label if applicable	
      $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES) . "&nbsp;</td>";
	
      $restype = $restype ? xl('Yes') : xl('No');  
      $s .= "<td class='text' valign='top'>" . htmlspecialchars($restype,ENT_NOQUOTES) . "</td></tr>";
      $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote,ENT_NOQUOTES) . "</td></tr>";
      $s .= "</tr>";
    }
    $s .= "</table>";
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list for smoking status.
  else if ($data_type == 28 || $data_type == 32) {
    $tmp = explode('|', $currvalue);
    switch(count($tmp)) {
      case "4": {
        $resnote = $tmp[0];
        $restype = $tmp[1];
        $resdate = $tmp[2];
        $reslist = $tmp[3];
      } break;
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
     if ($data_type == 28)
    {
    if (!empty($resnote)) $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote,ENT_NOQUOTES) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
    }
     //VicarePlus :: Tobacco field has a listbox, text box, date field and 3 radio buttons.
     else if ($data_type == 32)
    {
       if (!empty($reslist)) $s .= "<td class='text' valign='top'>" . generate_display_field(array('data_type'=>'1','list_id'=>$list_id),$reslist) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
       if (!empty($resnote)) $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote,ENT_NOQUOTES) . "&nbsp;&nbsp;</td>";
    }

	if (!empty($res)) $s .= "<td class='text' valign='top'><b>" . htmlspecialchars(xl('Status'),ENT_NOQUOTES) . "</b>:&nbsp;" . htmlspecialchars($res,ENT_NOQUOTES) . "&nbsp;</td>";
    if ($restype == "quit".$field_id) $s .= "<td class='text' valign='top'>" . htmlspecialchars($resdate,ENT_NOQUOTES) . "&nbsp;</td>";
    $s .= "</tr>";
    $s .= "</table>";
  }

  // static text.  read-only, of course.
  else if ($data_type == 31) {
    $s .= nl2br($frow['description']);
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
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_name, seq", array($formtype) );

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
        echo htmlspecialchars(xl_layout_label($group_name),ENT_NOQUOTES);
	  
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
      $titlecols_esc = htmlspecialchars( $titlecols, ENT_QUOTES);
      echo "<td class='label' colspan='$titlecols_esc' ";
      //if ($cell_count == 2) echo " style='padding-left:10pt'";
      echo ">";
      $cell_count += $titlecols;
    }
    ++$item_count;

    // Added 5-09 by BM - Translate label if applicable
    if ($frow['title']) echo htmlspecialchars(xl_layout_label($frow['title']).":",ENT_NOQUOTES); else echo "&nbsp;";

    // Handle starting of a new data cell.
    if ($datacols > 0) {
      disp_end_cell();
      //echo "<td class='text data' colspan='$datacols' valign='top'";
      $datacols_esc = htmlspecialchars( $datacols, ENT_QUOTES);      
      echo "<td class='text data' colspan='$datacols_esc'";
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
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_name, seq", array($formtype) );

  $first = true;
  while ($frow = sqlFetchArray($fres)) {
	  $this_group = $frow['group_name'];
      $group_name = substr($this_group, 1);
      ?>
		<li <?php echo $first ? 'class="current"' : '' ?>>
			<a href="/play/javascript-tabbed-navigation/" id="header_tab_<?php echo ".htmlspecialchars($group_name,ENT_QUOTES)."?>">
                        <?php echo htmlspecialchars(xl_layout_label($group_name),ENT_NOQUOTES); ?></a>
		</li>
	  <?php
	  $first = false;
  }
}

function display_layout_tabs_data($formtype, $result1, $result2='') {
  global $item_count, $cell_count, $last_group, $CPR;

  $fres = sqlStatement("SELECT distinct group_name FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_name, seq", array($formtype));

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
		"WHERE form_id = ? AND uor > 0 AND group_name = ? " .
		"ORDER BY seq", array($formtype, $this_group) );
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
					  $titlecols_esc = htmlspecialchars( $titlecols, ENT_QUOTES);
					  echo "<td class='label' colspan='$titlecols_esc' ";
					  echo ">";
					  $cell_count += $titlecols;
					}
					++$item_count;

					// Added 5-09 by BM - Translate label if applicable
					if ($group_fields['title']) echo htmlspecialchars(xl_layout_label($group_fields['title']).":",ENT_NOQUOTES); else echo "&nbsp;";

					// Handle starting of a new data cell.
					if ($datacols > 0) {
					  disp_end_cell();
					  $datacols_esc = htmlspecialchars( $datacols, ENT_QUOTES);
					  echo "<td class='text data' colspan='$datacols_esc'";
					  echo ">";
					  $cell_count += $datacols;
					}

					++$item_count;
					echo generate_display_field($group_fields, $currvalue);
				  }

        disp_end_row();
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
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_name, seq", array($formtype) );

	$first = true;
	while ($frow = sqlFetchArray($fres)) {
		$this_group = $frow['group_name'];
		$group_name = substr($this_group, 1);
	        $group_name_esc = htmlspecialchars( $group_name, ENT_QUOTES);
		$titlecols  = $frow['titlecols'];
		$datacols   = $frow['datacols'];
		$data_type  = $frow['data_type'];
		$field_id   = $frow['field_id'];
		$list_id    = $frow['list_id'];
		$currvalue  = '';

		$group_fields_query = sqlStatement("SELECT * FROM layout_options " .
		"WHERE form_id = ? AND uor > 0 AND group_name = ? " .
		"ORDER BY seq", array($formtype,$this_group) );
	?>

		<div class="tab <?php echo $first ? 'current' : '' ?>" id="tab_<?php echo $group_name_esc?>" >
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
					  $titlecols_esc = htmlspecialchars( $titlecols, ENT_QUOTES);
					  echo "<td class='label' colspan='$titlecols_esc' ";
					  echo ">";
					  $cell_count += $titlecols;
					}
					++$item_count;

					// Added 5-09 by BM - Translate label if applicable
					if ($group_fields['title']) echo (htmlspecialchars( xl_layout_label($group_fields['title']), ENT_NOQUOTES).":"); else echo "&nbsp;";

					// Handle starting of a new data cell.
					if ($datacols > 0) {
					  disp_end_cell();
					  $datacols_esc = htmlspecialchars( $datacols, ENT_QUOTES);
					  echo "<td class='text data' colspan='$datacols_esc'";
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
  // Bring in $sanitize_all_escapes variable, which will decide
  //  the variable escaping method.
  global $sanitize_all_escapes;
    
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
    else if ($data_type == 28 || $data_type == 32) {
      // $_POST["form_$field_id"] is an date text fields with companion
      // radio buttons to be imploded into "notes|type|date".
      $restype = $_POST["radio_{$field_id}"];
      if (empty($restype)) $restype = '0';
      $resdate = str_replace('|', ' ', $_POST["date_$field_id"]);
      $resnote = str_replace('|', ' ', $_POST["form_$field_id"]);
      if ($data_type == 32)
      {
      //VicarePlus :: Smoking status data is imploded into "note|type|date|list".
      $reslist = str_replace('|', ' ', $_POST["form_$field_id"]);
      $res_text_note = str_replace('|', ' ', $_POST["form_text_$field_id"]);
      $value = "$res_text_note|$restype|$resdate|$reslist";
      }
      else
      $value = "$resnote|$restype|$resdate";
    }
    else {
      $value = $_POST["form_$field_id"];
    }
  }

  // Better to die than to silently truncate data!
  if ($maxlength && $data_type != 3 && strlen($value) > $maxlength)
    die(htmlspecialchars( xl('ERROR: Field') . " '$field_id' " . xl('is too long'), ENT_NOQUOTES) .
    ":<br />&nbsp;<br />".htmlspecialchars( $value, ENT_NOQUOTES));

  // Make sure the return value is quote-safe.
  if ($sanitize_all_escapes) {
    //escapes already removed and using binding/placemarks in sql calls
    // so only need to trim value
    return trim($value);
  }
  else {
    //need to explicitly prepare value
    return formTrim($value);
  }
}

// Generate JavaScript validation logic for the required fields.
//
function generate_layout_validation($form_id) {
  $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 AND field_id != '' " .
    "ORDER BY group_name, seq", array($form_id) );

  while ($frow = sqlFetchArray($fres)) {
    if ($frow['uor'] < 2) continue;
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    $fldtitle  = $frow['title'];
    if (!$fldtitle) $fldtitle  = $frow['description'];
    $fldname   = htmlspecialchars( "form_$field_id", ENT_QUOTES);
    switch($data_type) {
      case  1:
      case 11:
      case 12:
      case 13:
      case 14:
      case 26:
      case 33:
        echo
        " if (f.$fldname.selectedIndex <= 0) {\n" .
        "  if (f.$fldname.focus) f.$fldname.focus();\n" .
        "  		errMsgs[errMsgs.length] = '" . htmlspecialchars( (xl_layout_label($fldtitle)), ENT_QUOTES) . "'; \n" .
        " }\n";
        break;
      case 27: // radio buttons
        echo
        " var i = 0;\n" .
        " for (; i < f.$fldname.length; ++i) if (f.$fldname[i].checked) break;\n" .
        " if (i >= f.$fldname.length) {\n" .
        "  		errMsgs[errMsgs.length] = '" . htmlspecialchars( (xl_layout_label($fldtitle)), ENT_QUOTES) . "'; \n" .
        " }\n";
        break;
      case  2:
      case  3:
      case  4:
      case 15:
        echo
        " if (trimlen(f.$fldname.value) == 0) {\n" .
        "  		if (f.$fldname.focus) f.$fldname.focus();\n" .
		"  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','red'); } ); " .
		"  		$('#" . $fldname . "').attr('style','background:red'); \n" .
        "  		errMsgs[errMsgs.length] = '" . htmlspecialchars( (xl_layout_label($fldtitle)), ENT_QUOTES) . "'; \n" .
        " } else { " .
		" 		$('#" . $fldname . "').attr('style',''); " .
		"  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','');  } ); " .
		" } \n";
        break;
    }
  }
}

/**
 * DROPDOWN FOR FACILITIES
 *
 * build a dropdown with all facilities
 *
 * @param string $selected - name of the currently selected facility
 *                           use '0' for "unspecified facility"
 *                           use '' for "All facilities" (the default)
 * @param string $name - the name/id for select form (defaults to "form_facility")
 * @param boolean $allow_unspecified - include an option for "unspecified" facility
 *                                     defaults to true
 * @return void - just echo the html encoded string
 *
 * Note: This should become a data-type at some point, according to Brady
 */
function dropdown_facility($selected = '', $name = 'form_facility', $allow_unspecified = true) {
  $have_selected = false;
  $query = "SELECT id, name FROM facility ORDER BY name";
  $fres = sqlStatement($query);

  $name = htmlspecialchars($name, ENT_QUOTES);
  echo "   <select name=\"$name\">\n";

  $option_value = '';
  $option_selected_attr = '';
  if ($selected == '') {
    $option_selected_attr = ' selected="selected"';
    $have_selected = true;
  }
  $option_content = htmlspecialchars('-- ' . xl('All Facilities') . ' --', ENT_NOQUOTES);
  echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";

  while ($frow = sqlFetchArray($fres)) {
    $facility_id = $frow['id'];
    $option_value = htmlspecialchars($facility_id, ENT_QUOTES);
    $option_selected_attr = '';
    if ($selected == $facility_id) {
      $option_selected_attr = ' selected="selected"';
      $have_selected = true;
    }
    $option_content = htmlspecialchars($frow['name'], ENT_NOQUOTES);
    echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
  }

  if ($allow_unspecified) {
    $option_value = '0';
    $option_selected_attr = '';
    if ( $selected == '0' ) {
      $option_selected_attr = ' selected="selected"';
      $have_selected = true;
    }
    $option_content = htmlspecialchars('-- ' . xl('Unspecified') . ' --', ENT_NOQUOTES);
    echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
  }

  if (!$have_selected) {
    $option_value = htmlspecialchars($selected, ENT_QUOTES);
    $option_label = htmlspecialchars('(' . xl('Do not change') . ')', ENT_QUOTES);
    $option_content = htmlspecialchars(xl('Missing or Invalid'), ENT_NOQUOTES);
    echo "    <option value='$option_value' label='$option_label' selected='selected'>$option_content</option>\n";
  }
  echo "   </select>\n";
}

// Expand Collapse Widget
//  This forms the header and functionality component of the widget. The information that is displayed
//  then follows this function followed by a closing div tag
//
// $title is the title of the section (already translated)
// $label is identifier used in the tag id's and sql columns
// $buttonLabel is the button label text (already translated)
// $buttonLink is the button link information
// $buttonClass is any additional needed class elements for the button tag
// $linkMethod is the button link method ('javascript' vs 'html')
// $bodyClass is to set class(es) of the body
// $auth is a flag to decide whether to show the button
// $fixedWidth is to flag whether width is fixed
// $forceExpandAlways is a flag to force the widget to always be expanded
//
function expand_collapse_widget($title, $label, $buttonLabel, $buttonLink, $buttonClass, $linkMethod, $bodyClass, $auth, $fixedWidth, $forceExpandAlways=false) {
  if ($fixedWidth) {
    echo "<div class='section-header'>";
  }
  else {
    echo "<div class='section-header-dynamic'>";
  }
  echo "<table><tr>";
  if ($auth) {
    // show button, since authorized
    // first prepare class string
    if ($buttonClass) {
      $class_string = "css_button_small ".htmlspecialchars( $buttonClass, ENT_NOQUOTES);
    }
    else {
      $class_string = "css_button_small";
    }
    // next, create the link
    if ($linkMethod == "javascript") {
      echo "<td><a class='" . $class_string . "' href='javascript:;' onclick='" . $buttonLink . "'";
    }
    else {
      echo "<td><a class='" . $class_string . "' href='" . $buttonLink . "'" .
        " onclick='top.restoreSession()'";
    }
    if (!$GLOBALS['concurrent_layout']) {
      echo " target='Main'";
    }
    echo "><span>" .
      htmlspecialchars( $buttonLabel, ENT_NOQUOTES) . "</span></a></td>";
  }
  if ($forceExpandAlways){
    // Special case to force the widget to always be expanded
    echo "<td><span class='text'><b>" . htmlspecialchars( $title, ENT_NOQUOTES) . "</b></span>";
    $indicatorTag ="style='display:none'";
  }
  echo "<td><a " . $indicatorTag . " href='javascript:;' class='small' onclick='toggleIndicator(this,\"" .
    htmlspecialchars( $label, ENT_QUOTES) . "_ps_expand\")'><span class='text'><b>";
  echo htmlspecialchars( $title, ENT_NOQUOTES) . "</b></span>";
  if (getUserSetting($label."_ps_expand")) {
    $text = xl('collapse');
  }
  else {
    $text = xl('expand');
  }
  echo " (<span class='indicator'>" . htmlspecialchars($text, ENT_QUOTES) .
    "</span>)</a></td>";
  echo "</tr></table>";
  echo "</div>";
  if ($forceExpandAlways) {
    // Special case to force the widget to always be expanded
    $styling = "";
  }
  else if (getUserSetting($label."_ps_expand")) {
    $styling = "";
  }
  else {
    $styling = "style='display:none'";
  }
  if ($bodyClass) {
    $styling .= " class='" . $bodyClass . "'";
  }
  //next, create the first div tag to hold the information
  // note the code that calls this function will then place the ending div tag after the data
  echo "<div id='" . htmlspecialchars( $label, ENT_QUOTES) . "_ps_expand' " . $styling . ">";
}

//billing_facility fuction will give the dropdown list which contain billing faciliies.
function billing_facility($name,$select){
	$qsql = sqlStatement("SELECT id, name FROM facility WHERE billing_location = 1");
		echo "   <select id='".htmlspecialchars($name, ENT_QUOTES)."' name='".htmlspecialchars($name, ENT_QUOTES)."'>";
			while ($facrow = sqlFetchArray($qsql)) {
				$selected = ( $facrow['id'] == $select ) ? 'selected="selected"' : '' ;
				 echo "<option value=".htmlspecialchars($facrow['id'],ENT_QUOTES)." $selected>".htmlspecialchars($facrow['name'], ENT_QUOTES)."</option>";
				}
			  echo "</select>";
}

?>
