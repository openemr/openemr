<?php

/**
 * ROS form
 * Forms generated from formsWiz
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc.php");

function ros_report($pid, $encounter, $cols, $id)
{

    $count = 0;

    $data = formFetch("form_ros", $id);

    if ($data) {
        $cmap = array(
                "id" => '',
                "pid" => '',
                "user" => '',
                "groupname" => '',
                "activity" => '',
                "authorized" => '',
                "date" => '',

                // This maps a label to custom text. For example "glaucoma_history" should be
                // displayed as "Glaucoma Family History". If this wasn't specified, the code
                // will display it as "Glaucoma History" due to some clever string manipulation.
                // Acronyms are handled in this map as well.
                "glaucoma_history" => "Glaucoma Family History",
                "irritation" => "Eye Irritation",
                "redness" => "Eye Redness",
                "discharge" => "ENT Discharge",
                "pain" => "ENT Pain",
                "biopsy" => "Breast Biopsy",
                "hemoptsyis" => "Hemoptysis",
                "copd" => "COPD",
                "pnd" => "PND",
                "doe" => "DOE",
                "peripheal" => "Peripheral",
                "legpain_cramping" => "Leg Pain/Cramping",
                "frequency" => "Urine Frequency",
                "urgency" => "Urine Urgency",
                "utis" => "UTIs",
                "hesitancy" => "Urine Hesitancy",
                "dribbling" => "Urine Dribbling",
                "stream" => "Urine Stream",
                "g" => "Female G",
                "p" => "Female P",
                "lc" => "Female LC",
                "ap" => "Female AP",
                "mearche" => "Menarche",
                "lmp" => "LMP",
                "f_frequency" => "Menstrual Frequency",
                "f_flow" => "Menstrual Flow",
                "f_symptoms" => "Female Symptoms",
                "f_hirsutism" => "Hirsutism/Striae",
                "swelling" => "Musc Swelling",
                "m_redness" => "Musc Redness",
                "m_warm" => "Musc Warm",
                "m_stiffness" => "Musc Stiffness",
                "m_aches" => "Musc Aches",
                "fms" => "FMS",
                "loc" => "LOC",
                "tia" => "TIA",
                "n_numbness" => "Neuro Numbness",
                "n_weakness" => "Neuro Weakness",
                "n_headache" => "Headache",
                "s_cancer" => "Skin Cancer",
                "s_acne" => "Acne",
                "s_other" => "Skin Other",
                "s_disease" => "Skin Disease",
                "p_diagnosis" => "Psych Diagnosis",
                "p_medication" => "Psych Medication",
                "abnormal_blood" => "Endo Abnormal Blood",
                "fh_blood_problems" => "FH Blood Problems",
                "hiv" => "HIV",
                "hai_status" => "HAI Status",
        );

        print "<div id='form_ros_values'><table class='report_results'><tr>";

        foreach ($data as $key => $value) {
            if (isset($cmap[$key])) {
                if ($cmap[$key] == '') {
                    continue;
                }

                $key = $cmap[$key];
            } else {
                $key = ucwords(str_replace("_", " ", $key));
            }

            // skip the N/A values -- cfapress, Jan 2009 OR blank or zero date values
            if (
                $value == "N/A" || $value == "" ||
                $value == "0000-00-00" || $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            printf("<td><span class=bold>%s: </span><span class=text>%s</span></td>", xlt($key), xlt($value));
            $count++;

            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table></div>";
}
