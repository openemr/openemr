<?php

/**
 * Document Template Download Module.
 *
 * Copyright (C) 2013-2014 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @author  Ruth Moulton
 * @link    http://www.open-emr.org
 */

// This module downloads a specified document template to the browser after
// substituting relevant patient data into its variables.
$is_module = $_POST['isModule'] ?? 0;
if ($is_module) {
    require_once(dirname(__file__) . '/../../interface/globals.php');
} else {
    require_once(dirname(__file__) . "/../verify_session.php");
    // ensure patient is bootstrapped (if sent)
    if (!empty($_POST['pid'])) {
        if ($_POST['pid'] != $_SESSION['pid']) {
            echo xlt("illegal Action");
            OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
            exit;
        }
    }
}

use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$templateService = new DocumentTemplateService();

require_once($GLOBALS['srcdir'] . '/appointments.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');

$form_id = $_POST['template_id'] ?? null;
$pid = $_POST['pid'] ?? 0;
$user = $_SESSION['authUserID'] ?? $_SESSION['sessionUser'];

$nextLocation = 0; // offset to resume scanning
$keyLocation = false; // offset of a potential {string} to replace
$keyLength = 0; // length of {string} to replace
$groupLevel = 0; // 0 if not in a {GRP} section
$groupCount = 0; // 0 if no items in the group yet
$itemSeparator = '; '; // separator between group items
$tcnt = $grcnt = $ckcnt = 0;
$html_flag = false;

// Flags to ignore new lines

// Check if the current location has the specified {string}.
function keySearch(&$s, $key)
{
    global $keyLocation, $keyLength;
    $keyLength = strlen($key);
    if ($keyLength == 0) {
        return false;
    }

    return $key == substr($s, $keyLocation, $keyLength);
}

// Replace the {string} at the current location with the specified data.
// Also update the location to resume scanning accordingly.
function keyReplace(&$s, $data)
{
    global $keyLocation, $keyLength, $nextLocation;
    $nextLocation = $keyLocation + strlen($data);
    return substr($s, 0, $keyLocation) . $data . substr($s, $keyLocation + $keyLength);
}

// Do some final processing of field data before it's put into the document.
function dataFixup($data, $title = '')
{
    global $groupLevel, $groupCount, $itemSeparator;
    if ($data !== '') {
        // Replace some characters that can mess up XML without assuming XML content type.
        $data = str_replace('&', '[and]', $data);
        $data = str_replace('<', '[less]', $data);
        $data = str_replace('>', '[greater]', $data);
        // If in a group, include labels and separators.
        if ($groupLevel) {
            if ($title !== '') {
                $data = $title . ': ' . $data;
            }

            if ($groupCount) {
                $data = $itemSeparator . $data;
            }

            ++$groupCount;
        }
    }

    return $data;
}

// Return a string naming all issues for the specified patient and issue type.
function getIssues($type)
{
    // global $itemSeparator;
    $tmp = '';
    $lres = sqlStatement("SELECT title, comments FROM lists WHERE " . "pid = ? AND type = ? AND enddate IS NULL " . "ORDER BY begdate", array(
        $GLOBALS['pid'],
        $type
    ));
    while ($lrow = sqlFetchArray($lres)) {
        if ($tmp) {
            $tmp .= '; ';
        }

        $tmp .= $lrow['title'];
        if ($lrow['comments']) {
            $tmp .= ' (' . $lrow['comments'] . ')';
        }
    }

    return $tmp;
}

// Top level function for scanning and replacement of a file's contents.
function doSubs($s)
{
    global $ptrow, $hisrow, $enrow, $nextLocation, $keyLocation, $keyLength;
    global $groupLevel, $groupCount, $itemSeparator, $pid, $user, $encounter;
    global $tcnt, $grcnt, $ckcnt, $is_module;
    global $html_flag;
    $nextLocation = 0;
    $groupLevel = 0;
    $groupCount = 0;

    while (($keyLocation = strpos($s, '{', $nextLocation)) !== false) {
        $nextLocation = $keyLocation + 1;

        if (keySearch($s, '{PatientSignature}')) {
            $sigfld = '<script>page.presentPatientSignature=true;</script><span>';
            $sigfld .= '<img class="signature" id="patientSignature" style="cursor:pointer;color:red;height:65px !important;width:auto;" data-type="patient-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$pid) . '" data-user="' . attr($user) . '" src="">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{AdminSignature}')) {
            $sigfld = '<script>page.presentAdminSignature=true;</script><span>';
            $sigfld .= '<img class="signature" id="adminSignature" style="cursor:pointer;color:red;height:65px !important;width:auto;" data-type="admin-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$pid) . '" data-user="' . attr($user) . '" src="">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{WitnessSignature}')) {
            $sigfld = '<script>page.presentWitnessSignature=true;</script><span>';
            $sigfld .= '<img class="signature" id="witnessSignature" style="cursor:pointer;color:red;height:65px !important;width:auto;" data-type="witness-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$pid) . '" data-user="' . attr((int)$user) . '" src="">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{SignaturesRequired}')) {
            $sigfld = '<script>page.signaturesRequired=true;var signMsg=' . xlj("A signature is required for this document. Please sign document where required") . ';</script>' . "\n";
            $s = keyReplace($s, $sigfld);
        } elseif (preg_match('/^{(AcknowledgePdf):(.*):(.*)}/', substr($s, $keyLocation), $matches)) {
            global $templateService;
            $keyLength = strlen($matches[0]);
            $formname = $matches[2];
            $form_id = null;
            if (is_numeric($formname)) {
                $form_id = $formname;
                $formname = '';
            }
            $formtitle = text($formname . ' ' . $matches[3]);
            $content = $templateService->fetchTemplate($form_id, $formname)['template_content'];
            $content = 'data:application/pdf;base64,' . base64_encode($content);
            $sigfld = '<script>page.pdfFormName=' . js_escape($formname) . '</script>';
            $sigfld .= "<div class='d-none' id='showPdf'>\n";
            $sigfld .= "<object data='$content' type='application/pdf' width='100%' height='675em'></object>\n";
            $sigfld .= '</div>';
            $sigfld .= "<a class='btn btn-link' id='pdfView' onclick='" . 'document.getElementById("showPdf").classList.toggle("d-none")' . "'>" . $formtitle . "</a>";
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{ParseAsHTML}')) {
            $html_flag = true;
            $s = keyReplace($s, "");
        } elseif (keySearch($s, '{ParseAsText}')) {
            $html_flag = false;
            $s = keyReplace($s, '');
        } elseif (preg_match('/^\{(EncounterForm):(\w+)\}/', substr($s, $keyLocation), $matches)) {
            $formname = $matches[2];
            $keyLength = strlen($matches[0]);
            $sigfld = "<script>page.isFrameForm=1;page.lbfFormName=" . js_escape($formname) . "</script>";
            $sigfld .= "<iframe id='lbfForm' class='lbfFrame' style='height:100vh;width:100%;border:0;'></iframe>";
            $s = keyReplace($s, $sigfld);
        } elseif (preg_match('/^\{(TextBox):([0-9][0-9])x([0-9][0-9][0-9])\}/', substr($s, $keyLocation), $matches)) {
            $rows = $matches[2];
            $cols = $matches[3];
            $keyLength = strlen($matches[0]);
            $sigfld = '<span>';
            $sigfld .= '<textarea class="templateInput" rows="' . attr($rows) . '" cols="' . attr($cols) . '" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);"></textarea>';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{TextBox}')) { // legacy 03by040
            $sigfld = '<span>';
            $sigfld .= '<textarea class="templateInput" rows="3" cols="40" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);"></textarea>';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{TextInput}')) {
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{smTextInput}')) {
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;max-width:50px;" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (preg_match('/^\{(sizedTextInput):(\w+)\}/', substr($s, $keyLocation), $matches)) {
            $len = $matches[2];
            $keyLength = strlen($matches[0]);
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;min-width:' . $len . ';" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{StandardDatePicker}')) {
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput" type="date" maxlength="10" size="10" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{DatePicker}')) {
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput datepicker" type="text" maxlength="10" size="10" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{DateTimePicker}')) {
            $sigfld = '<span>';
            $sigfld .= '<input class="templateInput datetimepicker" type="text" maxlength="18" size="18" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{CheckMark}')) {
            $ckcnt++;
            $sigfld = '<span class="checkMark" data-id="check' . $ckcnt . '">';
            $sigfld .= '<input type="checkbox"  id="check' . $ckcnt . '" data-value="" onclick="templateCheckMark(this);">';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{ynRadioGroup}')) {
            $grcnt++;
            $sigfld = '<span class="ynuGroup" data-value="false" data-id="' . $grcnt . '" id="rgrp' . $grcnt . '">';
            $sigfld .= '<label class="ml-1 mr-2"><input class="mr-1" onclick="templateRadio(this)" type="radio" name="ynradio' . $grcnt . '" data-id="' . $grcnt . '" value="Yes">' . xlt("Yes") . '</label>';
            $sigfld .= '<label><input class="mr-1" onclick="templateRadio(this)" type="radio" name="ynradio' . $grcnt . '" data-id="' . $grcnt . '" value="No">' . xlt("No") . '</label>';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{TrueFalseRadioGroup}')) {
            $grcnt++;
            $sigfld = '<span class="tfuGroup" data-value="False" data-id="' . $grcnt . '" id="tfrgrp' . $grcnt . '">';
            $sigfld .= '<label class="ml-1 mr-2"><input class="mr-1" onclick="tfTemplateRadio(this)" type="radio" name="tfradio' . $grcnt . '" data-id="' . $grcnt . '" value="True">' . xlt("True") . '</label>';
            $sigfld .= '<label><input class="mr-1" onclick="tfTemplateRadio(this)" type="radio" name="tfradio' . $grcnt . '" data-id="' . $grcnt . '" value="False">' . xlt("False") . '</label>';
            $sigfld .= '</span>';
            $s = keyReplace($s, $sigfld);
        } elseif (keySearch($s, '{PatientName}')) {
            $tmp = $ptrow['fname'];
            if ($ptrow['mname']) {
                if ($tmp) {
                    $tmp .= ' ';
                }
                $tmp .= $ptrow['mname'];
            }
            if ($ptrow['lname']) {
                if ($tmp) {
                    $tmp .= ' ';
                }
                $tmp .= $ptrow['lname'];
            }
            $s = keyReplace($s, dataFixup($tmp, xl('Name')));
        } elseif (keySearch($s, '{PatientID}')) {
            $s = keyReplace($s, dataFixup($ptrow['pubpid'], xl('Chart ID')));
        } elseif (keySearch($s, '{Address}')) {
            $s = keyReplace($s, dataFixup($ptrow['street'], xl('Street')));
        } elseif (keySearch($s, '{City}')) {
            $s = keyReplace($s, dataFixup($ptrow['city'], xl('City')));
        } elseif (keySearch($s, '{State}')) {
            $s = keyReplace($s, dataFixup(getListItemTitle('state', $ptrow['state']), xl('State')));
        } elseif (keySearch($s, '{Zip}')) {
            $s = keyReplace($s, dataFixup($ptrow['postal_code'], xl('Postal Code')));
        } elseif (keySearch($s, '{PatientPhone}')) {
            $ptphone = $ptrow['phone_contact'];
            if (empty($ptphone)) {
                $ptphone = $ptrow['phone_home'];
            }
            if (empty($ptphone)) {
                $ptphone = $ptrow['phone_cell'];
            }
            if (empty($ptphone)) {
                $ptphone = $ptrow['phone_biz'];
            }
            if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp)) {
                $ptphone = '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3];
            }
            $s = keyReplace($s, dataFixup($ptphone, xl('Phone')));
        } elseif (keySearch($s, '{PatientDOB}')) {
            $s = keyReplace($s, dataFixup(oeFormatShortDate($ptrow['DOB']), xl('Birth Date')));
        } elseif (keySearch($s, '{PatientSex}')) {
            $s = keyReplace($s, dataFixup(getListItemTitle('sex', $ptrow['sex']), xl('Sex')));
        } elseif (keySearch($s, '{DOS}')) {
            // $s = @keyReplace($s, dataFixup(oeFormatShortDate(substr($enrow['date'], 0, 10)), xl('Service Date'))); // changed DOS to todays date- add future enc DOS
            $s = @keyReplace($s, dataFixup(oeFormatShortDate(substr(date("Y-m-d"), 0, 10)), xl('Service Date')));
        } elseif (keySearch($s, '{ChiefComplaint}')) {
            $cc = $enrow['reason'];
            $patientid = $ptrow['pid'];
            $DOS = substr($enrow['date'], 0, 10);
            // Prefer appointment comment if one is present.
            $evlist = fetchEvents($DOS, $DOS, " AND pc_pid = ? ", null, false, 0, array($patientid));
            foreach ($evlist as $tmp) {
                if ($tmp['pc_pid'] == $pid && !empty($tmp['pc_hometext'])) {
                    $cc = $tmp['pc_hometext'];
                }
            }
            $s = keyReplace($s, dataFixup($cc, xl('Chief Complaint')));
        } elseif (keySearch($s, '{ReferringDOC}')) {
            $tmp = empty($ptrow['ur_fname']) ? '' : $ptrow['ur_fname'];
            if (!empty($ptrow['ur_mname'])) {
                if ($tmp) {
                    $tmp .= ' ';
                }
                $tmp .= $ptrow['ur_mname'];
            }
            if (!empty($ptrow['ur_lname'])) {
                if ($tmp) {
                    $tmp .= ' ';
                }
                $tmp .= $ptrow['ur_lname'];
            }
            $s = keyReplace($s, dataFixup($tmp, xl('Referer')));
        } elseif (keySearch($s, '{Allergies}')) {
            $tmp = generate_plaintext_field(array(
                'data_type' => '24',
                'list_id' => ''
            ), '');
            $s = keyReplace($s, dataFixup($tmp, xl('Allergies')));
        } elseif (keySearch($s, '{Medications}')) {
            $s = keyReplace($s, dataFixup(getIssues('medication'), xl('Medications')));
        } elseif (keySearch($s, '{ProblemList}')) {
            $s = keyReplace($s, dataFixup(getIssues('medical_problem'), xl('Problem List')));
        } elseif (keySearch($s, '{GRP}')) { // This tag indicates the fields from here until {/GRP} are a group of fields
            // separated by semicolons. Fields with no data are omitted, and fields with
            // data are prepended with their field label from the form layout.
            ++$groupLevel;
            $groupCount = 0;
            $s = keyReplace($s, '');
        } elseif (keySearch($s, '{/GRP}')) {
            if ($groupLevel > 0) {
                --$groupLevel;
            }
            $s = keyReplace($s, '');
        } elseif (preg_match('/^\{ITEMSEP\}(.*?)\{\/ITEMSEP\}/', substr($s, $keyLocation), $matches)) {
            // This is how we specify the separator between group items in a way that
            // is independent of the document format. Whatever is between {ITEMSEP} and
            // {/ITEMSEP} is the separator string. Default is "; ".
            $itemSeparator = $matches[1];
            $keyLength = strlen($matches[0]);
            $s = keyReplace($s, '');
        } elseif (preg_match('/^\{(LBF\w+):(\w+)\}/', substr($s, $keyLocation), $matches)) {
            // This handles keys like {LBFxxx:fieldid} for layout-based encounter forms.
            $formname = $matches[1];
            $fieldid = $matches[2];
            $keyLength = 3 + strlen($formname) + strlen($fieldid);
            $data = '';
            $currvalue = '';
            $title = '';
            $frow = sqlQuery("SELECT * FROM layout_options " . "WHERE form_id = ? AND field_id = ? LIMIT 1", array(
                $formname,
                $fieldid
            ));
            if (!empty($frow)) {
                $ldrow = sqlQuery("SELECT ld.field_value " . "FROM lbf_data AS ld, forms AS f WHERE " . "f.pid = ? AND f.encounter = ? AND f.formdir = ? AND f.deleted = 0 AND " . "ld.form_id = f.form_id AND ld.field_id = ? " . "ORDER BY f.form_id DESC LIMIT 1", array(
                    $pid,
                    $encounter,
                    $formname,
                    $fieldid
                ));
                if (!empty($ldrow)) {
                    $currvalue = $ldrow['field_value'];
                    $title = $frow['title'];
                }
                if ($currvalue !== '') {
                    $data = generate_plaintext_field($frow, $currvalue);
                }
            }
            $s = keyReplace($s, dataFixup($data, $title));
        } elseif (preg_match('/^\{(DEM|HIS):(\w+)\}/', substr($s, $keyLocation), $matches)) {
            // This handles keys like {DEM:fieldid} and {HIS:fieldid}.
            $formname = $matches[1];
            $fieldid = $matches[2];
            $keyLength = 3 + strlen($formname) + strlen($fieldid);
            $data = '';
            $currvalue = '';
            $title = '';
            $frow = sqlQuery("SELECT * FROM layout_options " . "WHERE form_id = ? AND field_id = ? LIMIT 1", array(
                $formname,
                $fieldid
            ));
            if (!empty($frow)) {
                $tmprow = $formname == 'DEM' ? $ptrow : $hisrow;
                if (isset($tmprow[$fieldid])) {
                    $currvalue = $tmprow[$fieldid];
                    $title = $frow['title'];
                }
                if ($currvalue !== '') {
                    $data = generate_plaintext_field($frow, $currvalue);
                }
            }
            $s = keyReplace($s, dataFixup($data, $title));
        } elseif (preg_match('/^{(CurrentDate):(.*?)}/', substr($s, $keyLocation), $matches)) {
            /* defaults to ISO standard date format yyyy-mm-dd
             * modified by string following ':' as follows
             * 'global' will use the global date format setting
             * 'YYYY-MM-DD', 'MM/DD/YYYY', 'DD/MM/YYYY' override the global setting
             * anything else is ignored
             *
             * oeFormatShortDate($date = 'today', $showYear = true) - OpenEMR function to format
             * date using global setting, defaults to ISO standard yyyy-mm-dd
            */
            $keyLength = strlen($matches[0]);
            $matched = $matches[0];
            $format = 'Y-m-d'; /* default yyyy-mm-dd */
            $currentdate = '';
            if (preg_match('/GLOBAL/i', $matched, $matches)) {
                /* use global setting */
                $currentdate = oeFormatShortDate(date('Y-m-d'), true);
            } elseif (
                /* there's an overiding format */
                preg_match('/YYYY-MM-DD/i', $matched, $matches)
            ) {
                /* nothing to do here as this is the default format */
            } elseif (preg_match('[MM/DD/YYYY]i', $matched, $matches)) {
                $format = 'm/d/Y';
            } elseif (preg_match('[DD/MM/YYYY]i', $matched, $matches)) {
                $format = 'd/m/Y';
            }

            if (!$currentdate) {
                $currentdate = date($format);  /* get the current date in specified format */
            }
            $s = keyReplace($s, dataFixup($currentdate, xl('Date')));
        } elseif (keySearch($s, '{CurrentTime}')) {
            $format = 'H:i';  /* 24 hour clock with leading zeros */
            $currenttime = date($format); /* format to hh:mm for local time zone */
            $s = keyReplace($s, dataFixup($currenttime, xl('Time')));
        }
    } // End if { character found.

    return $s;
}

// Get patient demographic info. pd.ref_providerID
$ptrow = sqlQuery("SELECT pd.*, " . "ur.fname AS ur_fname, ur.mname AS ur_mname, ur.lname AS ur_lname, ur.title AS ur_title, ur.specialty AS ur_specialty " . "FROM patient_data AS pd " . "LEFT JOIN users AS ur ON ur.id = ? " . "WHERE pd.pid = ?", array($user, $pid));

$hisrow = sqlQuery("SELECT * FROM history_data WHERE pid = ? " . "ORDER BY date DESC LIMIT 1", array(
    $pid
));

$enrow = array();

// Get some info for the currently selected encounter.
if ($encounter) {
    $enrow = sqlQuery("SELECT * FROM form_encounter WHERE pid = ? AND " . "encounter = ?", array(
        $pid,
        $encounter
    ));
}
$template = $templateService->fetchTemplate($form_id);
$edata = $template['template_content'];

// purify html (and remove js)
$edata = (new \HTMLPurifier(\HTMLPurifier_Config::createDefault()))->purify($edata);

// do the substitutions (ie. magic)
$edata = doSubs($edata);

if ($html_flag) { // return raw minified html template
    $html = trim(str_replace(["\r\n", "\r", "\n"], '', $edata));
} else { // add br for lf in text template
    $html = trim(str_replace(["\r\n", "\r", "\n"], '<br />', $edata));
}
echo $html;
