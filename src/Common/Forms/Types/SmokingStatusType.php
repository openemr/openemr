<?php
/*
 * SmokingStatusType.php - Form Type for Smoking Status Field used in options.inc.php
 * with the LBF forms.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * Copyright (C) 2007-2021 Rod Roark <rod@sunsetsystems.com>
 * Copyright © 2010 by Andrew Moore <amoore@cpan.org>
 * Copyright © 2010 by "Boyd Stephen Smith Jr." <bss@iguanasuicide.net>
 * Copyright (c) 2017 - 2021 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (c) 2021 Robert Down <robertdown@live.com>
 * Copyright (c) 2025 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Common\Layouts\LayoutsUtils;

class SmokingStatusType implements IOptionFormType {

    const OPTIONS_TYPE_INDEX = 32;


    public const COLUMN_TOBACCO_INDEX_SMOKING_STATUS = 3;
    public const COLUMN_TOBACCO_INDEX_SMOKING_PACK_COUNT = 4;

    public function buildPrintView($frow, $currvalue, $value_allowed = true)
    {
        $printView = '';
        $field_id    = $frow['field_id'];
        $list_id     = $frow['list_id'] ?? null;

        [$smokingQuantity, $resnote, $restype, $resdate, $reslist] = $this->getSmokingData($currvalue);

        $fldlength = empty($frow['fld_length']) ?  20 : $frow['fld_length'];
        $printView .= "<table class='table'>";
        $printView .= "<tr>";
        $fldlength = htmlspecialchars((string) $fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars((string) $resnote, ENT_QUOTES);
        $resdate = htmlspecialchars((string) $resdate, ENT_QUOTES);

        $printView .= "<tr><td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$resnote' /></td></tr>";
        $fldlength = 30;
        $smoking_status_title = generate_display_field(['data_type' => '1','list_id' => $list_id], $reslist);
        $printView .= "<td><input type='text'" .
            " size='$fldlength'" .
            " class='under form-control'" .
            " value='$smoking_status_title' /></td>";
        $printView .= "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars((string) xl('Status'), ENT_NOQUOTES) . ":&nbsp;&nbsp;</td>";

        $printView .= "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            $printView .= " checked";
        }

        $printView .= "/>" . htmlspecialchars((string) xl('Current'), ENT_NOQUOTES) . "&nbsp;</td>";

        $printView .= "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            $printView .= " checked";
        }

        $printView .= "/>" . htmlspecialchars((string) xl('Quit'), ENT_NOQUOTES) . "&nbsp;</td>";

        $printView .= "<td><input type='text' size='6'" .
            " value='$resdate'" .
            " class='under form-control'" .
            " /></td>";

        $printView .= "<td><input type='radio' class='form-check-inline'";
        if ($restype == "current" . $field_id) {
            $printView .= " checked";
        }

        $printView .= " />" . htmlspecialchars((string) xl('Never'), ENT_NOQUOTES) . "</td>";

        $printView .= "<td><input type='radio' class='form-check-inline'";
        if ($restype == "not_applicable" . $field_id) {
            $printView .= " checked";
        }

        $printView .= " />" . htmlspecialchars((string) xl('N/A'), ENT_NOQUOTES) . "&nbsp;</td>";
        $printView .= "<td><input type='text' size='6'" .
            " value='" . attr($smokingQuantity) . "' class='under form-control'" .
            " /></td>";
        $printView .= "</tr>";
        $printView .= "</table>";
        return $printView;
    }

    public function buildPlaintextView($frow, $currvalue)
    {
        $field_id    = $frow['field_id'];
        $list_id     = $frow['list_id'] ?? null;
        $s = '';
        // VicarePlus :: A selection list for smoking status.
        [$smokingQuantity, $resnote, $restype, $resdate, $reslist] = $this->getSmokingData($currvalue);
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

     // Tobacco field has a listbox, text box, date field and 3 radio buttons.
        if (!empty($reslist)) {
            $s .= generate_plaintext_field(['data_type' => '1','list_id' => $list_id], $reslist);
        }

        if (!empty($resnote)) {
            $s .= ' ' . $resnote;
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

        if ($smokingQuantity > 0) {
            $s .= xl('Cigarette Pack Years') . ' ' . $smokingQuantity;
        }
        return $s;
    }

    public function buildDisplayView($frow, $currvalue): string
    {
        $field_id    = $frow['field_id'];
        $list_id     = $frow['list_id'] ?? null;
        $s = '';
        // and a date text field:
        // VicarePlus :: A selection list for smoking status.
        [$smokingQuantity, $resnote, $restype, $resdate, $reslist] = $this->getSmokingData($currvalue);

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
       //VicarePlus :: Tobacco field has a listbox, text box, date field and 3 radio buttons.
        // changes on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
        $smoke_codes = getSmokeCodes();
        $code_desc = '';
        if (!empty($reslist)) {
            if ($smoke_codes[$reslist] != "") {
                $code_desc = "( " . $smoke_codes[$reslist] . " )";
            }

            $s .= "<td class='text align-top'>" . generate_display_field(['data_type' => '1','list_id' => $list_id], $reslist) . "&nbsp;" . text($code_desc) . "&nbsp;&nbsp;&nbsp;&nbsp;</td>";
        }

        if (!empty($resnote)) {
            $s .= "<td class='text align-top'>" . htmlspecialchars((string) $resnote, ENT_NOQUOTES) . "&nbsp;&nbsp;</td>";
        }
        if (!empty($res)) {
            $s .= "<td class='text align-top'><strong>" . htmlspecialchars((string) xl('Status'), ENT_NOQUOTES) . "</strong>:&nbsp;" . htmlspecialchars((string) $res, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        if ($restype == "quit" . $field_id) {
            $s .= "<td class='text align-top'>" . htmlspecialchars((string) $resdate, ENT_NOQUOTES) . "&nbsp;</td>";
        }

        $s .= "<td class='text align-top'><strong>" . xl('Cigarette Pack Years') . '</strong> ' . htmlspecialchars((string) $smokingQuantity, ENT_NOQUOTES) . "&nbsp;</td>";

        $s .= "</tr>";
        $s .= "</table>";
        return $s;
    }

    public function buildFormView($frow, $currvalue): string
    {
        $formView = '';
        $edit_options = $frow['edit_options'] ?? null;
        $field_id    = $frow['field_id'];
        $list_id     = $frow['list_id'] ?? null;
        $form_id = $frow['form_id'] ?? null;
        // 'smallform' can be 'true' if we want a smaller form field, otherwise
        // can be used to assign arbitrary CSS classes to data entry fields.
        $smallform = $frow['smallform'] ?? null;
        if ($smallform === 'true') {
            $smallform = ' form-control-sm';
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

        $field_id_esc = htmlspecialchars((string) $field_id, ENT_QUOTES);
        // Added 5-09 by BM - Translate description if applicable
        $description = (isset($frow['description']) ? htmlspecialchars((string) xl_layout_label($frow['description']), ENT_QUOTES) : '');
        $disabled = LayoutsUtils::isOption($edit_options, '0') === false ? '' : 'disabled';

        $lbfchange = (
            !empty($form_id) &&
            (
                str_starts_with((string) $form_id, 'LBF') ||
                str_starts_with((string) $form_id, 'LBT') ||
                str_starts_with((string) $form_id, 'DEM') ||
                str_starts_with((string) $form_id, 'HIS')
            )
        ) ? "checkSkipConditions();" : "";
        $lbfonchange = $lbfchange ? "onchange='$lbfchange'" : "";

        // and a date text field:
        // VicarePlus :: A selection list box for smoking status:

        // TODO: this whole thing needs to be rewritten as a proper data structure
        [$smokingQuantity, $resnote, $restype, $resdate, $reslist] = $this->getSmokingData($currvalue);

        $maxlength = $frow['max_length'];
        $string_maxlength = "";
        // if max_length is set to zero, then do not set a maxlength
        if ($maxlength) {
            $string_maxlength = "maxlength='" . attr($maxlength) . "'";
        }

        $fldlength = empty($frow['fld_length']) ? 20 : $frow['fld_length'];

        $fldlength = htmlspecialchars((string)$fldlength, ENT_QUOTES);
        $resnote = htmlspecialchars((string) $resnote, ENT_QUOTES);
        $resdate = htmlspecialchars((string)$resdate, ENT_QUOTES);
        $formView .= "<table class='table'>";
        $formView .= "<tr>";

        // input text
        $formView .= "<td><input type='text'" .
            " name='form_text_$field_id_esc'" .
            " id='form_text_$field_id_esc'" .
            " size='$fldlength'" .
            " class='form-control$smallform'" .
            " $string_maxlength" .
            " value='$resnote' $disabled />&nbsp;</td></tr>";
        $formView .= "<td>";
        //Selection list for smoking status
        $onchange = 'radioChange(this.options[this.selectedIndex].value)';//VicarePlus :: The javascript function for selection list.
        $formView .= generate_select_list(
            "form_$field_id",
            $list_id,
            $reslist,
            $description,
            ($showEmpty ? $empty_title : ''),
            $smallform,
            $onchange,
            '',
            ($disabled ? ['disabled' => 'disabled'] : null)
        );
        $formView .= "</td>";
        $formView .= "<td class='font-weight-bold'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . xlt('Status') . ":&nbsp;&nbsp;</td>";

        // current
        $formView .= "<td class='text'><input type='radio'" .
            " name='radio_{$field_id_esc}'" .
            " id='radio_{$field_id_esc}[current]'" .
            " class='form-check-inline'" .
            " value='current" . $field_id_esc . "' $lbfonchange";
        if ($restype == "current" . $field_id) {
            $formView .= " checked";
        }
        $formView .= " onClick='smoking_statusClicked(this)'";

        $formView .= " />" . xlt('Current') . "&nbsp;</td>";
        // quit
        $formView .= "<td class='text'><input type='radio'" .
            " name='radio_{$field_id_esc}'" .
            " id='radio_{$field_id_esc}[quit]'" .
            " class='form-check-inline'" .
            " value='quit" . $field_id_esc . "' $lbfonchange";
        if ($restype == "quit" . $field_id) {
            $formView .= " checked";
        }

        $formView .= " onClick='smoking_statusClicked(this)'";

        $formView .= " $disabled />" . xlt('Quit') . "&nbsp;</td>";
        // quit date
        $formView .= "<td class='text'><input type='text' size='6' class='form-control datepicker' name='date_$field_id_esc' id='date_$field_id_esc'" .
            " value='$resdate'" .
            " title='$description'" .
            " $disabled />";
        $formView .= "&nbsp;</td>";
        // never
        $formView .= "<td class='text'><input type='radio'" .
            " name='radio_{$field_id_esc}'" .
            " class='form-check-inline'" .
            " id='radio_{$field_id_esc}[never]'" .
            " value='never" . $field_id_esc . "' $lbfonchange";
        if ($restype == "never" . $field_id) {
            $formView .= " checked";
        }
        $formView .= " onClick='smoking_statusClicked(this)'";

        $formView .= " />" . xlt('Never') . "&nbsp;</td>";
        // Not Applicable
        $formView .= "<td class='text'><input type='radio'" .
            " class='form-check-inline' " .
            " name='radio_{$field_id}'" .
            " id='radio_{$field_id}[not_applicable]'" .
            " value='not_applicable" . $field_id . "' $lbfonchange";
        if ($restype == "not_applicable" . $field_id) {
            $formView .= " checked";
        }

        $formView .= " onClick='smoking_statusClicked(this)'";

        $formView .= " $disabled />" . xlt('N/A') . "&nbsp;</td>";
        //
        //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
        $formView .= "<td class='text'><div id='smoke_code'></div></td>";
        $formView .= "</tr>";
        // default display hidden field for cigarette pack years if $restype is not 'never' or 'not_applicable'
        $defaultDisplayClassname = $this->smokingDetailsDisabled($restype, $field_id) ? 'd-none' : '';
        $formView .= $this->buildFormViewSmokerPacksPerDay($defaultDisplayClassname, $smokingQuantity, $field_id_esc, $smallform, $lbfonchange, $disabled);
        $formView .= "</table>";
        return $formView;
    }

    protected function smokingDetailsDisabled(string $restype, string $field_id): bool
    {
        return in_array($restype, ["never" . $field_id, "not_applicable" . $field_id]);
    }

    private function buildFormViewSmokerPacksPerDay(string $defaultDisplayClassname, int $smokingQuantity, string $field_id, ?string $smallform, string $lbfonchange, string $disabled)
    {
        ob_start();
        ?>
        <tr class="row-smoking-status-year-packs <?php echo attr($defaultDisplayClassname); ?>" >
            <td>
                <label for="pack_years"><?php echo xlt("Cigarette pack-years (Number of packs per day multiplied by number of years smoked)"); ?>
            </td>
            <td>
                <td class='text'>
                    <input type='text' class='form-control <?php echo $smallform ?? ''; ?>'
                            name='form_packyears_<?php echo attr($field_id); ?>'
                            id='form_packyears_<?php echo attr($field_id); ?>'
                            value='<?php echo attr($smokingQuantity); ?>'
                            <?php echo $lbfonchange; ?>
                            <?php echo $disabled; ?>
                    />
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    // TODO: @adunsulag should we add this to the interface? I'm assuming so, but not sure if we want to change up
    // the method signature for all implementations once we decide on a better approach for data saving.
    public function getValueFromRequest($request, $frow, $prefix = 'form_',): ?string
    {
        $field_id  = $frow['field_id'];
        // $request["$prefix$field_id"] is an date text fields with companion
        // radio buttons to be imploded into "notes|type|date".
        $restype = $request["radio_{$field_id}"] ?? '';
        if (empty($restype)) {
            $restype = '0';
        }

        $resdate = DateToYYYYMMDD(str_replace('|', ' ', $_POST["date_$field_id"]));
        $resnote = str_replace('|', ' ', $request["$prefix$field_id"]);
        // Smoking status data is imploded into "note|type|date|list|smokingQuantity".
        $reslist = str_replace('|', ' ', $request["$prefix$field_id"]);
        $res_text_note = str_replace('|', ' ', $request["{$prefix}text_$field_id"]);
        $smokingQuantity = str_replace('|', ' ', $request["{$prefix}packyears_$field_id"]);
        if ($this->smokingDetailsDisabled($restype, $field_id)) {
            $smokingQuantity = 0; // reset smoking quantity if not applicable or never smoked
        }
        $smokingArray[0] = $res_text_note;
        $smokingArray[1] = $restype;
        $smokingArray[2] = $resdate;
        $smokingArray[self::COLUMN_TOBACCO_INDEX_SMOKING_STATUS] = $reslist;
        $smokingArray[self::COLUMN_TOBACCO_INDEX_SMOKING_PACK_COUNT] = $smokingQuantity;
        return implode('|', $smokingArray);
    }

    /**
     * @param string $currvalue
     * @return array
     */
    private function getSmokingData(string $currvalue): array
    {
        $tmp = explode('|', $currvalue);
        $resnote = $tmp[0] ?? '';
        $restype = $tmp[1] ?? '';
        $resdate = !empty($tmp[2]) ? oeFormatShortDate($tmp[2]) : '';
        $reslist = $tmp[self::COLUMN_TOBACCO_INDEX_SMOKING_STATUS] ?? '';
        $smokingQuantity = intval($tmp[self::COLUMN_TOBACCO_INDEX_SMOKING_PACK_COUNT] ?? 0);
        return [$smokingQuantity, $resnote, $restype, $resdate, $reslist];
    }

}
