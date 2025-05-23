<?php

/**
 * Billing Code Type represents a Billing Code selector widget that can be used in the LBF forms or independently in the system.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Common\Layouts\LayoutsUtils;

class BillingCodeType
{
    const OPTIONS_TYPE_INDEX = 15;

    public function buildPrintView()
    {
    }
    public function getAccumActionConditions($frow, $condition_str, $action)
    {
        // For billing codes handle requirement to display its description.
        $tmp = explode('=', $action, 2);
        if (!empty($tmp[1])) {
            return "valdesc:" . js_escape(getCodeDescription($tmp[1])) . ", ";
        }
        return "";
    }
    public function buildPlaintextView($frow, $currvalue)
    {
        return $currvalue;
    }
    public function buildDisplayView($frow, $currvalue): string
    {
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
                    $s .= text($tmp);
                } else {
                    $s .= text($codestring) . ' (' . xlt('not found') . ')';
                }
            }
        }
        return $s;
    }

    public function buildFormView($frow, $currvalue): string
    {
        $currescaped = htmlspecialchars($currvalue ?? '', ENT_QUOTES);
        $field_id = $frow['field_id'];
        $list_id = $frow['list_id'] ?? null;
        $edit_options = $frow['edit_options'] ?? null;
        $form_id = $frow['form_id'] ?? null;

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

        // Added 5-09 by BM - Translate description if applicable
        $description = (isset($frow['description']) ? htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES) : '');

        // Support edit option T which assigns the (possibly very long) description as
        // the default value.
        if ($this->isOption($edit_options, 'T') !== false) {
            if (strlen($currescaped) == 0) {
                $currescaped = $description;
            }

            // Description used in this way is not suitable as a title.
            $description = '';
        }

        $disabled = $this->isOption($edit_options, '0') === false ? '' : 'disabled';

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

        $codetype = '';
        if (!empty($frow['description']) && isset($code_types[$frow['description']])) {
            $codetype = $frow['description'];
        }
        $string_maxlength = "";
        $maxlength = $frow['max_length'];
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }
        $fldlength = attr($frow['fld_length']);
        // Edit option E means allow multiple (Extra) billing codes in a field.
        // We invent a class name for this because JavaScript needs to know.
        $className = '';
        if (strpos($frow['edit_options'], 'E') !== false) {
            $className = 'EditOptionE';
        }
        //
        if ($this->isOption($edit_options, '2') !== false) {
            // Option "2" generates a hidden input for the codes, and a matching visible field
            // displaying their descriptions. First step is computing the description string.
            $currdescstring = '';
            if (!empty($currvalue)) {
                $relcodes = explode(';', $currvalue);
                foreach ($relcodes as $codestring) {
                    if ($codestring === '') {
                        continue;
                    }
                    if ($currdescstring !== '') {
                        $currdescstring .= '; ';
                    }
                    $currdescstring .= getCodeDescription($codestring, $codetype);
                }
            }

            $currdescstring = attr($currdescstring);
            $result = [];
            $result[] = "<div>"; // wrapper for myHideOrShow()
            $result[] = "<input type='text'" .
                " name='form_$field_id_esc'" .
                " id='form_related_code'" .
                " class='" . attr($className) . "'" .
                " size='$fldlength'" .
                " value='$currescaped'" .
                " style='display:none'" .
                " $lbfonchange readonly $disabled />";
            // Extra readonly input field for optional display of code description(s).
            $result[] = "<input type='text'" .
                " name='form_$field_id_esc" . "__desc'" .
                " size='$fldlength'" .
                " title='$description'" .
                " value='$currdescstring'";
            if (!$disabled) {
                $result[] = " onclick='sel_related(this," . attr_js($codetype) . ")'";
            }

            $result[] = "class='form-control$smallform'";
            $result[] = " readonly $disabled />";
            $result[] = "</div>";
        } else {
            $result[] = "<input type='text'" .
                " name='form_$field_id_esc'" .
                " id='form_related_code'" .
                " class='form-control $smallform " . attr($className) . "'" .
                " size='$fldlength'" .
                " $string_maxlength" .
                " title='$description'" .
                " value='$currescaped'";
            if (!$disabled) {
                $result[] = " onclick='sel_related(this," . attr_js($codetype) . ")'";
            }

            $result[] = "class='form-control$smallform'";
            $result[] = " $lbfonchange readonly $disabled />";
        }
        return implode("", $result);
    }

    private function isOption($options, $test): bool
    {
        return LayoutsUtils::isOption($options, $test);
    }
}
