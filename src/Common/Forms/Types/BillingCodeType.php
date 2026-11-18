<?php

/**
 * Billing Code Type represents a Billing Code selector widget that can be used in the LBF forms or independently in the system.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Services\CodeTypesService;

class BillingCodeType implements IOptionFormType
{
    const OPTIONS_TYPE_INDEX = 15;

    public function __construct(
        private readonly CodeTypesService $codeTypes = new CodeTypesService(),
    ) {
    }

    public function buildPrintView(mixed $frow, mixed $currvalue, mixed $value_allowed = true): string
    {
        return '';
    }

    public function getAccumActionConditions(mixed $frow, mixed $condition_str, mixed $action): string
    {
        // For billing codes handle requirement to display its description.
        $code = explode('=', self::stringValue($action), 2)[1] ?? '';
        if (self::isBlank($code)) {
            return '';
        }
        $description = getCodeDescription($code);
        return 'valdesc:' . js_escape(is_string($description) ? $description : '') . ', ';
    }

    public function buildPlaintextView(mixed $frow, mixed $currvalue): mixed
    {
        return $currvalue;
    }

    public function buildDisplayView(mixed $frow, mixed $currvalue): string
    {
        $value = self::stringValue($currvalue);
        if (self::isBlank($value)) {
            return '';
        }

        $s = '';
        foreach (explode(';', $value) as $codestring) {
            if ($codestring === '') {
                continue;
            }
            $tmp = lookup_code_descriptions($codestring);
            if ($s !== '') {
                $s .= '; ';
            }
            if (self::isBlank($tmp)) {
                $s .= text($codestring) . ' (' . xlt('not found') . ')';
            } else {
                $s .= text($tmp);
            }
        }
        return $s;
    }

    public function buildFormView(mixed $frow, mixed $currvalue): string
    {
        $frow = is_array($frow) ? $frow : [];
        $options = BillingCodeEditOption::enabledIn($frow['edit_options'] ?? null);
        $currentValue = self::stringValue($currvalue);
        $currescaped = htmlspecialchars($currentValue, ENT_QUOTES);
        $fieldDescription = self::stringValue($frow['description'] ?? null);
        $formId = self::stringValue($frow['form_id'] ?? null);

        // 'smallform' can be 'true' if we want a smaller form field, otherwise
        // can be used to assign arbitrary CSS classes to data entry fields.
        $rawSmallform = $frow['smallform'] ?? null;
        $smallform = $rawSmallform === 'true' ? ' form-control-sm' : self::stringValue($rawSmallform);

        // historically we've used smallform to append classes if the value is NOT true
        // to make it EXPLICIT what we are doing and to aid maintainability we are supporting
        // an actual 'classNames' attribute for assigning arbitrary CSS classes to data entry fields
        $classesToAppend = self::stringValue($frow['classNames'] ?? null);
        if (!self::isBlank($classesToAppend)) {
            $smallform = $rawSmallform === null ? $classesToAppend : $smallform . ' ' . $classesToAppend;
        }

        // escaped variables to use in html
        $field_id_esc = htmlspecialchars(self::stringValue($frow['field_id'] ?? null), ENT_QUOTES);

        // Added 5-09 by BM - Translate description if applicable
        $description = isset($frow['description']) ? htmlspecialchars(xl_layout_label($fieldDescription), ENT_QUOTES) : '';

        // Support edit option T which assigns the (possibly very long) description as
        // the default value.
        if (in_array(BillingCodeEditOption::DescriptionIsDefault, $options, true)) {
            if ($currescaped === '') {
                $currescaped = $description;
            }

            // Description used in this way is not suitable as a title.
            $description = '';
        }

        $disabled = in_array(BillingCodeEditOption::ReadOnly, $options, true) ? 'disabled' : '';

        $lbfchange = (
            str_starts_with($formId, 'LBF') ||
            str_starts_with($formId, 'LBT') ||
            str_starts_with($formId, 'DEM') ||
            str_starts_with($formId, 'HIS')
        ) ? 'checkSkipConditions();' : '';
        $lbfonchange = $lbfchange === '' ? '' : "onchange='$lbfchange'";

        // A description naming an installed code type limits the picker to that type.
        $codetype = '';
        if (!self::isBlank($fieldDescription) && $this->codeTypes->isInstalledCodeType($fieldDescription)) {
            $codetype = $fieldDescription;
        }
        $string_maxlength = '';
        $maxlength = self::stringValue($frow['max_length'] ?? null);
        // if max_length is set to zero, then do not set a maxlength
        if (!self::isBlank($maxlength)) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }
        $fldlength = attr(self::stringValue($frow['fld_length'] ?? null));
        // Edit option E means allow multiple (Extra) billing codes in a field.
        // We invent a class name for this because JavaScript needs to know.
        $className = in_array(BillingCodeEditOption::MultipleCodes, $options, true) ? 'EditOptionE' : '';

        $result = [];
        if (in_array(BillingCodeEditOption::ShowDescriptions, $options, true)) {
            // Option "2" generates a hidden input for the codes, and a matching visible field
            // displaying their descriptions. First step is computing the description string.
            $currdescstring = '';
            if (!self::isBlank($currentValue)) {
                foreach (explode(';', $currentValue) as $codestring) {
                    if ($codestring === '') {
                        continue;
                    }
                    if ($currdescstring !== '') {
                        $currdescstring .= '; ';
                    }
                    $codeDescription = getCodeDescription($codestring, $codetype);
                    $currdescstring .= is_string($codeDescription) ? $codeDescription : '';
                }
            }

            $currdescstring = attr($currdescstring);
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
            if ($disabled === '') {
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
            if ($disabled === '') {
                $result[] = " onclick='sel_related(this," . attr_js($codetype) . ")'";
            }

            $result[] = "class='form-control$smallform'";
            $result[] = " $lbfonchange readonly $disabled />";
        }
        return implode("", $result);
    }

    /**
     * Layout rows store values as strings or numbers; anything else counts as no value.
     */
    private static function stringValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return '';
    }

    /**
     * True for '' and '0', the two strings this class has always treated as no value.
     */
    private static function isBlank(string $value): bool
    {
        return $value === '' || $value === '0';
    }
}
