<?php
// Copyright (C) 2007-2017 Rod Roark <rod@sunsetsystems.com>
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
// H = Read-only field copied from static history (this is obsolete)
// L = Lab Order ("ord_lab") types only (address book)
// N = Show in New Patient form
// O = Procedure Order ("ord_*") types only (address book)
// P = Default to previous value when current value is not yet set
// R = Distributor types only (address book)
// T = Use description as default Text
// U = Capitalize all letters (text fields)
// V = Vendor types only (address book)
// 0 = Read Only - the input element's "disabled" property is set
// 1 = Write Once (not editable when not empty) (text fields)
// 2 = Show descriptions instead of codes for billing code input

require_once("user.inc");
require_once("patient.inc");
require_once("lists.inc");
require_once(dirname(dirname(__FILE__)) . "/custom/code_types.inc.php");

use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$date_init = "";

function get_pharmacies()
{
    return sqlStatement("SELECT d.id, d.name, a.line1, a.city, " .
    "p.area_code, p.prefix, p.number FROM pharmacies AS d " .
    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
    "AND p.type = 2 " .
    "ORDER BY name, area_code, prefix, number");
}

function optionalAge($frow, $date, &$asof, $description = '')
{
    $asof = '';
    if (empty($date)) {
        return '';
    }

    $date = substr($date, 0, 10);
    if (isOption($frow['edit_options'], 'A') !== false) {
        $format = 0;
    } else if (isOption($frow['edit_options'], 'B') !== false) {
        $format = 3;
    } else {
        return '';
    }

    if (isOption($frow['form_id'], 'LBF') === 0) {
        $tmp = sqlQuery(
            "SELECT date FROM form_encounter WHERE " .
            "pid = ? AND encounter = ? ORDER BY id DESC LIMIT 1",
            array($GLOBALS['pid'], $GLOBALS['encounter'])
        );
        if (!empty($tmp['date'])) {
            $asof = substr($tmp['date'], 0, 10);
        }
    }
    if ($description === '') {
        $prefix = ($format ? xl('Gest age') : xl('Age')) . ' ';
    } else {
        $prefix = $description . ' ';
    }
    return $prefix . oeFormatAge($date, $asof, $format);
}

// Function to generate a drop-list.
//
function generate_select_list(
    $tag_name,
    $list_id,
    $currvalue,
    $title,
    $empty_name = ' ',
    $class = '',
    $onchange = '',
    $tag_id = '',
    $custom_attributes = null,
    $multiple = false,
    $backup_list = ''
) {
        $s = '';

    $tag_name_esc = attr($tag_name);

    if ($multiple) {
        $tag_name_esc = $tag_name_esc . "[]";
    }

    $s .= "<select name='$tag_name_esc'";

    if ($multiple) {
        $s .= " multiple='multiple'";
    }

    $tag_id_esc = attr($tag_name);

    if ($tag_id != '') {
        $tag_id_esc = attr($tag_id);
    }

    $s .= " id='$tag_id_esc'";

    if (!empty($class)) {
        $class_esc = attr($class);
        $s .= " class='form-control $class_esc'";
    } else {
        $s .= " class='form-control'";
    }

    if ($onchange) {
        $s .= " onchange='$onchange'";
    }

    if ($custom_attributes != null && is_array($custom_attributes)) {
        foreach ($custom_attributes as $attr => $val) {
            if (isset($custom_attributes [$attr])) {
                $s .= " " . attr($attr) . "='" . attr($val) . "'";
            }
        }
    }

    $selectTitle = attr($title);
    $s .= " title='$selectTitle'>";
    $selectEmptyName = xlt($empty_name);
    if ($empty_name) {
        $s .= "<option value=''>" . $selectEmptyName . "</option>";
    }

        // List order depends on language translation options.
        //  (Note we do not need to worry about the list order in the algorithm
        //   after the below code block since that is where searches for exceptions
        //   are done which include inactive items or items from a backup
        //   list; note these will always be shown at the bottom of the list no matter the
        //   chosen order.)
        $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        // sort by title
    if (($lang_id == '1' && !empty($GLOBALS['skip_english_translation'])) || !$GLOBALS['translate_lists']) {
        // do not translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "seq, title";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "title, seq";
        }

        $lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity=1 ORDER BY " . $order_by_sql, array($list_id));
    } else {
        // do translate
        if ($GLOBALS['gb_how_sort_list'] == '0') {
            // order by seq
            $order_by_sql = "lo.seq, IF(LENGTH(ld.definition),ld.definition,lo.title)";
        } else { //$GLOBALS['gb_how_sort_list'] == '1'
            // order by title
            $order_by_sql = "IF(LENGTH(ld.definition),ld.definition,lo.title), lo.seq";
        }

        $lres = sqlStatement("SELECT lo.option_id, lo.is_default, " .
        "IF(LENGTH(ld.definition),ld.definition,lo.title) AS title " .
        "FROM list_options AS lo " .
        "LEFT JOIN lang_constants AS lc ON lc.constant_name = lo.title " .
        "LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND " .
        "ld.lang_id = ? " .
        "WHERE lo.list_id = ?  AND lo.activity=1 " .
        "ORDER BY " . $order_by_sql, array($lang_id, $list_id));
    }

    $got_selected = false;

    while ($lrow = sqlFetchArray($lres)) {
        $selectedValues = explode("|", $currvalue);

        $optionValue = attr($lrow ['option_id']);
        $s .= "<option value='$optionValue'";

        if ((strlen($currvalue) == 0 && $lrow ['is_default']) || (strlen($currvalue) > 0 && in_array($lrow ['option_id'], $selectedValues))) {
            $s .= " selected";
            $got_selected = true;
        }

        // Already has been translated above (if applicable), so do not need to use
        // the xl_list_label() function here
        $optionLabel = text($lrow ['title']);
        $s .= ">$optionLabel</option>\n";
    }

    /*
	  To show the inactive item in the list if the value is saved to database
	  */
    if (!$got_selected && strlen($currvalue) > 0) {
        $lres_inactive = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 0 AND option_id = ? ORDER BY seq, title", array($list_id, $currvalue));
        $lrow_inactive = sqlFetchArray($lres_inactive);
        if ($lrow_inactive['option_id']) {
            $optionValue = htmlspecialchars($lrow_inactive['option_id'], ENT_QUOTES);
            $s .= "<option value='$optionValue' selected>" . htmlspecialchars(xl_list_label($lrow_inactive['title']), ENT_NOQUOTES) . "</option>\n";
            $got_selected = true;
        }
    }

    if (!$got_selected && strlen($currvalue) > 0 && !$multiple) {
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
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } else if (!$got_selected && strlen($currvalue) > 0 && $multiple) {
        //if not found in main list, display all selected values that exist in backup list
        $list_id = $backup_list;

        $got_selected_backup = false;
        if (!empty($backup_list)) {
            $lres_backup = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            while ($lrow_backup = sqlFetchArray($lres_backup)) {
                $selectedValues = explode("|", $currvalue);

                $optionValue = attr($lrow_backup['option_id']);

                if (in_array($lrow_backup ['option_id'], $selectedValues)) {
                    $s .= "<option value='$optionValue'";
                    $s .= " selected";
                    $optionLabel = text(xl_list_label($lrow_backup ['title']));
                    $s .= ">$optionLabel</option>\n";
                    $got_selected_backup = true;
                }
            }
        }

        if (!$got_selected_backup) {
            $selectedValues = explode("|", $currvalue);
            foreach ($selectedValues as $selectedValue) {
                $s .= "<option value='" . attr($selectedValue) . "'";
                $s .= " selected";
                $s .= ">* " . text($selectedValue) . " *</option>\n";
            }

            $s .= "</select>";
            $fontTitle = xlt('Please choose a valid selection from the list.');
            $fontText = xlt('Fix this');
            $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } else {
        $s .= "</select>";
    }

    return $s;
}

// Parsing for data type 31, static text.
function parse_static_text($frow)
{
    $tmp = $frow['description'];
    // Translate if it does not look like HTML.
    if (substr($tmp, 0, 1) != '<') {
        $tmp = nl2br(xl_layout_label($tmp));
    }
    $s = '';
    if ($frow['source'] == 'D' || $frow['source'] == 'H') {
        // Source is demographics or history. This case supports value substitution.
        while (preg_match('/^(.*?)\{(\w+)\}(.*)$/', $tmp, $matches)) {
            $s .= $matches[1];
            $tmprow = $frow;
            $tmprow['field_id'] = $matches[2];
            $s .= lbf_current_value($tmprow, 0, 0);
            $tmp = $matches[3];
        }
    }
    $s .= $tmp;
    return $s;
}

// $frow is a row from the layout_options table.
// $currvalue is the current value, if any, of the associated item.
//
function generate_form_field($frow, $currvalue)
{
    global $rootdir, $date_init, $ISSUE_TYPES, $code_types;

    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

    $data_type   = $frow['data_type'];
    $field_id    = $frow['field_id'];
    $list_id     = $frow['list_id'];
    $backup_list = $frow['list_backup_id'];

  // escaped variables to use in html
    $field_id_esc= htmlspecialchars($field_id, ENT_QUOTES);
    $list_id_esc = htmlspecialchars($list_id, ENT_QUOTES);

  // Added 5-09 by BM - Translate description if applicable
    $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');

  // Support edit option T which assigns the (possibly very long) description as
  // the default value.
    if (isOption($frow['edit_options'], 'T') !== false) {
        if (strlen($currescaped) == 0) {
            $currescaped = $description;
        }

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
        } else {
            $empty_title = $frow['empty_title'];
        }
    } else {
        $empty_title = "Unassigned";
    }

    $disabled = isOption($frow['edit_options'], '0') === false ? '' : 'disabled';

    $lbfchange = (
        strpos($frow['form_id'], 'LBF') === 0 ||
        strpos($frow['form_id'], 'LBT') === 0 ||
        $frow['form_id'] == 'DEM'             ||
        $frow['form_id'] == 'HIS'
    ) ? "checkSkipConditions();" : "";
    $lbfonchange = $lbfchange ? "onchange='$lbfchange'" : "";

  // generic single-selection list or Race and Ethnicity.
  // These data types support backup lists.
    if ($data_type == 1 || $data_type == 33) {
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            '',
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );
    } // simple text field
    else if ($data_type == 2) {
        $fldlength = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        echo "<input type='text'" .
        " class='form-control'" .
        " name='form_$field_id_esc'" .
        " id='form_$field_id_esc'" .
        " size='$fldlength'" .
        " $string_maxlength" .
        " title='$description'" .
        " value='$currescaped'";
        $tmp = $lbfchange;
        if (isOption($frow['edit_options'], 'C') !== false) {
            $tmp .= "capitalizeMe(this);";
        } else if (isOption($frow['edit_options'], 'U') !== false) {
            $tmp .= "this.value = this.value.toUpperCase();";
        }

        if ($tmp) {
            echo " onchange='$tmp'";
        }

        $tmp = htmlspecialchars($GLOBALS['gbl_mask_patient_id'], ENT_QUOTES);
        // If mask is for use at save time, treat as no mask.
        if (strpos($tmp, '^') !== false) {
            $tmp = '';
        }
        if ($field_id == 'pubpid' && strlen($tmp) > 0) {
            echo " onkeyup='maskkeyup(this,\"$tmp\")'";
            echo " onblur='maskblur(this,\"$tmp\")'";
        }

        if (isOption($frow['edit_options'], '1') !== false && strlen($currescaped) > 0) {
            echo " readonly";
        }

        if ($disabled) {
            echo ' disabled';
        }

        echo " />";
    } // long or multi-line text field
    else if ($data_type == 3) {
        $textCols = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $textRows = htmlspecialchars($frow['fld_rows'], ENT_QUOTES);
        echo "<textarea" .
        " name='form_$field_id_esc'" .
        " class='form-control'" .
        " id='form_$field_id_esc'" .
        " title='$description'" .
        " cols='$textCols'" .
        " rows='$textRows' $lbfonchange $disabled" .
        ">" . $currescaped . "</textarea>";
    } // date
    else if ($data_type == 4) {
        $age_asof_date = ''; // optionalAge() sets this
        $age_format = isOption($frow['edit_options'], 'A') === false ? 3 : 0;
        $agestr = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($agestr) {
            echo "<table cellpadding='0' cellspacing='0'><tr><td class='text'>";
        }

        $onchange_string = '';
        if (!$disabled && $agestr) {
            $onchange_string = "onchange=\"if (typeof(updateAgeString) == 'function') " .
            "updateAgeString('$field_id','$age_asof_date', $age_format, '$description')\"";
        }
        if ($data_type == 4) {
            $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
            if (!$modtmp) {
                $dateValue  = oeFormatShortDate(substr($currescaped, 0, 10));
                echo "<input type='text' size='10' class='datepicker form-control' name='form_$field_id_esc' id='form_$field_id_esc'" .
                " value='" .  attr($dateValue)  ."'";
            } else {
                $dateValue  = oeFormatDateTime(substr($currescaped, 0, 20), 0);
                echo "<input type='text' size='20' class='datetimepicker form-control' name='form_$field_id_esc' id='form_$field_id_esc'" .
                    " value='" . attr($dateValue) . "'";
            }
        }
        if (!$agestr) {
            echo " title='$description'";
        }

        echo " $onchange_string $lbfonchange $disabled />";

        // Optional display of age or gestational age.
        if ($agestr) {
            echo "</td></tr><tr><td id='span_$field_id' class='text'>" . text($agestr) . "</td></tr></table>";
        }
    } // provider list, local providers only
    else if ($data_type == 10) {
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND authorized = 1 " .
        "ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control'>";
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

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // provider list, including address book entries with an NPI number
    else if ($data_type == 11) {
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND ( authorized = 1 OR ( username = '' AND npi != '' ) ) " .
        "ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
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

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // pharmacy list
    else if ($data_type == 12) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value='0'></option>";
        $pres = get_pharmacies();
        $got_selected = false;
        while ($prow = sqlFetchArray($pres)) {
            $key = $prow['id'];
            $optionValue = htmlspecialchars($key, ENT_QUOTES);
            $optionLabel = htmlspecialchars($prow['name'] . ' ' . $prow['area_code'] . '-' .
            $prow['prefix'] . '-' . $prow['number'] . ' / ' .
            $prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            if ($currvalue == $key) {
                  echo " selected";
                  $got_selected = true;
            }

            echo ">$optionLabel</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // squads
    else if ($data_type == 13) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>&nbsp;</option>";
        $squads = acl_get_squads();
        if ($squads) {
            foreach ($squads as $key => $value) {
                $optionValue = htmlspecialchars($key, ENT_QUOTES);
                $optionLabel = htmlspecialchars($value[3], ENT_NOQUOTES);
                echo "<option value='$optionValue'";
                if ($currvalue == $key) {
                    echo " selected";
                }

                echo ">$optionLabel</option>\n";
            }
        }

        echo "</select>";
    } // Address book, preferring organization name if it exists and is not in
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
        if (isOption($frow['edit_options'], 'L') !== false) {
            $tmp = "abook_type = 'ord_lab'";
        } else if (isOption($frow['edit_options'], 'O') !== false) {
            $tmp = "abook_type LIKE 'ord\\_%'";
        } else if (isOption($frow['edit_options'], 'V') !== false) {
            $tmp = "abook_type LIKE 'vendor%'";
        } else if (isOption($frow['edit_options'], 'R') !== false) {
            $tmp = "abook_type LIKE 'dist'";
        } else {
            $tmp = "( username = '' OR authorized = 1 )";
        }

        $ures = sqlStatement("SELECT id, fname, lname, organization, username FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND $tmp " .
        "ORDER BY organization, lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>" . htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES) . "</option>";
        while ($urow = sqlFetchArray($ures)) {
            $uname = $urow['organization'];
            if (empty($uname) || substr($uname, 0, 1) == '(') {
                $uname = $urow['lname'];
                if ($urow['fname']) {
                    $uname .= ", " . $urow['fname'];
                }
            }

            $optionValue = htmlspecialchars($urow['id'], ENT_QUOTES);
            $optionLabel = htmlspecialchars($uname, ENT_NOQUOTES);
            echo "<option value='$optionValue'";
            // Failure to translate Local and External is not an error here;
            // they are only used as internal flags and must not be translated!
            $title = $urow['username'] ? 'Local' : 'External';
            $optionTitle = htmlspecialchars($title, ENT_QUOTES);
            echo " title='$optionTitle'";
            if ($urow['id'] == $currvalue) {
                echo " selected";
            }

            echo ">$optionLabel</option>";
        }

        echo "</select>";
    } // A billing code. If description matches an existing code type then that type is used.
    else if ($data_type == 15) {
        $codetype = '';
        if (!empty($frow['description']) && isset($code_types[$frow['description']])) {
            $codetype = $frow['description'];
        }
        $fldlength = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        //
        if (isOption($frow['edit_options'], '2') !== false && substr($frow['form_id'], 0, 3) == 'LBF') {
            // Option "2" generates a hidden input for the codes, and a matching visible field
            // displaying their descriptions. First step is computing the description string.
            $currdescstring = '';
            if (!empty($currvalue)) {
                $relcodes = explode(';', $currvalue);
                foreach ($relcodes as $codestring) {
                    if ($codestring === '') {
                        continue;
                    }

                    $code_text = lookup_code_descriptions($codestring);
                    if ($currdescstring !== '') {
                        $currdescstring .= '; ';
                    }

                    if (!empty($code_text)) {
                        $currdescstring .= $code_text;
                    } else {
                        $currdescstring .= $codestring;
                    }
                }
            }

            $currdescstring = attr($currdescstring);
            //
            echo "<input type='text'" .
            " name='form_$field_id_esc'" .
            " id='form_related_code'" .
            " size='$fldlength'" .
            " value='$currescaped'" .
            " style='display:none'" .
            " $lbfonchange readonly $disabled />";
            // Extra readonly input field for optional display of code description(s).
            echo "<input type='text'" .
            " name='form_$field_id_esc" . "__desc'" .
            " size='$fldlength'" .
            " title='$description'" .
            " value='$currdescstring'";
            if (!$disabled) {
                echo " onclick='sel_related(this,\"$codetype\")'";
            }

            echo "class='form-control'";
            echo " readonly $disabled />";
        } else {
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

            echo "class='form-control'";
            echo " $lbfonchange readonly $disabled />";
        }
    } // insurance company list
    else if ($data_type == 16) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'>";
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

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // issue types
    else if ($data_type == 17) {
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'>";
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
        } else {
            echo "</select>";
        }
    } // Visit categories.
    else if ($data_type == 18) {
        $cres = sqlStatement("SELECT pc_catid, pc_catname " .
        "FROM openemr_postcalendar_categories ORDER BY pc_catname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control' title='$description'" .
        " $lbfonchange $disabled>";
        echo "<option value=''>" . xlt($empty_title) . "</option>";
        $got_selected = false;
        while ($crow = sqlFetchArray($cres)) {
            $catid = $crow['pc_catid'];
            if (($catid < 9 && $catid != 5) || $catid == 11) {
                continue;
            }

            echo "<option value='" . attr($catid) . "'";
            if ($catid == $currvalue) {
                echo " selected";
                $got_selected = true;
            }

            echo ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <font color='red' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</font>";
        } else {
            echo "</select>";
        }
    } // a set of labeled checkboxes
    else if ($data_type == 21) {
        // If no list then it's a single checkbox and its value is "Yes" or empty.
        if (!$list_id) {
            echo "<input type='checkbox' name='form_{$field_id_esc}' " .
            "id='form_{$field_id_esc}' value='Yes' $lbfonchange";
            if ($currvalue) {
                echo " checked";
            }
            echo " $disabled />";
        } else {
            // In this special case, fld_length is the number of columns generated.
            $cols = max(1, $frow['fld_length']);
            $avalue = explode('|', $currvalue);
            $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            echo "<table cellpadding='0' cellspacing='0' width='100%' title='".attr($description)."'>";
            $tdpct = (int) (100 / $cols);
            for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                $option_id = $lrow['option_id'];
                $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
                // if ($count) echo "<br />";
                if ($count % $cols == 0) {
                    if ($count) {
                        echo "</tr>";
                    }
                    echo "<tr>";
                }
                echo "<td width='" . attr($tdpct) . "%' nowrap>";
                echo "<input type='checkbox' name='form_{$field_id_esc}[$option_id_esc]'" .
                "id='form_{$field_id_esc}[$option_id_esc]' class='form-control' value='1' $lbfonchange";
                if (in_array($option_id, $avalue)) {
                    echo " checked";
                }
                // Added 5-09 by BM - Translate label if applicable
                echo " $disabled />" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
                echo "</td>";
            }
            if ($count) {
                echo "</tr>";
                if ($count > $cols) {
                    // Add some space after multiple rows of checkboxes.
                    $cols = htmlspecialchars($cols, ENT_QUOTES);
                    echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
                }
            }
            echo "</table>";
        }
    } // a set of labeled text input fields
    else if ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $maxlength = $frow['max_length'];
            $string_maxlength = "";
            // if max_length is set to zero, then do not set a maxlength
            if ($maxlength) {
                $string_maxlength = "maxlength='".attr($maxlength)."'";
            }

            $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $optionValue = htmlspecialchars($avalue[$option_id], ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " class='form-control'" .
            " $string_maxlength" .
            " value='$optionValue'";
            echo " $lbfonchange $disabled /></td></tr>";
        }

        echo "</table>";
    } // a set of exam results; 3 radio buttons and a text field:
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
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr><td>&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('N/A'), ENT_NOQUOTES) .
        "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
        "<td class='bold'>" .
        htmlspecialchars(xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            for ($i = 0; $i < 3; ++$i) {
                $inputValue = htmlspecialchars($i, ENT_QUOTES);
                echo "<td><input type='radio'" .
                " name='radio_{$field_id_esc}[$option_id_esc]'" .
                " id='radio_{$field_id_esc}[$option_id_esc]'" .
                " value='$inputValue' $lbfonchange";
                if ($restype === "$i") {
                    echo " checked";
                }

                echo " $disabled /></td>";
            }

            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } // the list of active allergies for the current patient
  // this is read-only!
    else if ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
        "pid = ? AND type = 'allergy' AND enddate IS NULL " .
        "ORDER BY begdate";
        // echo "<!-- $query -->\n"; // debugging
        $lres = sqlStatement($query, array($GLOBALS['pid']));
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) {
                echo "<br />";
            }

            echo htmlspecialchars($lrow['title'], ENT_NOQUOTES);
            if ($lrow['comments']) {
                echo ' (' . htmlspecialchars($lrow['comments'], ENT_NOQUOTES) . ')';
            }
        }
    } // a set of labeled checkboxes, each with a text field:
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
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $option_id = htmlspecialchars($option_id, ENT_QUOTES);
            echo "<td><input type='checkbox' name='check_{$field_id_esc}[$option_id_esc]'" .
            " id='check_{$field_id_esc}[$option_id_esc]' class='form-control' value='1' $lbfonchange";
            if ($restype) {
                echo " checked";
            }

            echo " $disabled />&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            echo "<td><input type='text'" .
            " name='form_{$field_id_esc}[$option_id_esc]'" .
            " id='form_{$field_id_esc}[$option_id_esc]'" .
            " size='$fldlength'" .
            " class='form-control' " .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } // single-selection list with ability to add to it
    else if ($data_type == 26) {
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            'addtolistclass_'.$list_id,
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );
        // show the add button if user has access to correct list
        $inputValue = htmlspecialchars(xl('Add'), ENT_QUOTES);
        $outputAddButton = "<input type='button' id='addtolistid_" . $list_id_esc . "' fieldid='form_" .
        $field_id_esc . "' class='addtolist' value='$inputValue' $disabled />";
        if (aco_exist('lists', $list_id)) {
           // a specific aco exist for this list, so ensure access
            if (acl_check('lists', $list_id)) {
                echo $outputAddButton;
            }
        } else {
           // no specific aco exist for this list, so check for access to 'default' list
            if (acl_check('lists', 'default')) {
                echo $outputAddButton;
            }
        }
    } // a set of labeled radio buttons
    else if ($data_type == 27) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        $got_selected = false;
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            if ($count % $cols == 0) {
                if ($count) {
                    echo "</tr>";
                }

                echo "<tr>";
            }

            echo "<td width='" . attr($tdpct) . "%'>";
            echo "<input type='radio' name='form_{$field_id_esc}' class='form-control' id='form_{$field_id_esc}[$option_id_esc]'" .
            " value='$option_id_esc' $lbfonchange";
            if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
              (strlen($currvalue)  > 0 && $option_id == $currvalue)) {
                echo " checked";
                $got_selected = true;
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
            $fontTitle = htmlspecialchars(xl('Please choose a valid selection.'), ENT_QUOTES);
            $fontText = htmlspecialchars(xl('Fix this'), ENT_NOQUOTES);
            echo "$currescaped <font color='red' title='$fontTitle'>$fontText!</font>";
        }
    } // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list box for smoking status:
    else if ($data_type == 28 || $data_type == 32) {
        $tmp = explode('|', $currvalue);
        switch (count($tmp)) {
            case "4":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                $reslist = $tmp[3];
                break;
            case "3":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                break;
            default:
                $restype = $resdate = $resnote = "";
                break;
        }

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='".attr($maxlength)."'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

        $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars($resnote, ENT_QUOTES);
        $resdate = htmlspecialchars($resdate, ENT_QUOTES);
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr>";
        if ($data_type == 28) {
          // input text
            echo "<td><input type='text'" .
            " name='form_$field_id_esc'" .
            " id='form_$field_id_esc'" .
            " size='$fldlength'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            htmlspecialchars(xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
        } else if ($data_type == 32) {
          // input text
            echo "<tr><td><input type='text'" .
            " name='form_text_$field_id_esc'" .
            " id='form_text_$field_id_esc'" .
            " size='$fldlength'" .
            " class='form-control'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td></tr>";
            echo "<td>";
          //Selection list for smoking status
            $onchange = 'radioChange(this.options[this.selectedIndex].value)';//VicarePlus :: The javascript function for selection list.
            echo generate_select_list(
                "form_$field_id",
                $list_id,
                $reslist,
                $description,
                ($showEmpty ? $empty_title : ''),
                '',
                $onchange,
                '',
                ($disabled ? array('disabled' => 'disabled') : null)
            );
            echo "</td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . xlt('Status') . ":&nbsp;&nbsp;</td>";
        }

        // current
        echo "<td class='text' ><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " id='radio_{$field_id_esc}[current]'" .
        " class='form-control'" .
        " value='current" . $field_id_esc . "' $lbfonchange";
        if ($restype == "current" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " />" . xlt('Current') . "&nbsp;</td>";
        // quit
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " id='radio_{$field_id_esc}[quit]'" .
        " class='form-control'" .
        " value='quit".$field_id_esc."' $lbfonchange";
        if ($restype == "quit" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " $disabled />" . xlt('Quit') . "&nbsp;</td>";
        // quit date
        echo "<td class='text'><input type='text' size='6' class='datepicker' name='date_$field_id_esc' id='date_$field_id_esc'" .
        " value='$resdate'" .
        " title='$description'" .
        " $disabled />";
        echo "&nbsp;</td>";
        // never
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " class='form-control'" .
        " id='radio_{$field_id_esc}[never]'" .
        " value='never" . $field_id_esc . "' $lbfonchange";
        if ($restype == "never" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " />" . xlt('Never') . "&nbsp;</td>";
        // Not Applicable
        echo "<td class='text'><input type='radio'" .
        " class='form-control' " .
        " name='radio_{$field_id}'" .
        " id='radio_{$field_id}[not_applicable]'" .
        " value='not_applicable" . $field_id . "' $lbfonchange";
        if ($restype == "not_applicable" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " $disabled />" . xlt('N/A') . "&nbsp;</td>";
        //
        //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
        echo "<td class='text' ><div id='smoke_code'></div></td>";
        echo "</tr>";
        echo "</table>";
    } // static text.  read-only, of course.
    else if ($data_type == 31) {
        echo parse_static_text($frow);
    } //$data_type == 33
  // Race and Ethnicity. After added support for backup lists, this is now the same as datatype 1; so have migrated it there.
  //$data_type == 33

    else if ($data_type == 34) {
        $arr = explode("|*|*|*|", $currvalue);
        echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=".htmlspecialchars($list_id_esc, ENT_QUOTES)."' class='iframe_medium' style='text-decoration:none;color:black;'>";
        echo "<div id='form_{$field_id}_div' class='text-area' style='min-width:100pt'>" . $arr[0] . "</div>";
        echo "<div style='display:none'><textarea name='form_{$field_id}' id='form_{$field_id}' class='form-control' style='display:none' $lbfonchange $disabled>" . $currvalue . "</textarea></div>";
        echo "</a>";
    } //facilities drop-down list
    else if ($data_type == 35) {
        if (empty($currvalue)) {
            $currvalue = 0;
        }

        dropdown_facility(
            $selected = $currvalue,
            $name = "form_$field_id_esc",
            $allow_unspecified = true,
            $allow_allfacilities = false,
            $disabled,
            $lbfchange
        );
    } //multiple select
  // supports backup list
    else if ($data_type == 36) {
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            $showEmpty ? $empty_title : '',
            '',
            $lbfchange,
            '',
            null,
            true,
            $backup_list
        );
    } // Canvas and related elements for browser-side image drawing.
  // Note you must invoke lbf_canvas_head() (below) to use this field type in a form.
    else if ($data_type == 40) {
        // Unlike other field types, width and height are in pixels.
        $canWidth  = intval($frow['fld_length']);
        $canHeight = intval($frow['fld_rows']);
        if (empty($currvalue)) {
            if (preg_match('/\\bimage=([a-zA-Z0-9._-]*)/', $frow['description'], $matches)) {
                // If defined this is the filename of the default starting image.
                $currvalue = $GLOBALS['web_root'] . '/sites/' . $_SESSION['site_id'] . '/images/' . $matches[1];
            }
        }

        $mywidth  = 50 + ($canWidth  > 250 ? $canWidth  : 250);
        $myheight = 31 + ($canHeight > 261 ? $canHeight : 261);
        echo "<div id='form_$field_id_esc' style='width:$mywidth; height:$myheight;'></div>";
        // Hidden form field exists to send updated data to the server at submit time.
        echo "<input type='hidden' name='form_$field_id_esc' value='' />";
        // Hidden image exists to support initialization of the canvas.
        echo "<img src='" . attr($currvalue) . "' id='form_{$field_id_esc}_img' style='display:none'>";
        // $date_init is a misnomer but it's the place for browser-side setup logic.
        $date_init .= " lbfCanvasSetup('form_$field_id_esc', $canWidth, $canHeight);\n";
    }
}

function generate_print_field($frow, $currvalue)
{
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
        } else {
            $empty_title = $frow['empty_title'];
        }
    } else {
        $empty_title = "Unassigned";
    }

  // generic single-selection list
  //  Supports backup lists.
    if (false && ($data_type == 1 || $data_type == 26 || $data_type == 33)) {
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
            "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$currvalue));
            $tmp = xl_list_label($lrow['title']);
            if ($lrow == 0 && !empty($backup_list)) {
                  // since primary list did not map, try to map to backup list
                  $lrow = sqlQuery("SELECT title FROM list_options " .
                    "WHERE list_id = ? AND option_id = ?", array($backup_list,$currvalue));
                    $tmp = xl_list_label($lrow['title']);
            }

            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
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
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

        echo $tmp;
    } // simple text field
    else if ($data_type == 2 || $data_type == 15) {
        /*****************************************************************
      echo "<input type='text'" .
        " size='$fld_length'" .
        " value='$currescaped'" .
        " class='under'" .
        " />";
        *****************************************************************/
        if ($currescaped === '') {
            $currescaped = '&nbsp;';
        }

        echo $currescaped;
    } // long or multi-line text field
    else if ($data_type == 3) {
        $fldlength = htmlspecialchars($fld_length, ENT_QUOTES);
        $maxlength = htmlspecialchars($frow['fld_rows'], ENT_QUOTES);
        echo "<textarea" .
        " class='form-control' " .
        " cols='$fldlength'" .
        " rows='$maxlength'>" .
        $currescaped . "</textarea>";
    } // date
    else if ($data_type == 4) {
        $age_asof_date = '';
        $agestr = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($currvalue === '') {
            echo '&nbsp;';
        } else {
            $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
            if (!$modtmp) {
                echo text(oeFormatShortDate($currvalue));
            } else {
                echo text(oeFormatDateTime($currvalue));
            }
            if ($agestr) {
                echo "&nbsp;(" . text($agestr) . ")";
            }
        }
    } // provider list
    else if ($data_type == 10 || $data_type == 11) {
        $tmp = '';
        if ($currvalue) {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
            "WHERE id = ?", array($currvalue));
            $tmp = ucwords($urow['fname'] . " " . $urow['lname']);
            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
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
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } // pharmacy list
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

            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
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
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } // squads
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

            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
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
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } // Address book.
    else if ($data_type == 14) {
        $tmp = '';
        if ($currvalue) {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
            "WHERE id = ?", array($currvalue));
            $uname = $urow['lname'];
            if ($urow['fname']) {
                $uname .= ", " . $urow['fname'];
            }

            $tmp = $uname;
            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
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
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } // insurance company list
    else if ($data_type == 16) {
        $tmp = '';
        if ($currvalue) {
            $insprovs = getInsuranceProviders();
            foreach ($insprovs as $key => $ipname) {
                if ($currvalue == $key) {
                    $tmp = $ipname;
                }
            }

            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
        }

        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

        echo $tmp;
    } // issue types
    else if ($data_type == 17) {
        $tmp = '';
        if ($currvalue) {
            foreach ($ISSUE_TYPES as $key => $value) {
                if ($currvalue == $key) {
                    $tmp = $value[1];
                }
            }

            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
        }

        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

        echo $tmp;
    } // Visit categories.
    else if ($data_type == 18) {
        $tmp = '';
        if ($currvalue) {
            $crow = sqlQuery(
                "SELECT pc_catid, pc_catname " .
                "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
                array($currvalue)
            );
            $tmp = xl_appt_category($crow['pc_catname']);
            if (empty($tmp)) {
                $tmp = "($currvalue)";
            }
        }

        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } // a single checkbox or set of labeled checkboxes
    else if ($data_type == 21) {
        if (!$list_id) {
            echo "<input type='checkbox'";
            if ($currvalue) {
                echo " checked";
            }
            echo " />";
        } else {
            // In this special case, fld_length is the number of columns generated.
            $cols = max(1, $fld_length);
            $avalue = explode('|', $currvalue);
            $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            echo "<table cellpadding='0' cellspacing='0' width='100%'>";
            $tdpct = (int) (100 / $cols);
            for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                $option_id = $lrow['option_id'];
                if ($count % $cols == 0) {
                    if ($count) {
                        echo "</tr>";
                    }

                    echo "<tr>";
                }
                echo "<td width='" . attr($tdpct) . "%'>";
                echo "<input type='checkbox'";
                if (in_array($option_id, $avalue)) {
                    echo " checked";
                }
                echo ">" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
                echo "</td>";
            }
            if ($count) {
                echo "</tr>";
                if ($count > $cols) {
                    // Add some space after multiple rows of checkboxes.
                    $cols = htmlspecialchars($cols, ENT_QUOTES);
                    echo "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
                }
            }
            echo "</table>";
        }
    } // a set of labeled text input fields
    else if ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $fldlength = empty($fld_length) ?  20 : $fld_length;
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $inputValue = htmlspecialchars($avalue[$option_id], ENT_QUOTES);
            echo "<td><input type='text'" .
            " class='form-control' " .
            " size='$fldlength'" .
            " value='$inputValue'" .
            " class='under'" .
            " /></td></tr>";
        }

        echo "</table>";
    } // a set of exam results; 3 radio buttons and a text field:
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
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr><td>&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('N/A'), ENT_NOQUOTES) .
        "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
        "<td class='bold'>" .
        htmlspecialchars(xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='bold'>" .
        htmlspecialchars(xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
            for ($i = 0; $i < 3; ++$i) {
                echo "<td><input type='radio'";
                if ($restype === "$i") {
                    echo " checked";
                }

                echo " /></td>";
            }

            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " value='$resnote'" .
            " class='under form-control' /></td>" .
            "</tr>";
        }

        echo "</table>";
    } // the list of active allergies for the current patient
  // this is read-only!
    else if ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
        "pid = ? AND type = 'allergy' AND enddate IS NULL " .
        "ORDER BY begdate";
        $lres = sqlStatement($query, array($GLOBALS['pid']));
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) {
                echo "<br />";
            }

            echo htmlspecialchars($lrow['title'], ENT_QUOTES);
            if ($lrow['comments']) {
                echo htmlspecialchars(' (' . $lrow['comments'] . ')', ENT_QUOTES);
            }
        }
    } // a set of labeled checkboxes, each with a text field:
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
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";
            echo "<td><input type='checkbox'";
            if ($restype) {
                echo " checked";
            }

            echo " />&nbsp;</td>";
            $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
            $resnote = htmlspecialchars($resnote, ENT_QUOTES);
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " class='form-control' " .
            " value='$resnote'" .
            " class='under'" .
            " /></td>" .
            "</tr>";
        }

        echo "</table>";
    } // a set of labeled radio buttons
    else if ($data_type == 27 || $data_type == 1 || $data_type == 26 || $data_type == 33) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            if ($count % $cols == 0) {
                if ($count) {
                    echo "</tr>";
                }

                echo "<tr>";
            }
            echo "<td width='" . attr($tdpct) . "%'>";
            echo "<input type='radio'";
            if (strlen($currvalue)  > 0 && $option_id == $currvalue) {
                // Do not use defaults for these printable forms.
                echo " checked";
            }

            echo ">" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
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
    } // special case for history of lifestyle status; 3 radio buttons and a date text field:
    else if ($data_type == 28 || $data_type == 32) {
        $tmp = explode('|', $currvalue);
        switch (count($tmp)) {
            case "4":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2])   ;
                $reslist = $tmp[3];
                break;
            case "3":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                break;
            default:
                $restype = $resdate = $resnote = "";
                break;
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        echo "<table cellpadding='0' cellspacing='0'>";
        echo "<tr>";
        $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars($resnote, ENT_QUOTES);
        $resdate = htmlspecialchars($resdate, ENT_QUOTES);
        if ($data_type == 28) {
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " class='under'" .
            " value='$resnote' /></td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
            htmlspecialchars(xl('Status'), ENT_NOQUOTES).":&nbsp;</td>";
        } else if ($data_type == 32) {
            echo "<tr><td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$resnote' /></td></tr>";
            $fldlength = 30;
            $smoking_status_title = generate_display_field(array('data_type'=>'1','list_id'=>$list_id), $reslist);
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$smoking_status_title' /></td>";
            echo "<td class='bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".htmlspecialchars(xl('Status'), ENT_NOQUOTES).":&nbsp;&nbsp;</td>";
        }

        echo "<td><input type='radio' class='form-control'";
        if ($restype == "current".$field_id) {
            echo " checked";
        }

        echo "/>".htmlspecialchars(xl('Current'), ENT_NOQUOTES)."&nbsp;</td>";

        echo "<td><input type='radio' class='form-control'";
        if ($restype == "current".$field_id) {
            echo " checked";
        }

        echo "/>".htmlspecialchars(xl('Quit'), ENT_NOQUOTES)."&nbsp;</td>";

        echo "<td><input type='text' size='6'" .
        " value='$resdate'" .
        " class='under form-control'" .
        " /></td>";

        echo "<td><input type='radio' class='form-control'";
        if ($restype == "current".$field_id) {
            echo " checked";
        }

        echo " />".htmlspecialchars(xl('Never'), ENT_NOQUOTES)."</td>";

        echo "<td><input type='radio' class='form-control'";
        if ($restype == "not_applicable".$field_id) {
            echo " checked";
        }

        echo " />".htmlspecialchars(xl('N/A'), ENT_NOQUOTES)."&nbsp;</td>";
        echo "</tr>";
        echo "</table>";
    } // static text.  read-only, of course.
    else if ($data_type == 31) {
        echo parse_static_text($frow);
    } else if ($data_type == 34) {
        echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=".htmlspecialchars($list_id_esc, ENT_QUOTES)."' class='iframe_medium' style='text-decoration:none;color:black;'>";
        echo "<div id='form_{$field_id}_div' class='text-area'></div>";
        echo "<div style='display:none'><textarea name='form_{$field_id}' class='form-control' id='form_{$field_id}' stye='display:none'></textarea></div>";
        echo "</a>";
    } //facilities drop-down list
    else if ($data_type == 35) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT id, name FROM facility ORDER BY name");
        echo "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['id'];
            if ($count % $cols == 0) {
                if ($count) {
                    echo "</tr>";
                }
                echo "<tr>";
            }
            echo "<td width='" . attr($tdpct) . "%'>";
            echo "<input type='radio'";
            if (strlen($currvalue)  > 0 && $option_id == $currvalue) {
                // Do not use defaults for these printable forms.
                echo " checked";
            }
            echo ">" . htmlspecialchars($lrow['name']);
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
    } //Multi-select
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
        foreach ($values_array as $value) {
            if ($value) {
                $lrow = sqlQuery("SELECT title FROM list_options " .
                    "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$value));
                $tmp = xl_list_label($lrow['title']);
                if ($lrow == 0 && !empty($backup_list)) {
                        // since primary list did not map, try to map to backup list
                        $lrow = sqlQuery("SELECT title FROM list_options " .
                            "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$currvalue));
                        $tmp = xl_list_label($lrow['title']);
                }

                if (empty($tmp)) {
                    $tmp = "($value)";
                }
            }

            if ($tmp === '') {
                $tmp = '&nbsp;';
            } else {
                $tmp = htmlspecialchars($tmp, ENT_QUOTES);
            }

            if ($i != 0 && $tmp != '&nbsp;') {
                echo ",";
            }

            echo $tmp;
                $i++;
        }
    } // Image from canvas drawing
    else if ($data_type == 40) {
        if ($currvalue) {
            echo "<img src='" . attr($currvalue) . "'>";
        }
    }
}

function generate_display_field($frow, $currvalue)
{
    global $ISSUE_TYPES, $facilityService;

    $data_type  = $frow['data_type'];
    $field_id   = isset($frow['field_id'])  ? $frow['field_id'] : null;
    $list_id    = $frow['list_id'];
    $backup_list = isset($frow['list_backup_id']) ? $frow['list_backup_id'] : null;

    $s = '';

    // generic selection list or the generic selection list with add on the fly
    // feature
    if ($data_type == 1 || $data_type == 26 || $data_type == 33) {
        $lrow = sqlQuery("SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$currvalue));
          $s = htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
        //if there is no matching value in the corresponding lists check backup list
        // only supported in data types 1,26,33
        if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 33)) {
              $lrow = sqlQuery("SELECT title FROM list_options " .
              "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$currvalue));
              $s = htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
        }
    } // simple text field
    else if ($data_type == 2) {
         $s = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
    } // long or multi-line text field
    else if ($data_type == 3) {
        $s = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
    } // date
    else if ($data_type == 4) {
        $asof = ''; //not used here, but set to prevent a php warning when call optionalAge
        $s = '';
        $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');
        $age_asof_date = '';
        $agestr = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($currvalue === '') {
            $s .= '&nbsp;';
        } else {
            $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
            if (!$modtmp) {
                $s .= text(oeFormatShortDate($currvalue));
            } else {
                $s .= text(oeFormatDateTime($currvalue));
            }
            if ($agestr) {
                $s .= "&nbsp;(" . text($agestr) . ")";
            }
        }
    } // provider
    else if ($data_type == 10 || $data_type == 11) {
        $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue));
        $s = htmlspecialchars(ucwords($urow['fname'] . " " . $urow['lname']), ENT_NOQUOTES);
    } // pharmacy list
    else if ($data_type == 12) {
        $pres = get_pharmacies();
        while ($prow = sqlFetchArray($pres)) {
            $key = $prow['id'];
            if ($currvalue == $key) {
                $s .= htmlspecialchars($prow['name'] . ' ' . $prow['area_code'] . '-' .
                $prow['prefix'] . '-' . $prow['number'] . ' / ' .
                $prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
            }
        }
    } // squads
    else if ($data_type == 13) {
        $squads = acl_get_squads();
        if ($squads) {
            foreach ($squads as $key => $value) {
                if ($currvalue == $key) {
                    $s .= htmlspecialchars($value[3], ENT_NOQUOTES);
                }
            }
        }
    } // address book
    else if ($data_type == 14) {
        $urow = sqlQuery("SELECT fname, lname, specialty, organization FROM users " .
        "WHERE id = ?", array($currvalue));
        //ViSolve: To display the Organization Name if it exist. Else it will display the user name.
        if ($urow['organization'] !="") {
            $uname = $urow['organization'];
        } else {
            $uname = $urow['lname'];
            if ($urow['fname']) {
                $uname .= ", " . $urow['fname'];
            }
        }

        $s = htmlspecialchars($uname, ENT_NOQUOTES);
    } // billing code
    else if ($data_type == 15) {
        $s = '';
        if (!empty($currvalue)) {
            $relcodes = explode(';', $currvalue);
            foreach ($relcodes as $codestring) {
                if ($codestring === '') {
                    continue;
                }
                $tmp = lookup_code_descriptions($codestring);
                if ($s !== '') {
                    $s .= '; ';
                }
                if (!empty($tmp)) {
                    $s .= $tmp;
                } else {
                    $s .= $codestring . ' (' . xl('not found') . ')';
                }
            }
        }
    } // insurance company list
    else if ($data_type == 16) {
        $insprovs = getInsuranceProviders();
        foreach ($insprovs as $key => $ipname) {
            if ($currvalue == $key) {
                $s .= htmlspecialchars($ipname, ENT_NOQUOTES);
            }
        }
    } // issue types
    else if ($data_type == 17) {
        foreach ($ISSUE_TYPES as $key => $value) {
            if ($currvalue == $key) {
                $s .= htmlspecialchars($value[1], ENT_NOQUOTES);
            }
        }
    } // visit category
    else if ($data_type == 18) {
        $crow = sqlQuery(
            "SELECT pc_catid, pc_catname " .
            "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
            array($currvalue)
        );
        $s = htmlspecialchars($crow['pc_catname'], ENT_NOQUOTES);
    } // a single checkbox or set of labeled checkboxes
    else if ($data_type == 21) {
        if (!$list_id) {
            $s .= $currvalue ? '[ x ]' : '[ &nbsp;&nbsp; ]';
        } else {
            // In this special case, fld_length is the number of columns generated.
            $cols = max(1, $frow['fld_length']);
            $avalue = explode('|', $currvalue);
            $lres = sqlStatement("SELECT * FROM list_options " .
                "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            $s .= "<table cellspacing='0' cellpadding='0'>";
            for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                $option_id = $lrow['option_id'];
                $option_id_esc = text($option_id);
                if ($count % $cols == 0) {
                    if ($count) {
                        $s .= "</tr>";
                    }
                    $s .= "<tr>";
                }
                $s .= "<td nowrap>";
                $checked = in_array($option_id, $avalue);
                $s .= $checked ? '[ x ]' : '[ &nbsp;&nbsp; ]';
                $s .= '&nbsp;' . text(xl_list_label($lrow['title'])). '&nbsp;&nbsp;';
                $s .= "</td>";
            }
            if ($count) {
                $s .= "</tr>";
            }
            $s .= "</table>";
        }
    } // a set of labeled text input fields
    else if ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            if (empty($avalue[$option_id])) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . ":&nbsp;</td>";

            $s .= "<td class='text' valign='top'>" . htmlspecialchars($avalue[$option_id], ENT_NOQUOTES) . "</td></tr>";
        }

        $s .= "</table>";
    } // a set of exam results; 3 radio buttons and a text field:
    else if ($data_type == 23) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $restype = ($restype == '1') ? xl('Normal') : (($restype == '2') ? xl('Abnormal') : xl('N/A'));
            // $s .= "<td class='text' valign='top'>$restype</td></tr>";
            // $s .= "<td class='text' valign='top'>$resnote</td></tr>";
            $s .= "<td class='text' valign='top'>" . htmlspecialchars($restype, ENT_NOQUOTES) . "&nbsp;</td>";
            $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "</td>";
            $s .= "</tr>";
        }

        $s .= "</table>";
    } // the list of active allergies for the current patient
    else if ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
        "pid = ? AND type = 'allergy' AND enddate IS NULL " .
        "ORDER BY begdate";
        // echo "<!-- $query -->\n"; // debugging
        $lres = sqlStatement($query, array($GLOBALS['pid']));
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) {
                $s .= "<br />";
            }

            $s .= htmlspecialchars($lrow['title'], ENT_NOQUOTES);
            if ($lrow['comments']) {
                $s .= ' (' . htmlspecialchars($lrow['comments'], ENT_NOQUOTES) . ')';
            }
        }
    } // a set of labeled checkboxes, each with a text field:
    else if ($data_type == 25) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='bold' valign='top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $restype = $restype ? xl('Yes') : xl('No');
            $s .= "<td class='text' valign='top'>" . htmlspecialchars($restype, ENT_NOQUOTES) . "&nbsp;</td>";
            $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "</td>";
            $s .= "</tr>";
        }

        $s .= "</table>";
    } // a set of labeled radio buttons
    else if ($data_type == 27) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT * FROM list_options " .
          "WHERE list_id = ? ORDER BY seq, title", array($list_id));
        $s .= "<table cellspacing='0' cellpadding='0'>";
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            $option_id_esc = text($option_id);
            if ($count % $cols == 0) {
                if ($count) {
                    $s .= "</tr>";
                }
                $s .= "<tr>";
            }
            $s .= "<td>";
            $checked = ((strlen($currvalue) == 0 && $lrow['is_default']) ||
                (strlen($currvalue)  > 0 && $option_id == $currvalue));
            $s .= $checked ? '[ x ]' : '[ &nbsp;&nbsp; ]';
            $s .= '&nbsp;' . text(xl_list_label($lrow['title'])). '&nbsp;&nbsp;';
            $s .= "</td>";
        }
        if ($count) {
            $s .= "</tr>";
        }
        $s .= "</table>";
    } // special case for history of lifestyle status; 3 radio buttons and a date text field:
    // VicarePlus :: A selection list for smoking status.
    else if ($data_type == 28 || $data_type == 32) {
        $tmp = explode('|', $currvalue);
        switch (count($tmp)) {
            case "4":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                $reslist = $tmp[3];
                break;
            case "3":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = oeFormatShortDate($tmp[2]);
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                break;
            default:
                $restype = $resdate = $resnote = "";
                break;
        }

        $s .= "<table cellpadding='0' cellspacing='0'>";

        $s .= "<tr>";
        $res = "";
        if ($restype == "current".$field_id) {
            $res = xl('Current');
        }

        if ($restype == "quit".$field_id) {
            $res = xl('Quit');
        }

        if ($restype == "never".$field_id) {
            $res = xl('Never');
        }

        if ($restype == "not_applicable".$field_id) {
            $res = xl('N/A');
        }

        // $s .= "<td class='text' valign='top'>$restype</td></tr>";
        // $s .= "<td class='text' valign='top'>$resnote</td></tr>";
        if ($data_type == 28) {
            if (!empty($resnote)) {
                $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            }
        } //VicarePlus :: Tobacco field has a listbox, text box, date field and 3 radio buttons.
        else if ($data_type == 32) {//changes on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
            $smoke_codes = getSmokeCodes();
            if (!empty($reslist)) {
                if ($smoke_codes[$reslist]!="") {
                    $code_desc = "( ".$smoke_codes[$reslist]." )";
                }

                $s .= "<td class='text' valign='top'>" . generate_display_field(array('data_type'=>'1','list_id'=>$list_id), $reslist) . "&nbsp;".text($code_desc)."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            }

            if (!empty($resnote)) {
                $s .= "<td class='text' valign='top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "&nbsp;&nbsp;</td>";
            }
        }

        if (!empty($res)) {
            $s .= "<td class='text' valign='top'><b>" . htmlspecialchars(xl('Status'), ENT_NOQUOTES) . "</b>:&nbsp;" . htmlspecialchars($res, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        if ($restype == "quit".$field_id) {
            $s .= "<td class='text' valign='top'>" . htmlspecialchars($resdate, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        $s .= "</tr>";
        $s .= "</table>";
    } // static text.  read-only, of course.
    else if ($data_type == 31) {
        $s .= parse_static_text($frow);
    } else if ($data_type == 34) {
        $arr = explode("|*|*|*|", $currvalue);
        for ($i=0; $i<sizeof($arr); $i++) {
            $s.=$arr[$i];
        }
    } // facility
    else if ($data_type == 35) {
        $urow = $facilityService->getById($currvalue);
        $s = htmlspecialchars($urow['name'], ENT_NOQUOTES);
    } // Multi select
  //  Supports backup lists
    else if ($data_type == 36) {
        $values_array = explode("|", $currvalue);
        $i = 0;
        foreach ($values_array as $value) {
            $lrow = sqlQuery("SELECT title FROM list_options " .
            "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$value));
            if ($lrow == 0 && !empty($backup_list)) {
                  //use back up list
                  $lrow = sqlQuery("SELECT title FROM list_options " .
                    "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$value));
            }

            if ($i > 0) {
                  $s = $s . ", " . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
            } else {
                $s = htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
            }

            $i++;
        }
    } // Image from canvas drawing
    else if ($data_type == 40) {
        if ($currvalue) {
            $s .= "<img src='" . attr($currvalue) . "'>";
        }
    }

    return $s;
}

// Generate plain text versions of selected LBF field types.
// Currently used by interface/patient_file/download_template.php and interface/main/finder/dynamic_finder_ajax.php.
// More field types might need to be supported here in the future.
//
function generate_plaintext_field($frow, $currvalue)
{
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
        "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$currvalue));
        $s = xl_list_label($lrow['title']);
        //if there is no matching value in the corresponding lists check backup list
        // only supported in data types 1,26,33
        if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 33)) {
              $lrow = sqlQuery("SELECT title FROM list_options " .
              "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$currvalue));
              $s = xl_list_label($lrow['title']);
        }
    } // simple or long text field
    else if ($data_type == 2 || $data_type == 3 || $data_type == 15) {
        $s = $currvalue;
    } // date
    else if ($data_type == 4) {
        $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
        if (!$modtmp) {
            $s = text(oeFormatShortDate($currvalue));
        } else {
            $s = text(oeFormatDateTime($currvalue));
        }
        $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');
        $age_asof_date = '';
        // Optional display of age or gestational age.
        $tmp = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($tmp) {
            $s .= ' ' . $tmp;
        }
    } // provider
    else if ($data_type == 10 || $data_type == 11) {
        $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue));
        $s = ucwords($urow['fname'] . " " . $urow['lname']);
    } // pharmacy list
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
    } // address book
    else if ($data_type == 14) {
        $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue));
        $uname = $urow['lname'];
        if ($urow['fname']) {
            $uname .= ", " . $urow['fname'];
        }

        $s = $uname;
    } // insurance company list
    else if ($data_type == 16) {
        $insprovs = getInsuranceProviders();
        foreach ($insprovs as $key => $ipname) {
            if ($currvalue == $key) {
                $s .= $ipname;
            }
        }
    } // issue type
    else if ($data_type == 17) {
        foreach ($ISSUE_TYPES as $key => $value) {
            if ($currvalue == $key) {
                $s .= $value[1];
            }
        }
    } // visit category
    else if ($data_type == 18) {
        $crow = sqlQuery(
            "SELECT pc_catid, pc_catname " .
            "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
            array($currvalue)
        );
        $s = $crow['pc_catname'];
    } // a set of labeled checkboxes
    else if ($data_type == 21) {
        if (!$list_id) {
            $s .= $currvalue ? xlt('Yes') : xlt('No');
        } else {
            $avalue = explode('|', $currvalue);
            $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            $count = 0;
            while ($lrow = sqlFetchArray($lres)) {
                $option_id = $lrow['option_id'];
                if (in_array($option_id, $avalue)) {
                    if ($count++) {
                        $s .= "; ";
                    }
                    $s .= xl_list_label($lrow['title']);
                }
            }
        }
    } // a set of labeled text input fields
    else if ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            if (empty($avalue[$option_id])) {
                continue;
            }

            if ($s !== '') {
                $s .= '; ';
            }

            $s .= xl_list_label($lrow['title']) . ': ';
            $s .= $avalue[$option_id];
        }
    } // A set of exam results; 3 radio buttons and a text field.
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
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            if ($restype != '2') {
                continue; // show abnormal results only
            }

            if ($s !== '') {
                $s .= '; ';
            }

            $s .= xl_list_label($lrow['title']);
            if (!empty($resnote)) {
                $s .= ': ' . $resnote;
            }
        }
    } // the list of active allergies for the current patient
    else if ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
        "pid = ? AND type = 'allergy' AND enddate IS NULL " .
        "ORDER BY begdate";
        $lres = sqlStatement($query, array($GLOBALS['pid']));
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) {
                $s .= "; ";
            }

            $s .= $lrow['title'];
            if ($lrow['comments']) {
                $s .= ' (' . $lrow['comments'] . ')';
            }
        }
    } // a set of labeled checkboxes, each with a text field:
    else if ($data_type == 25) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            if ($s !== '') {
                $s .= '; ';
            }

            $s .= xl_list_label($lrow['title']);
            $restype = $restype ? xl('Yes') : xl('No');
            $s .= $restype;
            if ($resnote) {
                $s .= ' ' . $resnote;
            }
        }
    } // special case for history of lifestyle status; 3 radio buttons and a date text field:
  // VicarePlus :: A selection list for smoking status.
    else if ($data_type == 28 || $data_type == 32) {
        $tmp = explode('|', $currvalue);
        $resnote = count($tmp) > 0 ? $tmp[0] : '';
        $restype = count($tmp) > 1 ? $tmp[1] : '';
        $resdate = count($tmp) > 2 ? oeFormatShortDate($tmp[2]) : '';
        $reslist = count($tmp) > 3 ? $tmp[3] : '';
        $res = "";
        if ($restype == "current"       . $field_id) {
            $res = xl('Current');
        }

        if ($restype == "quit"          . $field_id) {
            $res = xl('Quit');
        }

        if ($restype == "never"         . $field_id) {
            $res = xl('Never');
        }

        if ($restype == "not_applicable". $field_id) {
            $res = xl('N/A');
        }

        if ($data_type == 28) {
            if (!empty($resnote)) {
                $s .= $resnote;
            }
        } // Tobacco field has a listbox, text box, date field and 3 radio buttons.
        else if ($data_type == 32) {
            if (!empty($reslist)) {
                $s .= generate_plaintext_field(array('data_type'=>'1','list_id'=>$list_id), $reslist);
            }

            if (!empty($resnote)) {
                $s .= ' ' . $resnote;
            }
        }

        if (!empty($res)) {
            if ($s !== '') {
                $s .= ' ';
            }

            $s .= xl('Status') . ' ' . $res;
        }

        if ($restype == "quit".$field_id) {
            if ($s !== '') {
                $s .= ' ';
            }

            $s .= $resdate;
        }
    } // Multi select
  //  Supports backup lists
    else if ($data_type == 36) {
        $values_array = explode("|", $currvalue);

        $i = 0;
        foreach ($values_array as $value) {
            $lrow = sqlQuery("SELECT title FROM list_options " .
            "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$value));

            if ($lrow == 0 && !empty($backup_list)) {
                  //use back up list
                  $lrow = sqlQuery("SELECT title FROM list_options " .
                    "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$value));
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

function disp_end_cell()
{
    global $item_count, $cell_count;
    if ($item_count > 0) {
        echo "</td>";
        $item_count = 0;
    }
}

function disp_end_row()
{
    global $cell_count, $CPR;
    disp_end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR;
        ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function disp_end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        disp_end_row();
    }
}

// Accumulate action conditions into a JSON expression for the browser side.
function accumActionConditions($field_id, &$condition_str, &$condarr)
{
    $conditions = empty($condarr) ? array() : unserialize($condarr);
    $action = 'skip';
    foreach ($conditions as $key => $condition) {
        if ($key === 'action') {
            // If specified this should be the first array item.
            if ($condition) {
                $action = $condition;
            }
            continue;
        }
        if (empty($condition['id'])) {
            continue;
        }
        $andor = empty($condition['andor']) ? '' : $condition['andor'];
        if ($condition_str) {
            $condition_str .= ",\n";
        }
        $condition_str .= "{" .
            "target:'"   . addslashes($field_id)              . "', " .
            "action:'"   . addslashes($action)                . "', " .
            "id:'"       . addslashes($condition['id'])       . "', " .
            "itemid:'"   . addslashes($condition['itemid'])   . "', " .
            "operator:'" . addslashes($condition['operator']) . "', " .
            "value:'"    . addslashes($condition['value'])    . "', " .
            "andor:'"    . addslashes($andor)                 . "'}";
    }
}

// This checks if the given field with the given value should have an action applied.
// Originally the only action was skip, but now you can also set the field to a specified value.
// It somewhat mirrors the checkSkipConditions function in options.js.php.
// If you use this for multiple layouts in the same script, you should
// clear $sk_layout_items before each layout.
function isSkipped(&$frow, $currvalue)
{
    global $sk_layout_items;

    // Accumulate an array of the encountered fields and their values.
    // It is assumed that fields appear before they are tested by another field.
    // TBD: Bad assumption?
    $field_id = $frow['field_id'];
    if (!is_array($sk_layout_items)) {
        $sk_layout_items = array();
    }
    $sk_layout_items[$field_id] = array('row' => $frow, 'value' => $currvalue);

    if (empty($frow['conditions'])) {
        return false;
    }

    $skiprows  = unserialize($frow['conditions']);
    $prevandor = '';
    $prevcond  = false;
    $datatype  = $frow['data_type'];
    $action    = 'skip'; // default action if none specified

    foreach ($skiprows as $key => $skiprow) {
        // id         referenced field id
        // itemid     referenced array key if applicable
        // operator   "eq", "ne", "se" or "ns"
        // value      if eq or ne, some string to compare with
        // andor      "and", "or" or empty

        if ($key === 'action') {
            // Action value is a string. It can be "skip", or "value=" followed by a value.
            $action = $skiprow;
            continue;
        }

        if (empty($skiprow['id'])) {
            continue;
        }

        $id = $skiprow['id'];
        if (!isset($sk_layout_items[$id])) {
            error_log("Function isSkipped() cannot find skip source field '$id'.");
            continue;
        }
        $itemid   = $skiprow['itemid'];
        $operator = $skiprow['operator'];
        $skipval  = $skiprow['value'];
        $srcvalue = $sk_layout_items[$id]['value'];
        $src_datatype = $sk_layout_items[$id]['row']['data_type'];
        $src_list_id  = $sk_layout_items[$id]['row']['list_id'];

        // Some data types use itemid and we have to dig for their value.
        if ($src_datatype == 21 && $src_list_id) { // array of checkboxes
            $tmp = explode('|', $srcvalue);
            $srcvalue = in_array($itemid, $tmp);
        } else if ($src_datatype == 22 || $src_datatype == 23 || $src_datatype == 25) {
            $tmp = explode('|', $srcvalue);
            $srcvalue = '';
            foreach ($tmp as $tmp2) {
                if (strpos($tmp2, "$itemid:") === 0) {
                    if ($datatype == 22) {
                        $srcvalue = substr($tmp2, strlen($itemid) + 1);
                    } else {
                        $srcvalue = substr($tmp2, strlen($itemid) + 1, 1);
                    }
                }
            }
        }

        // Compute the result of the test for this condition row.
        // PHP's looseness with variable type conversion helps us here.
        $condition = false;
        if ($operator == 'eq') {
            $condition = $srcvalue == $skipval;
        } else if ($operator == 'ne') {
            $condition = $srcvalue != $skipval;
        } else if ($operator == 'se') {
            $condition = $srcvalue == true;
        } else if ($operator == 'ns') {
            $condition = $srcvalue != true;
        } else {
            error_log("Unknown skip operator '$operator' for field '$field_id'.");
        }

        // Logic to accumulate multiple conditions for the same target.
        if ($prevandor == 'and') {
            $condition = $condition && $prevcond;
        } else if ($prevandor == 'or') {
            $condition = $condition || $prevcond;
        }
        $prevandor = $skiprow['andor'];
        $prevcond = $condition;
    }
    return $prevcond ? $action : '';
}

// Load array of names of the given layout and its groups.
function getLayoutProperties($formtype, &$grparr, $sel = "grp_title")
{
    if ($sel != '*' && strpos($sel, 'grp_group_id') === false) {
        $sel = "grp_group_id, $sel";
    }
    $gres = sqlStatement("SELECT $sel FROM layout_group_properties WHERE grp_form_id = ? " .
        "ORDER BY grp_group_id", array($formtype));
    while ($grow = sqlFetchArray($gres)) {
        $grparr[$grow['grp_group_id']] = $grow;
    }
}

function display_layout_rows($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    $grparr = array();
    getLayoutProperties($formtype, $grparr, '*');

    $TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_id, seq", array($formtype));

    while ($frow = sqlFetchArray($fres)) {
        $this_group = $frow['group_id'];
        $titlecols  = $frow['titlecols'];
        $datacols   = $frow['datacols'];
        $data_type  = $frow['data_type'];
        $field_id   = $frow['field_id'];
        $list_id    = $frow['list_id'];
        $currvalue  = '';

        $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];

        if ($formtype == 'DEM') {
            if (strpos($field_id, 'em_') === 0) {
                // Skip employer related fields, if it's disabled.
                if ($GLOBALS['omit_employers']) {
                    continue;
                }

                $tmp = substr($field_id, 3);
                if (isset($result2[$tmp])) {
                    $currvalue = $result2[$tmp];
                }
            } else {
                if (isset($result1[$field_id])) {
                    $currvalue = $result1[$field_id];
                }
            }
        } else {
            if (isset($result1[$field_id])) {
                $currvalue = $result1[$field_id];
            }
        }

        // Handle a data category (group) change.
        if (strcmp($this_group, $last_group) != 0) {
            $group_name = $grparr[$this_group]['grp_title'];
            // totally skip generating the employer category, if it's disabled.
            if ($group_name === 'Employer' && $GLOBALS['omit_employers']) {
                continue;
            }

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
                    echo text(xl_layout_label($group_name));
                    $group_name = '';
                } else {
                    echo "<td valign='top'>&nbsp;";
                }

                  echo "</td>";
            }

            if ($item_count == 0 && $titlecols == 0) {
                $titlecols = 1;
            }

          // Handle starting of a new label cell.
            if ($titlecols > 0) {
                disp_end_cell();
                //echo "<td class='label_custom' colspan='$titlecols' valign='top'";
                $titlecols_esc = htmlspecialchars($titlecols, ENT_QUOTES);
                echo "<td class='label_custom' colspan='$titlecols_esc' ";
                //if ($cell_count == 2) echo " style='padding-left:10pt'";
                echo ">";
                $cell_count += $titlecols;
            }

            ++$item_count;

            // Added 5-09 by BM - Translate label if applicable
            if ($frow['title']) {
                $tmp = xl_layout_label($frow['title']);
                echo text($tmp);
                // Append colon only if label does not end with punctuation.
                if (strpos('?!.,:-=', substr($tmp, -1, 1)) === false) {
                    echo ':';
                }
            } else {
                echo "&nbsp;";
            }

          // Handle starting of a new data cell.
            if ($datacols > 0) {
                disp_end_cell();
                //echo "<td class='text data' colspan='$datacols' valign='top'";
                $datacols_esc = htmlspecialchars($datacols, ENT_QUOTES);
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

function display_layout_tabs($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    $grparr = array();
    getLayoutProperties($formtype, $grparr);

    $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
        "WHERE form_id = ? AND uor > 0 " .
        "ORDER BY group_id", array($formtype));

    $first = true;
    while ($frow = sqlFetchArray($fres)) {
        $this_group = $frow['group_id'];
        // $group_name = substr($this_group, 1);
        $group_name = $grparr[$this_group]['grp_title'];
        if ($group_name === 'Employer' && $GLOBALS['omit_employers']) {
            continue;
        }
?>
        <li <?php echo $first ? 'class="current"' : '' ?>>
        <a href="#" id="header_tab_<?php echo htmlspecialchars($group_name, ENT_QUOTES); ?>">
        <?php echo htmlspecialchars(xl_layout_label($group_name), ENT_NOQUOTES); ?></a>
        </li>
<?php
        $first = false;
    }
}

function display_layout_tabs_data($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    $grparr = array();
    getLayoutProperties($formtype, $grparr, '*');

    $TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

    $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
        "WHERE form_id = ? AND uor > 0 " .
        "ORDER BY group_id", array($formtype));

    $first = true;
    while ($frow = sqlFetchArray($fres)) {
        $this_group = isset($frow['group_id']) ? $frow['group_id'] : "" ;

        if ($grparr[$this_group]['grp_columns'] === 'Employer' && $GLOBALS['omit_employers']) {
            continue;
        }
        $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];
        $subtitle = empty($grparr[$this_group]['grp_subtitle']) ? '' : $grparr[$this_group]['grp_subtitle'];

        $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
          "WHERE form_id = ? AND uor > 0 AND group_id = ? " .
          "ORDER BY seq", array($formtype, $this_group));
    ?>

        <div class="tab <?php echo $first ? 'current' : '' ?>">
            <table border='0' cellpadding='0'>

            <?php
            while ($group_fields = sqlFetchArray($group_fields_query)) {
                $titlecols     = $group_fields['titlecols'];
                $datacols      = $group_fields['datacols'];
                $data_type     = $group_fields['data_type'];
                $field_id      = $group_fields['field_id'];
                $list_id       = $group_fields['list_id'];
                $currvalue     = '';
                $edit_options  = $group_fields['edit_options'];

                if ($formtype == 'DEM') {
                    if (strpos($field_id, 'em_') === 0) {
                      // Skip employer related fields, if it's disabled.
                        if ($GLOBALS['omit_employers']) {
                            continue;
                        }

                        $tmp = substr($field_id, 3);
                        if (isset($result2[$tmp])) {
                            $currvalue = $result2[$tmp];
                        }
                    } else {
                        if (isset($result1[$field_id])) {
                            $currvalue = $result1[$field_id];
                        }
                    }
                } else {
                    if (isset($result1[$field_id])) {
                        $currvalue = $result1[$field_id];
                    }
                }

                // Skip this field if action conditions call for that.
                // Note this also accumulates info for subsequent skip tests.
                $skip_this_field = isSkipped($group_fields, $currvalue) == 'skip';

                // Skip this field if its do-not-print option is set.
                if (isOption($edit_options, 'X') !== false) {
                    $skip_this_field = true;
                }

                // Handle a data category (group) change.
                if (strcmp($this_group, $last_group) != 0) {
                    $group_name = $grparr[$this_group]['grp_title'];
                    // totally skip generating the employer category, if it's disabled.
                    if ($group_name === 'Employer' && $GLOBALS['omit_employers']) {
                        continue;
                    }
                    $last_group = $this_group;
                }

                // Handle starting of a new row.
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
                    disp_end_row();
                    if ($subtitle) {
                        // Group subtitle exists and is not displayed yet.
                        echo "<tr><td class='label' style='background-color:#dddddd;padding:3pt' colspan='$CPR'>" . text($subtitle) . "</td></tr>\n";
                        echo "<tr><td class='label' style='height:4pt' colspan='$CPR'></td></tr>\n";
                        $subtitle = '';
                    }
                    echo "<tr>";
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

                // Handle starting of a new label cell.
                if ($titlecols > 0) {
                    disp_end_cell();
                    $titlecols_esc = htmlspecialchars($titlecols, ENT_QUOTES);
                    $field_id_label = 'label_'.$group_fields['field_id'];
                    echo "<td class='label_custom' colspan='$titlecols_esc' id='" . attr($field_id_label) . "'";
                    echo ">";
                    $cell_count += $titlecols;
                }

                ++$item_count;

                $field_id_label = 'label_'.$group_fields['field_id'];
                echo "<span id='".attr($field_id_label)."'>";
                if ($skip_this_field) {
                    // No label because skipping
                } else if ($group_fields['title']) {
                    $tmp = xl_layout_label($group_fields['title']);
                    echo text($tmp);
                    // Append colon only if label does not end with punctuation.
                    if (strpos('?!.,:-=', substr($tmp, -1, 1)) === false) {
                        echo ':';
                    }
                } else {
                    echo "&nbsp;";
                }
                echo "</span>";

                // Handle starting of a new data cell.
                if ($datacols > 0) {
                    disp_end_cell();
                    $datacols_esc = htmlspecialchars($datacols, ENT_QUOTES);
                    $field_id = 'text_'.$group_fields['field_id'];
                    echo "<td class='text data' colspan='$datacols_esc' id='" . attr($field_id) . "'  data-value='" . attr($currvalue) . "'";
                    if (!$skip_this_field && $data_type == 3) {
                        // Textarea gets a light grey border.
                        echo " style='border:1px solid #cccccc'";
                    }
                    echo ">";
                    $cell_count += $datacols;
                } else {
                    $field_id = 'text_'.$group_fields['field_id'];
                    echo "<span id='".attr($field_id)."' style='display:none'>" . text($currvalue) . "</span>";
                }

                ++$item_count;
                if (!$skip_this_field) {
                    echo generate_display_field($group_fields, $currvalue);
                }
            }

            disp_end_row();
            ?>

            </table>
        </div>

        <?php

        $first = false;
    }
}

function display_layout_tabs_data_editable($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR,$condition_str;

    $grparr = array();
    getLayoutProperties($formtype, $grparr, '*');

    $TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

    $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
        "WHERE form_id = ? AND uor > 0 " .
        "ORDER BY group_id", array($formtype));

    $first = true;
    $condition_str = '';

    while ($frow = sqlFetchArray($fres)) {
        $this_group = $frow['group_id'];
        $group_name = $grparr[$this_group]['grp_title'];
        $group_name_esc = text($group_name);

        if ($grparr[$this_group]['grp_title'] === 'Employer' && $GLOBALS['omit_employers']) {
            continue;
        }
        $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];
        $subtitle = empty($grparr[$this_group]['grp_subtitle']) ? '' : $grparr[$this_group]['grp_subtitle'];

        $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 AND group_id = ? " .
            "ORDER BY seq", array($formtype, $this_group));
    ?>

        <div class="tab <?php echo $first ? 'current' : '' ?>" id="tab_<?php echo str_replace(' ', '_', $group_name_esc)?>" >
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
                $action     = 'skip';

                // Accumulate action conditions into a JSON expression for the browser side.
                accumActionConditions($field_id, $condition_str, $group_fields['conditions']);

                if ($formtype == 'DEM') {
                    if (strpos($field_id, 'em_') === 0) {
                      // Skip employer related fields, if it's disabled.
                        if ($GLOBALS['omit_employers']) {
                            continue;
                        }

                        $tmp = substr($field_id, 3);
                        if (isset($result2[$tmp])) {
                            $currvalue = $result2[$tmp];
                        }
                    } else {
                        if (isset($result1[$field_id])) {
                            $currvalue = $result1[$field_id];
                        }
                    }
                } else {
                    if (isset($result1[$field_id])) {
                        $currvalue = $result1[$field_id];
                    }
                }

                // Handle a data category (group) change.
                if (strcmp($this_group, $last_group) != 0) {
                    // totally skip generating the employer category, if it's disabled.
                    if ($group_name === 'Employer' && $GLOBALS['omit_employers']) {
                        continue;
                    }

                    $last_group = $this_group;
                }

                // Handle starting of a new row.
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
                    disp_end_row();
                    if ($subtitle) {
                        // Group subtitle exists and is not displayed yet.
                        echo "<tr><td class='label' style='background-color:#dddddd;padding:3pt' colspan='$CPR'>" . text($subtitle) . "</td></tr>\n";
                        echo "<tr><td class='label' style='height:4pt' colspan='$CPR'></td></tr>\n";
                        $subtitle = '';
                    }
                    echo "<tr>";
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

                // Handle starting of a new label cell.
                if ($titlecols > 0) {
                    disp_end_cell();
                    $titlecols_esc = htmlspecialchars($titlecols, ENT_QUOTES);
                    $field_id_label = 'label_'.$group_fields['field_id'];
                    echo "<td class='label_custom' colspan='$titlecols_esc'";
                    // This ID is used by skip conditions.
                    echo " id='label_id_" . attr($field_id) . "'";
                    echo ">";
                    $cell_count += $titlecols;
                }

                ++$item_count;

                if ($group_fields['title']) {
                    $tmp = xl_layout_label($group_fields['title']);
                    echo text($tmp);
                    // Append colon only if label does not end with punctuation.
                    if (strpos('?!.,:-=', substr($tmp, -1, 1)) === false) {
                        echo ':';
                    }
                } else {
                    echo "&nbsp;";
                }

                // Handle starting of a new data cell.
                if ($datacols > 0) {
                    disp_end_cell();
                    $datacols_esc = htmlspecialchars($datacols, ENT_QUOTES);
                    $field_id = 'text_'.$group_fields['field_id'];
                    echo "<td class='text data' colspan='$datacols_esc'";
                    // This ID is used by action conditions.
                    echo " id='value_id_" . attr($field_id) . "'";
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
function get_layout_form_value($frow, $prefix = 'form_')
{
    $maxlength = empty($frow['max_length']) ? 0 : intval($frow['max_length']);
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
    $value  = '';
    if (isset($_POST["$prefix$field_id"])) {
        if ($data_type == 4) {
            $modtmp = isOption($frow['edit_options'], 'F') === false ? 0 : 1;
            if (!$modtmp) {
                $value = DateToYYYYMMDD($_POST["$prefix$field_id"]);
            } else {
                $value = DateTimeToYYYYMMDDHHMMSS($_POST["$prefix$field_id"]);
            }
        } else if ($data_type == 21) {
            if (!$frow['list_id']) {
                if (!empty($_POST["form_$field_id"])) {
                    $value = xlt('Yes');
                }
            } else {
                // $_POST["$prefix$field_id"] is an array of checkboxes and its keys
                // must be concatenated into a |-separated string.
                foreach ($_POST["$prefix$field_id"] as $key => $val) {
                    if (strlen($value)) {
                        $value .= '|';
                    }
                    $value .= $key;
                }
            }
        } else if ($data_type == 22) {
            // $_POST["$prefix$field_id"] is an array of text fields to be imploded
            // into "key:value|key:value|...".
            foreach ($_POST["$prefix$field_id"] as $key => $val) {
                $val = str_replace('|', ' ', $val);
                if (strlen($value)) {
                    $value .= '|';
                }

                $value .= "$key:$val";
            }
        } else if ($data_type == 23) {
            // $_POST["$prefix$field_id"] is an array of text fields with companion
            // radio buttons to be imploded into "key:n:notes|key:n:notes|...".
            foreach ($_POST["$prefix$field_id"] as $key => $val) {
                $restype = $_POST["radio_{$field_id}"][$key];
                if (empty($restype)) {
                    $restype = '0';
                }

                $val = str_replace('|', ' ', $val);
                if (strlen($value)) {
                    $value .= '|';
                }

                $value .= "$key:$restype:$val";
            }
        } else if ($data_type == 25) {
            // $_POST["$prefix$field_id"] is an array of text fields with companion
            // checkboxes to be imploded into "key:n:notes|key:n:notes|...".
            foreach ($_POST["$prefix$field_id"] as $key => $val) {
                $restype = empty($_POST["check_{$field_id}"][$key]) ? '0' : '1';
                $val = str_replace('|', ' ', $val);
                if (strlen($value)) {
                    $value .= '|';
                }

                $value .= "$key:$restype:$val";
            }
        } else if ($data_type == 28 || $data_type == 32) {
            // $_POST["$prefix$field_id"] is an date text fields with companion
            // radio buttons to be imploded into "notes|type|date".
            $restype = $_POST["radio_{$field_id}"];
            if (empty($restype)) {
                $restype = '0';
            }

            $resdate = DateToYYYYMMDD(str_replace('|', ' ', $_POST["date_$field_id"]));
            $resnote = str_replace('|', ' ', $_POST["$prefix$field_id"]);
            if ($data_type == 32) {
                //VicarePlus :: Smoking status data is imploded into "note|type|date|list".
                $reslist = str_replace('|', ' ', $_POST["$prefix$field_id"]);
                $res_text_note = str_replace('|', ' ', $_POST["{$prefix}text_$field_id"]);
                $value = "$res_text_note|$restype|$resdate|$reslist";
            } else {
                $value = "$resnote|$restype|$resdate";
            }
        } else if ($data_type == 36) {
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
        } else {
            $value = $_POST["$prefix$field_id"];
        }
    }

  // Better to die than to silently truncate data!
    if ($maxlength && $maxlength != 0 && strlen($value) > $maxlength) {
        die(htmlspecialchars(xl('ERROR: Field') . " '$field_id' " . xl('is too long'), ENT_NOQUOTES) .
        ":<br />&nbsp;<br />".htmlspecialchars($value, ENT_NOQUOTES));
    }

    return trim($value);
}

// Generate JavaScript validation logic for the required fields.
//
function generate_layout_validation($form_id)
{
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 AND field_id != '' " .
    "ORDER BY group_id, seq", array($form_id));

    while ($frow = sqlFetchArray($fres)) {
        $data_type = $frow['data_type'];
        $field_id  = $frow['field_id'];
        $fldtitle  = $frow['title'];
        if (!$fldtitle) {
            $fldtitle  = $frow['description'];
        }

        $fldname   = htmlspecialchars("form_$field_id", ENT_QUOTES);

        if ($data_type == 40) {
            $fldid = addslashes("form_$field_id");
            // Move canvas image data to its hidden form field so the server will get it.
            echo
            " var canfld = f['$fldid'];\n" .
            " if (canfld) canfld.value = lbfCanvasGetData('$fldid');\n";
            continue;
        }

        if ($frow['uor'] < 2) {
            continue;
        }

        echo " if (f.$fldname && !f.$fldname.disabled) {\n";
        switch ($data_type) {
            case 1:
            case 11:
            case 12:
            case 13:
            case 14:
            case 26:
                echo
                "  if (f.$fldname.selectedIndex <= 0) {\n" .
                "   alert(\"" . addslashes(xl('Please choose a value for')) .
                ":\\n" . addslashes(xl_layout_label($fldtitle)) . "\");\n" .
                "   if (f.$fldname.focus) f.$fldname.focus();\n" .
                "   return false;\n" .
                "  }\n";
                break;
            case 33:
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
                "   alert(\"" . addslashes(xl('Please choose a value for')) .
                ":\\n" . addslashes(xl_layout_label($fldtitle)) . "\");\n" .
                "   return false;\n" .
                " }\n";
                break;
            case 2:
            case 3:
            case 4:
            case 15:
                echo
                " if (trimlen(f.$fldname.value) == 0) {\n" .
                "  		if (f.$fldname.focus) f.$fldname.focus();\n" .
                "  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','red'); } ); " .
                "  		$('#" . $fldname . "').attr('style','background:red'); \n" .
                "  		errMsgs[errMsgs.length] = '" . addslashes(xl_layout_label($fldtitle)) . "'; \n" .
                " } else { " .
                " 		$('#" . $fldname . "').attr('style',''); " .
                "  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','');  } ); " .
                " } \n";
                break;
            case 36: // multi select
                echo
                " var multi_select=f['$fldname"."[]']; \n " .
                " var multi_choice_made=false; \n".
                " for (var options_index=0; options_index < multi_select.length; options_index++) { ".
                    " multi_choice_made=multi_choice_made || multi_select.options[options_index].selected; \n".
                "    } \n" .
                " if(!multi_choice_made)
            errMsgs[errMsgs.length] = '" . addslashes(xl_layout_label($fldtitle)) . "'; \n" .
                "";
                break;
        }
        echo " }\n";
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
function dropdown_facility(
    $selected = '',
    $name = 'form_facility',
    $allow_unspecified = true,
    $allow_allfacilities = true,
    $disabled = '',
    $onchange = ''
) {

    global $facilityService;

    $have_selected = false;
    $fres = $facilityService->getAll();

    $name = htmlspecialchars($name, ENT_QUOTES);
    echo "   <select class='form-control' name='$name' id='$name'";
    if ($onchange) {
        echo " onchange='$onchange'";
    }

    echo " $disabled>\n";

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
        if ($selected == '0') {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = htmlspecialchars('-- ' . xl('Unspecified') . ' --', ENT_NOQUOTES);
        echo "    <option value=\"$option_value\" $option_selected_attr>$option_content</option>\n";
    }

    foreach ($fres as $frow) {
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
        if ($selected == '0') {
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
function expand_collapse_widget($title, $label, $buttonLabel, $buttonLink, $buttonClass, $linkMethod, $bodyClass, $auth, $fixedWidth, $forceExpandAlways = false)
{
    if ($fixedWidth) {
        echo "<div class='section-header'>";
    } else {
        echo "<div class='section-header-dynamic'>";
    }

    echo "<table><tr>";
    if ($auth) {
        // show button, since authorized
        // first prepare class string
        if ($buttonClass) {
            $class_string = "css_button_small ".htmlspecialchars($buttonClass, ENT_NOQUOTES);
        } else {
            $class_string = "css_button_small";
        }

        // next, create the link
        if ($linkMethod == "javascript") {
            echo "<td><a class='" . $class_string . "' href='javascript:;' onclick='" . $buttonLink . "'";
        } else {
            echo "<td><a class='" . $class_string . "' href='" . $buttonLink . "'";
            if (!isset($_SESSION['patient_portal_onsite']) && !isset($_SESSION['patient_portal_onsite_two'])) {
                // prevent an error from occuring when calling the function from the patient portal
                echo " onclick='top.restoreSession()'";
            }
        }

        echo "><span>" .
        htmlspecialchars($buttonLabel, ENT_NOQUOTES) . "</span></a></td>";
    }

    if ($forceExpandAlways) {
        // Special case to force the widget to always be expanded
        echo "<td><span class='text'><b>" . htmlspecialchars($title, ENT_NOQUOTES) . "</b></span>";
        $indicatorTag ="style='display:none'";
    }

    $indicatorTag = isset($indicatorTag) ?  $indicatorTag : "";
    echo "<td><a " . $indicatorTag . " href='javascript:;' class='small' onclick='toggleIndicator(this,\"" .
    htmlspecialchars($label, ENT_QUOTES) . "_ps_expand\")'><span class='text'><b>";
    echo htmlspecialchars($title, ENT_NOQUOTES) . "</b></span>";

    if (isset($_SESSION['patient_portal_onsite']) || isset($_SESSION['patient_portal_onsite_two'])) {
        // collapse all entries in the patient portal
        $text = xl('expand');
    } else if (getUserSetting($label."_ps_expand")) {
        $text = xl('collapse');
    } else {
        $text = xl('expand');
    }

    echo " (<span class='indicator'>" . htmlspecialchars($text, ENT_QUOTES) .
    "</span>)</a></td>";
    echo "</tr></table>";
    echo "</div>";
    if ($forceExpandAlways) {
        // Special case to force the widget to always be expanded
        $styling = "";
    } else if (isset($_SESSION['patient_portal_onsite']) || isset($_SESSION['patient_portal_onsite_two'])) {
        // collapse all entries in the patient portal
        $styling = "style='display:none'";
    } else if (getUserSetting($label."_ps_expand")) {
        $styling = "";
    } else {
        $styling = "style='display:none'";
    }

    if ($bodyClass) {
        $styling .= " class='" . $bodyClass . "'";
    }

  //next, create the first div tag to hold the information
  // note the code that calls this function will then place the ending div tag after the data
    echo "<div id='" . htmlspecialchars($label, ENT_QUOTES) . "_ps_expand' " . $styling . ">";
}

//billing_facility fuction will give the dropdown list which contain billing faciliies.
function billing_facility($name, $select)
{
    global $facilityService;

    $fres = $facilityService->getAllBillingLocations();
        echo "   <select id='".htmlspecialchars($name, ENT_QUOTES)."' class='form-control' name='".htmlspecialchars($name, ENT_QUOTES)."'>";
    foreach ($fres as $facrow) {
            $selected = ( $facrow['id'] == $select ) ? 'selected="selected"' : '' ;
             echo "<option value=".htmlspecialchars($facrow['id'], ENT_QUOTES)." $selected>".htmlspecialchars($facrow['name'], ENT_QUOTES)."</option>";
    }

              echo "</select>";
}

// Generic function to get the translated title value for a particular list option.
//
function getListItemTitle($list, $option)
{
    $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = ? AND option_id = ? AND activity = 1", array($list, $option));
    if (empty($row['title'])) {
        return $option;
    }

    return xl_list_label($row['title']);
}
//Added on 5-jun-2k14 (regarding get the smoking code descriptions)
function getSmokeCodes()
{
     $smoking_codes_arr = array();
     $smoking_codes = sqlStatement("SELECT option_id,codes FROM list_options WHERE list_id='smoking_status' AND activity = 1");
    while ($codes_row = sqlFetchArray($smoking_codes)) {
        $smoking_codes_arr[$codes_row['option_id']] = $codes_row['codes'];
    }

     return $smoking_codes_arr;
}

// Get the current value for a layout based form field.
// Depending on options this might come from lbf_data, patient_data,
// form_encounter, shared_attributes or elsewhere.
// Returns FALSE if the field ID is invalid (layout error).
//
function lbf_current_value($frow, $formid, $encounter)
{
    global $pid;
    $formname = $frow['form_id'];
    $field_id = $frow['field_id'];
    $source   = $frow['source'];
    $currvalue = '';
    $deffname = $formname . '_default_' . $field_id;
    if ($source == 'D' || $source == 'H') {
        // Get from patient_data, employer_data or history_data.
        if ($source == 'H') {
            $table = 'history_data';
            $orderby = 'ORDER BY date DESC LIMIT 1';
        } else if (strpos($field_id, 'em_') === 0) {
            $field_id = substr($field_id, 3);
            $table = 'employer_data';
            $orderby = 'ORDER BY date DESC LIMIT 1';
        } else {
            $table = 'patient_data';
            $orderby = '';
        }

        // It is an error if the field does not exist, but don't crash.
        $tmp = sqlQuery("SHOW COLUMNS FROM $table WHERE Field = ?", array($field_id));
        if (empty($tmp)) {
            return '*?*';
        }

        $pdrow = sqlQuery("SELECT `$field_id` AS field_value FROM $table WHERE pid = ? $orderby", array($pid));
        if (isset($pdrow)) {
            $currvalue = $pdrow['field_value'];
        }
    } else if ($source == 'E') {
        $sarow = false;
        if ($encounter) {
            // Get value from shared_attributes of the current encounter.
            $sarow = sqlQuery(
                "SELECT field_value FROM shared_attributes WHERE " .
                "pid = ? AND encounter = ? AND field_id = ?",
                array($pid, $encounter, $field_id)
            );
            if (!empty($sarow)) {
                $currvalue = $sarow['field_value'];
            }
        } else if ($formid) {
            // Get from shared_attributes of the encounter that this form is linked to.
            // Note the importance of having an index on forms.form_id.
            $sarow = sqlQuery(
                "SELECT sa.field_value " .
                "FROM forms AS f, shared_attributes AS sa WHERE " .
                "f.form_id = ? AND f.formdir = ? AND f.deleted = 0 AND " .
                "sa.pid = f.pid AND sa.encounter = f.encounter AND sa.field_id = ?",
                array($formid, $formname, $field_id)
            );
            if (!empty($sarow)) {
                $currvalue = $sarow['field_value'];
            }
        } else {
            // New form and encounter not available, this should not happen.
        }
        if (empty($sarow) && !$formid) {
            // New form, see if there is a custom default from a plugin.
            if (function_exists($deffname)) {
                $currvalue = call_user_func($deffname);
            }
        }
    } else if ($source == 'V') {
        if ($encounter) {
            // Get value from the current encounter's form_encounter.
            $ferow = sqlQuery(
                "SELECT * FROM form_encounter WHERE " .
                "pid = ? AND encounter = ?",
                array($pid, $encounter)
            );
            if (isset($ferow[$field_id])) {
                $currvalue = $ferow[$field_id];
            }
        } else if ($formid) {
            // Get value from the form_encounter that this form is linked to.
            $ferow = sqlQuery(
                "SELECT fe.* " .
                "FROM forms AS f, form_encounter AS fe WHERE " .
                "f.form_id = ? AND f.formdir = ? AND f.deleted = 0 AND " .
                "fe.pid = f.pid AND fe.encounter = f.encounter",
                array($formid, $formname)
            );
            if (isset($ferow[$field_id])) {
                $currvalue = $ferow[$field_id];
            }
        } else {
            // New form and encounter not available, this should not happen.
        }
    } else if ($formid) {
        // This is a normal form field.
        $ldrow = sqlQuery("SELECT field_value FROM lbf_data WHERE " .
        "form_id = ? AND field_id = ?", array($formid, $field_id));
        if (!empty($ldrow)) {
            $currvalue = $ldrow['field_value'];
        }
    } else {
        // New form, see if there is a custom default from a plugin.
        if (function_exists($deffname)) {
            $currvalue = call_user_func($deffname);
        }
    }

    return $currvalue;
}

// This returns stuff that needs to go into the <head> section of a caller using
// the drawable image field type in a form.
// A TRUE argument makes the widget controls smaller.
//
function lbf_canvas_head($small = true)
{
    $s = <<<EOD
<link  href="{$GLOBALS['assets_static_relative']}/literallycanvas-0-4-13/css/literallycanvas.css" rel="stylesheet" />
<script src="{$GLOBALS['assets_static_relative']}/react-15-1-0/react-with-addons.min.js"></script>
<script src="{$GLOBALS['assets_static_relative']}/react-15-1-0/react-dom.min.js"></script>
<script src="{$GLOBALS['assets_static_relative']}/literallycanvas-0-4-13/js/literallycanvas.min.js"></script>
EOD;
    if ($small) {
        $s .= <<<EOD
<style>
/* Custom LiterallyCanvas styling.
 * This makes the widget 25% less tall and adjusts some other things accordingly.
 */
.literally {
  min-height:100%;min-width:300px;        /* Was 400, unspecified */
}
.literally .lc-picker .toolbar-button {
  width:20px;height:20px;line-height:20px; /* Was 26, 26, 26 */
}
.literally .color-well {
  font-size:8px;width:49px;                /* Was 10, 60 */
}
.literally .color-well-color-container {
  width:21px;height:21px;                  /* Was 28, 28 */
}
.literally .lc-picker {
  width:50px;                              /* Was 61 */
}
.literally .lc-drawing.with-gui {
  left:50px;                               /* Was 61 */
}
.literally .lc-options {
  left:50px;                               /* Was 61 */
}
.literally .color-picker-popup {
  left:49px;bottom:0px;                   /* Was 60, 31 */
}
</style>
EOD;
    }

    return $s;
}

/**
 * Test if modifier($test) is in array of options for data type.
 *
 * @param json array $options or could be string of form "ABCU"
 * @param string $test
 * @return boolean
 */
function isOption($options, $test)
{
    if (empty($options) || !isset($test)) {
        return false; // why bother?
    }
    if (strpos($options, ',') === false) { // could be string of char's or single element of json
        json_decode($options);
        if (is_string($options) && ! (json_last_error() === JSON_ERROR_NONE)) { // nope, it's string.
            $t = str_split(trim($options)); // very good chance it's legacy modifier string.
            $options = json_encode($t); // make it array.
        }
    }
    $options = json_decode($options);

    return !is_null($options) && in_array($test, $options, true) ? true : false; // finally!
}
?>
