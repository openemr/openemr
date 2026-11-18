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

    /**
     * @param array<mixed> $frow
     */
    public function buildPrintView(array $frow, ?string $currvalue, bool $value_allowed = true): string
    {
        return '';
    }

    /**
     * @param array<mixed> $frow
     */
    public function getAccumActionConditions(array $frow, string $condition_str, string $action): string
    {
        // For billing codes handle requirement to display its description.
        $code = explode('=', $action, 2)[1] ?? '';
        if (self::isBlank($code)) {
            return '';
        }
        // Match the field's own lookup; with no type configured, keep the ICD10 default.
        $codeType = $this->configuredCodeType($frow);
        $description = $codeType === '' ? getCodeDescription($code) : getCodeDescription($code, $codeType);
        return 'valdesc:' . js_escape(is_string($description) ? $description : '') . ', ';
    }

    /**
     * @param array<mixed> $frow
     */
    public function buildPlaintextView(array $frow, ?string $currvalue): ?string
    {
        return $currvalue;
    }

    /**
     * @param array<mixed> $frow
     */
    public function buildDisplayView(array $frow, ?string $currvalue): string
    {
        $value = $currvalue ?? '';
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

    /**
     * @param array<mixed> $frow
     */
    public function buildFormView(array $frow, ?string $currvalue): string
    {
        $editOptions = $frow['edit_options'] ?? null;
        $options = BillingCodeEditOption::enabledIn(is_string($editOptions) ? $editOptions : null);
        $currentValue = $currvalue ?? '';
        $currescaped = htmlspecialchars($currentValue, ENT_QUOTES);
        $fieldDescription = self::field($frow, 'description');
        $formId = self::field($frow, 'form_id');

        // 'smallform' can be 'true' if we want a smaller form field, otherwise
        // can be used to assign arbitrary CSS classes to data entry fields.
        $rawSmallform = $frow['smallform'] ?? null;
        $smallform = $rawSmallform === 'true' ? ' form-control-sm' : self::field($frow, 'smallform');

        // historically we've used smallform to append classes if the value is NOT true
        // to make it EXPLICIT what we are doing and to aid maintainability we are supporting
        // an actual 'classNames' attribute for assigning arbitrary CSS classes to data entry fields
        $classesToAppend = self::field($frow, 'classNames');
        if (!self::isBlank($classesToAppend)) {
            $smallform = $rawSmallform === null ? $classesToAppend : $smallform . ' ' . $classesToAppend;
        }

        // escaped variables to use in html
        $field_id_esc = htmlspecialchars(self::field($frow, 'field_id'), ENT_QUOTES);

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

        $codetype = $this->configuredCodeType($frow);
        $string_maxlength = '';
        $maxlength = self::field($frow, 'max_length');
        // if max_length is set to zero, then do not set a maxlength
        if (!self::isBlank($maxlength)) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }
        $fldlength = attr(self::field($frow, 'fld_length'));
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
     * A description naming an installed code type limits the field to that type; otherwise ''.
     *
     * @param array<mixed> $frow
     */
    private function configuredCodeType(array $frow): string
    {
        $description = self::field($frow, 'description');
        if (self::isBlank($description) || !$this->codeTypes->isInstalledCodeType($description)) {
            return '';
        }
        return $description;
    }

    /**
     * Layout rows store values as strings or numbers; anything else counts as no value.
     *
     * @param array<mixed> $frow
     */
    private static function field(array $frow, string $key): string
    {
        $value = $frow[$key] ?? null;
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
