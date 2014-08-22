<?php
// Copyright (C) 2007-2013 Rod Roark <rod@sunsetsystems.com>
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
// A = Age as years or "xx month(s)"
// B = Gestational age as "xx week(s) y day(s)"
// C = Capitalize first letter of each word (text fields)
// D = Check for duplicates in New Patient form
// G = Graphable (for numeric fields in forms supporting historical data)
// H = Read-only field copied from static history
// L = Lab Order ("ord_lab") types only (address book)
// N = Show in New Patient form
// O = Procedure Order ("ord_*") types only (address book)
// R = Distributor types only (address book)
// T = Use description as default Text
// U = Capitalize all letters (text fields)
// V = Vendor types only (address book)
// 0 = Read Only - the input element's "disabled" property is set
// 1 = Write Once (not editable when not empty) (text fields)
// 2 = Show descriptions instead of codes for billing code input

require_once("formdata.inc.php");
require_once("formatting.inc.php");
require_once("user.inc");
require_once("patient.inc");
require_once("lists.inc");

$date_init = "";

function get_pharmacies() {
  return sqlStatement("SELECT d.id, d.name, a.line1, a.city, " .
    "p.area_code, p.prefix, p.number FROM pharmacies AS d " .
    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
    "AND p.type = 2 " .
    "ORDER BY name, area_code, prefix, number");
}

function optionalAge($frow, $date, &$asof) {
  $asof = '';
  if (empty($date)) return '';
  $date = substr($date, 0, 10);
  if (strpos($frow['edit_options'], 'A') !== FALSE) {
    $format = 0;
  }
  else if (strpos($frow['edit_options'], 'B') !== FALSE) {
    $format = 3;
  }
  else {
    return '';
  }
  if (strpos($frow['form_id'], 'LBF') === 0) {
    $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
      "pid = ? AND encounter = ? ORDER BY id DESC LIMIT 1",
      array($GLOBALS['pid'], $GLOBALS['encounter']));
    if (!empty($tmp['date'])) $asof = substr($tmp['date'], 0, 10);
  }
  $prefix = ($format ? xl('Gest age') : xl('Age')) . ' ';
  return $prefix . oeFormatAge($date, $asof, $format);
}

// Function to generate a drop-list.
//
function generate_select_list($tag_name, $list_id, $currvalue, $title, $empty_name = ' ', $class = '',
		$onchange = '', $tag_id = '', $custom_attributes = null, $multiple = false, $backup_list = '') {
	$s = '';	
	
	$tag_name_esc = attr($tag_name);
	
	if ($multiple) {
		$tag_name_esc = $tag_name_esc . "[]";
	}
	$s .= "<select name='$tag_name_esc'";
	
	if ($multiple) {
		$s .= " multiple='multiple'";
	}
	
	$tag_id_esc = $tag_name_esc;
	if ($tag_id != '') {
		$tag_id_esc = attr($tag_id);
	}
	
	if ($multiple) {
		$tag_id_esc = $tag_id_esc . "[]";
	}
	$s .= " id='$tag_id_esc'";
	
	if ($class) {
                $class_esc = attr($class);
		$s .= " class='$class_esc'";
	}
	if ($onchange) {
		$s .= " onchange='$onchange'";
	}
	if ($custom_attributes != null && is_array ( $custom_attributes )) {
		foreach ( $custom_attributes as $attr => $val ) {
			if (isset ( $custom_attributes [$attr] )) {
				$s .= " " . attr($attr) . "='" . attr($val) . "'";
			}
		}
	}
	$selectTitle = attr($title);
	$s .= " title='$selectTitle'>";
	$selectEmptyName = xlt($empty_name);
	if ($empty_name)
		$s .= "<option value=''>" . $selectEmptyName . "</option>";
	$lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? ORDER BY seq, title", array($list_id));
	$got_selected = FALSE;
	
	while ( $lrow = sqlFetchArray ( $lres ) ) {
		$selectedValues = explode ( "|", $currvalue );
		
		$optionValue = attr($lrow ['option_id']);
		$s .= "<option value='$optionValue'";

		if ($multiple && (strlen ( $currvalue ) == 0 && $lrow ['is_default']) || (strlen ( $currvalue ) > 0 && in_array ( $lrow ['option_id'], $selectedValues ))) {
			$s .= " selected";
			$got_selected = TRUE;
		}
		
		$optionLabel = text(xl_list_label($lrow ['title']));
		$s .= ">$optionLabel</option>\n";
	}

	if (!$got_selected && strlen ( $currvalue ) > 0 && !$multiple) {
		$list_id = $backup_list;
		$lrow = sqlQuery("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue));

		if ($lrow > 0 && !empty($backup_list)) {
			$selected = text(xl_list_label($lrow ['title']));
			$s .= "<option value='$currescaped' selected> $selected </option>";
			$s .= "</select>";
		} else {
			$s .= "<option value='$currescaped' selected>* $currescaped *</option>";
			$s .= "</select>";
			$fontTitle = xlt('Please choose a valid selection from the list.');
			$fontText = xlt( 'Fix this' );
			$s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
		}		
		
	} else if (!$got_selected && strlen ( $currvalue ) > 0 && $multiple) {
		//if not found in main list, display all selected values that exist in backup list
		$list_id = $backup_list;
		
		$lres_backup = sqlStatement("SELECT * FROM list_options WHERE list_id = ? ORDER BY seq, title", array($list_id));
		
		$got_selected_backup = FALSE;
		if (!empty($backup_list)) {
			while ( $lrow_backup = sqlFetchArray ( $lres_backup ) ) {
				$selectedValues = explode ( "|", $currvalue );
			
				$optionValue = attr($lrow ['option_id']);
			
				if ($multiple && (strlen ( $currvalue ) == 0 && $lrow_backup ['is_default']) || 
						(strlen ( $currvalue ) > 0 && in_array ( $lrow_backup ['option_id'], $selectedValues ))) {
					$s .= "<option value='$optionValue'";
					$s .= " selected";
					$optionLabel = text(xl_list_label($lrow_backup ['title']));
					$s .= ">$optionLabel</option>\n";				
					$got_selected_backup = TRUE;
				}
			}
		}
		if (!$got_selected_backup) {
			$s .= "<option value='$currescaped' selected>* $currescaped *</option>";
			$s .= "</select>";
			$fontTitle = xlt('Please choose a valid selection from the list.');
			$fontText = xlt( 'Fix this' );
			$s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
		}
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
  global $rootdir, $date_init, $ISSUE_TYPES, $code_types;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];
  $backup_list = $frow['list_backup_id'];
  
  // escaped variables to use in html
  $field_id_esc= htmlspecialchars( $field_id, ENT_QUOTES);
  $list_id_esc = htmlspecialchars( $list_id, ENT_QUOTES);

  // Added 5-09 by BM - Translate description if applicable  
  $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');

  // Support edit option T which assigns the (possibly very long) description as
  // the default value.
  if (strpos($frow['edit_options'], 'T') !== FALSE) {
    if (strlen($currescaped) == 0) $currescaped = $description;
    // Description used in this way is not suitable as a title.
    $description = '';
  }

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

  $disabled = strpos($frow['edit_options'], '0') === FALSE ? '' : 'disabled';
    
  // generic single-selection list or Race and Ethnicity.
  // These data types support backup lists.
  if ($data_type == 1 || $data_type == 33) {
    echo generate_select_list("form_$field_id", $list_id, $currvalue,
      $description, ($showEmpty ? $empty_title : ''), '', $onchange, '',
      ($disabled ? array('disabled' => 'disabled') : null), false, $backup_list);
  }

  // simple text field
  else if ($data_type == 2) {
    $fldlength = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $maxlength = $frow['max_length'];
    $string_maxlength = "";
    // if max_length is set to zero, then do not set a maxlength
    if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
    echo "<input type='text'" .
      " name='form_$field_id_esc'" .
      " id='form_$field_id_esc'" .
      " size='$fldlength'" .
      " $string_maxlength" .
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
    if (strpos($frow['edit_options'], '1') !== FALSE && strlen($currescaped) > 0) {
      echo " readonly";
    }
	if ($disabled) echo ' disabled';
    echo " />";
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    $textCols = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $textRows = htmlspecialchars( $frow['fld_rows'], ENT_QUOTES);
    echo "<textarea" .
      " name='form_$field_id_esc'" .
      " id='form_$field_id_esc'" .
      " title='$description'" .
      " cols='$textCols'" .
      " rows='$textRows' $disabled>" .
      $currescaped . "</textarea>";
  }

  // date
  else if ($data_type == 4) {
    $age_asof_date = ''; // optionalAge() sets this
    $age_format = strpos($frow['edit_options'], 'A') === FALSE ? 3 : 0;
    $agestr = optionalAge($frow, $currvalue, $age_asof_date);
    if ($agestr) {
      echo "<table cellpadding='0' cellspacing='0'><tr><td class='text'>";
    }
    echo "<input type='text' size='10' name='form_$field_id_esc' id='form_$field_id_esc'" .
      " value='" . substr($currescaped, 0, 10) . "'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' $disabled />";
    if (!$disabled) {
      echo "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id_esc' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />";
      $date_init .= " Calendar.setup({" .
        "inputField:'form_$field_id', " .
        "ifFormat:'%Y-%m-%d', ";
      if ($agestr) {
        $date_init .= "onUpdate: function() {" .
          "if (typeof(updateAgeString) == 'function') updateAgeString('$field_id','$age_asof_date', $age_format);" .
        "}, ";
      }
      $date_init .= "button:'img_$field_id'})\n";
    }
    // Optional display of age or gestational age.
    if ($agestr) {
      echo "</td></tr><tr><td id='span_$field_id' class='text'>" . text($agestr) . "</td></tr></table>";
    }
  }

  // provider list, local providers only
  else if ($data_type == 10) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND authorized = 1 " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
    echo "<option value=''>" . xlt($empty_title) . "</option>";
    $got_selected = false;
    while ($urow = sqlFetchArray($ures)) {
      $uname = text($urow['fname'] . ' ' . $urow['lname']);
      $optionId = attr($urow['id']);
      echo "<option value='$optionId'";
      if ($urow['id'] == $currvalue) {
        echo " selected";
        $got_selected = true;
      }
      echo ">$uname</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // provider list, including address book entries with an NPI number
  else if ($data_type == 11) {
    $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
      "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
      "AND ( authorized = 1 OR ( username = '' AND npi != '' ) ) " .
      "ORDER BY lname, fname");
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
    echo "<option value=''>" . xlt('Unassigned') . "</option>";
    $got_selected = false;
    while ($urow = sqlFetchArray($ures)) {
      $uname = text($urow['fname'] . ' ' . $urow['lname']);
      $optionId = attr($urow['id']);
      echo "<option value='$optionId'";
      if ($urow['id'] == $currvalue) {
        echo " selected";
        $got_selected = true;
      }
      echo ">$uname</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // pharmacy list
  else if ($data_type == 12) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
    echo "<option value='0'></option>";
    $pres = get_pharmacies();
    $got_selected = false;
    while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      $optionValue = htmlspecialchars( $key, ENT_QUOTES);
      $optionLabel = htmlspecialchars( $prow['name'] . ' ' . $prow['area_code'] . '-' .
        $prow['prefix'] . '-' . $prow['number'] . ' / ' .
        $prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
      echo "<option value='$optionValue'";
      if ($currvalue == $key) {
        echo " selected";
        $got_selected = true;
      }
      echo ">$optionLabel</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // squads
  else if ($data_type == 13) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
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
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
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

  // A billing code. If description matches an existing code type then that type is used.
  else if ($data_type == 15) {
    $fldlength = htmlspecialchars( $frow['fld_length'], ENT_QUOTES);
    $maxlength = $frow['max_length'];
    $string_maxlength = "";
    // if max_length is set to zero, then do not set a maxlength
    if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
    //
    if (strpos($frow['edit_options'], '2') !== FALSE && substr($frow['form_id'], 0, 3) == 'LBF') {
      // Option "2" generates a hidden input for the codes, and a matching visible field
      // displaying their descriptions. First step is computing the description string.
      $currdescstring = '';
      if (!empty($currvalue)) {
        $relcodes = explode(';', $currvalue);
        foreach ($relcodes as $codestring) {
          if ($codestring === '') continue;
          list($codetype, $code) = explode(':', $codestring);
          $query = "SELECT c.code_text FROM codes AS c, code_types AS ct WHERE " .
            "ct.ct_key = '$codetype' AND " .
            "c.code_type = ct.ct_id AND " .
            "c.code = '$code' AND c.active = 1 " .
            "ORDER BY c.id LIMIT 1";
          $nrow = sqlQuery($query);
          if ($currdescstring !== '') $currdescstring .= '; ';
          if (!empty($nrow['code_text'])) {
            $currdescstring .= $nrow['code_text'];
          }
          else {
            $currdescstring .= $codestring;
          }
        }
      }
      $currdescstring = htmlspecialchars($currdescstring, ENT_QUOTES);
      //
      echo "<input type='text'" .
        " name='form_$field_id_esc'" .
        " id='form_related_code'" .
        " size='$fldlength'" .
        " value='$currescaped'" .
        " style='display:none'" .
        " readonly $disabled />";
      // Extra readonly input field for optional display of code description(s).
      echo "<input type='text'" .
        " name='form_$field_id_esc" . "__desc'" .
        " size='$fldlength'" .
        " title='$description'" .
        " value='$currdescstring'";
      if (!$disabled) {
        echo " onclick='sel_related(this,\"$codetype\")'";
      }
      echo " readonly $disabled />";
    }
    else {
      echo "<input type='text'" .
        " name='form_$field_id_esc'" .
        " id='form_related_code'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " title='$description'" .
        " value='$currescaped'";
      if (!$disabled) {
        echo " onclick='sel_related(this,\"$codetype\")'";
      }
      echo " readonly $disabled />";
    }
  }

  // insurance company list
  else if ($data_type == 16) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value='0'></option>";
    $insprovs = getInsuranceProviders();
    $got_selected = false;
    foreach ($insprovs as $key => $ipname) {
      $optionValue = htmlspecialchars($key, ENT_QUOTES);
      $optionLabel = htmlspecialchars($ipname, ENT_NOQUOTES);
      echo "<option value='$optionValue'";
      if ($currvalue == $key) {
        echo " selected";
        $got_selected = true;
      }
      echo ">$optionLabel</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // issue types
  else if ($data_type == 17) {
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description'>";
    echo "<option value='0'></option>";
    $got_selected = false;
    foreach ($ISSUE_TYPES as $key => $value) {
      $optionValue = htmlspecialchars($key, ENT_QUOTES);
      $optionLabel = htmlspecialchars($value[1], ENT_NOQUOTES);
      echo "<option value='$optionValue'";
      if ($currvalue == $key) {
        echo " selected";
        $got_selected = true;
      }
      echo ">$optionLabel</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
  }

  // Visit categories.
  else if ($data_type == 18) {
    $cres = sqlStatement("SELECT pc_catid, pc_catname " .
      "FROM openemr_postcalendar_categories ORDER BY pc_catname");
    echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $disabled>";
    echo "<option value=''>" . xlt($empty_title) . "</option>";
    $got_selected = false;
    while ($crow = sqlFetchArray($cres)) {
      $catid = $crow['pc_catid'];
      if (($catid < 9 && $catid != 5) || $catid == 11) continue;
      echo "<option value='" . attr($catid) . "'";
      if ($catid == $currvalue) {
        echo " selected";
        $got_selected = true;
      }
      echo ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
      echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
      echo "</select>";
      echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
    }
    else {
      echo "</select>";
    }
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
      echo " $disabled />" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES);
	
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
      $maxlength = $frow['max_length'];
      $string_maxlength = "";
      // if max_length is set to zero, then do not set a maxlength
      if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
      $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

      // Added 5-09 by BM - Translate label if applicable
      echo "<tr><td>" . htmlspecialchars( xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $optionValue = htmlspecialchars( $avalue[$option_id], ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " value='$optionValue'";
      echo " $disabled /></td></tr>";
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
    $maxlength = $frow['max_length'];
    $string_maxlength = "";
    // if max_length is set to zero, then do not set a maxlength
    if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
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
        echo " $disabled /></td>";
      }
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " value='$resnote' $disabled /></td>";
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
    $maxlength = $frow['max_length'];
    $string_maxlength = "";
    // if max_length is set to zero, then do not set a maxlength
    if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
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
      echo " $disabled />&nbsp;</td>";
      $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
      $resnote = htmlspecialchars( $resnote, ENT_QUOTES);
      echo "<td><input type='text'" .
        " name='form_{$field_id_esc}[$option_id_esc]'" .
        " id='form_{$field_id_esc}[$option_id_esc]'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " value='$resnote' $disabled /></td>";
      echo "</tr>";
    }
    echo "</table>";
  }
  
  // single-selection list with ability to add to it
  else if ($data_type == 26) {
    echo generate_select_list("form_$field_id", $list_id, $currvalue,
      $description, $showEmpty ? $empty_title : '', 'addtolistclass_'.$list_id, $onchange, '',
      ($disabled ? array('disabled' => 'disabled') : null), false, $backup_list);
    // show the add button if user has access to correct list
    $inputValue = htmlspecialchars( xl('Add'), ENT_QUOTES);
    $outputAddButton = "<input type='button' id='addtolistid_" . $list_id_esc . "' fieldid='form_" .
      $field_id_esc . "' class='addtolist' value='$inputValue' $disabled />";
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
      echo " $disabled />" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
      echo "</td>";
    }
    if ($count) {
      echo "</tr>";
      if ($count > $cols) {
        // Add some space after multiple rows of radio buttons.
        $cols = htmlspecialchars($cols, ENT_QUOTES);
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
    $maxlength = $frow['max_length'];
    $string_maxlength = "";
    // if max_length is set to zero, then do not set a maxlength
    if ($maxlength) $string_maxlength = "maxlength='".attr($maxlength)."'";
    $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

    $fldlength = htmlspecialchars( $fldlength, ENT_QUOTES);
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
      " $string_maxlength" .
      " value='$resnote' $disabled />&nbsp;</td>";
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
      " $string_maxlength" .
      " value='$resnote' $disabled />&nbsp;</td></tr>";
    echo "<td>";
    //Selection list for smoking status
    $onchange = 'radioChange(this.options[this.selectedIndex].value)';//VicarePlus :: The javascript function for selection list.
    echo generate_select_list("form_$field_id", $list_id, $reslist,
      $description, ($showEmpty ? $empty_title : ''), '', $onchange, '',
      ($disabled ? array('disabled' => 'disabled') : null));
    echo "</td>";
    echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".htmlspecialchars( xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
    }
    // current
    echo "<td class='text' ><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[current]'" .
      " value='current".$field_id_esc."'";
    if ($restype == "current".$field_id) echo " checked";
      echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Current'), ENT_NOQUOTES)."&nbsp;</td>";
    // quit
    echo "<td class='text'><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[quit]'" .
      " value='quit".$field_id_esc."'";
    if ($restype == "quit".$field_id) echo " checked";
    //
    // echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Quit'), ENT_NOQUOTES)."&nbsp;</td>";
    // wtf? Doing this instead:
    if($data_type == 32) echo " onClick='smoking_statusClicked(this)'";
    echo " $disabled />" . htmlspecialchars(xl('Quit'), ENT_NOQUOTES) . "&nbsp;</td>";
    //
    // quit date
    echo "<td class='text'><input type='text' size='6' name='date_$field_id_esc' id='date_$field_id_esc'" .
      " value='$resdate'" .
      " title='$description'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' $disabled />";
    if (!$disabled) {
      echo "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_$field_id_esc' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />";
      $date_init .= " Calendar.setup({inputField:'date_$field_id', ifFormat:'%Y-%m-%d', button:'img_$field_id'});\n";
    }
    echo "&nbsp;</td>";
    // never
    echo "<td class='text'><input type='radio'" .
      " name='radio_{$field_id_esc}'" .
      " id='radio_{$field_id_esc}[never]'" .
      " value='never".$field_id_esc."'";
    if ($restype == "never".$field_id) echo " checked";
    echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('Never'), ENT_NOQUOTES)."&nbsp;</td>";
	// Not Applicable
    echo "<td class='text'><input type='radio'" .
      " name='radio_{$field_id}'" .
      " id='radio_{$field_id}[not_applicable]'" .
      " value='not_applicable".$field_id."'";
    if ($restype == "not_applicable".$field_id) echo " checked";
    //
    // echo " if($data_type == 32) { onClick='smoking_statusClicked(this)' } />".htmlspecialchars( xl('N/A'), ENT_QUOTES)."&nbsp;</td>";
    // wtf? Doing this instead:
    if($data_type == 32) echo " onClick='smoking_statusClicked(this)'";
    echo " $disabled />" . htmlspecialchars(xl('N/A'), ENT_NOQUOTES) . "&nbsp;</td>";
    //
    //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
    echo "<td class='text' ><div id='smoke_code'></div></td>";
    echo "</tr>";
    echo "</table>";
  }

  // static text.  read-only, of course.
  else if ($data_type == 31) {
    echo nl2br($frow['description']);
  }

  //$data_type == 33
  // Race and Ethnicity. After added support for backup lists, this is now the same as datatype 1; so have migrated it there.
  //$data_type == 33

  else if($data_type == 34){
    $arr = explode("|*|*|*|",$currvalue);
    echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=".htmlspecialchars($list_id_esc,ENT_QUOTES)."' class='iframe_medium' style='text-decoration:none;color:black;'>";
    echo "<div id='form_{$field_id}_div' class='text-area'>".htmlspecialchars($arr[0],ENT_QUOTES)."</div>";
    echo "<div style='display:none'><textarea name='form_{$field_id}' id='form_{$field_id}' stye='display:none' $disabled>".$currvalue."</textarea></div>";
    echo "</a>";
  }

  //facilities drop-down list
  else if ($data_type == 35) {   
    if (empty($currvalue)){
   	  $currvalue = 0;
    }
    dropdown_facility($selected = $currvalue, $name = "form_$field_id_esc", $allow_unspecified = true, $allow_allfacilities = false, $disabled);
  }

  //multiple select
  // supports backup list
  else if ($data_type == 36) {
  	echo generate_select_list("form_$field_id", $list_id, $currvalue,
      $description, $showEmpty ? $empty_title : '', '', $onchange, '', null, true, $backup_list);
  	
  }
}

function generate_print_field($frow, $currvalue) {
  global $rootdir, $date_init, $ISSUE_TYPES;

  $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

  $data_type   = $frow['data_type'];
  $field_id    = $frow['field_id'];
  $list_id     = $frow['list_id'];
  $fld_length  = $frow['fld_length'];
  $backup_list = $frow['list_backup_id'];

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
  //  Supports backup lists.
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
      if ($lrow == 0 && !empty($backup_list)) {
        // since primary list did not map, try to map to backup list
        $lrow = sqlQuery("SELECT title FROM list_options " .
          "WHERE list_id = ? AND option_id = ?", array($backup_list,$currvalue));
        $tmp = xl_list_label($lrow['title']);
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
    if ($tmp === '') {
      $tmp = '&nbsp;';
    }
    else {
      $tmp = htmlspecialchars( $tmp, ENT_QUOTES);
    }
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
    $maxlength = htmlspecialchars( $frow['fld_rows'], ENT_QUOTES);
    echo "<textarea" .
      " cols='$fldlength'" .
      " rows='$maxlength'>" .
      $currescaped . "</textarea>";
  }

  // date
  else if ($data_type == 4) {
    $agestr = optionalAge($frow, $currvalue);
    if ($agestr) {
      echo "<table cellpadding='0' cellspacing='0'><tr><td class='text'>";
    }
    if ($currvalue === '') {
      echo '&nbsp;';
    }
    else {
      echo text(oeFormatShortDate($currvalue));
    }
    // Optional display of age or gestational age.
    if ($agestr) {
      echo "</td></tr><tr><td class='text'>" . text($agestr) . "</td></tr></table>";
    }
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

  // insurance company list
  else if ($data_type == 16) {
    $tmp = '';
    if ($currvalue) {
      $insprovs = getInsuranceProviders();
      foreach ($insprovs as $key => $ipname) {
        if ($currvalue == $key) {
          $tmp = $ipname;
        }
      }
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    if ($tmp === '') $tmp = '&nbsp;';
    else $tmp = htmlspecialchars($tmp, ENT_QUOTES);
    echo $tmp;
  }

  // issue types
  else if ($data_type == 17) {
    $tmp = '';
    if ($currvalue) {
      foreach ($ISSUE_TYPES as $key => $value) {
        if ($currvalue == $key) {
          $tmp = $value[1];
        }
      }
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    if ($tmp === '') $tmp = '&nbsp;';
    else $tmp = htmlspecialchars($tmp, ENT_QUOTES);
    echo $tmp;
  }

  // Visit categories.
  else if ($data_type == 18) {
    $tmp = '';
    if ($currvalue) {
      $crow = sqlQuery("SELECT pc_catid, pc_catname " .
        "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
        array($currvalue));
      $tmp = xl_appt_category($crow['pc_catname']);
      if (empty($tmp)) $tmp = "($currvalue)";
    }
    if ($tmp === '') { $tmp = '&nbsp;'; }
    else { $tmp = htmlspecialchars($tmp, ENT_QUOTES); }
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
  
  else if($data_type == 34){
    echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=".htmlspecialchars($list_id_esc,ENT_QUOTES)."' class='iframe_medium' style='text-decoration:none;color:black;'>";
    echo "<div id='form_{$field_id}_div' class='text-area'></div>";
    echo "<div style='display:none'><textarea name='form_{$field_id}' id='form_{$field_id}' stye='display:none'></textarea></div>";
    echo "</a>";
  }

  //facilities drop-down list
  else if ($data_type == 35) {
    if (empty($currvalue)){
      $currvalue = 0;
    }
    dropdown_facility($selected = $currvalue, $name = "form_$field_id_esc", $allow_unspecified = true, $allow_allfacilities = false);
  }

  //Multi-select
  // Supports backup lists.
  else if ($data_type == 36) {
  	if (empty($fld_length)) {
  		if ($list_id == 'titles') {
  			$fld_length = 3;
  		} else {
  			$fld_length = 10;
  		}
  	}
  	$tmp = '';
  	
  	$values_array = explode("|", $currvalue);

        $i=0;
  	foreach($values_array as $value) {
  		if ($value) {
  			$lrow = sqlQuery("SELECT title FROM list_options " .
  					"WHERE list_id = ? AND option_id = ?", array($list_id,$value));
  			$tmp = xl_list_label($lrow['title']);
			if ($lrow == 0 && !empty($backup_list)) {
				// since primary list did not map, try to map to backup list
				$lrow = sqlQuery("SELECT title FROM list_options " .
					"WHERE list_id = ? AND option_id = ?", array($backup_list,$currvalue));
				$tmp = xl_list_label($lrow['title']);
			}
  			if (empty($tmp)) $tmp = "($value)";
  		}
  		
  		if ($tmp === '') {
			$tmp = '&nbsp;';
		}
  		else {
			$tmp = htmlspecialchars( $tmp, ENT_QUOTES);
		}
                if ($i != 0 && $tmp != '&nbsp;') echo ",";
  		echo $tmp;
                $i++;
  	}
  }

}

function generate_display_field($frow, $currvalue) {
  global $ISSUE_TYPES;

  $data_type  = $frow['data_type'];
  $field_id   = isset($frow['field_id'])  ? $frow['field_id'] : null;
  $list_id    = $frow['list_id'];
  $backup_list = $frow['list_backup_id'];
  
  $s = '';

  // generic selection list or the generic selection list with add on the fly
  // feature, or radio buttons
  //  Supports backup lists for datatypes 1,26,33
  if ($data_type == 1 || $data_type == 26 || $data_type == 27 || $data_type == 33) {
    $lrow = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue) );
      $s = htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
    //if there is no matching value in the corresponding lists check backup list
    // only supported in data types 1,26,33
    if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 33)) {
      $lrow = sqlQuery("SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ?", array($backup_list,$currvalue) );
      $s = htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
    }
  }

  // simple text field
  else if ($data_type == 2) {
     $s = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
  }

  // long or multi-line text field
  else if ($data_type == 3) {
    $s = nl2br(htmlspecialchars($currvalue,ENT_NOQUOTES));
  }

  // date
  else if ($data_type == 4) {
    $s = '';
    $agestr = optionalAge($frow, $currvalue);
    if ($agestr) {
      $s .= "<table cellpadding='0' cellspacing='0'><tr><td class='text'>";
    }
    if ($currvalue === '') {
      $s .= '&nbsp;';
    }
    else {
      $s .= text(oeFormatShortDate($currvalue));
    }
    // Optional display of age or gestational age.
    if ($agestr) {
      $s .= "</td></tr><tr><td class='text'>" . text($agestr) . "</td></tr></table>";
    }
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

  // insurance company list
  else if ($data_type == 16) {
    $insprovs = getInsuranceProviders();
    foreach ($insprovs as $key => $ipname) {
      if ($currvalue == $key) {
        $s .= htmlspecialchars($ipname, ENT_NOQUOTES);
      }
    }
  }

  // issue types
  else if ($data_type == 17) {
    foreach ($ISSUE_TYPES as $key => $value) {
      if ($currvalue == $key) {
        $s .= htmlspecialchars($value[1], ENT_NOQUOTES);
      }
    }
  }

  // visit category
  else if ($data_type == 18) {
    $crow = sqlQuery("SELECT pc_catid, pc_catname " .
      "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
      array($currvalue));
    $s = htmlspecialchars($crow['pc_catname'],ENT_NOQUOTES);
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
        $s .= nl2br(htmlspecialchars(xl_list_label($lrow['title'])),ENT_NOQUOTES);
	    
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
    {//changes on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
       $smoke_codes = getSmokeCodes(); 
       if (!empty($reslist)) {
           if($smoke_codes[$reslist]!="")
               $code_desc = "( ".$smoke_codes[$reslist]." )";
           
           $s .= "<td class='text' valign='top'>" . generate_display_field(array('data_type'=>'1','list_id'=>$list_id),$reslist) . "&nbsp;".text($code_desc)."&nbsp;&nbsp;&nbsp;&nbsp;</td>";}
       
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
  
  else if($data_type == 34){
    $arr = explode("|*|*|*|",$currvalue);
    for($i=0;$i<sizeof($arr);$i++){
      $s.=$arr[$i];
    }
  }

  // facility
  else if ($data_type == 35) {
    $urow = sqlQuery("SELECT id, name FROM facility ".
      "WHERE id = ?", array($currvalue) );
    $s = htmlspecialchars($urow['name'],ENT_NOQUOTES);
  }

  // Multi select
  //  Supports backup lists
  else if ($data_type == 36) {
    $values_array = explode("|", $currvalue);
    
    $i = 0;
    foreach($values_array as $value) {
      $lrow = sqlQuery("SELECT title FROM list_options " .
          "WHERE list_id = ? AND option_id = ?", array($list_id,$value) );
      
      if ($lrow == 0 && !empty($backup_list)) {
      	//use back up list
      	$lrow = sqlQuery("SELECT title FROM list_options " .
      			"WHERE list_id = ? AND option_id = ?", array($backup_list,$value) );
      }
      
      if ($i > 0) {
        $s = $s . ", " . htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
	  } else {
        $s = htmlspecialchars(xl_list_label($lrow['title']),ENT_NOQUOTES);
      }

      $i++;
    }
  }

  return $s;
}

// Generate plain text versions of selected LBF field types.
// Currently used by interface/patient_file/download_template.php.
// More field types might need to be supported here in the future.
//
function generate_plaintext_field($frow, $currvalue) {
  global $ISSUE_TYPES;

  $data_type = $frow['data_type'];
  $field_id  = isset($frow['field_id']) ? $frow['field_id'] : null;
  $list_id   = $frow['list_id'];
  $backup_list = $frow['backup_list'];
  $s = '';

  // generic selection list or the generic selection list with add on the fly
  // feature, or radio buttons
  //  Supports backup lists (for datatypes 1,26,33)
  if ($data_type == 1 || $data_type == 26 || $data_type == 27 || $data_type == 33) {
    $lrow = sqlQuery("SELECT title FROM list_options " .
      "WHERE list_id = ? AND option_id = ?", array($list_id,$currvalue) );
    $s = xl_list_label($lrow['title']);
    //if there is no matching value in the corresponding lists check backup list
    // only supported in data types 1,26,33
    if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 33)) {
      $lrow = sqlQuery("SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ?", array($backup_list,$currvalue) );
      $s = xl_list_label($lrow['title']);
    }
  }

  // simple or long text field
  else if ($data_type == 2 || $data_type == 3 || $data_type == 15) {
    $s = $currvalue;
  }

  // date
  else if ($data_type == 4) {
    $s = oeFormatShortDate($currvalue);
    // Optional display of age or gestational age.
    $tmp = optionalAge($frow, $currvalue);
    if ($tmp) $s .= ' ' . $tmp;
  }

  // provider
  else if ($data_type == 10 || $data_type == 11) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = ?", array($currvalue) );
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

  // address book
  else if ($data_type == 14) {
    $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
      "WHERE id = ?", array($currvalue));
    $uname = $urow['lname'];
    if ($urow['fname']) $uname .= ", " . $urow['fname'];
    $s = $uname;
  }

  // insurance company list
  else if ($data_type == 16) {
    $insprovs = getInsuranceProviders();
    foreach ($insprovs as $key => $ipname) {
      if ($currvalue == $key) {
        $s .= $ipname;
      }
    }
  }

  // issue type
  else if ($data_type == 17) {
    foreach ($ISSUE_TYPES as $key => $value) {
      if ($currvalue == $key) {
        $s .= $value[1];
      }
    }
  }

  // visit category
  else if ($data_type == 18) {
    $crow = sqlQuery("SELECT pc_catid, pc_catname " .
      "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
      array($currvalue));
    $s = $crow['pc_catname'];
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
        if ($count++) $s .= "; ";
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      if (empty($avalue[$option_id])) continue;
      if ($s !== '') $s .= '; ';
      $s .= xl_list_label($lrow['title']) . ': ';
      $s .= $avalue[$option_id];
    }
  }

  // A set of exam results; 3 radio buttons and a text field.
  // This shows abnormal results only.
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
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
      if ($restype != '2') continue; // show abnormal results only
      if ($s !== '') $s .= '; ';
      $s .= xl_list_label($lrow['title']);
      if (!empty($resnote)) $s .= ': ' . $resnote;
    }
  }

  // the list of active allergies for the current patient
  else if ($data_type == 24) {
    $query = "SELECT title, comments FROM lists WHERE " .
      "pid = ? AND type = 'allergy' AND enddate IS NULL " .
      "ORDER BY begdate";
    $lres = sqlStatement($query, array($GLOBALS['pid']));
    $count = 0;
    while ($lrow = sqlFetchArray($lres)) {
      if ($count++) $s .= "; ";
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
      "WHERE list_id = ? ORDER BY seq, title", array($list_id));
    while ($lrow = sqlFetchArray($lres)) {
      $option_id = $lrow['option_id'];
      $restype = substr($avalue[$option_id], 0, 1);
      $resnote = substr($avalue[$option_id], 2);
      if (empty($restype) && empty($resnote)) continue;
      if ($s !== '') $s .= '; ';
      $s .= xl_list_label($lrow['title']);
      $restype = $restype ? xl('Yes') : xl('No');  
      $s .= $restype;
      if ($resnote) $s .= ' ' . $resnote;
    }
  }

  // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list for smoking status.
  else if ($data_type == 28 || $data_type == 32) {
    $tmp = explode('|', $currvalue);
    $resnote = count($tmp) > 0 ? $tmp[0] : '';
    $restype = count($tmp) > 1 ? $tmp[1] : '';
    $resdate = count($tmp) > 2 ? $tmp[2] : '';
    $reslist = count($tmp) > 3 ? $tmp[3] : '';
    $res = "";
    if ($restype == "current"       . $field_id) $res = xl('Current');
    if ($restype == "quit"          . $field_id) $res = xl('Quit');
    if ($restype == "never"         . $field_id) $res = xl('Never');
    if ($restype == "not_applicable". $field_id) $res = xl('N/A');

    if ($data_type == 28) {
      if (!empty($resnote)) $s .= $resnote;
    }
    // Tobacco field has a listbox, text box, date field and 3 radio buttons.
    else if ($data_type == 32) {
      if (!empty($reslist)) $s .= generate_plaintext_field(array('data_type'=>'1','list_id'=>$list_id),$reslist);
      if (!empty($resnote)) $s .= ' ' . $resnote;
    }
    if (!empty($res)) {
      if ($s !== '') $s .= ' ';
      $s .= xl('Status') . ' ' . $res;
    }
    if ($restype == "quit".$field_id) {
      if ($s !== '') $s .= ' ';
      $s .= $resdate;
    }
  }

  // Multi select
  //  Supports backup lists
  else if ($data_type == 36) {
    $values_array = explode("|", $currvalue);

    $i = 0;
    foreach($values_array as $value) {
      $lrow = sqlQuery("SELECT title FROM list_options " .
          "WHERE list_id = ? AND option_id = ?", array($list_id,$value) );

      if ($lrow == 0 && !empty($backup_list)) {
        //use back up list
        $lrow = sqlQuery("SELECT title FROM list_options " .
                        "WHERE list_id = ? AND option_id = ?", array($backup_list,$value) );
      }

      if ($i > 0) {
        $s = $s . ", " . xl_list_label($lrow['title']);
          } else {
        $s = xl_list_label($lrow['title']);
      }

      $i++;
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

    // filter out all the empty field data from the patient report.
    if (!empty($currvalue) && !($currvalue == '0000-00-00 00:00:00')) {
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
		$this_group = isset($frow['group_name']) ? $frow['group_name'] : "" ;
		$titlecols  = isset($frow['titlecols']) ? $frow['titlecols'] : "";
		$datacols   = isset($frow['datacols']) ? $frow['datacols'] : "";
		$data_type  = isset($frow['data_type']) ? $frow['data_type'] : "";
		$field_id   = isset($frow['field_id']) ? $frow['field_id'] : "";
		$list_id    = isset($frow['list_id']) ? $frow['list_id'] : "";
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
					$backup_list = $group_fields['list_backup_id'];
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
function get_layout_form_value($frow, $prefix='form_') {
  // Bring in $sanitize_all_escapes variable, which will decide
  //  the variable escaping method.
  global $sanitize_all_escapes;

  $maxlength = empty($frow['max_length']) ? 0 : intval($frow['max_length']);
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  $value  = '';
  if (isset($_POST["$prefix$field_id"])) {
    if ($data_type == 21) {
      // $_POST["$prefix$field_id"] is an array of checkboxes and its keys
      // must be concatenated into a |-separated string.
      foreach ($_POST["$prefix$field_id"] as $key => $val) {
        if (strlen($value)) $value .= '|';
        $value .= $key;
      }
    }
    else if ($data_type == 22) {
      // $_POST["$prefix$field_id"] is an array of text fields to be imploded
      // into "key:value|key:value|...".
      foreach ($_POST["$prefix$field_id"] as $key => $val) {
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$val";
      }
    }
    else if ($data_type == 23) {
      // $_POST["$prefix$field_id"] is an array of text fields with companion
      // radio buttons to be imploded into "key:n:notes|key:n:notes|...".
      foreach ($_POST["$prefix$field_id"] as $key => $val) {
        $restype = $_POST["radio_{$field_id}"][$key];
        if (empty($restype)) $restype = '0';
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$restype:$val";
      }
    }
    else if ($data_type == 25) {
      // $_POST["$prefix$field_id"] is an array of text fields with companion
      // checkboxes to be imploded into "key:n:notes|key:n:notes|...".
      foreach ($_POST["$prefix$field_id"] as $key => $val) {
        $restype = empty($_POST["check_{$field_id}"][$key]) ? '0' : '1';
        $val = str_replace('|', ' ', $val);
        if (strlen($value)) $value .= '|';
        $value .= "$key:$restype:$val";
      }
    }
    else if ($data_type == 28 || $data_type == 32) {
      // $_POST["$prefix$field_id"] is an date text fields with companion
      // radio buttons to be imploded into "notes|type|date".
      $restype = $_POST["radio_{$field_id}"];
      if (empty($restype)) $restype = '0';
      $resdate = str_replace('|', ' ', $_POST["date_$field_id"]);
      $resnote = str_replace('|', ' ', $_POST["$prefix$field_id"]);
      if ($data_type == 32)
      {
      //VicarePlus :: Smoking status data is imploded into "note|type|date|list".
      $reslist = str_replace('|', ' ', $_POST["$prefix$field_id"]);
      $res_text_note = str_replace('|', ' ', $_POST["{$prefix}text_$field_id"]);
      $value = "$res_text_note|$restype|$resdate|$reslist";
      }
      else
      $value = "$resnote|$restype|$resdate";
    }
    else if ($data_type == 36) {
	  $value_array = $_POST["form_$field_id"];
	  $i = 0;
	  foreach ($value_array as $key => $valueofkey) {
	    if ($i == 0) {
	      $value = $valueofkey;
	    } else {
	      $value =  $value . "|" . $valueofkey;
	    }
	    $i++;
	  }
    }
    else {
      $value = $_POST["$prefix$field_id"];
    }
  }

  // Better to die than to silently truncate data!
  if ($maxlength && $maxlength != 0 && strlen($value) > $maxlength)
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
      case 36:
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
function dropdown_facility($selected = '', $name = 'form_facility', $allow_unspecified = true, $allow_allfacilities = true, $disabled='') {
  $have_selected = false;
  $query = "SELECT id, name FROM facility ORDER BY name";
  $fres = sqlStatement($query);

  $name = htmlspecialchars($name, ENT_QUOTES);
  echo "   <select name=\"$name\" id=\"$name\" $disabled>\n";

  if ($allow_allfacilities) {
    $option_value = '';
    $option_selected_attr = '';	
    if ($selected == '') {
      $option_selected_attr = ' selected="selected"';
      $have_selected = true;
    }
    $option_content = htmlspecialchars('-- ' . xl('All Facilities') . ' --', ENT_NOQUOTES);
    echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
  } elseif ($allow_unspecified) {
  	$option_value = '0';
    $option_selected_attr = '';
    if ( $selected == '0' ) {
      $option_selected_attr = ' selected="selected"';
      $have_selected = true;
    }
    $option_content = htmlspecialchars('-- ' . xl('Unspecified') . ' --', ENT_NOQUOTES);
    echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    }
  
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

  if ($allow_unspecified && $allow_allfacilities) {
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
      echo "<td><a class='" . $class_string . "' href='" . $buttonLink . "'";
      if (!isset($_SESSION['patient_portal_onsite'])) {
        // prevent an error from occuring when calling the function from the patient portal
        echo " onclick='top.restoreSession()'";
      }
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
  $indicatorTag = isset($indicatorTag) ?  $indicatorTag : "";
  echo "<td><a " . $indicatorTag . " href='javascript:;' class='small' onclick='toggleIndicator(this,\"" .
    htmlspecialchars( $label, ENT_QUOTES) . "_ps_expand\")'><span class='text'><b>";
  echo htmlspecialchars( $title, ENT_NOQUOTES) . "</b></span>";

  if (isset($_SESSION['patient_portal_onsite'])) {
    // collapse all entries in the patient portal
    $text = xl('expand');
  }
  else if (getUserSetting($label."_ps_expand")) {
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
  else if (isset($_SESSION['patient_portal_onsite'])) {
    // collapse all entries in the patient portal
    $styling = "style='display:none'";
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

// Generic function to get the translated title value for a particular list option.
//
function getListItemTitle($list, $option) {
  $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = ? AND option_id = ?", array($list, $option));
  if (empty($row['title'])) return $option;
  return xl_list_label($row['title']);
}
//Added on 5-jun-2k14 (regarding get the smoking code descriptions)
function getSmokeCodes()
{
     $smoking_codes_arr = array();
     $smoking_codes = sqlStatement("SELECT option_id,codes FROM list_options WHERE list_id='smoking_status'");
     while($codes_row = sqlFetchArray($smoking_codes))
      {
          $smoking_codes_arr[$codes_row['option_id']] = $codes_row['codes'];
      }
     return $smoking_codes_arr;
}
?>
