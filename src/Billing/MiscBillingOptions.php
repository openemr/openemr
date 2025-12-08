<?php

/**
 * Reusable data entries for new Box 14 and Box 15 date qualifiers that are part of
 * HCFA 1500 02/12 format
 *
 * For details on format refer to:n
 * <http://www.nucc.org/index.php?option=com_content&view=article&id=186&Itemid=138>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @copyright Copyright (C) 2017-2025 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

class MiscBillingOptions
{
    public $box_14_qualifier_options;
    public $box_15_qualifier_options;
    public $hcfa_date_quals;

    public function __construct()
    {
        $this->box_14_qualifier_options = [
          [xl("Onset of Current Symptoms or Illness"),"431"],
          [xl("Last Menstrual Period"),"484"]
        ];

        $this->box_15_qualifier_options = [
          [xl("Initial Treatment"),"454"],
          [xl("Latest Visit or Consultation"),"304"],
          [xl("Acute Manifestation of a Chronic Condition"),"453"],
          [xl("Accident"),"439"],
          [xl("Last X-ray"),"455"],
          [xl("Prescription"),"471"],
          [xl("Report Start (Assumed Care Date)"),"090"],
          [xl("Report End (Relinquished Care Date)"),"091"],
          [xl("First Visit or Consultation"),"444"]
        ];

        $this->hcfa_date_quals = [
          "box_14_date_qual" => $this->box_14_qualifier_options,
          "box_15_date_qual" => $this->box_15_qualifier_options
        ];
    }

    public function generateDateQualifierSelect(string $name, array $options, array $obj): void
    {
    /* ai generated code by google-labs-jules starts */
        $current_value = $obj[$name] ?? null;
        echo     "<select name='" . attr($name) . "' class='form-control'>"; // Added form-control for consistent styling
        // Add a blank/default option
        echo "<option value=''";
        if (empty($current_value)) {
            echo " selected";
        }
        echo ">" . text(xl('-- Select --')) . "</option>";

        for ($idx = 0; $idx < count($options); $idx++) {
            echo "<option value='" . attr($options[$idx][1]) . "'";
            // Ensure that current_value is not null and matches the option value
            if (!empty($current_value) && ($current_value == $options[$idx][1])) {
/* ai gen'ed code ends */
                echo " selected";
            }
            echo ">" . text($options[$idx][0]) . "</option>";
        }

        echo     "</select>";
    }

    public function getReferringProviders(): array
    {
        $query = "SELECT id, lname, fname,npi FROM users WHERE npi != '' AND npi IS NOT NULL ORDER BY lname, fname";
        return QueryUtils::fetchRecords($query, []);
    }

    public function genReferringProviderSelect(string $selname, string $toptext, int $default = 0, bool $disabled = false): void
    {
        $providers = $this->getReferringProviders();
        echo "<select name='" . attr($selname) . "' id='" . attr($selname) . "' class='form-control'";
        if ($disabled) {
            echo " disabled";
        }

        echo ">";
        echo "<option value=''>" . text($toptext);
        foreach ($providers as $row) {
            $provid = $row['id'];
            echo "<option value='" . attr($provid) . "'";
            if ($provid == $default) {
                echo " selected";
            }

            echo ">" . text($row['lname'] . ", " . $row['fname']);
        }

        echo "</select>\n";
    }

    public function getOrderingProviders(): array
    {
        $query = "SELECT id, lname, fname,npi FROM users WHERE npi != '' ORDER BY lname, fname";
        return QueryUtils::fetchRecords($query, []);
    }

    public function genOrderingProviderSelect(string $selname, string $toptext, int $default = 0, bool $disabled = false): void
    {
        $orderingProviders = $this->getOrderingProviders();
        echo "<select name='" . attr($selname) . "' id='" . attr($selname) . "' class='form-control'";
        if ($disabled) {
            echo " disabled";
        }

        echo ">";
        echo "<option value=''>" . text($toptext);
        foreach ($orderingProviders as $row) {
            $provid = $row['id'];
            echo "<option value='" . attr($provid) . "'";
            if ($provid == $default) {
                echo " selected";
            }

            echo ">" . text($row['lname'] . ", " . $row['fname']);
        }

        echo "</select>\n";
    }

    public function qual_id_to_description(string $qual_type, string $value): ?string
    {
        // Return null if qual_type doesn't exist
        if (!isset($this->hcfa_date_quals[$qual_type])) {
            return null;
        }

        $options = $this->hcfa_date_quals[$qual_type];

        // Return null if options is not an array (shouldn't happen, but type-safe)
        if (!is_array($options)) {
            return null;
        }

        for ($idx = 0; $idx < count($options); $idx++) {
            if ($options[$idx][1] == $value) {
                return $options[$idx][0];
            }
        }

        return null;
    }
}
