<?php

/**
 * Document Template Rendering class.
 * Originated from download_template.php
 * to substitute directives with html to create a document.
 * Greatly expanded from a core feature by Rod Roark for portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Ruth Moulton
 * Copyright (C) 2013-2014 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\DocumentTemplates;

use HTMLPurifier;
use HTMLPurifier_Config;

require_once($GLOBALS['srcdir'] . '/appointments.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');

class DocumentTemplateRender
{
    private $pid;
    private $user;
    private int $nextLocation = 0; // offset to resume scanning
    private $keyLocation = false; // offset of a potential {string} to replace
    private int $keyLength = 0; // length of {string} to replace
    private int $groupLevel = 0; // 0 if not in a {GRP} section
    private int $groupCount = 0; // 0 if no items in the group yet
    private string $itemSeparator = '; '; // separator between group items
    private $ptrow = null;
    private $enrow = null;
    private $hisrow = null;
    private int $grcnt = 0;
    private int $ckcnt = 0;
    private bool $html_flag = false;
    private mixed $encounter;
    private DocumentTemplateService $templateService;

    public function __construct($pid, $user)
    {
        $this->pid = $pid;
        $this->user = $user;
        $this->encounter = $GLOBALS['encounter'];
        $this->templateService = new DocumentTemplateService();
    }

    /**
     * Parse and render template directives.
     *
     * @param $template_id
     * @param $template_content
     * @return string
     */
    public function doRender($template_id, $template_content = null): string
    {
        // Get patient demographic info. pd.ref_providerID
        $this->ptrow = sqlQuery("SELECT pd.*, " . "ur.fname AS ur_fname, ur.mname AS ur_mname, ur.lname AS ur_lname, ur.title AS ur_title, ur.specialty AS ur_specialty " . "FROM patient_data AS pd " . "LEFT JOIN users AS ur ON ur.id = ? " . "WHERE pd.pid = ?", array($this->user, $this->pid));

        $this->hisrow = sqlQuery("SELECT * FROM history_data WHERE pid = ? " . "ORDER BY date DESC LIMIT 1", array(
            $this->pid
        ));

        $this->enrow = array();
        // Get some info for the currently selected encounter.
        if ($this->encounter) {
            $this->enrow = sqlQuery("SELECT * FROM form_encounter WHERE pid = ? AND " . "encounter = ?", array(
                $this->pid,
                $this->encounter
            ));
        }

        // From database
        if (!empty($template_id)) {
            $template = $this->templateService->fetchTemplate($template_id)['template_content'];
        } else {
            $template = $template_content;
        }
        // snatch style tag content to replace after content purified. Ho-hum!
        $style_flag = preg_match('#<\s*?style\b[^>]*>(.*?)</style\b[^>]*>#s', $template, $style_matches);
        $style = str_replace('<style type="text/css">', '<style>', $style_matches);
        // purify html (and remove js)
        $config = HTMLPurifier_Config::createDefault();
        $purify = new HTMLPurifier($config);
        $edata = $purify->purify($template);
        // insert style tag from raw template content
        if ($style_flag && !empty($style[0] ?? '')) {
            $edata = $style[0] . $edata;
        }
        // Purify escapes URIs.
        // Add back escaped directive delimiters so any directives in a URL will be parsed by our engine.
        $edata = str_replace('%7B', '{', $edata);
        $edata = str_replace('%7D', '}', $edata);
        // do the substitutions (ie. magic)
        $edata = $this->doSubs($edata);

        if ($this->html_flag) { // return raw minified html template
            $html = trim(str_replace(["\r\n", "\r", "\n"], '', $edata));
        } else { // add br for lf in text template
            $html = trim(str_replace(["\r\n", "\r", "\n"], '<br />', $edata));
        }

        return $html;
    }

    /**
     * Top level function for scanning and replacement of a file's contents.
     *
     * @param $s
     * @return mixed|string
     */
    private function doSubs($s): mixed
    {
        $this->nextLocation = 0;
        $this->groupLevel = 0;
        $this->groupCount = 0;

        while (($this->keyLocation = strpos($s, '{', $this->nextLocation)) !== false) {
            $this->nextLocation = $this->keyLocation + 1;

            if ($this->keySearch($s, '{PatientSignature}')) {
                $sigfld = '<script>page.presentPatientSignature=true;</script><span>';
                $sigfld .= '<img class="signature" id="patientSignature" style="cursor:pointer;color: red;vertical-align: middle;max-height: 65px;height: 65px !important;width: auto !important;" data-type="patient-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$this->pid) . '" data-user="' . attr($this->user) . '" src="">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{AdminSignature}')) {
                $sigfld = '<script>page.presentAdminSignature=true;</script><span>';
                $sigfld .= '<img class="signature" id="adminSignature" style="cursor:pointer;color: red;vertical-align: middle;max-height: 65px;height: 65px !important;width: auto !important;" data-type="admin-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$this->pid) . '" data-user="' . attr($this->user) . '" src="">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{WitnessSignature}')) {
                $sigfld = '<script>page.presentWitnessSignature=true;</script><span>';
                $sigfld .= '<img class="signature" id="witnessSignature" style="cursor:pointer;color: red;vertical-align: middle;max-height: 65px;height: 65px !important;width: auto !important;" data-type="witness-signature" data-action="fetch_signature" alt="' . xla("Click in signature") . '" data-pid="' . attr((int)$this->pid) . '" data-user="' . attr((int)$this->user) . '" src="">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{SignaturesRequired}')) {
                $sigfld = '<script>page.signaturesRequired=true;var signMsg=' . xlj("A signature is required for this document. Please sign document where required") . ';</script>' . "\n";
                $s = $this->keyReplace($s, $sigfld);
            } elseif (preg_match('/^{(Questionnaire):(.*)}/', substr($s, $this->keyLocation), $matches)) {
                $q_id = $matches[2];
                $this->keyLength = strlen($matches[0]);
                $sigfld = "<script>page.isFrameForm=1;page.isQuestionnaire=1;page.encounterFormName=" . js_escape($q_id) . "</script>";
                $sigfld .= "<iframe id='encounterForm' class='questionnaires' style='height:100vh;width:100%;border:0;' src=''></iframe>";
                $s = $this->keyReplace($s, $sigfld);
            } elseif (preg_match('/^{(QuestionnaireURLLoinc)\|(.*)\|(.*)\|(.*)}/', substr($s, $this->keyLocation), $matches)) {
                // deprecated 09/23/2022 Unsure this directive is useful!
                $q_url = $matches[3];
                $form_id = $matches[4];
                $form_name = $matches[2];
                $this->keyLength = strlen($matches[0]);
                $src = './../questionnaire_template.php?isPortal=1&type=loinc_form&name=' . urlencode($form_name) . '&url=' . urlencode($q_url) . '&form_code=' . urlencode($form_id);
                $sigfld = "<script>page.isFrameForm=1;page.isQuestionnaire=1;page.encounterFormName=" . js_escape($q_id) . "</script>";
                $sigfld .= "<iframe id='encounterForm' class='questionnaires' style='height:100vh;width:100%;border:0;' src='" . attr($src) . "'></iframe>";
                $s = $this->keyReplace($s, $sigfld);
            } elseif (preg_match('/^{(AcknowledgePdf):(.*):(.*)}/', substr($s, $this->keyLocation), $matches)) {
                global $templateService;
                $this->keyLength = strlen($matches[0]);
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
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{ParseAsHTML}')) {
                $this->html_flag = true;
                $s = $this->keyReplace($s, "");
            } elseif ($this->keySearch($s, '{ParseAsText}')) {
                $this->html_flag = false;
                $s = $this->keyReplace($s, '');
            } elseif (preg_match('/^\{(EncounterForm):(\w+)\}/', substr($s, $this->keyLocation), $matches)) {
                $formname = $matches[2];
                $this->keyLength = strlen($matches[0]);
                $sigfld = "<script>page.isFrameForm=1;page.encounterFormName=" . js_escape($formname) . "</script>";
                $sigfld .= "<iframe id='encounterForm' class='lbfFrame' style='height:100vh;width:100%;border:0;'></iframe>";
                $s = $this->keyReplace($s, $sigfld);
            } elseif (preg_match('/^\{(TextBox):([0-9][0-9])x([0-9][0-9][0-9])\}/', substr($s, $this->keyLocation), $matches)) {
                $rows = $matches[2];
                $cols = $matches[3];
                $this->keyLength = strlen($matches[0]);
                $sigfld = '<span>';
                $sigfld .= '<textarea class="templateInput" rows="' . attr($rows) . '" cols="' . attr($cols) . '" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);"></textarea>';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{TextBox}')) { // legacy 03by040
                $sigfld = '<span>';
                $sigfld .= '<textarea class="templateInput" rows="3" cols="40" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);"></textarea>';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{TextInput}')) {
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{smTextInput}')) {
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;max-width:50px;" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif (preg_match('/^\{(sizedTextInput):(\w+)\}/', substr($s, $this->keyLocation), $matches)) {
                $len = $matches[2];
                $this->keyLength = strlen($matches[0]);
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput" type="text" style="margin:2px 2px;min-width:' . $len . ';" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{StandardDatePicker}')) {
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput" type="date" maxlength="10" size="10" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{DatePicker}')) {
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput datepicker" type="text" maxlength="10" size="10" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{DateTimePicker}')) {
                $sigfld = '<span>';
                $sigfld .= '<input class="templateInput datetimepicker" type="text" maxlength="18" size="18" style="margin:2px 2px;" data-textvalue="" onblur="templateText(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{CheckMark}')) {
                $this->ckcnt++;
                $sigfld = '<span class="checkMark" data-id="check' . $this->ckcnt . '">';
                $sigfld .= '<input type="checkbox"  id="check' . $this->ckcnt . '" data-value="" onclick="templateCheckMark(this);">';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{ynRadioGroup}')) {
                $this->grcnt++;
                $sigfld = '<span class="ynuGroup" data-value="false" data-id="' . $this->grcnt . '" id="rgrp' . $this->grcnt . '">';
                $sigfld .= '<label class="ml-1 mr-2"><input class="mr-1" onclick="templateRadio(this)" type="radio" name="ynradio' . $this->grcnt . '" data-id="' . $this->grcnt . '" value="Yes">' . xlt("Yes") . '</label>';
                $sigfld .= '<label><input class="mr-1" onclick="templateRadio(this)" type="radio" name="ynradio' . $this->grcnt . '" data-id="' . $this->grcnt . '" value="No">' . xlt("No") . '</label>';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{TrueFalseRadioGroup}')) {
                $this->grcnt++;
                $sigfld = '<span class="tfuGroup" data-value="False" data-id="' . $this->grcnt . '" id="tfrgrp' . $this->grcnt . '">';
                $sigfld .= '<label class="ml-1 mr-2"><input class="mr-1" onclick="tfTemplateRadio(this)" type="radio" name="tfradio' . $this->grcnt . '" data-id="' . $this->grcnt . '" value="True">' . xlt("True") . '</label>';
                $sigfld .= '<label><input class="mr-1" onclick="tfTemplateRadio(this)" type="radio" name="tfradio' . $this->grcnt . '" data-id="' . $this->grcnt . '" value="False">' . xlt("False") . '</label>';
                $sigfld .= '</span>';
                $s = $this->keyReplace($s, $sigfld);
            } elseif ($this->keySearch($s, '{PatientName}')) {
                $tmp = $this->ptrow['fname'];
                if ($this->ptrow['mname']) {
                    if ($tmp) {
                        $tmp .= ' ';
                    }
                    $tmp .= $this->ptrow['mname'];
                }
                if ($this->ptrow['lname']) {
                    if ($tmp) {
                        $tmp .= ' ';
                    }
                    $tmp .= $this->ptrow['lname'];
                }
                $s = $this->keyReplace($s, $this->dataFixup($tmp, xl('Name')));
            } elseif ($this->keySearch($s, '{PatientID}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->ptrow['pubpid'], xl('Chart ID')));
            } elseif ($this->keySearch($s, '{Address}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->ptrow['street'], xl('Street')));
            } elseif ($this->keySearch($s, '{City}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->ptrow['city'], xl('City')));
            } elseif ($this->keySearch($s, '{State}')) {
                $s = $this->keyReplace($s, $this->dataFixup(getListItemTitle('state', $this->ptrow['state']), xl('State')));
            } elseif ($this->keySearch($s, '{Zip}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->ptrow['postal_code'], xl('Postal Code')));
            } elseif ($this->keySearch($s, '{PatientPhone}')) {
                $ptphone = $this->ptrow['phone_contact'];
                if (empty($ptphone)) {
                    $ptphone = $this->ptrow['phone_home'];
                }
                if (empty($ptphone)) {
                    $ptphone = $this->ptrow['phone_cell'];
                }
                if (empty($ptphone)) {
                    $ptphone = $this->ptrow['phone_biz'];
                }
                if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/", $ptphone, $tmp)) {
                    $ptphone = '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3];
                }
                $s = $this->keyReplace($s, $this->dataFixup($ptphone, xl('Phone')));
            } elseif ($this->keySearch($s, '{PatientDOB}')) {
                $s = $this->keyReplace($s, $this->dataFixup(oeFormatShortDate($this->ptrow['DOB']), xl('Birth Date')));
            } elseif ($this->keySearch($s, '{PatientSex}')) {
                $s = $this->keyReplace($s, $this->dataFixup(getListItemTitle('sex', $this->ptrow['sex']), xl('Sex')));
            } elseif ($this->keySearch($s, '{DOS}')) {
                // $s = @$this->keyReplace($s, $this->dataFixup(oeFormatShortDate(substr($this->enrow['date'], 0, 10)), xl('Service Date')));     // changed DOS to todays date- add future enc DOS
                $s = @$this->keyReplace($s, $this->dataFixup(oeFormatShortDate(substr(date("Y-m-d"), 0, 10)), xl('Service Date')));
            } elseif ($this->keySearch($s, '{ChiefComplaint}')) {
                $cc = $this->enrow['reason'];
                $patientid = $this->ptrow['pid'];
                $DOS = substr($this->enrow['date'], 0, 10);
                // Prefer appointment comment if one is present.
                $evlist = fetchEvents($DOS, $DOS, " AND pc_pid = ? ", null, false, 0, array($patientid));
                foreach ($evlist as $tmp) {
                    if ($tmp['pc_pid'] == $this->pid && !empty($tmp['pc_hometext'])) {
                        $cc = $tmp['pc_hometext'];
                    }
                }
                $s = $this->keyReplace($s, $this->dataFixup($cc, xl('Chief Complaint')));
            } elseif ($this->keySearch($s, '{ReferringDOC}')) {
                $tmp = empty($this->ptrow['ur_fname']) ? '' : $this->ptrow['ur_fname'];
                if (!empty($this->ptrow['ur_mname'])) {
                    if ($tmp) {
                        $tmp .= ' ';
                    }
                    $tmp .= $this->ptrow['ur_mname'];
                }
                if (!empty($this->ptrow['ur_lname'])) {
                    if ($tmp) {
                        $tmp .= ' ';
                    }
                    $tmp .= $this->ptrow['ur_lname'];
                }
                $s = $this->keyReplace($s, $this->dataFixup($tmp, xl('Referer')));
            } elseif ($this->keySearch($s, '{Allergies}')) {
                $tmp = generate_plaintext_field(array(
                    'data_type' => '24',
                    'list_id' => ''
                ), '');
                $s = $this->keyReplace($s, $this->dataFixup($tmp, xl('Allergies')));
            } elseif ($this->keySearch($s, '{Medications}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->getIssues('medication'), xl('Medications')));
            } elseif ($this->keySearch($s, '{ProblemList}')) {
                $s = $this->keyReplace($s, $this->dataFixup($this->getIssues('medical_problem'), xl('Problem List')));
            } elseif ($this->keySearch($s, '{GRP}')) {     // This tag indicates the fields from here until {/GRP} are a group of fields
                // separated by semicolons. Fields with no data are omitted, and fields with
                // data are prepended with their field label from the form layout.
                ++$this->groupLevel;
                $this->groupCount = 0;
                $s = $this->keyReplace($s, '');
            } elseif ($this->keySearch($s, '{/GRP}')) {
                if ($this->groupLevel > 0) {
                    --$this->groupLevel;
                }
                $s = $this->keyReplace($s, '');
            } elseif (preg_match('/^\{ITEMSEP\}(.*?)\{\/ITEMSEP\}/', substr($s, $this->keyLocation), $matches)) {
                // This is how we specify the separator between group items in a way that
                // is independent of the document format. Whatever is between {ITEMSEP} and
                // {/ITEMSEP} is the separator string. Default is "; ".
                $itemSeparator = $matches[1];
                $this->keyLength = strlen($matches[0]);
                $s = $this->keyReplace($s, '');
            } elseif (preg_match('/^\{(LBF\w+):(\w+)\}/', substr($s, $this->keyLocation), $matches)) {
                // This handles keys like {LBFxxx:fieldid} for layout-based encounter forms.
                $formname = $matches[1];
                $fieldid = $matches[2];
                $this->keyLength = 3 + strlen($formname) + strlen($fieldid);
                $data = '';
                $currvalue = '';
                $title = '';
                $frow = sqlQuery("SELECT * FROM layout_options " . "WHERE form_id = ? AND field_id = ? LIMIT 1", array(
                    $formname,
                    $fieldid
                ));
                if (!empty($frow)) {
                    $ldrow = sqlQuery("SELECT ld.field_value " . "FROM lbf_data AS ld, forms AS f WHERE " . "f.pid = ? AND f.encounter = ? AND f.formdir = ? AND f.deleted = 0 AND " . "ld.form_id = f.form_id AND ld.field_id = ? " . "ORDER BY f.form_id DESC LIMIT 1", array(
                        $this->pid,
                        $this->encounter,
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
                $s = $this->keyReplace($s, $this->dataFixup($data, $title));
            } elseif (preg_match('/^\{(DEM|HIS):(\w+)\}/', substr($s, $this->keyLocation), $matches)) {
                // This handles keys like {DEM:fieldid} and {HIS:fieldid}.
                $formname = $matches[1];
                $fieldid = $matches[2];
                $this->keyLength = 3 + strlen($formname) + strlen($fieldid);
                $data = '';
                $currvalue = '';
                $title = '';
                $frow = sqlQuery("SELECT * FROM layout_options " . "WHERE form_id = ? AND field_id = ? LIMIT 1", array(
                    $formname,
                    $fieldid
                ));
                if (!empty($frow)) {
                    $tmprow = $formname == 'DEM' ? $this->ptrow : $this->hisrow;
                    if (isset($tmprow[$fieldid])) {
                        $currvalue = $tmprow[$fieldid];
                        $title = $frow['title'];
                    }
                    if ($currvalue !== '') {
                        $data = generate_plaintext_field($frow, $currvalue);
                    }
                }
                $s = $this->keyReplace($s, $this->dataFixup($data, $title));
            } elseif (preg_match('/^{(CurrentDate):(.*?)}/', substr($s, $this->keyLocation), $matches)) {
                /* defaults to ISO standard date format yyyy-mm-dd
                 * modified by string following ':' as follows
                 * 'global' will use the global date format setting
                 * 'YYYY-MM-DD', 'MM/DD/YYYY', 'DD/MM/YYYY' override the global setting
                 * anything else is ignored
                 *
                 * oeFormatShortDate($date = 'today', $showYear = true) - OpenEMR function to format
                 * date using global setting, defaults to ISO standard yyyy-mm-dd
                */
                $this->keyLength = strlen($matches[0]);
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
                $s = $this->keyReplace($s, $this->dataFixup($currentdate, xl('Date')));
            } elseif ($this->keySearch($s, '{CurrentTime}')) {
                $format = 'H:i';  /* 24 hour clock with leading zeros */
                $currenttime = date($format); /* format to hh:mm for local time zone */
                $s = $this->keyReplace($s, $this->dataFixup($currenttime, xl('Time')));
            }
        } // End if { character found.

        return $s;
    }

    /**
     * Check if the current location has the specified {string}.
     *
     * @param $s
     * @param $key
     * @return bool
     */
    private function keySearch($s, $key): bool
    {
        $this->keyLength = strlen($key);
        if ($this->keyLength == 0) {
            return false;
        }

        return $key == substr($s, $this->keyLocation, $this->keyLength);
    }

    /**
     * Replace the {string} at the current location with the specified data.
     * Also update the location to resume scanning accordingly.
     *
     * @param $s
     * @param $data
     * @return string
     */
    private function keyReplace(&$s, $data): string
    {
        $this->nextLocation = $this->keyLocation + strlen($data);
        return substr($s, 0, $this->keyLocation) . $data . substr($s, $this->keyLocation + $this->keyLength);
    }

    /**
     * Do some final processing of field data before it's put into the document.
     *
     * @param        $data
     * @param string $title
     * @return array|string|string[]
     */
    private function dataFixup($data, string $title = ''): array|string
    {
        if ($data !== '') {
            // Replace some characters that can mess up XML without assuming XML content type.
            $data = str_replace('&', '[and]', $data);
            $data = str_replace('<', '[less]', $data);
            $data = str_replace('>', '[greater]', $data);
            // If in a group, include labels and separators.
            if ($this->groupLevel) {
                if ($title !== '') {
                    $data = $title . ': ' . $data;
                }

                if ($this->groupCount) {
                    $data = $this->itemSeparator . $data;
                }
                ++$this->groupCount;
            }
        }

        return $data;
    }

    /**
     * Return a string naming all issues for the specified patient and issue type.
     *
     * @param $type
     * @return string
     */
    private function getIssues($type): string
    {
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
}
