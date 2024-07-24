<?php

// Copyright (C) 2007-2021 Rod Roark <rod@sunsetsystems.com>
// Copyright © 2010 by Andrew Moore <amoore@cpan.org>
// Copyright © 2010 by "Boyd Stephen Smith Jr." <bss@iguanasuicide.net>
// Copyright (c) 2017 - 2021 Jerry Padgett <sjpadgett@gmail.com>
// Copyright (c) 2021 Robert Down <robertdown@live.com>
// Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
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
// J = Jump to Next Row
// K = Prepend Blank Row
// L = Lab Order ("ord_lab") types only (address book)
// M = Radio Group Master (currently for radio buttons only)
// m = Radio Group Member (currently for radio buttons only)
// N = Show in New Patient form
// O = Procedure Order ("ord_*") types only (address book)
// P = Default to previous value when current value is not yet set
// R = Distributor types only (address book)
// T = Use description as default Text
// DAP = Use description as placeholder
// U = Capitalize all letters (text fields)
// V = Vendor types only (address book)
// 0 = Read Only - the input element's "disabled" property is set
// 1 = Write Once (not editable when not empty) (text fields)
// 2 = Show descriptions instead of codes for billing code input

// note: isOption() returns true/false

// NOTE: All of the magic constants for the data types here are found in library/layout.inc.php

require_once("user.inc.php");
require_once("patient.inc.php");
require_once("lists.inc.php");
require_once(dirname(dirname(__FILE__)) . "/custom/code_types.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Layouts\LayoutsUtils;
use OpenEMR\Common\Forms\Types\BillingCodeType;
use OpenEMR\Common\Forms\Types\LocalProviderListType;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PatientNameHistoryService;
use OpenEMR\Events\PatientDemographics\RenderPharmacySectionEvent;

$facilityService = new FacilityService();

$date_init = "";
$membership_group_number = 0;

// Our base Bootstrap column class, referenced here and in some other modules.
// Using col-lg allow us to have additional breakpoint at col-md.(992px, 768px)
// col-md-auto will let BS decide with col-12 always for sm devices.
$BS_COL_CLASS = 'col-12 col-md-auto col-lg';

function get_pharmacies()
{
    return sqlStatement("SELECT d.id, d.name, a.line1, a.city, " .
    "p.area_code, p.prefix, p.number FROM pharmacies AS d " .
    "LEFT OUTER JOIN addresses AS a ON a.foreign_id = d.id " .
    "LEFT OUTER JOIN phone_numbers AS p ON p.foreign_id = d.id " .
    "AND p.type = 2 " .
    "ORDER BY a.state, a.city, d.name, p.area_code, p.prefix, p.number");
}

function optionalAge($frow, $date, &$asof, $description = '')
{
    $asof = '';
    if (empty($date)) {
        return '';
    }

    $edit_options = $frow['edit_options'] ?? null;

    $date = substr($date, 0, 10);
    if (isOption($edit_options, 'A') !== false) {
        $format = 0;
    } elseif (isOption($edit_options, 'B') !== false) {
        $format = 3;
    } else {
        return '';
    }

    if (isOption($frow['form_id'], 'LBF') === false) {
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
    $multiple = false,  // new #10
    $backup_list = '',  // new #11
    $ignore_default = false,
    $include_inactive = false,
    $tabIndex = false
) {
    $attributes = [];
    $_options = [];
    $_metadata = [];

    $tag_name_esc = attr($tag_name);

    $attributes['name'] = ($multiple) ? $tag_name_esc . "[]" : $tag_name_esc;

    if ($tabIndex !== false) {
        $attributes['tabindex'] = attr($tabIndex);
    }

    if ($multiple) {
        $attributes['multiple'] = "multiple";
    }

    $attributes['id'] = attr($tag_name);
    $attributes['class'] = (!empty($class)) ? "form-control " . attr($class) : "form-control";

    if ($onchange) {
        $attributes['onchange'] = $onchange;
    }

    if ($custom_attributes != null && is_array($custom_attributes)) {
        foreach ($custom_attributes as $attr => $val) {
            if (isset($custom_attributes [$attr])) {
                $attributes[attr($attr)] = attr($val);
            }
        }
    }

    $attributes['title'] = attr($title);

    $selectEmptyName = xlt($empty_name);
    if ($empty_name) {
        preg_match_all('/select2/m', ($class ?? ''), $matches, PREG_SET_ORDER, 0);
        if (array_key_exists('placeholder', $attributes) && count($matches) > 0) {
            // We have a placeholder attribute as well as a select2 class indicating there
            // should be provide a truley empty option.
            $_options[] = [];
        } else {
            $_options[] = [
                'label' => $selectEmptyName,
                'value' => '',
                'isSelected' => true,
            ];
        }
    }

    $got_selected = false;

    for ($active = 1; $active == 1 || ($active == 0 && $include_inactive); --$active) {
        $_optgroup = ($include_inactive) ? true : false;

        // List order depends on language translation options.
        //  (Note we do not need to worry about the list order in the algorithm
        //   after the below code block since that is where searches for exceptions
        //   are done which include inactive items or items from a backup
        //   list; note these will always be shown at the bottom of the list no matter the
        //   chosen order.)
        // This block should be migrated to the ListService but the service currently does not translate or offer a sort option.
        $lang_id = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
        // sort by title
        $order_by_sql = ($GLOBALS['gb_how_sort_list'] == '0') ? "seq, title" : "title, seq";
        if (!$GLOBALS['translate_lists']) {
            // do not translate
            $lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = ? ORDER BY $order_by_sql", [$list_id, $active]);
        } else {
            // do translate
            $order_by_sql = str_replace("seq", "lo.seq", $order_by_sql);
            $sql = "SELECT lo.option_id, lo.is_default,
                        COALESCE((SELECT ld.definition FROM lang_constants AS lc, lang_definitions AS ld
                            WHERE lc.constant_name = lo.title AND ld.cons_id = lc.cons_id AND ld.lang_id = ? AND ld.definition IS NOT NULL
                                AND ld.definition != ''
                            LIMIT 1), lo.title) AS title
                    FROM list_options AS lo
                    WHERE lo.list_id = ? AND lo.activity = ?
                    ORDER BY {$order_by_sql}";
            $lres = sqlStatement($sql, [$lang_id, $list_id, $active]);
        }

        // Populate the options array with pertinent values

        while ($lrow = sqlFetchArray($lres)) {
            $selectedValues = explode("|", $currvalue ?? '');
            $isSelected = false;

            $optionValue = attr($lrow ['option_id']);

            if (
                (strlen($currvalue ?? '') == 0 && $lrow['is_default'] && !$ignore_default) ||
                (strlen($currvalue ?? '') > 0 && in_array($lrow['option_id'], $selectedValues))
            ) {
                $got_selected = true;
                $isSelected = true;
            }

            // Already has been translated above (if applicable), so do not need to use
            // the xl_list_label() function here
            $optionLabel = text($lrow ['title']);

            $_tmp = [
                'label' => $optionLabel,
                'value' => $optionValue,
                'isSelected' => $isSelected,
                'isActive' => $include_inactive,
            ];

            if ($_optgroup) {
                $_tmp['optGroupOptions'] = $_tmp;
                $_tmp['optgroupLabel'] = ($active) ? xla('Active') : xla('Inactive');
            }

            $_options[] = $_tmp;
        }
    } // end $active loop

    /*
      To show the inactive item in the list if the value is saved to database
      */
    if (!$got_selected && strlen($currvalue ?? '') > 0) {
        $_sql = "SELECT * FROM list_options WHERE list_id = ? AND activity = 0 AND option_id = ? ORDER BY seq, title";
        $lres_inactive = sqlStatement($_sql, [$list_id, $currvalue]);
        $lrow_inactive = sqlFetchArray($lres_inactive);
        if (!empty($lrow_inactive['option_id'])) {
            $optionValue = htmlspecialchars($lrow_inactive['option_id'], ENT_QUOTES);
            $_options[] = [
                'label' => htmlspecialchars(xl_list_label($lrow_inactive['title']), ENT_NOQUOTES),
                'value' => $optionValue,
                'isSelected' => true,
                'isActive' => false,
            ];
            $got_selected = true;
        }
    }

    if (!$got_selected && strlen($currvalue ?? '') > 0 && !$multiple) {
        $list_id = $backup_list;
        $lrow = sqlQuery("SELECT title FROM list_options WHERE list_id = ? AND option_id = ?", [$list_id, $currvalue]);

        $_options[] = [
            'value' => attr($currvalue),
            'selected' => true,
            'label' => ($lrow > 0 && !empty($backup_list)) ? text(xl_list_label($lrow['title'])) : text($currvalue),
        ];
        if (empty($lrow) && empty($backup_list)) {
            $metadata['error'] = [
                'title' => xlt('Please choose a valid selection from the list.'),
                'text' => xlt('Fix this'),
            ];
        }
    } elseif (!$got_selected && strlen($currvalue ?? '') > 0 && $multiple) {
        //if not found in main list, display all selected values that exist in backup list
        $list_id = $backup_list;

        $got_selected_backup = false;
        if (!empty($backup_list)) {
            $lres_backup = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
            while ($lrow_backup = sqlFetchArray($lres_backup)) {
                $selectedValues = explode("|", $currvalue);
                $optionValue = attr($lrow_backup['option_id']);

                if (in_array($lrow_backup ['option_id'], $selectedValues)) {
                    $_options[] = [
                        'label' => text(xl_list_label($lrow_backup['title'])),
                        'value' => $optionValue,
                        'isSelected' => true,
                    ];
                    $got_selected_backup = true;
                }
            }
        }

        if (!$got_selected_backup) {
            $selectedValues = explode("|", $currvalue);
            foreach ($selectedValues as $selectedValue) {
                $_options[] = [
                    'label' => text($selectedValue),
                    'value' => attr($selectedValue),
                    'isSelected' => true,
                ];
            }

            $_metadata['error'] = [
                'title' => xlt('Please choose a valid selection from the list.'),
                'text' => xlt('Fix this'),
            ];
        }
    }

    $_parsedOptions = [];
    $_og = false;
    foreach ($_options as $o) {
        $_isOG = (array_key_exists('optGroupOptions', $o) && count($o['optGroupOptions']) > 0) ? true : false;
        $_currOG = $o['optgroupLabel'] ?? false;

        // Render only if the current optgroup label is not triple equal to the previous label
        if ($_og !== $_currOG) {
            $_parsedOptions[] = "</optgroup>";
        }

        // Must have an opt group and it must be different than the previous
        if ($_isOG && $_og !== $_currOG) {
            $_parsedOptions[] = sprintf('<optgroup label="%s">', $_currOG);
        }

        $_parsedOptions[] = _create_option_element($o);

        $_og = $_currOG;
    }
    $optionString = implode("\n", $_parsedOptions);

    $_parsedAttributes = [];
    foreach ($attributes as $attr => $val) {
        $_parsedAttributes[] = sprintf('%s="%s"', $attr, $val);
    }
    $attributeString = implode("\n", $_parsedAttributes);

    $_selectString = sprintf("<select %s>%s</select>", $attributeString, $optionString);
    $output[] = $_selectString;

    if (array_key_exists('error', $_metadata)) {
        $_errorString = sprintf("<span title=\"%s\">%s</span>", $_metadata['error']['title'], $metadata['error']['text']);
        $output[] = $_errorString;
    }

    return implode("", $output);
}

function _create_option_element(array $o): string
{
    $_valStr = (array_key_exists('value', $o)) ? "value=\"{$o['value']}\"" : "";
    $_selStr = (array_key_exists('isSelected', $o) && $o['isSelected'] == true) ? "selected" : "";
    $_labStr = (array_key_exists('label', $o)) ? $o['label'] : "";
    return "<option $_valStr $_selStr>$_labStr</option>";
}

// Parsing for data type 31, static text.
function parse_static_text($frow, $value_allowed = true)
{
    $tmp = str_replace("\r\n", "\n", $frow['description']);
    // Translate if it does not look like HTML.
    if (substr($tmp, 0, 1) != '<') {
        $tmp2 = $frow['description'];
        $tmp3 = xl_layout_label($tmp);
        if ($tmp3 == $tmp && $tmp2 != $tmp) {
            // No translation, try again without the CRLF substitution.
            $tmp3 = xl_layout_label($tmp2);
        }
        $tmp = nl2br($tmp3);
    }
    $s = '';
    if ($frow['source'] == 'D' || $frow['source'] == 'H') {
        // Source is demographics or history. This case supports value substitution.
        while (preg_match('/^(.*?)\{(\w+)\}(.*)$/', $tmp, $matches)) {
            $s .= $matches[1];
            if ($value_allowed) {
                $tmprow = $frow;
                $tmprow['field_id'] = $matches[2];
                $s .= lbf_current_value($tmprow, 0, 0);
            }
            $tmp = $matches[3];
        }
    }
    $s .= $tmp;
    return $s;
}

function genLabResultsTextItem($name, $value, $outtype, $size, $maxlength, $disabled = '')
{
    $string_maxlength = $maxlength ? ("maxlength='" . attr($maxlength) . "'") : '';
    $s = "<td align='center'>";
    if ($outtype == 2) {
        $s .= text($value);
    } else {
        $s .= "<input type='text'";
        if ($outtype == 0) {
            $s .= " name='" . attr($name) . "' id='" . attr($name) . "'";
        }
        $s .= " size='" . attr($size) . "' $string_maxlength" .
            " value='" . attr($value) . "'" .
            " $under $disabled />";
    }
    $s .= "&nbsp;</td>";
    return $s;
}

// $outtype = 0 for form, 1 for print, 2 for display, 3 for plain text.
function genLabResults($frow, $currvalue, $outtype = 0, $disabled = '')
{
    $field_id = $frow['field_id'];
    $list_id  = $frow['list_id'];
    $field_id_esc = text($field_id);
    $under = $outtype == 1 ? "class='under'" : "";
    $s = '';

    $avalue = json_decode($currvalue, true);
    if (empty($avalue)) {
        $avalue = array();
    }
    // $avalue[$option_id][0] : gestation
    // $avalue[$option_id][1] : radio button value
    // $avalue[$option_id][2] : test value
    // $avalue[$option_id][3] : notes

    $maxlength = $frow['max_length'];
    $fldlength = empty($frow['fld_length']) ? 20 : $frow['fld_length'];

    $under = $outtype == 1 ? "class='under'" : "";

    $s .= "<table cellpadding='0' cellspacing='0'>";
    if ($outtype < 2) {
        $s .= "<tr>" .
            "<td class='bold' align='center'>" . xlt('Test/Screening') . "&nbsp;</td>" .
            "<td class='bold' align='center'>" . xlt('Gest wks') . "&nbsp;</td>" .
            "<td class='bold' align='center'>&nbsp;" . xlt('N/A') . "&nbsp;</td>" .
            "<td class='bold' align='center'>" . xlt('Neg/Nrml') . "</td>" .
            "<td class='bold' align='center'>&nbsp;" . xlt('Pos/Abn') . "&nbsp;</td>" .
            "<td class='bold' align='center'>" . xlt('Test Value') . "&nbsp;</td>" .
            "<td class='bold' align='center'>" . xlt('Date/Notes') . "&nbsp;</td>" .
            "</tr>";
    }

    $lres = sqlStatement(
        "SELECT * FROM list_options WHERE " .
        "list_id = ? AND activity = 1 ORDER BY seq, title",
        array($list_id)
    );

    while ($lrow = sqlFetchArray($lres)) {
        $option_id = $lrow['option_id'];
        $option_id_esc = text($option_id);

        if ($outtype >= 2 && empty($avalue[$option_id][1])) {
            continue;
        }

        if ($outtype == 3) {
            if (isset($avalue[$option_id][1]) && $avalue[$option_id][1] == '2') {
                if ($s !== '') {
                    $s .= '; ';
                }
                $s .= text(xl_list_label($lrow['title']));
                $s .= ':' . text($avalue[$option_id][0]);
                $s .= ':' . text($avalue[$option_id][2]);
                $s .= ':' . text($avalue[$option_id][3]);
            }
            continue;
        }

        $s .= "<tr>";
        $s .= $outtype == 2 ? "<td class='bold'>" : "<td>";
        $s .= text(xl_list_label($lrow['title'])) . "&nbsp;</td>";

        $s .= genLabResultsTextItem(
            "form_{$field_id_esc}[$option_id_esc][0]",
            (isset($avalue[$option_id][0]) ? $avalue[$option_id][0] : ''),
            $outtype,
            3,
            2,
            $disabled,
            $under
        );

        if ($outtype == 2) {
            $tmp = isset($avalue[$option_id][1]) ? $avalue[$option_id][1] : '0';
            $restype = ($tmp == '1') ? xl('Normal') : (($tmp == '2') ? xl('Abnormal') : xl('N/A'));
            $s .= "<td>" . text($restype) . "&nbsp;</td>";
        } else {
            for ($i = 0; $i < 3; ++$i) {
                $s .= "<td align='center'>";
                $s .= "<input type='radio'";
                if ($outtype == 0) {
                    $s .= " name='radio_{$field_id_esc}[$option_id_esc]'" .
                          " id='radio_{$field_id_esc}[$option_id_esc]'";
                }
                $s .= " value='$i' $lbfonchange";
                if (isset($avalue[$option_id][1]) && $avalue[$option_id][1] == "$i") {
                    $s .= " checked";
                }
                $s .= " $disabled />";
                $s .= "</td>";
            }
        }
        $s .= genLabResultsTextItem(
            "form_{$field_id_esc}[$option_id_esc][2]",
            (isset($avalue[$option_id][2]) ? $avalue[$option_id][2] : ''),
            $outtype,
            10,
            30,
            $disabled,
            $under
        );
        $s .= genLabResultsTextItem(
            "form_{$field_id_esc}[$option_id_esc][3]",
            (isset($avalue[$option_id][3]) ? $avalue[$option_id][3] : ''),
            $outtype,
            $fldlength,
            $maxlength,
            $disabled,
            $under
        );
        $s .= "</tr>";
    }
    if ($outtype != 3) {
        $s .= "</table>";
    }

    return $s;
}

// $frow is a row from the layout_options table.
// $currvalue is the current value, if any, of the associated item.
//
function generate_form_field($frow, $currvalue)
{
    global $rootdir, $date_init, $ISSUE_TYPES, $code_types, $membership_group_number;

    $currescaped = htmlspecialchars($currvalue ?? '', ENT_QUOTES);

    $data_type   = $frow['data_type'];
    $field_id    = $frow['field_id'];
    $list_id     = $frow['list_id'] ?? null;
    $backup_list = $frow['list_backup_id'] ?? null;
    $edit_options = $frow['edit_options'] ?? null;
    $form_id = $frow['form_id'] ?? null;
    $autoComplete = $frow['autocomplete'] ?? false;

    // 'smallform' can be 'true' if we want a smaller form field, otherwise
    // can be used to assign arbitrary CSS classes to data entry fields.
    $smallform = $frow['smallform'] ?? null;
    if ($smallform === 'true') {
        $smallform = ' form-control-sm';
    }

    // historically we've used smallform to append classes if the value is NOT true
    // to make it EXPLICIT what we are doing and to aid maintainability we are supporting
    // an actual 'classNames' attribute for assigning arbitrary CSS classes to data entry fields
    $classesToAppend = $frow['classNames'] ?? '';
    if (!empty($classesToAppend)) {
        $smallform = isset($smallform) ? $smallform . ' ' . $classesToAppend : $classesToAppend;
    }

    // escaped variables to use in html
    $field_id_esc = htmlspecialchars($field_id, ENT_QUOTES);
    $list_id_esc = htmlspecialchars(($list_id ?? ''), ENT_QUOTES);

    // Added 5-09 by BM - Translate description if applicable
    $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');

    // Support edit option T which assigns the (possibly very long) description as
    // the default value.
    if (isOption($edit_options, 'T') !== false) {
        if (strlen($currescaped) == 0) {
            $currescaped = $description;
        }

        // Description used in this way is not suitable as a title.
        $description = '';
    }

    // Support using the description as a placeholder
    $placeholder = (isOption($edit_options, 'DAP') === true) ? " placeholder='{$description}' " : '';

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

    $disabled = isOption($edit_options, '0') === false ? '' : 'disabled';

    $lbfchange = (
        !empty($form_id) &&
        (
            strpos($form_id, 'LBF') === 0 ||
            strpos($form_id, 'LBT') === 0 ||
            strpos($form_id, 'DEM') === 0 ||
            strpos($form_id, 'HIS') === 0
        )
    ) ? "checkSkipConditions();" : "";
    $lbfonchange = $lbfchange ? "onchange='$lbfchange'" : "";

    // generic single-selection list or single-selection list with search or single-selection list with comment support.
    // These data types support backup lists.
    if ($data_type == 1 || $data_type == 43 || $data_type == 46) {
        if ($data_type == 46) {
            // support for single-selection list with comment support
            $lbfchange = "processCommentField(" . attr_js($field_id) . ");" . $lbfchange;
        }

        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            (($data_type == 43) ? "select-dropdown" : $smallform),
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );

        if ($data_type == 46) {
            // support for single-selection list with comment support
            $selectedValues = explode("|", $currvalue);
            if (!preg_match('/^comment_/', $currvalue) || (count($selectedValues) == 1)) {
                $display = "display:none";
                $comment = "";
            } else {
                $display = "display:inline-block";
                $comment = $selectedValues[count($selectedValues) - 1];
            }
            echo "<input type='text'" .
                " name='form_text_" . attr($field_id) . "'" .
                " id='form_text_" . attr($field_id) . "'" .
                " size='" . attr($frow['fld_length']) . "'" .
                " class='form-control'" .
                $placeholder .
                " " . ((!empty($frow['max_length'])) ? "maxlength='" . attr($frow['max_length']) . "'" : "") . " " .
                " style='" . $display . "'" .
                " value='" . attr($comment) . "'/>";
        }
    } elseif ($data_type == 2) { // simple text field
        $fldlength = htmlspecialchars($frow['fld_length'] ?? '', ENT_QUOTES);
        $maxlength = $frow['max_length'] ?? '';
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }

        echo "<input type='text'
            class='form-control{$smallform}'
            name='form_{$field_id_esc}'
            id='form_{$field_id_esc}'
            size='{$fldlength}'
            {$string_maxlength}
            {$placeholder}
            title='{$description}'
            value='{$currescaped}'";
        $tmp = $lbfchange;
        if (isOption($edit_options, 'C') !== false) {
            $tmp .= "capitalizeMe(this);";
        } elseif (isOption($edit_options, 'U') !== false) {
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

        if (isOption($edit_options, '1') !== false && strlen($currescaped) > 0) {
            echo " readonly";
        }

        if ($disabled) {
            echo ' disabled';
        }

        echo " />";
    } elseif ($data_type == 3) { // long or multi-line text field
        $textCols = htmlspecialchars($frow['fld_length'], ENT_QUOTES);
        $textRows = htmlspecialchars($frow['fld_rows'], ENT_QUOTES);
        echo "<textarea" .
        " name='form_$field_id_esc'" .
        " class='form-control$smallform'" .
        " id='form_$field_id_esc'" .
        " title='$description'" .
        $placeholder .
        " cols='$textCols'" .
        " rows='$textRows' $lbfonchange $disabled" .
        ">" . $currescaped . "</textarea>";
    } elseif ($data_type == 4) { // date
        $age_asof_date = ''; // optionalAge() sets this
        $age_format = isOption($edit_options, 'A') === false ? 3 : 0;
        $agestr = optionalAge($frow, $currvalue, $age_asof_date, $description);
        if ($agestr) {
            echo "<table class='table'><tr><td class='text'>";
        }

        $onchange_string = '';
        if (!$disabled && $agestr) {
            $onchange_string = "onchange=\"if (typeof(updateAgeString) == 'function') " .
            "updateAgeString('$field_id','$age_asof_date', $age_format, '$description')\"";
        }
        if ($data_type == 4) {
            $modtmp = isOption($edit_options, 'F') === false ? 0 : 1;
            $datetimepickerclass = ($frow['validation'] ?? null) === 'past_date' ? '-past' : ( ($frow['validation'] ?? null) === 'future_date' ? '-future' : '' );
            if (!$modtmp) {
                $dateValue  = oeFormatShortDate(substr($currescaped, 0, 10));
                echo "<input type='text' size='10' class='datepicker$datetimepickerclass form-control$smallform' name='form_$field_id_esc' id='form_$field_id_esc'" . " value='" .  attr($dateValue)  . "'";
            } else {
                $dateValue  = oeFormatDateTime(substr($currescaped, 0, 20), 0);
                echo "<input type='text' size='20' class='datetimepicker$datetimepickerclass form-control$smallform' name='form_$field_id_esc' id='form_$field_id_esc'" . " value='" . attr($dateValue) . "'";
            }
        }
        if (!$agestr) {
            echo " title='$description'";
        }
        // help chrome users avoid autocomplete interfere with datepicker widget display
        if ($autoComplete !== false) {
            echo " autocomplete='" . attr($autoComplete) . "'";
        } else if ($frow['field_id'] == 'DOB') {
            echo " autocomplete='off'";
        }
        echo " $onchange_string $lbfonchange $disabled />";

        // Optional display of age or gestational age.
        if ($agestr) {
            echo "</td></tr><tr><td id='span_$field_id' class='text'>" . text($agestr) . "</td></tr></table>";
        }
    } elseif ($data_type == 10) { // provider list, local providers only
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND authorized = 1 " .
        "ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control$smallform'>";
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
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }
    } elseif ($data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) { // provider list, including address book entries with an NPI number
        $obj = new LocalProviderListType();
        echo $obj->buildFormView($frow, $currvalue);
    } elseif ($data_type == 12) { // pharmacy list
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control$smallform'";
        echo " $lbfonchange $disabled>";
        echo "<option value='0'></option>";
        $pres = get_pharmacies();
        $got_selected = false;
        $zone = '';
        while ($prow = sqlFetchArray($pres)) {
            if ($zone != strtolower(trim($prow['city'] ?? ''))) {
                if ($zone != '') {
                    echo "</optgroup>";
                }
                $zone = strtolower(trim($prow['city']));
                echo "<optgroup label='" . attr($prow['city']) . "'>";
            }
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
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }

        /**
         * if anyone wants to render something after the pharmacy section on the demographics form,
         * they would have to listen to this event.
        */
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch(new RenderPharmacySectionEvent(), RenderPharmacySectionEvent::RENDER_AFTER_PHARMACY_SECTION, 10);
    } elseif ($data_type == 13) { // squads
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control$smallform'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>&nbsp;</option>";
        $squads = AclExtended::aclGetSquads();
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
    } elseif ($data_type == 14) {
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

        if (isOption($edit_options, 'L') !== false) {
            $tmp = "abook_type = 'ord_lab'";
        } elseif (isOption($edit_options, 'O') !== false) {
            $tmp = "abook_type LIKE 'ord\\_%'";
        } elseif (isOption($edit_options, 'V') !== false) {
            $tmp = "abook_type LIKE 'vendor%'";
        } elseif (isOption($edit_options, 'R') !== false) {
            $tmp = "abook_type LIKE 'dist'";
        } else {
            $tmp = "( username = '' OR authorized = 1 )";
        }

        $ures = sqlStatement("SELECT id, fname, lname, organization, username, npi FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND $tmp " .
        "ORDER BY organization, lname, fname, npi");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' title='$description' class='form-control$smallform'";
        echo " $lbfonchange $disabled>";
        echo "<option value=''>" . htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES) . "</option>";
        while ($urow = sqlFetchArray($ures)) {
            $uname = $urow['organization'];
            if (empty($uname) || substr($uname, 0, 1) == '(') {
                $uname = $urow['lname'];
                if ($urow['fname']) {
                    $uname .= ", " . $urow['fname'];
                }
                if ($urow['npi']) {
                    $uname .= ": " . $urow['npi'];
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
    } elseif ($data_type == BillingCodeType::OPTIONS_TYPE_INDEX) { // A billing code. If description matches an existing code type then that type is used.
        $billingCodeType = new BillingCodeType();
        echo $billingCodeType->buildFormView($frow, $currvalue);
    } elseif ($data_type == 16) { // insurance company list
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control$smallform' title='$description'>";
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
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }
    } elseif ($data_type == 17) { // issue types
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control$smallform' title='$description'>";
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
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }
    } elseif ($data_type == 18) { // Visit categories.
        $cres = sqlStatement("SELECT pc_catid, pc_catname " .
        "FROM openemr_postcalendar_categories ORDER BY pc_catname");
        echo "<select name='form_$field_id_esc' id='form_$field_id_esc' class='form-control$smallform' title='$description'" . " $lbfonchange $disabled>";
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
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }
    } elseif ($data_type == 21) { // a set of labeled checkboxes
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
            echo "<table class='w-100' cellpadding='0' cellspacing='0' title='" . attr($description) . "'>";
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
                "id='form_{$field_id_esc}[$option_id_esc]' class='form-check-inline' value='1' $lbfonchange";
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
                    echo "<tr><td colspan='$cols' style='height:0.7rem'></td></tr>";
                }
            }
            echo "</table>";
        }
    } elseif ($data_type == 22) { // a set of labeled text input fields
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='table'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $maxlength = $frow['max_length'];
            $string_maxlength = "";
            // if max_length is set to zero, then do not set a maxlength
            if ($maxlength) {
                $string_maxlength = "maxlength='" . attr($maxlength) . "'";
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
            $placeholder .
            " class='form-control$smallform'" .
            " $string_maxlength" .
            " value='$optionValue'";
            echo " $lbfonchange $disabled /></td></tr>";
        }

        echo "</table>";
    } elseif ($data_type == 23) { // a set of exam results; 3 radio buttons and a text field:
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
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='table'>";
        echo "<tr><td class='font-weight-bold'>" . htmlspecialchars(xl('Exam or Test'), ENT_NOQUOTES) .
        "</td><td class='font-weight-bold'>" . htmlspecialchars(xl('N/A'), ENT_NOQUOTES) .
        "&nbsp;</td><td class='font-weight-bold'>" .
        htmlspecialchars(xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
        "<td class='font-weight-bold'>" .
        htmlspecialchars(xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='font-weight-bold'>" .
        htmlspecialchars(xl('Date/Notes'), ENT_NOQUOTES) . "</td></tr>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr(($avalue[$option_id] ?? ''), 0, 1);
            $resnote = substr(($avalue[$option_id] ?? ''), 2);

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
            " class='form-control'" .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } elseif ($data_type == 24) { // the list of active allergies for the current patient
        // this is read-only!
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
    } elseif ($data_type == 25) { // a set of labeled checkboxes, each with a text field:
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
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='table'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $option_id_esc = htmlspecialchars($option_id, ENT_QUOTES);
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);

            // Added 5-09 by BM - Translate label if applicable
            echo "<tr><td>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $option_id = htmlspecialchars($option_id, ENT_QUOTES);
            echo "<td><input type='checkbox' name='check_{$field_id_esc}[$option_id_esc]'" .
            " id='check_{$field_id_esc}[$option_id_esc]' class='form-check-inline' value='1' $lbfonchange";
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
            " class='form-control$smallform' " .
            " $string_maxlength" .
            " value='$resnote' $disabled /></td>";
            echo "</tr>";
        }

        echo "</table>";
    } elseif ($data_type == 26) { // single-selection list with ability to add to it
        echo "<div class='input-group'>";
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            ($showEmpty ? $empty_title : ''),
            'addtolistclass_' . $list_id . $smallform,
            $lbfchange,
            '',
            ($disabled ? array('disabled' => 'disabled') : null),
            false,
            $backup_list
        );
        // show the add button if user has access to correct list
        $inputValue = htmlspecialchars(xl('Add'), ENT_QUOTES);
        $btnSize = ($smallform) ? "btn-sm" : "";
        $outputAddButton = "<div class='input-group-append'><input type='button' class='btn btn-secondary $btnSize mb-1 addtolist' id='addtolistid_" . $list_id_esc . "' fieldid='form_" .
        $field_id_esc . "' value='$inputValue' $disabled /></div>";
        if (AclExtended::acoExist('lists', $list_id)) {
            // a specific aco exist for this list, so ensure access
            if (AclMain::aclCheckCore('lists', $list_id)) {
                echo $outputAddButton;
            }
        } else {
            // no specific aco exist for this list, so check for access to 'default' list
            if (AclMain::aclCheckCore('lists', 'default')) {
                echo $outputAddButton;
            }
        }
        echo "</div>";
    } elseif ($data_type == 27) { // a set of labeled radio buttons
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        // Support for edit option M.
        if (isOption($edit_options, 'M')) {
            ++$membership_group_number;
        }
        //
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='table w-100'>";
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
            echo "<td width='" . attr($tdpct) . "%' nowrap>";
            echo "<input type='radio' name='form_{$field_id_esc}' id='form_{$field_id_esc}[$option_id_esc]'" .
            " value='$option_id_esc' $lbfonchange";
            // Support for edit options M and m.
            if (isOption($edit_options, 'M')) {
                echo " class='form-check-inline'";
                echo " onclick='checkGroupMembers(this, $membership_group_number);'";
            } elseif (isOption($edit_options, 'm')) {
                echo " class='form-check-inline lbf_memgroup_$membership_group_number'";
            } else {
                echo " class='form-check-inline'";
            }
            //
            if (
                (strlen($currvalue) == 0 && $lrow['is_default']) ||
                (strlen($currvalue)  > 0 && $option_id == $currvalue)
            ) {
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
                echo "<tr><td colspan='$cols' style='height: 0.7rem'></td></tr>";
            }
        }

        echo "</table>";
        if (!$got_selected && strlen($currvalue) > 0) {
            $fontTitle = htmlspecialchars(xl('Please choose a valid selection.'), ENT_QUOTES);
            $fontText = htmlspecialchars(xl('Fix this'), ENT_NOQUOTES);
            echo "$currescaped <span class='text-danger' title='$fontTitle'>$fontText!</span>";
        }
    } elseif ($data_type == 28 || $data_type == 32) { // special case for history of lifestyle status; 3 radio buttons
        // and a date text field:
        // VicarePlus :: A selection list box for smoking status:
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
                $reslist = '';
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                $reslist = '';
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                $reslist = '';
                break;
            default:
                $restype = $resdate = $resnote = "";
                $reslist = '';
                break;
        }

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];

        $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars($resnote, ENT_QUOTES);
        $resdate = htmlspecialchars($resdate, ENT_QUOTES);
        echo "<table class='table'>";
        echo "<tr>";
        if ($data_type == 28) {
            // input text
            echo "<td><input type='text' class='form-control'" .
            " name='form_$field_id_esc'" .
            " id='form_$field_id_esc'" .
            " size='$fldlength'" .
            " class='form-control$smallform'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td>";
            echo "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            htmlspecialchars(xl('Status'), ENT_NOQUOTES) . ":&nbsp;&nbsp;</td>";
        } elseif ($data_type == 32) {
            // input text
            echo "<tr><td><input type='text'" .
            " name='form_text_$field_id_esc'" .
            " id='form_text_$field_id_esc'" .
            " size='$fldlength'" .
            " class='form-control$smallform'" .
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
                $smallform,
                $onchange,
                '',
                ($disabled ? array('disabled' => 'disabled') : null)
            );
            echo "</td>";
            echo "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . xlt('Status') . ":&nbsp;&nbsp;</td>";
        }

        // current
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " id='radio_{$field_id_esc}[current]'" .
        " class='form-check-inline'" .
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
        " class='form-check-inline'" .
        " value='quit" . $field_id_esc . "' $lbfonchange";
        if ($restype == "quit" . $field_id) {
            echo " checked";
        }

        if ($data_type == 32) {
            echo " onClick='smoking_statusClicked(this)'";
        }

        echo " $disabled />" . xlt('Quit') . "&nbsp;</td>";
        // quit date
        echo "<td class='text'><input type='text' size='6' class='form-control datepicker' name='date_$field_id_esc' id='date_$field_id_esc'" .
        " value='$resdate'" .
        " title='$description'" .
        " $disabled />";
        echo "&nbsp;</td>";
        // never
        echo "<td class='text'><input type='radio'" .
        " name='radio_{$field_id_esc}'" .
        " class='form-check-inline'" .
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
        " class='form-check-inline' " .
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
        echo "<td class='text'><div id='smoke_code'></div></td>";
        echo "</tr>";
        echo "</table>";
    } elseif ($data_type == 31) { // static text.  read-only, of course.
        echo parse_static_text($frow);
    } elseif ($data_type == 34) {
        // $data_type == 33
        // Race and Ethnicity. After added support for backup lists, this is now the same as datatype 36; so have migrated it there.
        // $data_type == 33

        $arr = explode("|*|*|*|", $currvalue);
        echo "<div>"; // wrapper for myHideOrShow()
        echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=" . htmlspecialchars($list_id_esc, ENT_QUOTES) . "' class='iframe_medium text-body text-decoration-none'>";
        echo "<div id='form_{$field_id}_div' class='text-area' style='min-width: 133px'>" . $arr[0] . "</div>";
        echo "<div style='display: none'><textarea name='form_{$field_id}' id='form_{$field_id}' class='form-control$smallform' style='display: none' $lbfonchange $disabled>" . $currvalue . "</textarea></div>";
        echo "</a>";
        echo "</div>";
    } elseif ($data_type == 35) { //facilities drop-down list
        if (empty($currvalue)) {
            $currvalue = 0;
        }

        dropdown_facility(
            $selected = $currvalue,
            $name = "form_$field_id_esc",
            $allow_unspecified = true,
            $allow_allfacilities = false,
            $disabled,
            $lbfchange,
            false,
            $smallform
        );
    } elseif ($data_type == 36 || $data_type == 33) { //multiple select, supports backup list
        echo generate_select_list(
            "form_$field_id",
            $list_id,
            $currvalue,
            $description,
            $showEmpty ? $empty_title : '',
            $smallform,
            $lbfchange,
            '',
            null,
            true,
            $backup_list
        );

    // A set of lab test results; Gestation, 3 radio buttons, test value, notes field:
    } elseif ($data_type == 37) {
        echo genLabResults($frow, $currvalue, 0, $disabled);
    } elseif ($data_type == 40) { // Canvas and related elements for browser-side image drawing.
        // Note you must invoke lbf_canvas_head() (below) to use this field type in a form.
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
        echo "<div>"; // wrapper for myHideOrShow()
        echo "<div id='form_$field_id_esc' style='width:{$mywidth}px; height:{$myheight}px;'></div>";
        // Hidden form field exists to send updated data to the server at submit time.
        echo "<input type='hidden' name='form_$field_id_esc' value='' />";
        // Hidden image exists to support initialization of the canvas.
        echo "<img src='" . attr($currvalue) . "' id='form_{$field_id_esc}_img' style='display:none'>";
        echo "</div>";
        // $date_init is a misnomer but it's the place for browser-side setup logic.
        $date_init .= " lbfCanvasSetup('form_$field_id_esc', $canWidth, $canHeight);\n";
    } elseif ($data_type == 41 || $data_type == 42) {
        $datatype = 'patient-signature';
        $cpid = $GLOBALS['pid'];
        $cuser = $_SESSION['authUserID'];
        if ($data_type == 42) {
            $datatype = 'admin-signature';
        }
        echo "<input type='hidden' id='form_$field_id_esc' name='form_$field_id_esc' value='' />\n";
        echo "<img class='signature' id='form_{$field_id_esc}_img' title='$description'
            data-pid='$cpid' data-user='$cuser' data-type='$datatype'
            data-action='fetch_signature' alt='Get Signature' src='" . attr($currvalue) . "'>\n";
    } elseif ($data_type == 44) { //multiple select facility
        if (empty($currvalue)) {
            $currvalue = 0;
        }

        dropdown_facility(
            $selected = $currvalue,
            $name = "form_$field_id_esc",
            $allow_unspecified = false,
            $allow_allfacilities = false,
            $disabled,
            $lbfchange,
            true,
            $smallform
        );
    } elseif ($data_type == 45) { // Multiple provider list, local providers only
        $ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
        "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
        "AND authorized = 1 ORDER BY lname, fname");
        echo "<select name='form_$field_id_esc" . "[]'" . " id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control$smallform select-dropdown' style='width:100%;'  multiple='multiple'>";
        $got_selected = false;
        while ($urow = sqlFetchArray($ures)) {
            $uname = text($urow['fname'] . ' ' . $urow['lname']);
            $optionId = attr($urow['id']);
            echo "<option value='$optionId'";
            $selectedValues = explode("|", $currvalue);

            if (in_array($optionId, $selectedValues)) {
                echo " selected";
                $got_selected = true;
            }

            echo ">$uname</option>";
        }

        if (!$got_selected && $currvalue) {
            echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
            echo "</select>";
            echo " <span class='text-danger' title='" . xla('Please choose a valid selection from the list.') . "'>" . xlt('Fix this') . "!</span>";
        } else {
            echo "</select>";
        }

    // Patient selector field.
    } elseif ($data_type == 51) {
        $fldlength = attr($frow['fld_length']);
        $currdescstring = '';
        if (!empty($currvalue)) {
            $currdescstring .= getPatientDescription($currvalue);
        }
        $currdescstring = htmlspecialchars($currdescstring, ENT_QUOTES);
        echo "<div>"; // wrapper for myHideOrShow()
        echo "<input type='text'" .
            " name='form_$field_id_esc'" .
            " size='$fldlength'" .
            " value='$currescaped'" .
            " style='display:none'" .
            " $lbfonchange readonly $disabled />";
        // Extra readonly input field for patient description (name and pid).
        echo "<input type='text'" .
            " name='form_$field_id_esc" . "__desc'" .
            " size='$fldlength'" .
            " title='$description'" .
            " value='$currdescstring'";
        if (!$disabled) {
            echo " onclick='sel_patient(this, this.form.form_$field_id_esc)'";
        }
        echo " readonly $disabled />";
        echo "</div>";
    // Previous Patient Names with add. Somewhat mirrors data types 44,45.
    } elseif ($data_type == 52) {
        global $pid;
        $pid = ($frow['blank_form'] ?? null) ? null : $pid;
        $patientNameService = new PatientNameHistoryService();
        $res = $patientNameService->getPatientNameHistory($pid);
        echo "<div class='input-group w-75'>";
        echo "<select name='form_$field_id_esc" . "[]'" . " id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control$smallform select-previous-names' multiple='multiple'>";
        foreach ($res as $row) {
            $pname = $row['formatted_name']; // esc'ed in fetch.
            $optionId = attr($row['id']);
            // all names always selected
            echo "<option value='$optionId'" . " selected>$pname</option>";
        }
        echo "</select>";
        echo "<button type='button' class='btn btn-primary btn-sm' id='type_52_add' onclick='return specialtyFormDialog()'>" . xlt('Add') . "</button></div>";
    // Patient Encounter List Field
    } elseif ($data_type == 53) {
        global $pid;
        $pid = ($frow['blank_form'] ?? null) ? 0 : $pid;
        $encounterService = new EncounterService();
        $res = $encounterService->getEncountersForPatientByPid($pid);
        echo "<div class='input-group w-75'>";
        echo "<select name='form_$field_id_esc'" . " id='form_$field_id_esc' title='$description' $lbfonchange $disabled class='form-control$smallform select-encounters'>";
        echo "<option value=''>" . xlt("Select Encounter") . "</option>";
        foreach ($res as $row) {
            $label = text(date("Y-m-d", strtotime($row['date']))  . " " . ($row['pc_catname'] ?? ''));
            $optionId = attr($row['eid']);
            // all names always selected
            if ($currvalue == $row['eid']) {
                echo "<option value='$optionId'" . " selected>$label</option>";
            } else {
                echo "<option value='$optionId'>$label</option>";
            }
        }
        echo "</select>";
    } elseif ($data_type == 54) {
        include "templates/address_list_form.php";
    }
}

function generate_print_field($frow, $currvalue, $value_allowed = true)
{
    global $rootdir, $date_init, $ISSUE_TYPES;

    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

    $data_type   = $frow['data_type'];
    $field_id    = $frow['field_id'] ?? null;
    $list_id     = $frow['list_id'];
    $fld_length  = $frow['fld_length'] ?? null;
    $backup_list = $frow['list_backup_id'] ?? null;

    $description = attr(xl_layout_label($frow['description'] ?? ''));

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
    // if (false && ($data_type == 1 || $data_type == 26 || $data_type == 33 || $data_type == 43 || $data_type == 46)) {
    // We used to show all the list options but this was undone per CV request 2017-12-07
    // (see alternative code below).
    if ($data_type == 1 || $data_type == 26 || $data_type == 33 || $data_type == 43 || $data_type == 46) {
        if (empty($fld_length)) {
            if ($list_id == 'titles') {
                $fld_length = 3;
            } else {
                $fld_length = 10;
            }
        }

        $tmp = '';
        if ($currvalue) {
            if ($data_type == 46) {
                // support for single-selection list with comment support
                $selectedValues = explode("|", $currvalue);
                $currvalue = $selectedValues[0];
            }
            $lrow = sqlQuery(
                "SELECT title FROM list_options " .
                "WHERE list_id = ? AND option_id = ? AND activity = 1",
                array($list_id,$currvalue)
            );
            // For lists Race and Ethnicity if there is no matching value in the corresponding lists check ethrace list
            if (empty($lrow) && $data_type == 33) {
                $lrow = sqlQuery(
                    "SELECT title FROM list_options " .
                    "WHERE list_id = ? AND option_id = ? AND activity = 1",
                    array('ethrace', $currvalue)
                );
            }

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

            if ($data_type == 46) {
                // support for single-selection list with comment support
                $resnote = $selectedValues[1] ?? null;
                if (!empty($resnote)) {
                    $tmp .= " (" . $resnote . ")";
                }
            }
        }

        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }
        echo $tmp;
    } elseif ($data_type == 2 || $data_type == BillingCodeType::OPTIONS_TYPE_INDEX) { // simple text field
        if ($currescaped === '') {
            $currescaped = '&nbsp;';
        }

        echo $currescaped;
    } elseif ($data_type == 3) { // long or multi-line text field
        $fldlength = htmlspecialchars($fld_length, ENT_QUOTES);
        $maxlength = htmlspecialchars($frow['fld_rows'], ENT_QUOTES);
        echo "<textarea" .
        " class='form-control' " .
        " cols='$fldlength'" .
        " rows='$maxlength'>" .
        $currescaped . "</textarea>";
    } elseif ($data_type == 4) { // date
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
    } elseif ($data_type == 10 || $data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) { // provider list
        if ($data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) {
            $obj = new LocalProviderListType();
            echo $obj->buildPrintView($frow, $currvalue, $value_allowed);
        } else {
            $tmp = '';
            if ($currvalue) {
                $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                    "WHERE id = ?", array($currvalue));
                $tmp = ucwords($urow['fname'] . " " . $urow['lname']);
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
        }
    } elseif ($data_type == 12) { // pharmacy list
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
        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } elseif ($data_type == 13) { // squads
        $tmp = '';
        if ($currvalue) {
            $squads = AclExtended::aclGetSquads();
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
        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } elseif ($data_type == 14) { // Address book.
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
        if ($tmp === '') {
            $tmp = '&nbsp;';
        } else {
            $tmp = htmlspecialchars($tmp, ENT_QUOTES);
        }

            echo $tmp;
    } elseif ($data_type == 16) { // insurance company list
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
    } elseif ($data_type == 17) { // issue types
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
    } elseif ($data_type == 18) { // Visit categories.
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
    } elseif ($data_type == 21) { // a single checkbox or set of labeled checkboxes
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
            echo "<table class='w-100' cellpadding='0' cellspacing='0'>";
            $tdpct = (int) (100 / $cols);
            for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
                $option_id = $lrow['option_id'];
                if ($count % $cols == 0) {
                    if ($count) {
                        echo "</tr>";
                    }

                    echo "<tr>";
                }
                echo "<td width='" . attr($tdpct) . "%' nowrap>";
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
    } elseif ($data_type == 22) { // a set of labeled text input fields
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='table'>";
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
    } elseif ($data_type == 23) { // a set of exam results; 3 radio buttons and a text field:
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
        echo "<table class='table'>";
        echo "<tr><td><td class='font-weight-bold'>" .
        htmlspecialchars(xl('Exam or Test'), ENT_NOQUOTES) . "</td><td class='font-weight-bold'>" .
        htmlspecialchars(xl('N/A'), ENT_NOQUOTES) .
        "&nbsp;</td><td class='font-weight-bold'>" .
        htmlspecialchars(xl('Nor'), ENT_NOQUOTES) . "&nbsp;</td>" .
        "<td class='font-weight-bold'>" .
        htmlspecialchars(xl('Abn'), ENT_NOQUOTES) . "&nbsp;</td><td class='font-weight-bold'>" .
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
    } elseif ($data_type == 24) { // the list of active allergies for the current patient
        // this is read-only!
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
    } elseif ($data_type == 25) { // a set of labeled checkboxes, each with a text field:
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
        echo "<table class='table'>";
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
    } elseif ($data_type == 27) { // Removed: || $data_type == 1 || $data_type == 26 || $data_type == 33
        // a set of labeled radio buttons
        // In this special case, fld_length is the number of columns generated.

        $cols = max(1, ($frow['fld_length'] ?? null));
        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        echo "<table class='w-100' cellpadding='0' cellspacing='0'>";
        $tdpct = (int) (100 / $cols);
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            if ($count % $cols == 0) {
                if ($count) {
                    echo "</tr>";
                }
                echo "<tr>";
            }
            echo "<td width='" . attr($tdpct) . "%' nowrap>";
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

    // special case for history of lifestyle status; 3 radio buttons and a date text field:
    } elseif ($data_type == 28 || $data_type == 32) {
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
                $reslist = '';
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                $reslist = '';
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                $reslist = '';
                break;
            default:
                $restype = $resdate = $resnote = "";
                $reslist = '';
                break;
        }

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        echo "<table class='table'>";
        echo "<tr>";
        $fldlength = htmlspecialchars($fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars($resnote, ENT_QUOTES);
        $resdate = htmlspecialchars($resdate, ENT_QUOTES);
        if ($data_type == 28) {
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " class='under'" .
            " value='$resnote' /></td>";
            echo "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            htmlspecialchars(xl('Status'), ENT_NOQUOTES) . ":&nbsp;</td>";
        } elseif ($data_type == 32) {
            echo "<tr><td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$resnote' /></td></tr>";
            $fldlength = 30;
            $smoking_status_title = generate_display_field(array('data_type' => '1','list_id' => $list_id), $reslist);
            echo "<td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$smoking_status_title' /></td>";
            echo "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars(xl('Status'), ENT_NOQUOTES) . ":&nbsp;&nbsp;</td>";
        }

        echo "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            echo " checked";
        }

        echo "/>" . htmlspecialchars(xl('Current'), ENT_NOQUOTES) . "&nbsp;</td>";

        echo "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            echo " checked";
        }

        echo "/>" . htmlspecialchars(xl('Quit'), ENT_NOQUOTES) . "&nbsp;</td>";

        echo "<td><input type='text' size='6'" .
        " value='$resdate'" .
        " class='under form-control'" .
        " /></td>";

        echo "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            echo " checked";
        }

        echo " />" . htmlspecialchars(xl('Never'), ENT_NOQUOTES) . "</td>";

        echo "<td><input type='radio' class='form-check-inline'";
        if ($restype == "not_applicable" . $field_id) {
            echo " checked";
        }

        echo " />" . htmlspecialchars(xl('N/A'), ENT_NOQUOTES) . "&nbsp;</td>";
        echo "</tr>";
        echo "</table>";
    } elseif ($data_type == 31) { // static text.  read-only, of course.
        echo parse_static_text($frow, $value_allowed);
    } elseif ($data_type == 34) {
        echo "<a href='../../../library/custom_template/custom_template.php?type=form_{$field_id}&contextName=" . htmlspecialchars($list_id_esc, ENT_QUOTES) . "' class='iframe_medium text-body text-decoration-none'>";
        echo "<div id='form_{$field_id}_div' class='text-area'></div>";
        echo "<div style='display: none'><textarea name='form_{$field_id}' class='form-control' id='form_{$field_id}' style='display: none'></textarea></div>";
        echo "</a>";

    // Facilities. Changed 2017-12-15 to not show the choices.
    } elseif ($data_type == 35) {
        $urow = sqlQuery(
            "SELECT id, name FROM facility WHERE id = ?",
            array($currvalue)
        );
        echo empty($urow['id']) ? '&nbsp;' : text($urow['name']);
    } elseif ($data_type == 36) { //Multi-select. Supports backup lists.
        if (empty($fld_length)) {
            if ($list_id == 'titles') {
                $fld_length = 3;
            } else {
                $fld_length = 10;
            }
        }

        $tmp = '';

        $values_array = explode("|", $currvalue);

        $i = 0;
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

    // A set of lab test results; Gestation, 3 radio buttons, test value, notes field:
    } elseif ($data_type == 37) {
        echo genLabResults($frow, $currvalue, 1, $disabled);
    } elseif ($data_type == 40) { // Image from canvas drawing
        if (empty($currvalue)) {
            if (preg_match('/\\bimage=([a-zA-Z0-9._-]*)/', $frow['description'], $matches)) {
                $currvalue = $GLOBALS['web_root'] . '/sites/' . $_SESSION['site_id'] . '/images/' . $matches[1];
            }
        }
        if ($currvalue) {
            echo "<img src='" . attr($currvalue) . "'>";
        }
    } elseif ($data_type == 41 || $data_type == 42) {
        if ($currvalue) {
            echo "<img class='w-auto' style='height: 70px;' src='" . attr($currvalue) . "'>";
        }
    } elseif ($data_type == 44 || $data_type == 45) {
        $tmp = '';

        $values_array = explode("|", $currvalue);

        $i = 0;
        foreach ($values_array as $value) {
            if ($value) {
                if ($data_type == 44) {
                    $lrow = sqlQuery("SELECT name as name FROM facility WHERE id = ?", array($value));
                }
                if ($data_type == 45) {
                    $lrow = sqlQuery("SELECT CONCAT(fname,' ',lname) as name FROM users WHERE id = ?", array($value));
                }
                $tmp = $lrow['name'];
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

    // Patient selector field.
    } elseif ($data_type == 51) {
        if (!empty($currvalue)) {
            $tmp = text(getPatientDescription($currvalue));
        } else {
            echo '&nbsp;';
        }
    }
}

/**
 * @param $list_id
 * @param bool $translate
 * @return array
 *
 * Generate a key-value array containing each row of the specified list,
 * with the option ID as the index, and the title as the element
 *
 * Pass in the list_id to specify this list.
 *
 * Use the translate flag to run the title element through the translator
 */
function generate_list_map($list_id, $translate = false)
{
    $result = sqlStatement("SELECT option_id, title FROM list_options WHERE list_id = ?", [$list_id]);
    $map = [];
    while ($row = sqlFetchArray($result)) {
        if ($translate === true) {
            $title = xl_list_label($row['title']);
        } else {
            $title = $row['title'];
        }
        $map[$row['option_id']] = $title;
    }

    return $map;
}

function generate_display_field($frow, $currvalue)
{
    global $ISSUE_TYPES, $facilityService;

    $data_type  = $frow['data_type'];
    $field_id   = isset($frow['field_id'])  ? $frow['field_id'] : null;
    $list_id    = $frow['list_id'];
    $backup_list = isset($frow['list_backup_id']) ? $frow['list_backup_id'] : null;
    $show_unchecked_arr = array();
    getLayoutProperties($frow['form_id'] ?? null, $show_unchecked_arr, 'grp_unchecked', "1");
    $show_unchecked = strval($show_unchecked_arr['']['grp_unchecked'] ?? null) == "0" ? false : true;

    $s = '';

    // generic selection list or the generic selection list with add on the fly
    // feature
    if ($data_type == 1 || $data_type == 26 || $data_type == 43 || $data_type == 46) {
        if ($data_type == 46) {
            // support for single-selection list with comment support
            $selectedValues = explode("|", $currvalue);
            $currvalue = $selectedValues[0];
        }

        $lrow = sqlQuery("SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ? AND activity = 1", array($list_id,$currvalue));
          $s = htmlspecialchars(xl_list_label($lrow['title'] ?? ''), ENT_NOQUOTES);
        //if there is no matching value in the corresponding lists check backup list
        // only supported in data types 1,26,43,46
        if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 43 || $data_type == 46)) {
              $lrow = sqlQuery("SELECT title FROM list_options " .
              "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list,$currvalue));
              $s = htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES);
        }

        // If match is not found in main and backup lists, return the key with exclamation mark
        if ($s == '') {
            $s = nl2br(text(xl_list_label($currvalue))) .
                '<span> <i class="fa fas fa-exclamation-circle ml-1"></i></span>';
        }

        if ($data_type == 46) {
            // support for single-selection list with comment support
            $resnote = $selectedValues[1] ?? null;
            if (!empty($resnote)) {
                $s .= " (" . text($resnote) . ")";
            }
        }
    } elseif ($data_type == 2) { // simple text field
        $s = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
    } elseif ($data_type == 3) { // long or multi-line text field
        $s = nl2br(htmlspecialchars($currvalue, ENT_NOQUOTES));
    } elseif ($data_type == 4) { // date
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
    } elseif ($data_type == 10 || $data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) { // provider
        if ($data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) {
            $obj = new LocalProviderListType();
            $s = $obj->buildDisplayView($frow, $currvalue);
        } else {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                "WHERE id = ?", array($currvalue));
            $s = text(ucwords(($urow['fname'] ?? '') . " " . ($urow['lname'] ?? '')));
        }
    } elseif ($data_type == 12) { // pharmacy list
        $pres = get_pharmacies();
        while ($prow = sqlFetchArray($pres)) {
            $key = $prow['id'];
            if ($currvalue == $key) {
                $s .= htmlspecialchars($prow['name'] . ' ' . $prow['area_code'] . '-' .
                $prow['prefix'] . '-' . $prow['number'] . ' / ' .
                $prow['line1'] . ' / ' . $prow['city'], ENT_NOQUOTES);
            }
        }
        /**
         * if anyone wants to render something after the pharmacy section on the patient chart/dashboard,
         * they would have to listen to this event.
        */
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch(new RenderPharmacySectionEvent(), RenderPharmacySectionEvent::RENDER_AFTER_SELECTED_PHARMACY_SECTION, 10);
    } elseif ($data_type == 13) { // squads
        $squads = AclExtended::aclGetSquads();
        if ($squads) {
            foreach ($squads as $key => $value) {
                if ($currvalue == $key) {
                    $s .= htmlspecialchars($value[3], ENT_NOQUOTES);
                }
            }
        }
    } elseif ($data_type == 14) { // address book
        $urow = sqlQuery("SELECT fname, lname, specialty, organization FROM users " .
        "WHERE id = ?", array($currvalue));
        //ViSolve: To display the Organization Name if it exist. Else it will display the user name.
        if (!empty($urow['organization'])) {
            $uname = $urow['organization'];
        } else {
            $uname = $urow['lname'] ?? '';
            if (!empty($urow['fname'])) {
                $uname .= ", " . $urow['fname'];
            }
        }

        $s = htmlspecialchars($uname, ENT_NOQUOTES);
    } elseif ($data_type == BillingCodeType::OPTIONS_TYPE_INDEX) { // billing code
        $billingCodeType = new BillingCodeType();
        $s = $billingCodeType->buildDisplayView($frow, $currvalue);
    } elseif ($data_type == 16) { // insurance company list
        $insprovs = getInsuranceProviders();
        foreach ($insprovs as $key => $ipname) {
            if ($currvalue == $key) {
                $s .= htmlspecialchars($ipname, ENT_NOQUOTES);
            }
        }
    } elseif ($data_type == 17) { // issue types
        foreach ($ISSUE_TYPES as $key => $value) {
            if ($currvalue == $key) {
                $s .= htmlspecialchars($value[1], ENT_NOQUOTES);
            }
        }
    } elseif ($data_type == 18) { // visit category
        $crow = sqlQuery(
            "SELECT pc_catid, pc_catname " .
            "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
            array($currvalue)
        );
        $s = htmlspecialchars($crow['pc_catname'], ENT_NOQUOTES);
    } elseif ($data_type == 21) { // a single checkbox or set of labeled checkboxes
        if (!$list_id) {
            $s .= $currvalue ? '&#9745;' : '&#9744;';
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
                $checked = in_array($option_id, $avalue);
                if (!$show_unchecked && $checked) {
                    $s .= "<td nowrap>";
                    $s .= text(xl_list_label($lrow['title'])) . '&nbsp;&nbsp;';
                    $s .= "</td>";
                } elseif ($show_unchecked) {
                    $s .= "<td nowrap>";
                    $s .= $checked ? '&#9745;' : '&#9744;';
                    $s .= '&nbsp;' . text(xl_list_label($lrow['title'])) . '&nbsp;&nbsp;';
                    $s .= "</td>";
                }
            }
            if ($count) {
                $s .= "</tr>";
            }
            $s .= "</table>";
        }
    } elseif ($data_type == 22) { // a set of labeled text input fields
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table class='table'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            if (empty($avalue[$option_id])) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='font-weight-bold align-top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . ":&nbsp;</td>";

            $s .= "<td class='text align-top'>" . htmlspecialchars($avalue[$option_id], ENT_NOQUOTES) . "</td></tr>";
        }

        $s .= "</table>";
    } elseif ($data_type == 23) { // a set of exam results; 3 radio buttons and a text field:
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table class='table'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr(($avalue[$option_id] ?? ''), 0, 1);
            $resnote = substr(($avalue[$option_id] ?? ''), 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='font-weight-bold align-top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $restype = ($restype == '1') ? xl('Normal') : (($restype == '2') ? xl('Abnormal') : xl('N/A'));
            // $s .= "<td class='text align-top'>$restype</td></tr>";
            // $s .= "<td class='text align-top'>$resnote</td></tr>";
            $s .= "<td class='text align-top'>" . htmlspecialchars($restype, ENT_NOQUOTES) . "&nbsp;</td>";
            $s .= "<td class='text align-top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "</td>";
            $s .= "</tr>";
        }

        $s .= "</table>";
    } elseif ($data_type == 24) { // the list of active allergies for the current patient
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
    } elseif ($data_type == 25) { // a set of labeled checkboxes, each with a text field:
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }

        $lres = sqlStatement("SELECT * FROM list_options " .
        "WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
        $s .= "<table class='table'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            if (empty($restype) && empty($resnote)) {
                continue;
            }

            // Added 5-09 by BM - Translate label if applicable
            $s .= "<tr><td class='font-weight-bold align-top'>" . htmlspecialchars(xl_list_label($lrow['title']), ENT_NOQUOTES) . "&nbsp;</td>";

            $restype = $restype ? xl('Yes') : xl('No');
            $s .= "<td class='text align-top'>" . htmlspecialchars($restype, ENT_NOQUOTES) . "&nbsp;</td>";
            $s .= "<td class='text align-top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "</td>";
            $s .= "</tr>";
        }

        $s .= "</table>";
    } elseif ($data_type == 27) { // a set of labeled radio buttons
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
            $checked = ((strlen($currvalue) == 0 && $lrow['is_default']) ||
                (strlen($currvalue)  > 0 && $option_id == $currvalue));
            if (!$show_unchecked && $checked) {
                $s .= "<td nowrap>";
                $s .= text(xl_list_label($lrow['title'])) . '&nbsp;&nbsp;';
                $s .= "</td>";
            } elseif ($show_unchecked) {
                $s .= "<td nowrap>";
                $s .= $checked ? '&#9745;' : '&#9744;';
                $s .= '&nbsp;' . text(xl_list_label($lrow['title'])) . '&nbsp;&nbsp;';
                $s .= "</td>";
            }
        }
        if ($count) {
            $s .= "</tr>";
        }
        $s .= "</table>";
    } elseif ($data_type == 28 || $data_type == 32) { // special case for history of lifestyle status; 3 radio buttons
        // and a date text field:
        // VicarePlus :: A selection list for smoking status.
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
                $reslist = '';
                break;
            case "2":
                $resnote = $tmp[0];
                $restype = $tmp[1];
                $resdate = "";
                $reslist = '';
                break;
            case "1":
                $resnote = $tmp[0];
                $resdate = $restype = "";
                $reslist = '';
                break;
            default:
                $restype = $resdate = $resnote = "";
                $reslist = '';
                break;
        }

        $s .= "<table class='table'>";

        $s .= "<tr>";
        $res = "";
        if ($restype == "current" . $field_id) {
            $res = xl('Current');
        }

        if ($restype == "quit" . $field_id) {
            $res = xl('Quit');
        }

        if ($restype == "never" . $field_id) {
            $res = xl('Never');
        }

        if ($restype == "not_applicable" . $field_id) {
            $res = xl('N/A');
        }

        // $s .= "<td class='text align-top'>$restype</td></tr>";
        // $s .= "<td class='text align-top'>$resnote</td></tr>";
        if ($data_type == 28) {
            if (!empty($resnote)) {
                $s .= "<td class='text align-top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            }
        } elseif ($data_type == 32) { //VicarePlus :: Tobacco field has a listbox, text box, date field and 3 radio buttons.
            // changes on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
            $smoke_codes = getSmokeCodes();
            if (!empty($reslist)) {
                if ($smoke_codes[$reslist] != "") {
                    $code_desc = "( " . $smoke_codes[$reslist] . " )";
                }

                $s .= "<td class='text align-top'>" . generate_display_field(array('data_type' => '1','list_id' => $list_id), $reslist) . "&nbsp;" . text($code_desc) . "&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            }

            if (!empty($resnote)) {
                $s .= "<td class='text align-top'>" . htmlspecialchars($resnote, ENT_NOQUOTES) . "&nbsp;&nbsp;</td>";
            }
        }

        if (!empty($res)) {
            $s .= "<td class='text align-top'><strong>" . htmlspecialchars(xl('Status'), ENT_NOQUOTES) . "</strong>:&nbsp;" . htmlspecialchars($res, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        if ($restype == "quit" . $field_id) {
            $s .= "<td class='text align-top'>" . htmlspecialchars($resdate, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        $s .= "</tr>";
        $s .= "</table>";
    } elseif ($data_type == 31) { // static text.  read-only, of course.
        $s .= parse_static_text($frow);
    } elseif ($data_type == 34) {
        $arr = explode("|*|*|*|", $currvalue);
        for ($i = 0; $i < sizeof($arr); $i++) {
            $s .= $arr[$i];
        }
    } elseif ($data_type == 35) { // facility
        $urow = $facilityService->getById($currvalue);
        $s = htmlspecialchars($urow['name'] ?? '', ENT_NOQUOTES);
    } elseif ($data_type == 36 || $data_type == 33) { // Multi select. Supports backup lists
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

            $title = $lrow['title'] ?? '';
            if ($i > 0) {
                  $s = $s . ", " . text(xl_list_label($title));
            } else {
                $s = text(xl_list_label($title));
            }

            $i++;
        }

    // A set of lab test results; Gestation, 3 radio buttons, test value, notes field:
    } elseif ($data_type == 37) {
        $s .= genLabResults($frow, $currvalue, 2, '');
    } elseif ($data_type == 40) { // Image from canvas drawing
        if (empty($currvalue)) {
            if (preg_match('/\\bimage=([a-zA-Z0-9._-]*)/', $frow['description'], $matches)) {
                $currvalue = $GLOBALS['web_root'] . '/sites/' . $_SESSION['site_id'] . '/images/' . $matches[1];
            }
        }
        if ($currvalue) {
            $s .= "<img src='" . attr($currvalue) . "'>";
        }
    } elseif ($data_type == 41 || $data_type == 42) {
        if ($currvalue) {
            $s .= "<img class='w-auto' style='height: 70px;' src='" . attr($currvalue) . "'>";
        }
    } elseif ($data_type == 44 || $data_type == 45) { // Multiple select facility and provider
        $values_array = explode("|", $currvalue);
        $i = 0;
        foreach ($values_array as $value) {
            if ($data_type == 44) {
                $lrow = sqlQuery("SELECT name as name FROM facility WHERE id = ?", array($value));
            }
            if ($data_type == 45) {
                $lrow = sqlQuery("SELECT CONCAT(fname,' ',lname) as name FROM users WHERE id = ?", array($value));
            }
            if ($i > 0) {
                  $s = $s . ", " . htmlspecialchars($lrow['name'], ENT_NOQUOTES);
            } else {
                $s = text($lrow['name'] ?? '');
            }
            $i++;
        }

    // Patient selector field.
    } elseif ($data_type == 51) {
        if (!empty($currvalue)) {
            $s .= text(getPatientDescription($currvalue));
        }
    } elseif ($data_type == 52) {
        global $pid;
        $patientNameService = new PatientNameHistoryService();
        $rows = $patientNameService->getPatientNameHistory($pid);
        $i = 0;
        foreach ($rows as $row) {
            // name escaped in fetch
            if ($i > 0) {
                $s .= ", " . $row['formatted_name'];
            } else {
                $s = $row['formatted_name'] ?? '';
            }
            $i++;
        }
        // now that we've concatenated everything, let's escape it.
        $s = text($s);
    } elseif ($data_type == 53) {
        $service = new EncounterService();
        if (!empty($currvalue)) {
            $encounterResult = $service->getEncounterById($currvalue);
            if (!empty($encounterResult) && $encounterResult->hasData()) {
                $encounter = reset($encounterResult->getData());
                $s = text($encounter['date'] ?? '');
            }
        }
    } elseif ($data_type == 54) {
        include "templates/address_list_display.php";
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
    $backup_list = $frow['backup_list'] ?? null;
    $edit_options = $frow['edit_options'] ?? null;
    $s = '';

    // generic selection list or the generic selection list with add on the fly
    // feature, or radio buttons
    //  Supports backup lists (for datatypes 1,26,43)
    if ($data_type == 1 || $data_type == 26 || $data_type == 27 || $data_type == 43 || $data_type == 46) {
        if ($data_type == 46) {
            // support for single-selection list with comment support
            $selectedValues = explode("|", $currvalue);
            $currvalue = $selectedValues[0];
        }

        $lrow = sqlQuery(
            "SELECT title FROM list_options " .
            "WHERE list_id = ? AND option_id = ? AND activity = 1",
            array($list_id, $currvalue)
        );
        $s = xl_list_label($lrow['title'] ?? '');
        //if there is no matching value in the corresponding lists check backup list
        // only supported in data types 1,26,43
        if ($lrow == 0 && !empty($backup_list) && ($data_type == 1 || $data_type == 26 || $data_type == 43 || $data_type == 46)) {
            $lrow = sqlQuery("SELECT title FROM list_options " .
            "WHERE list_id = ? AND option_id = ? AND activity = 1", array($backup_list, $currvalue));
            $s = xl_list_label($lrow['title']);
        }

        if ($data_type == 46) {
            // support for single-selection list with comment support
            $resnote = $selectedValues[1] ?? null;
            if (!empty($resnote)) {
                $s .= " (" . $resnote . ")";
            }
        }
    } elseif ($data_type == 2 || $data_type == 3) { // simple or long text field
        $s = $currvalue;
    } else if ($data_type == BillingCodeType::OPTIONS_TYPE_INDEX) {
        $billingCodeType = new BillingCodeType();
        $s = $billingCodeType->buildPlaintextView($frow, $currvalue);
    } elseif ($data_type == 4) { // date
        $modtmp = isOption($edit_options, 'F') === false ? 0 : 1;
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
    } elseif ($data_type == 10 || $data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) { // provider
        if ($data_type == LocalProviderListType::OPTIONS_TYPE_INDEX) {
            $obj = new LocalProviderListType();
            $s = $obj->buildPlaintextView($frow, $currvalue);
        } else {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                "WHERE id = ?", array($currvalue));
            $s = ucwords($urow['fname'] . " " . $urow['lname']);
        }
    } elseif ($data_type == 12) { // pharmacy list
        $pres = get_pharmacies();
        while ($prow = sqlFetchArray($pres)) {
            $key = $prow['id'];
            if ($currvalue == $key) {
                $s .= $prow['name'] . ' ' . $prow['area_code'] . '-' .
                $prow['prefix'] . '-' . $prow['number'] . ' / ' .
                $prow['line1'] . ' / ' . $prow['city'];
            }
        }
    } elseif ($data_type == 14) { // address book
        $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
        "WHERE id = ?", array($currvalue));
        $uname = $urow['lname'];
        if ($urow['fname']) {
            $uname .= ", " . $urow['fname'];
        }

        $s = $uname;
    } elseif ($data_type == 16) { // insurance company list
        $insprovs = getInsuranceProviders();
        foreach ($insprovs as $key => $ipname) {
            if ($currvalue == $key) {
                $s .= $ipname;
            }
        }
    } elseif ($data_type == 17) { // issue type
        foreach ($ISSUE_TYPES as $key => $value) {
            if ($currvalue == $key) {
                $s .= $value[1];
            }
        }
    } elseif ($data_type == 18) { // visit category
        $crow = sqlQuery(
            "SELECT pc_catid, pc_catname " .
            "FROM openemr_postcalendar_categories WHERE pc_catid = ?",
            array($currvalue)
        );
        $s = $crow['pc_catname'];
    } elseif ($data_type == 21) { // a set of labeled checkboxes
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
    } elseif ($data_type == 22) { // a set of labeled text input fields
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
    } elseif ($data_type == 23) { // A set of exam results; 3 radio buttons and a text field.
        // This shows abnormal results only.
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
    } elseif ($data_type == 24) { // the list of active allergies for the current patient
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
    } elseif ($data_type == 25) { // a set of labeled checkboxes, each with a text field:
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
    } elseif ($data_type == 28 || $data_type == 32) { // special case for history of lifestyle status; 3 radio buttons and a date text field:
        // VicarePlus :: A selection list for smoking status.
        $tmp = explode('|', $currvalue);
        $resnote = count($tmp) > 0 ? $tmp[0] : '';
        $restype = count($tmp) > 1 ? $tmp[1] : '';
        $resdate = count($tmp) > 2 ? oeFormatShortDate($tmp[2]) : '';
        $reslist = count($tmp) > 3 ? $tmp[3] : '';
        $res = "";
        if ($restype == "current" . $field_id) {
            $res = xl('Current');
        }

        if ($restype == "quit" . $field_id) {
            $res = xl('Quit');
        }

        if ($restype == "never" . $field_id) {
            $res = xl('Never');
        }

        if ($restype == "not_applicable" . $field_id) {
            $res = xl('N/A');
        }

        if ($data_type == 28) {
            if (!empty($resnote)) {
                $s .= $resnote;
            }
        } elseif ($data_type == 32) { // Tobacco field has a listbox, text box, date field and 3 radio buttons.
            if (!empty($reslist)) {
                $s .= generate_plaintext_field(array('data_type' => '1','list_id' => $list_id), $reslist);
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

        if ($restype == "quit" . $field_id) {
            if ($s !== '') {
                $s .= ' ';
            }

            $s .= $resdate;
        }
    } elseif ($data_type == 35) { // Facility, so facility can be listed in plain-text, as in patient finder column
        $facilityService = new FacilityService();
        $facility = $facilityService->getById($currvalue);
        $s = $facility['name'];
    } elseif ($data_type == 36 || $data_type == 33) { // Multi select. Supports backup lists
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

    // A set of lab test results; Gestation, 3 radio buttons, test value, notes field:
    } elseif ($data_type == 37) {
        $s .= genLabResults($frow, $currvalue, 3, '');
    } elseif ($data_type == 44 || $data_type == 45) {
        $values_array = explode("|", $currvalue);

        $i = 0;
        foreach ($values_array as $value) {
            if ($data_type == 44) {
                $lrow = sqlQuery("SELECT name as name FROM facility WHERE id = ?", array($value));
            }
            if ($data_type == 45) {
                $lrow = sqlQuery("SELECT CONCAT(fname,' ',lname) as name FROM users WHERE id = ?", array($value));
            }

            if ($i > 0) {
                $s = $s . ", " . $lrow['name'];
            } else {
                $s = $lrow['name'];
            }

            $i++;
        }

    // Patient selector field.
    } elseif ($data_type == 51) {
        if (!empty($currvalue)) {
            $s .= getPatientDescription($currvalue);
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
        for (; $cell_count < $CPR; ++$cell_count) {
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

// Bootstrapped versions of disp_end_* functions:

function bs_disp_end_cell()
{
    global $item_count;
    if ($item_count > 0) {
        echo "</div>"; // end BS column
        $item_count = 0;
    }
}

function bs_disp_end_row()
{
    global $cell_count, $CPR, $BS_COL_CLASS;
    bs_disp_end_cell();
    if ($cell_count > 0 && $cell_count < $CPR) {
        // Create a cell occupying the remaining bootstrap columns.
        // BS columns will be less than 12 if $CPR is not 2, 3, 4, 6 or 12.
        $bs_cols_remaining = ($CPR - $cell_count) * intval(12 / $CPR);
        echo "<div class='$BS_COL_CLASS-$bs_cols_remaining'></div>";
    }
    if ($cell_count > 0) {
        echo "</div><!-- End BS row -->\n";
        $cell_count = 0;
    }
}

function bs_disp_end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        bs_disp_end_row();
    }
}

//

function getPatientDescription($pid)
{
    $prow = sqlQuery("SELECT lname, fname FROM patient_data WHERE pid = ?", array($pid));
    if ($prow) {
        return $prow['lname'] . ", " . $prow['fname'] . " ($pid)";
    }
    return xl('Unknown') . " ($pid)";
}

// Accumulate action conditions into a JSON expression for the browser side.
function accumActionConditions(&$frow, &$condition_str)
{
    $field_id = $frow['field_id'];
    $conditions = empty($frow['conditions']) ? array() : unserialize($frow['conditions'], ['allowed_classes' => false]);
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
            "target:"   . js_escape($field_id)              . ", " .
            "action:"   . js_escape($action)                . ", " .
            "id:"       . js_escape($condition['id'])       . ", " .
            "itemid:"   . js_escape($condition['itemid'])   . ", " .
            "operator:" . js_escape($condition['operator']) . ", " .
            "value:"    . js_escape($condition['value'])    . ", ";
        if ($frow['data_type'] == BillingCodeType::OPTIONS_TYPE_INDEX && strpos($frow['edit_options'], '2') !== false) {
            $billingCodeType = new BillingCodeType();
            // For billing codes handle requirement to display its description.
            $condition_str .= $billingCodeType->getAccumActionConditions($frow, $condition_str, $action);
        }
        $condition_str .= "andor:" . js_escape($andor) . "}";
    }
}

function getCodeDescription($codestring, $defaulttype = 'ICD10')
{
    if ($codestring === '') {
        return '';
    }
    list($ctype, $code) = explode(':', $codestring);
    if (empty($code)) {
        $code = $ctype;
        $ctype = $defaulttype;
    }
    $desc = lookup_code_descriptions("$ctype:$code");
    if (!empty($desc)) {
        return $desc;
    } else {
        return $codestring;
    }
}

// This checks if the given field with the given value should have an action applied.
// Originally the only action was skip, but now you can also set the field to a
// specified value, or "skip and otherwise set a value".
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

    $skiprows  = unserialize($frow['conditions'], ['allowed_classes' => false]);
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
            // Action value is a string. It can be "skip", or "value=" or "hsval=" followed by a value.
            $action = $skiprow;
            continue;
        }

        if (empty($skiprow['id'])) {
            continue;
        }

        $id = $skiprow['id'];
        if (!isset($sk_layout_items[$id])) {
            error_log("Function isSkipped() cannot find skip source field '" . errorLogEscape($id) . "'.");
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
        } elseif ($src_datatype == 22 || $src_datatype == 23 || $src_datatype == 25) {
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
        } elseif ($operator == 'ne') {
            $condition = $srcvalue != $skipval;
        } elseif ($operator == 'se') {
            $condition = $srcvalue == true;
        } elseif ($operator == 'ns') {
            $condition = $srcvalue != true;
        } else {
            error_log("Unknown skip operator '" . errorLogEscape($operator) . "' for field '" . errorLogEscape($field_id) . "'.");
        }

        // Logic to accumulate multiple conditions for the same target.
        if ($prevandor == 'and') {
            $condition = $condition && $prevcond;
        } elseif ($prevandor == 'or') {
            $condition = $condition || $prevcond;
        }
        $prevandor = $skiprow['andor'];
        $prevcond = $condition;
    }

    if (substr($action, 0, 6) == 'hsval=') {
        return $prevcond ? 'skip' : ('value=' . substr($action, 6));
    }
    return $prevcond ? $action : '';
}

// Load array of names of the given layout and its groups.
function getLayoutProperties($formtype, &$grparr, $sel = "grp_title", $limit = null)
{
    if ($sel != '*' && strpos($sel, 'grp_group_id') === false) {
        $sel = "grp_group_id, $sel";
    }
    $gres = sqlStatement("SELECT $sel FROM layout_group_properties WHERE grp_form_id = ? " .
        " ORDER BY grp_group_id " .
        ($limit ? "LIMIT " . escape_limit($limit) : ""), array($formtype));
    while ($grow = sqlFetchArray($gres)) {
        // TBD: Remove this after grp_init_open column is implemented.
        if ($sel == '*' && !isset($grow['grp_init_open'])) {
            $tmprow = sqlQuery(
                "SELECT form_id FROM layout_options " .
                "WHERE form_id = ? AND group_id LIKE ? AND uor > 0 AND edit_options LIKE '%I%' " .
                "LIMIT 1",
                array($formtype, $grow['grp_group_id'] . '%')
            );
            $grow['grp_init_open'] = !empty($tmprow['form_id']);
        }
        $grparr[$grow['grp_group_id']] = $grow;
    }
}

function display_layout_rows($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    if ('HIS' == $formtype) {
        $formtype .= '%'; // TBD: DEM also?
    }
    $pres = sqlStatement(
        "SELECT grp_form_id, grp_seq, grp_title " .
        "FROM layout_group_properties " .
        "WHERE grp_form_id LIKE ? AND grp_group_id = '' " .
        "ORDER BY grp_seq, grp_title, grp_form_id",
        array("$formtype")
    );
    while ($prow = sqlFetchArray($pres)) {
        $formtype = $prow['grp_form_id'];
        $last_group = '';
        $cell_count = 0;
        $item_count = 0;

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
            $jump_new_row = isOption($frow['edit_options'], 'J');
            $prepend_blank_row = isOption($frow['edit_options'], 'K');
            $portal_exclude = (!empty($_SESSION["patient_portal_onsite_two"]) && isOption($frow['edit_options'], 'EP')) ?? null;
            $span_col_row = isOption($frow['edit_options'], 'SP');

            if (!empty($portal_exclude)) {
                continue;
            }

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
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0 || $prepend_blank_row || $jump_new_row) {
                    disp_end_row();
                    if ($prepend_blank_row) {
                        echo "<tr><td class='label' colspan='" . ($CPR + 1) . "'>&nbsp;</td></tr>\n";
                    }
                    echo "<tr>";
                    if ($group_name) {
                        echo "<td class='groupname'>";
                        echo text(xl_layout_label($group_name));
                        $group_name = '';
                    } else {
                        echo "<td class='align-top'>&nbsp;";
                    }

                      echo "</td>";
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

                // Handle starting of a new label cell.
                if ($titlecols > 0 || $span_col_row) {
                    disp_end_cell();
                    $titlecols = $span_col_row ? 0 : $titlecols;
                    $titlecols_esc = htmlspecialchars($titlecols, ENT_QUOTES);
                    if (!$span_col_row) {
                        echo "<td class='label_custom' colspan='$titlecols_esc' ";
                        echo ">";
                    }
                    $cell_count += $titlecols;
                }

                ++$item_count;

                // Prevent title write if span entire row.
                if (!$span_col_row) {
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
                }
                // Handle starting of a new data cell.
                if ($datacols > 0) {
                    disp_end_cell();
                    $datacols = $span_col_row ? $CPR : $datacols;
                    $datacols_esc = htmlspecialchars($datacols, ENT_QUOTES);
                    echo "<td class='text data' colspan='$datacols_esc'";
                    echo ">";
                    $cell_count += $datacols;
                }

                ++$item_count;
                echo generate_display_field($frow, $currvalue);
            }
        }
        disp_end_group();
    } // End this layout, there may be more in the case of history.
}

// This generates the tabs for a form.
//
function display_layout_tabs($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    if ('HIS' == $formtype) {
        $formtype .= '%'; // TBD: DEM also?
    }
    $pres = sqlStatement(
        "SELECT grp_form_id, grp_seq, grp_title " .
        "FROM layout_group_properties " .
        "WHERE grp_form_id LIKE ? AND grp_group_id = '' " .
        "ORDER BY grp_seq, grp_title, grp_form_id",
        array("$formtype")
    );
    $first = true;
    while ($prow = sqlFetchArray($pres)) {
        $formtype = $prow['grp_form_id'];
        $last_group = '';
        $cell_count = 0;
        $item_count = 0;

        $grparr = array();
        getLayoutProperties($formtype, $grparr);

        $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 " .
            "ORDER BY group_id", array($formtype));

        $prev_group = '';
        while ($frow = sqlFetchArray($fres)) {
            $this_group = $frow['group_id'];
            if (substr($prev_group, 0, 1) === substr($this_group, 0, 1)) {
                // Skip sub-groups, they will not start a new tab.
                continue;
            }
            $prev_group = $this_group;
            $group_name = $grparr[$this_group]['grp_title'];
            if ($group_name === 'Employer' && $GLOBALS['omit_employers']) {
                continue;
            }
            ?>
            <li <?php echo $first ? 'class="current"' : '' ?>>
            <a href="#" id="header_tab_<?php echo attr($group_name); ?>">
            <?php echo text(xl_layout_label($group_name)); ?></a>
            </li>
            <?php
            $first = false;
        }
    } // End this layout, there may be more in the case of history.
}

// This generates the tab contents of the display version of a form.
//
function display_layout_tabs_data($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR;

    if ('HIS' == $formtype) {
        $formtype .= '%'; // TBD: DEM also?
    }
    $pres = sqlStatement(
        "SELECT grp_form_id, grp_seq, grp_title " .
        "FROM layout_group_properties " .
        "WHERE grp_form_id LIKE ? AND grp_group_id = '' " .
        "ORDER BY grp_seq, grp_title, grp_form_id",
        array("$formtype")
    );
    $first = true;

    // This loops once per layout. Only Patient History can have multiple layouts.
    while ($prow = sqlFetchArray($pres)) {
        $formtype = $prow['grp_form_id'];
        $last_group = '';
        $cell_count = 0;
        $item_count = 0;

        $grparr = array();
        getLayoutProperties($formtype, $grparr, '*');

        $TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

        // By selecting distinct group_id from layout_options we avoid empty groups.
        $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 " .
            "ORDER BY group_id", array($formtype));

        $prev_group = '';

        // This loops once per group within a given layout.
        while ($frow = sqlFetchArray($fres)) {
            $this_group = isset($frow['group_id']) ? $frow['group_id'] : "" ;

            if ($grparr[$this_group]['grp_title'] === 'Employer' && $GLOBALS['omit_employers']) {
                continue;
            }
            $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];
            $subtitle = empty($grparr[$this_group]['grp_subtitle']) ? '' : xl_layout_label($grparr[$this_group]['grp_subtitle']);

            $group_fields_query = sqlStatement(
                "SELECT * FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 AND group_id = ? " .
                "ORDER BY seq",
                array($formtype, $this_group)
            );

            if (substr($this_group, 0, 1) !== substr($prev_group, 0, 1)) {
                // Each new top level group gets its own tab div.
                if (!$first) {
                    echo "</div>\n";
                }
                echo "<div class='tab" . ($first ? ' current' : '') . "'>\n";
            }
            echo "<table border='0' cellpadding='0'>\n";

            // This loops once per field within a given group.
            while ($group_fields = sqlFetchArray($group_fields_query)) {
                $titlecols     = $group_fields['titlecols'];
                $datacols      = $group_fields['datacols'];
                $data_type     = $group_fields['data_type'];
                $field_id      = $group_fields['field_id'];
                $list_id       = $group_fields['list_id'];
                $currvalue     = '';
                $edit_options  = $group_fields['edit_options'];
                $jump_new_row = isOption($edit_options, 'J');
                $prepend_blank_row = isOption($edit_options, 'K');
                $span_col_row = isOption($edit_options, 'SP');

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
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0 || $prepend_blank_row || $jump_new_row) {
                    disp_end_row();
                    if ($subtitle) {
                        // Group subtitle exists and is not displayed yet.
                        echo "<tr><td class='label' style='background-color: var(--gray300); padding: 4px' colspan='$CPR'>" . text($subtitle) . "</td></tr>\n";
                        echo "<tr><td class='label' style='height: 5px' colspan='$CPR'></td></tr>\n";
                        $subtitle = '';
                    }
                    if ($prepend_blank_row) {
                        echo "<tr><td class='label' style='font-size:25%' colspan='$CPR'>&nbsp;</td></tr>\n";
                    }
                    echo "<tr>";
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

                // Handle starting of a new label cell.
                if ($titlecols > 0 || $span_col_row) {
                    disp_end_cell();
                    $titlecols = $span_col_row ? 0 : $titlecols;
                    $titlecols_esc = htmlspecialchars($titlecols, ENT_QUOTES);
                    $field_id_label = 'label_' . $group_fields['field_id'];
                    if (!$span_col_row) {
                        echo "<td class='label_custom' colspan='$titlecols_esc' id='" . attr($field_id_label) . "'";
                        echo ">";
                    }
                    $cell_count += $titlecols;
                }

                ++$item_count;

                if ($datacols == 0) {
                    // Data will be in the same cell, so prevent wrapping to a new line.
                    echo "<span class='text-nowrap mr-2'>";
                }

                $field_id_label = 'label_' . $group_fields['field_id'];
                if (!$span_col_row) {
                    echo "<span id='" . attr($field_id_label) . "'>";
                    if ($skip_this_field) {
                        // No label because skipping
                    } elseif ($group_fields['title']) {
                        $tmp = xl_layout_label($group_fields['title']);
                        echo text($tmp);
                        // Append colon only if label does not end with punctuation.
                        if (!str_contains('?!.,:-=', $tmp[strlen($tmp) - 1])) {
                            echo ':';
                        }
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</span>";
                }

                // Handle starting of a new data cell.
                if ($datacols > 0) {
                    disp_end_cell();
                    $datacols = $span_col_row ? $CPR : $datacols;
                    $datacols_esc = htmlspecialchars($datacols, ENT_QUOTES);
                    $field_id = 'text_' . $group_fields['field_id'];
                    echo "<td class='text data' colspan='$datacols_esc' id='" . attr($field_id) . "'  data-value='" . attr($currvalue) . "'";
                    if (!$skip_this_field && $data_type == 3) {
                        // Textarea gets a light grey border.
                        echo " style='border: 1px solid var(--gray400)'";
                    }
                    echo ">";
                    $cell_count += $datacols;
                } else {
                    $field_id = 'text_' . $group_fields['field_id'];
                    echo "<span id='" . attr($field_id) . "' style='display: none'>" . text($currvalue) . "</span>";
                }

                ++$item_count;
                if (!$skip_this_field) {
                    if ($item_count > 1) {
                        echo "&nbsp;";
                    }
                    echo generate_display_field($group_fields, $currvalue);
                }
                if ($datacols == 0) {
                    // End nowrap
                    echo "</span> "; // space to allow wrap between spans
                }
            } // end field

            disp_end_row();

            // End table for the group.
            echo "</table>\n";

            $prev_group = $this_group;
            $first = false;
        } // End this group.
    } // End this layout, there may be more in the case of history.

    if (!$first) {
        echo "</div>\n";
    }
}

// This generates the tab contents of the data entry version of a form.
//
function display_layout_tabs_data_editable($formtype, $result1, $result2 = '')
{
    global $item_count, $cell_count, $last_group, $CPR, $condition_str, $BS_COL_CLASS;

    if ('HIS' == $formtype) {
        $formtype .= '%'; // TBD: DEM also?
    }
    $pres = sqlStatement(
        "SELECT grp_form_id, grp_seq, grp_title " .
        "FROM layout_group_properties " .
        "WHERE grp_form_id LIKE ? AND grp_group_id = '' " .
        "ORDER BY grp_seq, grp_title, grp_form_id",
        array("$formtype")
    );
    $first = true;
    $condition_str = '';

    // This loops once per layout. Only Patient History can have multiple layouts.
    while ($prow = sqlFetchArray($pres)) {
        $formtype = $prow['grp_form_id'];
        $last_group = '';
        $cell_count = 0;
        $item_count = 0;

        $grparr = array();
        getLayoutProperties($formtype, $grparr, '*');

        $TOPCPR = empty($grparr['']['grp_columns']) ? 4 : $grparr['']['grp_columns'];

        // Check the children of each top-level group to see if any of them are initially open.
        // If not, make the first such child initially open.
        foreach ($grparr as $tmprow1) {
            if (strlen($tmprow1['grp_group_id']) == 1) {
                $got_init_open = false;
                $keyfirst = false;
                foreach ($grparr as $key2 => $tmprow2) {
                    if (substr($tmprow2['grp_group_id'], 0, 1) == $tmprow1['grp_group_id'] && strlen($tmprow2['grp_group_id']) == 2) {
                        if (!$keyfirst) {
                            $keyfirst = $key2;
                        }
                        if ($tmprow2['grp_init_open']) {
                            $got_init_open = true;
                        }
                    }
                }
                if (!$got_init_open && $keyfirst) {
                    $grparr[$keyfirst]['grp_init_open'] = 1;
                }
            }
        }

        // Variables $gs_* are context for the group set in the current tab.
        $gs_display_style = 'block';
        // This string is the active group levels representing the current display state.
        // Each leading substring represents an instance of nesting.
        // As each new group is encountered, groups will be closed and opened as needed
        // until the display state matches the new group.
        $gs_group_levels = '';

        // By selecting distinct group_id from layout_options we avoid empty groups.
        $fres = sqlStatement("SELECT distinct group_id FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 " .
            "ORDER BY group_id", array($formtype));

        // This loops once per group within a given layout.
        while ($frow = sqlFetchArray($fres)) {
            $this_group = $frow['group_id'];
            $group_name = $grparr[$this_group]['grp_title'];
            $group_name_esc = text($group_name);

            if ($grparr[$this_group]['grp_title'] === 'Employer' && $GLOBALS['omit_employers']) {
                continue;
            }
            $CPR = empty($grparr[$this_group]['grp_columns']) ? $TOPCPR : $grparr[$this_group]['grp_columns'];
            $subtitle = empty($grparr[$this_group]['grp_subtitle']) ? '' : xl_layout_label($grparr[$this_group]['grp_subtitle']);

            $group_fields_query = sqlStatement("SELECT * FROM layout_options " .
                "WHERE form_id = ? AND uor > 0 AND group_id = ? " .
                "ORDER BY seq", array($formtype, $this_group));

            $gs_this_levels = $this_group;
            // Compute $gs_i as the number of initial matching levels.
            $gs_i = 0;
            $tmp = min(strlen($gs_this_levels), strlen($gs_group_levels));
            while ($gs_i < $tmp && $gs_this_levels[$gs_i] == $gs_group_levels[$gs_i]) {
                ++$gs_i;
            }

            // Close any groups that we are done with.
            while (strlen($gs_group_levels) > $gs_i) {
                $gs_group_name = $grparr[$gs_group_levels]['grp_title'];
                if (strlen($gs_group_levels) > 1) {
                    // No div for an empty sub-group name.
                    if (strlen($gs_group_name)) {
                        echo "</div>\n";
                    }
                } else {
                    // This is the top group level so ending this tab and will start a new one.
                    echo "</div>\n";
                }
                $gs_group_levels = substr($gs_group_levels, 0, -1); // remove last character
            }

            // If there are any new groups, open them.
            while ($gs_i < strlen($gs_this_levels)) {
                $gs_group_levels .= $gs_this_levels[$gs_i++];
                $gs_group_name = $grparr[substr($gs_group_levels, 0, $gs_i)]['grp_title'];
                $gs_init_open = $grparr[substr($gs_group_levels, 0, $gs_i)]['grp_init_open'];
                // Compute a short unique identifier for this group.
                $gs_group_seq = "grp-$formtype-$gs_group_levels";
                if ($gs_i <= 1) {
                    // Top level group so new tab.
                    echo "<div class='tab" . ($first ? ' current' : '') . "' id='tab_$group_name_esc'>\n";
                } else {
                    // Not a new tab so start the group inline.
                    // If group name is blank, no checkbox or div.
                    if (strlen($gs_group_name)) {
                        echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" .
                            attr($gs_group_seq) . "' value='1' " .
                            "onclick='return divclick(this," . attr_js('div_' . $gs_group_seq) . ");'";
                        $gs_display_style = $gs_init_open ? 'block' : 'none';
                        if ($gs_display_style == 'block') {
                            echo " checked";
                        }
                        echo " /><b>" . text(xl_layout_label($gs_group_name)) . "</b></span>\n";
                        echo "<div id='div_" . attr($gs_group_seq) .
                            "' class='section' style='display:" . attr($gs_display_style) . ";'>\n";
                    }
                }
            }

            // Each group or subgroup has its own separate container.
            $gs_group_table_active = true;
            echo "<div class='container-fluid lbfdata'>\n";
            if ($subtitle) {
                // There is a group subtitle so show it.
                $bs_cols = $CPR * intval(12 / $CPR);
                echo "<div class='row mb-2'>";
                echo "<div class='$BS_COL_CLASS-$bs_cols' style='color:#0000ff'>" . text($subtitle) . "</div>";
                echo "</div>\n";
            }

            // This loops once per field within a given group.
            while ($group_fields = sqlFetchArray($group_fields_query)) {
                $titlecols  = $group_fields['titlecols'];
                $datacols   = $group_fields['datacols'];
                $data_type  = $group_fields['data_type'];
                $field_id   = $group_fields['field_id'];
                $list_id    = $group_fields['list_id'];
                $backup_list = $group_fields['list_backup_id'];
                $currvalue  = '';
                $action     = 'skip';
                $jump_new_row = isOption($group_fields['edit_options'], 'J');
                $prepend_blank_row = isOption($group_fields['edit_options'], 'K');
                $span_col_row = isOption($group_fields['edit_options'], 'SP');

                // Accumulate action conditions into a JSON expression for the browser side.
                accumActionConditions($group_fields, $condition_str);

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
                if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0 || $prepend_blank_row || $jump_new_row) {
                    bs_disp_end_row();
                    $bs_cols = $CPR * intval(12 / $CPR);
                    if ($subtitle) {
                        // Group subtitle exists and is not displayed yet.
                        echo "<div class='form-row mb-2'>";
                        echo "<div class='$BS_COL_CLASS-$bs_cols p-2 label' style='background-color: var(--gray300)'>" . text($subtitle) . "</div>";
                        echo "</div>\n";
                        $subtitle = '';
                    }
                    if ($prepend_blank_row) {
                        echo "<div class='form-row'>";
                        echo "<div class='$BS_COL_CLASS-$bs_cols label' style='font-size: 25%'>&nbsp;</div>";
                        echo "</div>\n";
                    }
                    echo "<div class='form-row'>";
                }

                if ($item_count == 0 && $titlecols == 0) {
                    $titlecols = 1;
                }

                // Handle starting of a new label cell.
                if ($titlecols > 0 || $span_col_row) {
                    bs_disp_end_cell();
                    $titlecols = $span_col_row ? 0 : $titlecols;
                    $bs_cols = $titlecols * intval(12 / $CPR);
                    echo "<div class='$BS_COL_CLASS-$bs_cols pt-1 label_custom' ";
                    echo "id='label_id_" . attr($field_id) . "'";
                    echo ">";
                    $cell_count += $titlecols;
                }

                // $item_count is the number of title and data items in the current cell.
                ++$item_count;

                if ($datacols == 0) {
                    // Data will be in the same cell, so prevent wrapping to a new line.
                    echo "<span class='text-nowrap mr-2'>";
                }

                if (!$span_col_row) {
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
                }

                // Handle starting of a new data cell.
                if ($datacols > 0) {
                    bs_disp_end_cell();
                    $field_id = 'text_' . $group_fields['field_id'];
                    $datacols = $span_col_row ? $CPR : $datacols;
                    $bs_cols = $datacols * intval(12 / $CPR);
                    echo "<div class='$BS_COL_CLASS-$bs_cols'";
                    echo " id='value_id_" . attr($field_id) . "'";
                    echo ">";
                    $cell_count += $datacols;
                }

                ++$item_count;
                if ($item_count > 1) {
                    echo "&nbsp;";
                }
                // 'smallform' can be used to add arbitrary CSS classes. Note the leading space.
                $group_fields['smallform'] = ' form-control-sm mb-1 mw-100';
                echo generate_form_field($group_fields, $currvalue);
                if ($datacols == 0) {
                    // End nowrap
                    echo "</span> "; // space to allow wrap between spans
                }
            } // End of fields for this group.

            bs_disp_end_row(); // TBD: Does this belong here?
            echo "</div>\n"; // end container-fluid
            $first = false;
        } // End this group.

        // Close any groups still open.
        while (strlen($gs_group_levels) > 0) {
            $gs_group_name = $grparr[$gs_group_levels]['grp_title'];
            if (strlen($gs_group_levels) > 1) {
                // No div for an empty sub-group name.
                if (strlen($gs_group_name)) {
                    echo "</div>\n";
                }
            } else {
                // This is the top group level so ending this tab and will start a new one.
                echo "</div>\n";
            }
            $gs_group_levels = substr($gs_group_levels, 0, -1); // remove last character
        }
    } // End this layout, there may be more in the case of history.
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
        } elseif ($data_type == 21) {
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
        } elseif ($data_type == 22) {
            // $_POST["$prefix$field_id"] is an array of text fields to be imploded
            // into "key:value|key:value|...".
            foreach ($_POST["$prefix$field_id"] as $key => $val) {
                $val = str_replace('|', ' ', $val);
                if (strlen($value)) {
                    $value .= '|';
                }

                $value .= "$key:$val";
            }
        } elseif ($data_type == 23) {
            // $_POST["$prefix$field_id"] is an array of text fields with companion
            // radio buttons to be imploded into "key:n:notes|key:n:notes|...".
            foreach ($_POST["$prefix$field_id"] as $key => $val) {
                $restype = $_POST["radio_{$field_id}"][$key] ?? null;
                if (empty($restype)) {
                    $restype = '0';
                }

                $val = str_replace('|', ' ', $val);
                if (strlen($value)) {
                    $value .= '|';
                }

                $value .= "$key:$restype:$val";
            }
        } elseif ($data_type == 25) {
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
        } elseif ($data_type == 28 || $data_type == 32) {
            // $_POST["$prefix$field_id"] is an date text fields with companion
            // radio buttons to be imploded into "notes|type|date".
            $restype = $_POST["radio_{$field_id}"] ?? '';
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
        } elseif ($data_type == 37) {
            // $_POST["form_$field_id"] is an array of arrays of 3 text fields with companion
            // radio button set to be encoded as json.
            $tmparr = array();
            foreach ($_POST["form_$field_id"] as $key => $valarr) {
                // Each $key here is a list item ID. $valarr has 3 text field values keyed on 0, 2 and 3.
                $tmparr[$key][0] = $valarr['0'];
                $tmparr[$key][1] = $_POST["radio_{$field_id}"][$key];
                $tmparr[$key][2] = $valarr['2'];
                $tmparr[$key][3] = $valarr['3'];
            }
            $value .= json_encode($tmparr);
        } elseif ($data_type == 36 || $data_type == 44 || $data_type == 45 || $data_type == 33) {
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
        } elseif ($data_type == 46) {
            $reslist = trim($_POST["$prefix$field_id"]);
            if (preg_match('/^comment_/', $reslist)) {
                $res_comment = str_replace('|', ' ', $_POST["{$prefix}text_$field_id"]);
                $value = $reslist . "|" . $res_comment;
            } else {
                $value = $_POST["$prefix$field_id"];
            }
        } elseif ($data_type == 52) {
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
    if ($maxlength && $maxlength != 0 && mb_strlen(trim($value)) > $maxlength && !$frow['list_id']) {
        die(htmlspecialchars(xl('ERROR: Field') . " '$field_id' " . xl('is too long'), ENT_NOQUOTES) .
        ":<br />&nbsp;<br />" . htmlspecialchars($value, ENT_NOQUOTES));
    }

    if (is_string($value)) {
        return trim($value);
    } else {
        return $value;
    }
}

// Generate JavaScript validation logic for the required fields.
//
function generate_layout_validation($form_id)
{
    if ('HIS' == $form_id) {
        $form_id .= '%'; // TBD: DEM also?
    }
    $pres = sqlStatement(
        "SELECT grp_form_id, grp_seq, grp_title " .
        "FROM layout_group_properties " .
        "WHERE grp_form_id LIKE ? AND grp_group_id = '' " .
        "ORDER BY grp_seq, grp_title, grp_form_id",
        array("$form_id")
    );
    while ($prow = sqlFetchArray($pres)) {
        $form_id = $prow['grp_form_id'];

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

            $fldname   = attr("form_$field_id");

            if ($data_type == 40) {
                $fldid = "form_" . $field_id;
                // Move canvas image data to its hidden form field so the server will get it.
                echo
                " var canfld = f[" . js_escape($fldid) . "];\n" .
                " if (canfld) canfld.value = lbfCanvasGetData(" . js_escape($fldid) . ");\n";
                continue;
            }
            if ($data_type == 41 || $data_type == 42) {
                $fldid = "form_" . $field_id;
                // Move canvas image data to its hidden form field so the server will get it.
                echo " lbfSetSignature(" . js_escape($fldid) . ");\n";
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
                    "   alert(" . xlj('Please choose a value for') . " + " .
                    "\":\\n\" + " . js_escape(xl_layout_label($fldtitle)) . ");\n" .
                    "   if (f.$fldname.focus) f.$fldname.focus();\n" .
                    "   return false;\n" .
                    "  }\n";
                    break;
                case 33:
                    echo
                    " if (f.$fldname.selectedIndex <= 0) {\n" .
                    "  if (f.$fldname.focus) f.$fldname.focus();\n" .
                    "  		errMsgs[errMsgs.length] = " . js_escape(xl_layout_label($fldtitle)) . "; \n" .
                    " }\n";
                    break;
                case 27: // radio buttons
                    echo
                    " var i = 0;\n" .
                    " for (; i < f.$fldname.length; ++i) if (f.{$fldname}[i].checked) break;\n" .
                    " if (i >= f.$fldname.length) {\n" .
                    "   alert(" . xlj('Please choose a value for') . " + " .
                    "\":\\n\" + " . js_escape(xl_layout_label($fldtitle)) . ");\n" .
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
                    "  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','var(--danger)'); } ); " .
                    "  		$('#" . $fldname . "').attr('style','background: var(--danger)'); \n" .
                    "  		errMsgs[errMsgs.length] = " . js_escape(xl_layout_label($fldtitle)) . "; \n" .
                    " } else { " .
                    " 		$('#" . $fldname . "').attr('style',''); " .
                    "  		$('#" . $fldname . "').parents('div.tab').each( function(){ var tabHeader = $('#header_' + $(this).attr('id') ); tabHeader.css('color','');  } ); " .
                    " } \n";
                    break;
                case 36: // multi select
                    echo
                    " var multi_select=f['$fldname" . "[]']; \n " .
                    " var multi_choice_made=false; \n" .
                    " for (var options_index=0; options_index < multi_select.length; options_index++) { " .
                        " multi_choice_made=multi_choice_made || multi_select.options[options_index].selected; \n" .
                    "    } \n" .
                    " if(!multi_choice_made)
                errMsgs[errMsgs.length] = " . js_escape(xl_layout_label($fldtitle)) . "; \n" .
                    "";
                    break;
            }
            echo " }\n";
        }
    } // End this layout, there may be more in the case of history.
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
    $onchange = '',
    $multiple = false,
    $class = ''
) {
    global $facilityService;

    $have_selected = false;
    $fres = $facilityService->getAllFacility();
    $id = $name;

    if ($multiple) {
        $name = $name . "[]";
    }
    echo "   <select class='form-control$class";
    if ($multiple) {
        echo " select-dropdown";
    }
    echo "' name='" . attr($name) . "' id='" . attr($id) . "'";
    if ($onchange) {
        echo " onchange='$onchange'";
    }

    if ($multiple) {
        echo " multiple='multiple'";
    }

    echo " $disabled>\n";

    if ($allow_allfacilities) {
        $option_value = '';
        $option_selected_attr = '';
        if ($selected == '') {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = '-- ' . xl('All Facilities') . ' --';
        echo "    <option value='" . attr($option_value) . "' $option_selected_attr>" . text($option_content) . "</option>\n";
    } elseif ($allow_unspecified) {
        $option_value = '0';
        $option_selected_attr = '';
        if ($selected == '0') {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = '-- ' . xl('Unspecified') . ' --';
        echo "    <option value='" . attr($option_value) . "' $option_selected_attr>" . text($option_content) . "</option>\n";
    }

    foreach ($fres as $frow) {
        $facility_id = $frow['id'];
        $option_value = $facility_id;
        $option_selected_attr = '';
        if ($multiple) {
            $selectedValues = explode("|", $selected);

            if (in_array($facility_id, $selectedValues)) {
                $option_selected_attr = ' selected="selected"';
                $have_selected = true;
            }
        } else {
            if ($selected == $facility_id) {
                $option_selected_attr = ' selected="selected"';
                $have_selected = true;
            }
        }

        $option_content = $frow['name'];
        echo "    <option value='" . attr($option_value) . "' $option_selected_attr>" . text($option_content) . "</option>\n";
    }

    if ($allow_unspecified && $allow_allfacilities) {
        $option_value = '0';
        $option_selected_attr = '';
        if ($selected == '0') {
            $option_selected_attr = ' selected="selected"';
            $have_selected = true;
        }

        $option_content = '-- ' . xl('Unspecified') . ' --';
        echo "    <option value='" . attr($option_value) . "' $option_selected_attr>" . text($option_content) . "</option>\n";
    }

    if (!$have_selected && !$multiple) {
        $option_value = $selected;
        $option_label = '(' . xl('Do not change') . ')';
        $option_content = xl('Missing or Invalid');
        echo "    <option value='" . attr($option_value) . "' label='" . attr($option_label) . "' selected='selected'>" . text($option_content) . "</option>\n";
    }

    echo "   </select>\n";
}

/**
 * Expand Collapse Widget
 * This forms the header and functionality component of the widget. The information that is displayed
 * then follows this function followed by a closing div tag
 *
 * @var $title is the title of the section (already translated)
 * @var $label is identifier used in the tag id's and sql columns
 * @var $buttonLabel is the button label text (already translated)
 * @var $buttonLink is the button link information
 * @var $buttonClass is any additional needed class elements for the button tag
 * @var $linkMethod is the button link method ('javascript' vs 'html')
 * @var $bodyClass is to set class(es) of the body
 * @var $auth is a flag to decide whether to show the button
 * @var $fixedWidth is to flag whether width is fixed
 * @var $forceExpandAlways is a flag to force the widget to always be expanded
 *
 * @todo Convert to a modern layout
 */
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
            $class_string = "btn btn-primary btn-sm " . $buttonClass;
        } else {
            $class_string = "btn btn-primary btn-sm";
        }

        // next, create the link
        if ($linkMethod == "javascript") {
            echo "<td><a class='" . attr($class_string) . "' href='javascript:;' onclick='" . $buttonLink . "'";
        } else {
            echo "<td><a class='" . attr($class_string) . "' href='" . $buttonLink . "'";
            if (!isset($_SESSION['patient_portal_onsite_two'])) {
                // prevent an error from occuring when calling the function from the patient portal
                echo " onclick='top.restoreSession()'";
            }
        }

        echo "><span>" .
            text($buttonLabel) . "</span></a></td>";
    }

    if ($forceExpandAlways) {
        // Special case to force the widget to always be expanded
        echo "<td><span class='text font-weight-bold'>" . text($title) . "</span>";
        $indicatorTag = "style='display: none'";
    }

    $indicatorTag = isset($indicatorTag) ?  $indicatorTag : "";
    echo "<td><a " . $indicatorTag . " href='javascript:;' class='small' onclick='toggleIndicator(this," .
        attr_js($label . "_ps_expand") . ")'><span class='text font-weight-bold'>";
    echo text($title) . "</span>";

    if (isset($_SESSION['patient_portal_onsite_two'])) {
        // collapse all entries in the patient portal
        $text = xl('expand');
    } elseif (getUserSetting($label . "_ps_expand")) {
        $text = xl('collapse');
    } else {
        $text = xl('expand');
    }

    echo " (<span class='indicator'>" . text($text) .
    "</span>)</a></td>";
    echo "</tr></table>";
    echo "</div>";
    if ($forceExpandAlways) {
        // Special case to force the widget to always be expanded
        $styling = "";
    } elseif (isset($_SESSION['patient_portal_onsite_two'])) {
        // collapse all entries in the patient portal
        $styling = "style='display: none'";
    } elseif (getUserSetting($label . "_ps_expand")) {
        $styling = "";
    } else {
        $styling = "style='display: none'";
    }

    if ($bodyClass) {
        $styling .= " class='" . attr($bodyClass) . "'";
    }

    //next, create the first div tag to hold the information
    // note the code that calls this function will then place the ending div tag after the data
    echo "<div id='" . attr($label) . "_ps_expand' " . $styling . ">";
}

//billing_facility fuction will give the dropdown list which contain billing faciliies.
function billing_facility($name, $select)
{
    global $facilityService;

    $fres = $facilityService->getAllBillingLocations();
        echo "   <select id='" . htmlspecialchars($name, ENT_QUOTES) . "' class='form-control' name='" . htmlspecialchars($name, ENT_QUOTES) . "'>";
    foreach ($fres as $facrow) {
            $selected = ( $facrow['id'] == $select ) ? 'selected="selected"' : '' ;
             echo "<option value=" . htmlspecialchars($facrow['id'], ENT_QUOTES) . " $selected>" . htmlspecialchars($facrow['name'], ENT_QUOTES) . "</option>";
    }

              echo "</select>";
}

// Generic function to get the translated title value for a particular list option.
//
function getListItemTitle($list, $option)
{
    return LayoutsUtils::getListItemTitle($list, $option);
}

//function to get the translated title value in Patient Transactions
function getLayoutTitle($list, $option)
{
    $row = sqlQuery("SELECT grp_title FROM layout_group_properties " .
    "WHERE grp_mapping = ? AND grp_form_id = ? ", array($list, $option));

    if (empty($row['grp_title'])) {
        return $option;
    }
    return xl_list_label($row['grp_title']);
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
        } elseif (strpos($field_id, 'em_') === 0) {
            $field_id = substr($field_id, 3);
            $table = 'employer_data';
            $orderby = 'ORDER BY date DESC LIMIT 1';
        } else {
            $table = 'patient_data';
            $orderby = '';
        }

        // It is an error if the field does not exist, but don't crash.
        $tmp = sqlQuery("SHOW COLUMNS FROM " . escape_table_name($table) . " WHERE Field = ?", array($field_id));
        if (empty($tmp)) {
            return '*?*';
        }

        $pdrow = sqlQuery("SELECT `$field_id` AS field_value FROM " . escape_table_name($table) . " WHERE pid = ? $orderby", array($pid));
        if (isset($pdrow)) {
            $currvalue = $pdrow['field_value'];
        }
    } elseif ($source == 'E') {
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
        } elseif ($formid) {
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
    } elseif ($source == 'V') {
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
        } elseif ($formid) {
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
    } elseif ($formid) {
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

function signer_head()
{
    return <<<EOD
<link href="{$GLOBALS['web_root']}/portal/sign/css/signer_modal.css?v={$GLOBALS['v_js_includes']}" rel="stylesheet"/>
<script src="{$GLOBALS['web_root']}/portal/sign/assets/signature_pad.umd.js?v={$GLOBALS['v_js_includes']}"></script>
<script src="{$GLOBALS['web_root']}/portal/sign/assets/signer_api.js?v={$GLOBALS['v_js_includes']}"></script>
EOD;
}

// This returns stuff that needs to go into the <head> section of a caller using
// the drawable image field type in a form.
// A TRUE argument makes the widget controls smaller.
//
function lbf_canvas_head($small = true)
{
    $s = <<<EOD
<link  href="{$GLOBALS['assets_static_relative']}/literallycanvas/css/literallycanvas.css" rel="stylesheet" />
<script src="{$GLOBALS['assets_static_relative']}/react/build/react-with-addons.min.js"></script>
<script src="{$GLOBALS['assets_static_relative']}/react/build/react-dom.min.js"></script>
<script src="{$GLOBALS['assets_static_relative']}/literallycanvas/js/literallycanvas.min.js"></script>
EOD;
    if ($small) {
        $s .= <<<EOD
<style>
/* Custom LiterallyCanvas styling.
 * This makes the widget 25% less tall and adjusts some other things accordingly.
 */
.literally {
  min-height: 100%;
  min-width: 300px;        /* Was 400, unspecified */
}
.literally .lc-picker .toolbar-button {
  width: 20px;
  height: 20px;
  line-height: 20px; /* Was 26, 26, 26 */
}
.literally .color-well {
  font-size: 8px;
  width: 49px; /* Was 10, 60 */
}
.literally .color-well-color-container {
  width: 21px;
  height: 21px; /* Was 28, 28 */
}
.literally .lc-picker {
  width: 50px; /* Was 61 */
}
.literally .lc-drawing.with-gui {
  left: 50px;                               /* Was 61 */
}
.literally .lc-options {
  left: 50px;                               /* Was 61 */
}
.literally .color-picker-popup {
  left: 49px;
  bottom: 0px;                   /* Was 60, 31 */
}
</style>
EOD;
    }

    return $s;
}

/**
 *  Test if modifier($test) is in array of options for data type.
 * @deprecated use LayoutsUtils::isOption
 * @param json array $options ["G","P","T"], ["G"] or could be legacy string with form "GPT", "G", "012"
 * @param string $test
 * @return boolean
 */
function isOption($options, string $test): bool
{
    return LayoutsUtils::isOption($options, $test);
}
