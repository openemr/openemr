<?php

/**
 * Provider List Type represents a user selector widget for displaying local users that can be used in the LBF forms or independently in the system.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Layouts\LayoutsUtils;
use OpenEMR\Services\EncounterService;

class EncounterListOptionType implements IOptionFormType
{
    private ?EncounterService $encounterService = null;

    public function __construct(private $pid = null)
    {
    }

    /**
     * @return EncounterService
     */
    public function getEncounterService(): EncounterService
    {
        if (!isset($this->encounterService)) {
            $this->encounterService = new EncounterService();
        }
        return $this->encounterService;
    }

    public function setEncounterService(EncounterService $encounterService): void
    {
        $this->encounterService = $encounterService;
    }

    public function buildPrintView($frow, $currvalue, $value_allowed = true)
    {
        return $this->buildPlaintextView($frow, $currvalue);
    }

    public function buildPlaintextView($frow, $currvalue)
    {
        return $currvalue; // No special display formatting for now
    }
    public function buildDisplayView($frow, $currvalue): string
    {
       return $this->buildPlaintextView($frow, $currvalue);
    }

    public function buildFormView($frow, $currvalue): string
    {
        $field_id = $frow['field_id'];
        $edit_options = $frow['edit_options'] ?? null;
        $title = $frow['title'] ?? null;

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
        $field_id_esc = htmlspecialchars((string) $field_id, ENT_QUOTES);

        $disabled = LayoutsUtils::isOption($edit_options, '0') === false ? '' : 'disabled';
        // Return empty string if no patient ID provided
        if (empty($this->pid)) {
            return '';
        }

        // Get encounters for the patient
        $encounters = $this->getEncounterService()->getPatientEncounterListWithCategories($this->pid);
        $count = count($encounters);

        // Build the options list
        $optionsList = [];
        // go in reverse order so most recent encounter is first
        for ($i = $count - 1; $i >= 0; $i--) {
            // Create display text: "2024-01-15 14:30 - Office Visit"
            $displayText = $encounters['dates'][$i] . ' - ' . $encounters['categories'][$i];
            $optionValue = $encounters['ids'][$i];
            // Only add if we have a valid option value
            if (!empty($optionValue)) {
                $optionsList[$optionValue] = $displayText;
            }
        }
        $html = [];
        $html[] = "<select class=\"form-control " . attr($smallform) . "\" name=\"" . attr($field_id)
            . "\" id=\"" . attr($field_id) . "\" title=\"" . attr($title) . "\" " . $disabled . ">";
        if (!empty($frow['empty_name'])) {
            $html[] = "<option value=\"\">" . text($frow['empty_name']) . "</option>";
        }
        foreach ($optionsList as $value => $text) {
            $selected = ($value == $currvalue) ? ' selected' : '';
            $html[] = "<option value=\"" . attr($value) . "\"" . $selected . ">" . text($text) . "</option>";
        }
        $html[] = "</select>";
        return implode("", $html);
    }

    public function render(string $formName, string $selectedEncounterId = ''): string
    {
        $frow = [
            'field_id' => $formName,
            'title' => 'Select Encounter',
//            'smallform' => 'true',
            'classNames' => 'encounter-select-field',
            'empty_name' => '-- ' . xl('Select Encounter') . ' --'
        ];
        return $this->buildFormView($frow, $selectedEncounterId);
    }
}
