<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");
function ros2_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_ros2", $id);
  //echo "" . text($data['general_headache']) . "";
    if ($data) {
        print "<table cellpadding=0 cellspacing=3px border=0>";

        echo "<tr><td colspan='3'><span class='bold'><u>GENERAL:</u></span></td></tr>";
        if (($data["general_headache"] != "N/A" && $data["general_headache"] != "" && $data["general_headache"] != "--") || ( $data["general_headache_text"] != "" && $data["general_headache_text"] != null )) {
              echo "<tr>";
            echo "<td>";
            echo "<span class='text'>headache:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_headache']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_headache_text'] != null) {
                echo "<span class='text'>" . text($data['general_headache_text']) . "</span>";
            } else {
                echo "<br/>";
            }

                  echo "</td>";
              echo "</tr>";
        }

        if (($data["general_fever"] != "N/A" && $data["general_fever"] != "" && $data["general_fever"] != "--") || ( $data["general_fever_text"] != "" && $data["general_fever_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>fever:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_fever']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_fever_text'] != null) {
                echo "<span class='text'>(" . text($data['general_fever_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_chills"] != "N/A" && $data["general_chills"] != "" && $data["general_chills"] != "--") || ( $data["general_chills_text"] != "" && $data["general_chills_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>chills:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_chills']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_chills_text'] != null) {
                echo "<span class='text'>" . text($data['general_chills_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_body_aches"] != "N/A" && $data["general_body_aches"] != "" && $data["general_body_aches"] != "--") || ( $data["general_body_aches_text"] != "" && $data["general_body_aches_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>body aches:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_body_aches']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_body_aches_text'] != null) {
                echo "<span class='text'>(" . text($data['general_body_aches_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_fatigue"] != "N/A" && $data["general_fatigue"] != "" && $data["general_fatigue"] != "--") || ( $data["general_fatigue_text"] != "" && $data["general_fatigue_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>fatigue:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_fatigue']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_fatigue_text'] != null) {
                echo "<span class='text'>(" . text($data['general_fatigue_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_loss_of_appetite"] != "N/A" && $data["general_loss_of_appetite"] != "" && $data["general_loss_of_appetite"] != "--") || ( $data["general_loss_of_appetite_text"] != "" && $data["general_loss_of_appetite_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>loss of appetite:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_loss_of_appetite']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_loss_of_appetite_text'] != null) {
                echo "<span class='text'>(" . text($data['general_loss_of_appetite_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_weight_loss"] != "N/A" && $data["general_weight_loss"] != "" && $data["general_weight_loss"] != "--") || ( $data["general_weight_loss_text"] != "" && $data["general_weight_loss_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>weight loss:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_weight_loss']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_weight_loss_text'] != null) {
                echo "<span class='text'>(" . text($data['general_weight_loss_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_daytime_drowsiness"] != "N/A" && $data["general_daytime_drowsiness"] != "" && $data["general_daytime_drowsiness"] != "--") || ( $data["general_daytime_drowsiness_text"] != "" && $data["general_daytime_drowsiness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>daytime drowsiness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_daytime_drowsiness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_daytime_drowsiness_text'] != null) {
                echo "<span class='text'>(" . text($data['general_daytime_drowsiness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["general_excessive_snoring"] != "N/A" && $data["general_excessive_snoring"] != "" && $data["general_excessive_snoring"] != "--") || ( $data["general_excessive_snoring_text"] != "" && $data["general_excessive_snoring_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>excessive snoring:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['general_excessive_snoring']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['general_excessive_snoring_text'] != null) {
                echo "<span class='text'>(" . text($data['general_excessive_snoring_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>NEURO:</u></span></td></tr>";
        if (($data["neuro_disorientation"] != "N/A" && $data["neuro_disorientation"] != "" && $data["neuro_disorientation"] != "--") || ( $data["neuro_disorientation_text"] != "" && $data["neuro_disorientation_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>disorientation:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_disorientation']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_disorientation_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_disorientation_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_loss_of_consciousness"] != "N/A" && $data["neuro_loss_of_consciousness"] != "" && $data["neuro_loss_of_consciousness"] != "--") || ( $data["neuro_loss_of_consciousness_text"] != "" && $data["neuro_loss_of_consciousness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>loss of consciousness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_loss_of_consciousness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_loss_of_consciousness_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_loss_of_consciousness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_numbness"] != "N/A" && $data["neuro_numbness"] != "" && $data["neuro_numbness"] != "--") || ( $data["neuro_numbness_text"] != "" && $data["neuro_numbness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>numbness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_numbness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_numbness_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_numbness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_tingling"] != "N/A" && $data["neuro_tingling"] != "" && $data["neuro_tingling"] != "--") || ( $data["neuro_tingling_text"] != "" && $data["neuro_tingling_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>tingling:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_tingling']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_tingling_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_tingling_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_restlessness"] != "N/A" && $data["neuro_restlessness"] != "" && $data["neuro_restlessness"] != "--") || ( $data["neuro_restlessness_text"] != "" && $data["neuro_restlessness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>restlessness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_restlessness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_restlessness_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_restlessness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_dizziness"] != "N/A" && $data["neuro_dizziness"] != "" && $data["neuro_dizziness"] != "--") || ( $data["neuro_dizziness_text"] != "" && $data["neuro_dizziness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>dizziness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_dizziness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_dizziness_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_dizziness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_vertigo"] != "N/A" && $data["neuro_vertigo"] != "" && $data["neuro_vertigo"] != "--") || ( $data["neuro_vertigo_text"] != "" && $data["neuro_vertigo_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>vertigo:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_vertigo']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_vertigo_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_vertigo_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_amaurosis_fugax"] != "N/A" && $data["neuro_amaurosis_fugax"] != "" && $data["neuro_amaurosis_fugax"] != "--") || ( $data["neuro_amaurosis_fugax_text"] != "" && $data["neuro_amaurosis_fugax_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Amaurosis Fugax:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_amaurosis_fugax']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_amaurosis_fugax_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_amaurosis_fugax_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_stroke"] != "N/A" && $data["neuro_stroke"] != "" && $data["neuro_stroke"] != "--") || ( $data["neuro_stroke_text"] != "" && $data["neuro_stroke_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Stroke:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_stroke']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_stroke_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_stroke_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_gait_abnormality"] != "N/A" && $data["neuro_gait_abnormality"] != "" && $data["neuro_gait_abnormality"] != "--") || ( $data["neuro_gait_abnormality_text"] != "" && $data["neuro_gait_abnormality_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Gait Abnormality:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_gait_abnormality']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_gait_abnormality_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_gait_abnormality_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_frequent_headaches"] != "N/A" && $data["neuro_frequent_headaches"] != "" && $data["neuro_frequent_headaches"] != "--") || ( $data["neuro_frequent_headaches_text"] != "" && $data["neuro_frequent_headaches_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Frequent headaches:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_frequent_headaches']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_frequent_headaches_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_frequent_headaches_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_parathesias"] != "N/A" && $data["neuro_parathesias"] != "" && $data["neuro_parathesias"] != "--") || ( $data["neuro_parathesias_text"] != "" && $data["neuro_parathesias_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Parathesias:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_parathesias']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_parathesias_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_parathesias_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_seizures"] != "N/A" && $data["neuro_seizures"] != "" && $data["neuro_seizures"] != "--") || ( $data["neuro_seizures_text"] != "" && $data["neuro_seizures_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Seizures:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_seizures']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_seizures_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_seizures_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_trans_ischemic_attacks"] != "N/A" && $data["neuro_trans_ischemic_attacks"] != "" && $data["neuro_trans_ischemic_attacks"] != "--") || ( $data["neuro_trans_ischemic_attacks_text"] != "" && $data["neuro_trans_ischemic_attacks_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Trans Ischemic Attacks:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_trans_ischemic_attacks']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_trans_ischemic_attacks_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_trans_ischemic_attacks_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neuro_significant_tremors"] != "N/A" && $data["neuro_significant_tremors"] != "" && $data["neuro_significant_tremors"] != "--") || ( $data["neuro_significant_tremors_text"] != "" && $data["neuro_significant_tremors_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Significant Tremors:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neuro_significant_tremors']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neuro_significant_tremors_text'] != null) {
                echo "<span class='text'>(" . text($data['neuro_significant_tremors_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>NECK:</u></span></td></tr>";
        if (($data["neck_neck_stiffness"] != "N/A" && $data["neck_neck_stiffness"] != "" && $data["neck_neck_stiffness"] != "--") || ( $data["neck_neck_stiffness_text"] != "" && $data["neck_neck_stiffness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>neck stiffness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neck_neck_stiffness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neck_neck_stiffness_text'] != null) {
                echo "<span class='text'>(" . text($data['neck_neck_stiffness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neck_neck_pain"] != "N/A" && $data["neck_neck_pain"] != "" && $data["neck_neck_pain"] != "--") || ( $data["neck_neck_pain_text"] != "" && $data["neck_neck_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>neck pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neck_neck_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neck_neck_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['neck_neck_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neck_neck_masses"] != "N/A" && $data["neck_neck_masses"] != "" && $data["neck_neck_masses"] != "--") || ( $data["neck_neck_masses_text"] != "" && $data["neck_neck_masses_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Neck Masses:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neck_neck_masses']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neck_neck_masses_text'] != null) {
                echo "<span class='text'>(" . text($data['neck_neck_masses_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["neck_neck_tenderness"] != "N/A" && $data["neck_neck_tenderness"] != "" && $data["neck_neck_tenderness"] != "--") || ( $data["neck_neck_tenderness_text"] != "" && $data["neck_neck_tenderness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Neck Tenderness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['neck_neck_tenderness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['neck_neck_tenderness_text'] != null) {
                echo "<span class='text'>(" . text($data['neck_neck_tenderness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>HEENT:</u></span></td></tr>";
        if (($data["heent_oral_ulcers"] != "N/A" && $data["heent_oral_ulcers"] != "" && $data["heent_oral_ulcers"] != "--") || ( $data["heent_oral_ulcers_text"] != "" && $data["heent_oral_ulcers_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>oral ulcers:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_oral_ulcers']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_oral_ulcers_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_oral_ulcers_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_excessive_cavities"] != "N/A" && $data["heent_excessive_cavities"] != "" && $data["heent_excessive_cavities"] != "--") || ( $data["heent_excessive_cavities_text"] != "" && $data["heent_excessive_cavities_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Excessive Cavities:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_excessive_cavities']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_excessive_cavities_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_excessive_cavities_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_gingival_disease"] != "N/A" && $data["heent_gingival_disease"] != "" && $data["heent_gingival_disease"] != "--") || ( $data["heent_gingival_disease_text"] != "" && $data["heent_gingival_disease_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Gingival Disease:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_gingival_disease']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_gingival_disease_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_gingival_disease_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_persistent_hoarseness"] != "N/A" && $data["heent_persistent_hoarseness"] != "" && $data["heent_persistent_hoarseness"] != "--") || ( $data["heent_persistent_hoarseness_text"] != "" && $data["heent_persistent_hoarseness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Persistent hoarseness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_persistent_hoarseness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_persistent_hoarseness_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_persistent_hoarseness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_mouth_lesions"] != "N/A" && $data["heent_mouth_lesions"] != "" && $data["heent_mouth_lesions"] != "--") || ( $data["heent_mouth_lesions_text"] != "" && $data["heent_mouth_lesions_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Mouth Lesions:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_mouth_lesions']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_mouth_lesions_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_mouth_lesions_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_dysphagia"] != "N/A" && $data["heent_dysphagia"] != "" && $data["heent_dysphagia"] != "--") || ( $data["heent_dysphagia_text"] != "" && $data["heent_dysphagia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Dysphagia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_dysphagia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_dysphagia_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_dysphagia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_odynophagia"] != "N/A" && $data["heent_odynophagia"] != "" && $data["heent_odynophagia"] != "--") || ( $data["heent_odynophagia_text"] != "" && $data["heent_odynophagia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Odynophagia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_odynophagia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_odynophagia_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_odynophagia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_dental_pain"] != "N/A" && $data["heent_dental_pain"] != "" && $data["heent_dental_pain"] != "--") || ( $data["heent_dental_pain_text"] != "" && $data["heent_dental_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>dental pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_dental_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_dental_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_dental_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_sore_throat"] != "N/A" && $data["heent_sore_throat"] != "" && $data["heent_sore_throat"] != "--") || ( $data["heent_sore_throat_text"] != "" && $data["heent_sore_throat_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>sore throat:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_sore_throat']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_sore_throat_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_sore_throat_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_ear_pain"] != "N/A" && $data["heent_ear_pain"] != "" && $data["heent_ear_pain"] != "--") || ( $data["heent_ear_pain_text"] != "" && $data["heent_ear_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>ear pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_ear_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_ear_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_ear_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_ear_discharge"] != "N/A" && $data["heent_ear_discharge"] != "" && $data["heent_ear_discharge"] != "--") || ( $data["heent_ear_discharge_text"] != "" && $data["heent_ear_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>ear discharge:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_ear_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_ear_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_ear_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_tinnitus"] != "N/A" && $data["heent_tinnitus"] != "" && $data["heent_tinnitus"] != "--") || ( $data["heent_tinnitus_text"] != "" && $data["heent_tinnitus_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>tinnitus:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_tinnitus']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_tinnitus_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_tinnitus_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_hearing_loss"] != "N/A" && $data["heent_hearing_loss"] != "" && $data["heent_hearing_loss"] != "--") || ( $data["heent_hearing_loss_text"] != "" && $data["heent_hearing_loss_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>hearing loss:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_hearing_loss']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_hearing_loss_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_hearing_loss_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_allergic_rhinitis"] != "N/A" && $data["heent_allergic_rhinitis"] != "" && $data["heent_allergic_rhinitis"] != "--") || ( $data["heent_allergic_rhinitis_text"] != "" && $data["heent_allergic_rhinitis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Allergic Rhinitis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_allergic_rhinitis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_allergic_rhinitis_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_allergic_rhinitis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_nasal_congestion"] != "N/A" && $data["heent_nasal_congestion"] != "" && $data["heent_nasal_congestion"] != "--") || ( $data["heent_nasal_congestion_text"] != "" && $data["heent_nasal_congestion_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nasal Congestion:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_nasal_congestion']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_nasal_congestion_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_nasal_congestion_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_nasal_discharge"] != "N/A" && $data["heent_nasal_discharge"] != "" && $data["heent_nasal_discharge"] != "--") || ( $data["heent_nasal_discharge_text"] != "" && $data["heent_nasal_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nasal Discharge:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_nasal_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_nasal_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_nasal_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_nasal_injury"] != "N/A" && $data["heent_nasal_injury"] != "" && $data["heent_nasal_injury"] != "--") || ( $data["heent_nasal_injury_text"] != "" && $data["heent_nasal_injury_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nasal Injury:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_nasal_injury']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_nasal_injury_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_nasal_injury_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_nasal_surgery"] != "N/A" && $data["heent_nasal_surgery"] != "" && $data["heent_nasal_surgery"] != "--") || ( $data["heent_nasal_surgery_text"] != "" && $data["heent_nasal_surgery_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nasal Surgery:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_nasal_surgery']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_nasal_surgery_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_nasal_surgery_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_nose_bleeds"] != "N/A" && $data["heent_nose_bleeds"] != "" && $data["heent_nose_bleeds"] != "--") || ( $data["heent_nose_bleeds_text"] != "" && $data["heent_nose_bleeds_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nose Bleeds:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_nose_bleeds']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_nose_bleeds_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_nose_bleeds_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_post_nasal_drip"] != "N/A" && $data["heent_post_nasal_drip"] != "" && $data["heent_post_nasal_drip"] != "--") || ( $data["heent_post_nasal_drip_text"] != "" && $data["heent_post_nasal_drip_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>post nasal drip:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_post_nasal_drip']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_post_nasal_drip_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_post_nasal_drip_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_sinus_pressure"] != "N/A" && $data["heent_sinus_pressure"] != "" && $data["heent_sinus_pressure"] != "--") || ( $data["heent_sinus_pressure_text"] != "" && $data["heent_sinus_pressure_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>sinus pressure:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_sinus_pressure']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_sinus_pressure_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_sinus_pressure_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_sinus_pain"] != "N/A" && $data["heent_sinus_pain"] != "" && $data["heent_sinus_pain"] != "--") || ( $data["heent_sinus_pain_text"] != "" && $data["heent_sinus_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>sinus pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_sinus_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_sinus_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_sinus_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_headache"] != "N/A" && $data["heent_headache"] != "" && $data["heent_headache"] != "--") || ( $data["heent_headache_text"] != "" && $data["heent_headache_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>headache:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_headache']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_headache_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_headache_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_eye_pain"] != "N/A" && $data["heent_eye_pain"] != "" && $data["heent_eye_pain"] != "--") || ( $data["heent_eye_pain_text"] != "" && $data["heent_eye_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>eye pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_eye_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_eye_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_eye_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_eye_redness"] != "N/A" && $data["heent_eye_redness"] != "" && $data["heent_eye_redness"] != "--") || ( $data["heent_eye_redness_text"] != "" && $data["heent_eye_redness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>eye redness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_eye_redness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_eye_redness_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_eye_redness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_visual_changes"] != "N/A" && $data["heent_visual_changes"] != "" && $data["heent_visual_changes"] != "--") || ( $data["heent_visual_changes_text"] != "" && $data["heent_visual_changes_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>visual changes:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_visual_changes']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_visual_changes_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_visual_changes_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_blurry_vision"] != "N/A" && $data["heent_blurry_vision"] != "" && $data["heent_blurry_vision"] != "--") || ( $data["heent_blurry_vision_text"] != "" && $data["heent_blurry_vision_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>blurry vision:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_blurry_vision']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_blurry_vision_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_blurry_vision_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_eye_discharge"] != "N/A" && $data["heent_eye_discharge"] != "" && $data["heent_eye_discharge"] != "--") || ( $data["heent_eye_discharge_text"] != "" && $data["heent_eye_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Eye Discharge:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_eye_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_eye_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_eye_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_eye_glasses_contacts"] != "N/A" && $data["heent_eye_glasses_contacts"] != "" && $data["heent_eye_glasses_contacts"] != "--") || ( $data["heent_eye_glasses_contacts_text"] != "" && $data["heent_eye_glasses_contacts_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Eye Glasses/ Contacts:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_eye_glasses_contacts']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_eye_glasses_contacts_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_eye_glasses_contacts_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_excess_tearing"] != "N/A" && $data["heent_excess_tearing"] != "" && $data["heent_excess_tearing"] != "--") || ( $data["heent_excess_tearing_text"] != "" && $data["heent_excess_tearing_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Excess Tearing:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_excess_tearing']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_excess_tearing_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_excess_tearing_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_photophobia"] != "N/A" && $data["heent_photophobia"] != "" && $data["heent_photophobia"] != "--") || ( $data["heent_photophobia_text"] != "" && $data["heent_photophobia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Photophobia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_photophobia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_photophobia_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_photophobia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_scotomata"] != "N/A" && $data["heent_scotomata"] != "" && $data["heent_scotomata"] != "--") || ( $data["heent_scotomata_text"] != "" && $data["heent_scotomata_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Scotomata:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_scotomata']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_scotomata_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_scotomata_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_tunnel_vision"] != "N/A" && $data["heent_tunnel_vision"] != "" && $data["heent_tunnel_vision"] != "--") || ( $data["heent_tunnel_vision_text"] != "" && $data["heent_tunnel_vision_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Tunnel vision:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_tunnel_vision']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_tunnel_vision_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_tunnel_vision_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["heent_glaucoma"] != "N/A" && $data["heent_glaucoma"] != "" && $data["heent_glaucoma"] != "--") || ( $data["heent_glaucoma_text"] != "" && $data["heent_glaucoma_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Glaucoma:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['heent_glaucoma']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['heent_glaucoma_text'] != null) {
                echo "<span class='text'>(" . text($data['heent_glaucoma_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>CARDIOVASCULAR: </u></span></td></tr>";
        if (($data["cardiovascular_sub_sternal_or_left_chest_pain"] != "N/A" && $data["cardiovascular_sub_sternal_or_left_chest_pain"] != "" && $data["cardiovascular_sub_sternal_or_left_chest_pain"] != "--") || ( $data["cardiovascular_sub_sternal_or_left_chest_pain_text"] != "" && $data["cardiovascular_sub_sternal_or_left_chest_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>sub sternal or left chest pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_sub_sternal_or_left_chest_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_sub_sternal_or_left_chest_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_sub_sternal_or_left_chest_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_other_chest_pain"] != "N/A" && $data["cardiovascular_other_chest_pain"] != "" && $data["cardiovascular_other_chest_pain"] != "--") || ( $data["cardiovascular_other_chest_pain_text"] != "" && $data["cardiovascular_other_chest_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>other chest pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_other_chest_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_other_chest_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_other_chest_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_palpitations"] != "N/A" && $data["cardiovascular_palpitations"] != "" && $data["cardiovascular_palpitations"] != "--") || ( $data["cardiovascular_palpitations_text"] != "" && $data["cardiovascular_palpitations_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>palpitations:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_palpitations']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_palpitations_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_palpitations_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_irregular_rhythm"] != "N/A" && $data["cardiovascular_irregular_rhythm"] != "" && $data["cardiovascular_irregular_rhythm"] != "--") || ( $data["cardiovascular_irregular_rhythm_text"] != "" && $data["cardiovascular_irregular_rhythm_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>irregular rhythm:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_irregular_rhythm']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_irregular_rhythm_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_irregular_rhythm_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_jugular_vein_distention"] != "N/A" && $data["cardiovascular_jugular_vein_distention"] != "" && $data["cardiovascular_jugular_vein_distention"] != "--") || ( $data["cardiovascular_jugular_vein_distention_text"] != "" && $data["cardiovascular_jugular_vein_distention_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>jugular vein distention:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_jugular_vein_distention']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_jugular_vein_distention_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_jugular_vein_distention_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_claudication"] != "N/A" && $data["cardiovascular_claudication"] != "" && $data["cardiovascular_claudication"] != "--") || ( $data["cardiovascular_claudication_text"] != "" && $data["cardiovascular_claudication_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Claudication:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_claudication']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_claudication_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_claudication_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_dizziness"] != "N/A" && $data["cardiovascular_dizziness"] != "" && $data["cardiovascular_dizziness"] != "--") || ( $data["cardiovascular_dizziness_text"] != "" && $data["cardiovascular_dizziness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Dizziness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_dizziness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_dizziness_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_dizziness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_dyspnea_on_exertion"] != "N/A" && $data["cardiovascular_dyspnea_on_exertion"] != "" && $data["cardiovascular_dyspnea_on_exertion"] != "--") || ( $data["cardiovascular_dyspnea_on_exertion_text"] != "" && $data["cardiovascular_dyspnea_on_exertion_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Dyspnea on Exertion:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_dyspnea_on_exertion']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_dyspnea_on_exertion_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_dyspnea_on_exertion_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_orthopnea"] != "N/A" && $data["cardiovascular_orthopnea"] != "" && $data["cardiovascular_orthopnea"] != "--") || ( $data["cardiovascular_orthopnea_text"] != "" && $data["cardiovascular_orthopnea_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Orthopnea:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_orthopnea']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_orthopnea_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_orthopnea_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_noctural_dyspnea"] != "N/A" && $data["cardiovascular_noctural_dyspnea"] != "" && $data["cardiovascular_noctural_dyspnea"] != "--") || ( $data["cardiovascular_noctural_dyspnea_text"] != "" && $data["cardiovascular_noctural_dyspnea_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Noctural Dyspnea:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_noctural_dyspnea']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_noctural_dyspnea_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_noctural_dyspnea_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_edema"] != "N/A" && $data["cardiovascular_edema"] != "" && $data["cardiovascular_edema"] != "--") || ( $data["cardiovascular_edema_text"] != "" && $data["cardiovascular_edema_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Edema:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_edema']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_edema_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_edema_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_presyncope"] != "N/A" && $data["cardiovascular_presyncope"] != "" && $data["cardiovascular_presyncope"] != "--") || ( $data["cardiovascular_presyncope_text"] != "" && $data["cardiovascular_presyncope_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Presyncope:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_presyncope']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_presyncope_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_presyncope_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_syncope"] != "N/A" && $data["cardiovascular_syncope"] != "" && $data["cardiovascular_syncope"] != "--") || ( $data["cardiovascular_syncope_text"] != "" && $data["cardiovascular_syncope_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Syncope:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_syncope']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_syncope_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_syncope_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_heart_murmur"] != "N/A" && $data["cardiovascular_heart_murmur"] != "" && $data["cardiovascular_heart_murmur"] != "--") || ( $data["cardiovascular_heart_murmur_text"] != "" && $data["cardiovascular_heart_murmur_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Heart Murmur:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_heart_murmur']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_heart_murmur_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_heart_murmur_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_raynauds"] != "N/A" && $data["cardiovascular_raynauds"] != "" && $data["cardiovascular_raynauds"] != "--") || ( $data["cardiovascular_raynauds_text"] != "" && $data["cardiovascular_raynauds_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Raynauds:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_raynauds']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_raynauds_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_raynauds_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_severe_varicose_veins"] != "N/A" && $data["cardiovascular_severe_varicose_veins"] != "" && $data["cardiovascular_severe_varicose_veins"] != "--") || ( $data["cardiovascular_severe_varicose_veins_text"] != "" && $data["cardiovascular_severe_varicose_veins_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Severe Varicose Veins:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_severe_varicose_veins']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_severe_varicose_veins_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_severe_varicose_veins_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_deep_vein_thrombosis"] != "N/A" && $data["cardiovascular_deep_vein_thrombosis"] != "" && $data["cardiovascular_deep_vein_thrombosis"] != "--") || ( $data["cardiovascular_deep_vein_thrombosis_text"] != "" && $data["cardiovascular_deep_vein_thrombosis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Deep Vein Thrombosis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_deep_vein_thrombosis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_deep_vein_thrombosis_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_deep_vein_thrombosis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["cardiovascular_thrombophlebitis"] != "N/A" && $data["cardiovascular_thrombophlebitis"] != "" && $data["cardiovascular_thrombophlebitis"] != "--") || ( $data["cardiovascular_thrombophlebitis_text"] != "" && $data["cardiovascular_thrombophlebitis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Thrombophlebitis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['cardiovascular_thrombophlebitis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['cardiovascular_thrombophlebitis_text'] != null) {
                echo "<span class='text'>(" . text($data['cardiovascular_thrombophlebitis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>RESPIRATIONS:</u></span></td></tr>";
        if (($data["respirations_cough"] != "N/A" && $data["respirations_cough"] != "" && $data["respirations_cough"] != "--") || ( $data["respirations_cough_text"] != "" && $data["respirations_cough_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>cough:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_cough']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_cough_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_cough_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_sputum"] != "N/A" && $data["respirations_sputum"] != "" && $data["respirations_sputum"] != "--") || ( $data["respirations_sputum_text"] != "" && $data["respirations_sputum_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>sputum:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_sputum']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_sputum_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_sputum_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_dyspnea"] != "N/A" && $data["respirations_dyspnea"] != "" && $data["respirations_dyspnea"] != "--") || ( $data["respirations_dyspnea_text"] != "" && $data["respirations_dyspnea_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>dyspnea:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_dyspnea']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_dyspnea_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_dyspnea_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_wheezes"] != "N/A" && $data["respirations_wheezes"] != "" && $data["respirations_wheezes"] != "--") || ( $data["respirations_wheezes_text"] != "" && $data["respirations_wheezes_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>wheezes:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_wheezes']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_wheezes_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_wheezes_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_rales"] != "N/A" && $data["respirations_rales"] != "" && $data["respirations_rales"] != "--") || ( $data["respirations_rales_text"] != "" && $data["respirations_rales_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>rales:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_rales']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_rales_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_rales_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_labored_breathing"] != "N/A" && $data["respirations_labored_breathing"] != "" && $data["respirations_labored_breathing"] != "--") || ( $data["respirations_labored_breathing_text"] != "" && $data["respirations_labored_breathing_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>labored breathing:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_labored_breathing']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_labored_breathing_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_labored_breathing_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["respirations_hemoptysis"] != "N/A" && $data["respirations_hemoptysis"] != "" && $data["respirations_hemoptysis"] != "--") || ( $data["respirations_hemoptysis_text"] != "" && $data["respirations_hemoptysis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hemoptysis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['respirations_hemoptysis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['respirations_hemoptysis_text'] != null) {
                echo "<span class='text'>(" . text($data['respirations_hemoptysis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>GU</u></span></td></tr>";
        if (($data["gu_frequent_urination"] != "N/A" && $data["gu_frequent_urination"] != "" && $data["gu_frequent_urination"] != "--") || ( $data["gu_frequent_urination_text"] != "" && $data["gu_frequent_urination_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>frequent urination:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_frequent_urination']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_frequent_urination_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_frequent_urination_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_dysuria"] != "N/A" && $data["gu_dysuria"] != "" && $data["gu_dysuria"] != "--") || ( $data["gu_dysuria_text"] != "" && $data["gu_dysuria_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>dysuria:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_dysuria']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_dysuria_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_dysuria_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_dyspareunia"] != "N/A" && $data["gu_dyspareunia"] != "" && $data["gu_dyspareunia"] != "--") || ( $data["gu_dyspareunia_text"] != "" && $data["gu_dyspareunia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>dyspareunia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_dyspareunia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_dyspareunia_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_dyspareunia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_discharge"] != "N/A" && $data["gu_discharge"] != "" && $data["gu_discharge"] != "--") || ( $data["gu_discharge_text"] != "" && $data["gu_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>discharge:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_odor"] != "N/A" && $data["gu_odor"] != "" && $data["gu_odor"] != "--") || ( $data["gu_odor_text"] != "" && $data["gu_odor_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>odor:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_odor']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_odor_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_odor_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_fertility_problems"] != "N/A" && $data["gu_fertility_problems"] != "" && $data["gu_fertility_problems"] != "--") || ( $data["gu_fertility_problems_text"] != "" && $data["gu_fertility_problems_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>fertility problems:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_fertility_problems']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_fertility_problems_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_fertility_problems_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_flank_pain_kidney_stone"] != "N/A" && $data["gu_flank_pain_kidney_stone"] != "" && $data["gu_flank_pain_kidney_stone"] != "--") || ( $data["gu_flank_pain_kidney_stone_text"] != "" && $data["gu_flank_pain_kidney_stone_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Flank Pain Kidney Stone:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_flank_pain_kidney_stone']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_flank_pain_kidney_stone_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_flank_pain_kidney_stone_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_polyuria"] != "N/A" && $data["gu_polyuria"] != "" && $data["gu_polyuria"] != "--") || ( $data["gu_polyuria_text"] != "" && $data["gu_polyuria_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Polyuria:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_polyuria']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_polyuria_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_polyuria_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_hematuria"] != "N/A" && $data["gu_hematuria"] != "" && $data["gu_hematuria"] != "--") || ( $data["gu_hematuria_text"] != "" && $data["gu_hematuria_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hematuria:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_hematuria']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_hematuria_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_hematuria_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_pyuria"] != "N/A" && $data["gu_pyuria"] != "" && $data["gu_pyuria"] != "--") || ( $data["gu_pyuria_text"] != "" && $data["gu_pyuria_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Pyuria:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_pyuria']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_pyuria_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_pyuria_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_umbilical_hernia"] != "N/A" && $data["gu_umbilical_hernia"] != "" && $data["gu_umbilical_hernia"] != "--") || ( $data["gu_umbilical_hernia_text"] != "" && $data["gu_umbilical_hernia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Umbilical Hernia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_umbilical_hernia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_umbilical_hernia_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_umbilical_hernia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_incontinence"] != "N/A" && $data["gu_incontinence"] != "" && $data["gu_incontinence"] != "--") || ( $data["gu_incontinence_text"] != "" && $data["gu_incontinence_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Incontinence:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_incontinence']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_incontinence_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_incontinence_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_nocturia"] != "N/A" && $data["gu_nocturia"] != "" && $data["gu_nocturia"] != "--") || ( $data["gu_nocturia_text"] != "" && $data["gu_nocturia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nocturia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_nocturia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_nocturia_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_nocturia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_urinary_urgency"] != "N/A" && $data["gu_urinary_urgency"] != "" && $data["gu_urinary_urgency"] != "--") || ( $data["gu_urinary_urgency_text"] != "" && $data["gu_urinary_urgency_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Urinary Urgency:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_urinary_urgency']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_urinary_urgency_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_urinary_urgency_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_recurrent_utis"] != "N/A" && $data["gu_recurrent_utis"] != "" && $data["gu_recurrent_utis"] != "--") || ( $data["gu_recurrent_utis_text"] != "" && $data["gu_recurrent_utis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Recurrent UTIs:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_recurrent_utis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_recurrent_utis_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_recurrent_utis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gu_venereal_disease"] != "N/A" && $data["gu_venereal_disease"] != "" && $data["gu_venereal_disease"] != "--") || ( $data["gu_venereal_disease_text"] != "" && $data["gu_venereal_disease_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Venereal Disease:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gu_venereal_disease']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gu_venereal_disease_text'] != null) {
                echo "<span class='text'>(" . text($data['gu_venereal_disease_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>MALE GU</u></span></td></tr>";
        if (($data["male_gu_erectile_dysfunction"] != "N/A" && $data["male_gu_erectile_dysfunction"] != "" && $data["male_gu_erectile_dysfunction"] != "--") || ( $data["male_gu_erectile_dysfunction_text"] != "" && $data["male_gu_erectile_dysfunction_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Erectile Dysfunction:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_erectile_dysfunction']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_erectile_dysfunction_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_erectile_dysfunction_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_inguinal_hernia"] != "N/A" && $data["male_gu_inguinal_hernia"] != "" && $data["male_gu_inguinal_hernia"] != "--") || ( $data["male_gu_inguinal_hernia_text"] != "" && $data["male_gu_inguinal_hernia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Inguinal Hernia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_inguinal_hernia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_inguinal_hernia_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_inguinal_hernia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_penile_lesions"] != "N/A" && $data["male_gu_penile_lesions"] != "" && $data["male_gu_penile_lesions"] != "--") || ( $data["male_gu_penile_lesions_text"] != "" && $data["male_gu_penile_lesions_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Penile Lesions:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_penile_lesions']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_penile_lesions_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_penile_lesions_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_scrotal_mass"] != "N/A" && $data["male_gu_scrotal_mass"] != "" && $data["male_gu_scrotal_mass"] != "--") || ( $data["male_gu_scrotal_mass_text"] != "" && $data["male_gu_scrotal_mass_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Scrotal Mass:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_scrotal_mass']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_scrotal_mass_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_scrotal_mass_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_testicular_pain"] != "N/A" && $data["male_gu_testicular_pain"] != "" && $data["male_gu_testicular_pain"] != "--") || ( $data["male_gu_testicular_pain_text"] != "" && $data["male_gu_testicular_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Testicular Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_testicular_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_testicular_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_testicular_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_urethral_discharge"] != "N/A" && $data["male_gu_urethral_discharge"] != "" && $data["male_gu_urethral_discharge"] != "--") || ( $data["male_gu_urethral_discharge_text"] != "" && $data["male_gu_urethral_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Urethral Discharge:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_urethral_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_urethral_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_urethral_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["male_gu_weak_urinary_stream"] != "N/A" && $data["male_gu_weak_urinary_stream"] != "" && $data["male_gu_weak_urinary_stream"] != "--") || ( $data["male_gu_weak_urinary_stream_text"] != "" && $data["male_gu_weak_urinary_stream_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Weak Urinary Stream:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['male_gu_weak_urinary_stream']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['male_gu_weak_urinary_stream_text'] != null) {
                echo "<span class='text'>(" . text($data['male_gu_weak_urinary_stream_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>FEMALE GU</u></span></td></tr>";
        if (($data["female_gu_abnormal_menses"] != "N/A" && $data["female_gu_abnormal_menses"] != "" && $data["female_gu_abnormal_menses"] != "--") || ( $data["female_gu_abnormal_menses_text"] != "" && $data["female_gu_abnormal_menses_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Abnormal Menses:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['female_gu_abnormal_menses']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['female_gu_abnormal_menses_text'] != null) {
                echo "<span class='text'>(" . text($data['female_gu_abnormal_menses_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["female_gu_abnormal_vaginal_bleeding"] != "N/A" && $data["female_gu_abnormal_vaginal_bleeding"] != "" && $data["female_gu_abnormal_vaginal_bleeding"] != "--") || ( $data["female_gu_abnormal_vaginal_bleeding_text"] != "" && $data["female_gu_abnormal_vaginal_bleeding_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Abnormal Vaginal Bleeding:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['female_gu_abnormal_vaginal_bleeding']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['female_gu_abnormal_vaginal_bleeding_text'] != null) {
                echo "<span class='text'>(" . text($data['female_gu_abnormal_vaginal_bleeding_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["female_gu_vaginal_discharge"] != "N/A" && $data["female_gu_vaginal_discharge"] != "" && $data["female_gu_vaginal_discharge"] != "--") || ( $data["female_gu_vaginal_discharge_text"] != "" && $data["female_gu_vaginal_discharge_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Vaginal Discharge :<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['female_gu_vaginal_discharge']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['female_gu_vaginal_discharge_text'] != null) {
                echo "<span class='text'>(" . text($data['female_gu_vaginal_discharge_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>GI</u></span></td></tr>";
        if (($data["gi_abdominal_pain"] != "N/A" && $data["gi_abdominal_pain"] != "" && $data["gi_abdominal_pain"] != "--") || ( $data["gi_abdominal_pain_text"] != "" && $data["gi_abdominal_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>abdominal pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_abdominal_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_abdominal_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_abdominal_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_cramps"] != "N/A" && $data["gi_cramps"] != "" && $data["gi_cramps"] != "--") || ( $data["gi_cramps_text"] != "" && $data["gi_cramps_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>cramps:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_cramps']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_cramps_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_cramps_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_tenderness"] != "N/A" && $data["gi_tenderness"] != "" && $data["gi_tenderness"] != "--") || ( $data["gi_tenderness_text"] != "" && $data["gi_tenderness_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>tenderness:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_tenderness']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_tenderness_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_tenderness_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_vomiting"] != "N/A" && $data["gi_vomiting"] != "" && $data["gi_vomiting"] != "--") || ( $data["gi_vomiting_text"] != "" && $data["gi_vomiting_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>vomiting:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_vomiting']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_vomiting_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_vomiting_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_frequent_diarrhea"] != "N/A" && $data["gi_frequent_diarrhea"] != "" && $data["gi_frequent_diarrhea"] != "--") || ( $data["gi_frequent_diarrhea_text"] != "" && $data["gi_frequent_diarrhea_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>frequent diarrhea:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_frequent_diarrhea']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_frequent_diarrhea_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_frequent_diarrhea_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_significant_constipation"] != "N/A" && $data["gi_significant_constipation"] != "" && $data["gi_significant_constipation"] != "--") || ( $data["gi_significant_constipation_text"] != "" && $data["gi_significant_constipation_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>significant constipation:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_significant_constipation']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_significant_constipation_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_significant_constipation_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_excessive_belching"] != "N/A" && $data["gi_excessive_belching"] != "" && $data["gi_excessive_belching"] != "--") || ( $data["gi_excessive_belching_text"] != "" && $data["gi_excessive_belching_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Excessive Belching:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_excessive_belching']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_excessive_belching_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_excessive_belching_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_changed_bowel_habits"] != "N/A" && $data["gi_changed_bowel_habits"] != "" && $data["gi_changed_bowel_habits"] != "--") || ( $data["gi_changed_bowel_habits_text"] != "" && $data["gi_changed_bowel_habits_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Changed Bowel Habits:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_changed_bowel_habits']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_changed_bowel_habits_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_changed_bowel_habits_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_excessive_flatulence"] != "N/A" && $data["gi_excessive_flatulence"] != "" && $data["gi_excessive_flatulence"] != "--") || ( $data["gi_excessive_flatulence_text"] != "" && $data["gi_excessive_flatulence_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Excessive Flatulence:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_excessive_flatulence']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_excessive_flatulence_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_excessive_flatulence_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_hematemesis"] != "N/A" && $data["gi_hematemesis"] != "" && $data["gi_hematemesis"] != "--") || ( $data["gi_hematemesis_text"] != "" && $data["gi_hematemesis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hematemesis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_hematemesis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_hematemesis_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_hematemesis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_hemorrhoids"] != "N/A" && $data["gi_hemorrhoids"] != "" && $data["gi_hemorrhoids"] != "--") || ( $data["gi_hemorrhoids_text"] != "" && $data["gi_hemorrhoids_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hemorrhoids:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_hemorrhoids']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_hemorrhoids_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_hemorrhoids_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_hepatitis"] != "N/A" && $data["gi_hepatitis"] != "" && $data["gi_hepatitis"] != "--") || ( $data["gi_hepatitis_text"] != "" && $data["gi_hepatitis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hepatitis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_hepatitis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_hepatitis_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_hepatitis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_jaundice"] != "N/A" && $data["gi_jaundice"] != "" && $data["gi_jaundice"] != "--") || ( $data["gi_jaundice_text"] != "" && $data["gi_jaundice_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Jaundice:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_jaundice']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_jaundice_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_jaundice_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_lactose_intolerance"] != "N/A" && $data["gi_lactose_intolerance"] != "" && $data["gi_lactose_intolerance"] != "--") || ( $data["gi_lactose_intolerance_text"] != "" && $data["gi_lactose_intolerance_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Lactose Intolerance:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_lactose_intolerance']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_lactose_intolerance_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_lactose_intolerance_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_chronic_laxative_use"] != "N/A" && $data["gi_chronic_laxative_use"] != "" && $data["gi_chronic_laxative_use"] != "--") || ( $data["gi_chronic_laxative_use_text"] != "" && $data["gi_chronic_laxative_use_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Chronic Laxative Use:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_chronic_laxative_use']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_chronic_laxative_use_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_chronic_laxative_use_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_melena"] != "N/A" && $data["gi_melena"] != "" && $data["gi_melena"] != "--") || ( $data["gi_melena_text"] != "" && $data["gi_melena_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Melena:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_melena']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_melena_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_melena_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_frequent_nausea"] != "N/A" && $data["gi_frequent_nausea"] != "" && $data["gi_frequent_nausea"] != "--") || ( $data["gi_frequent_nausea_text"] != "" && $data["gi_frequent_nausea_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Frequent Nausea:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_frequent_nausea']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_frequent_nausea_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_frequent_nausea_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_rectal_bleeding"] != "N/A" && $data["gi_rectal_bleeding"] != "" && $data["gi_rectal_bleeding"] != "--") || ( $data["gi_rectal_bleeding_text"] != "" && $data["gi_rectal_bleeding_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Rectal Bleeding:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_rectal_bleeding']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_rectal_bleeding_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_rectal_bleeding_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_rectal_pain"] != "N/A" && $data["gi_rectal_pain"] != "" && $data["gi_rectal_pain"] != "--") || ( $data["gi_rectal_pain_text"] != "" && $data["gi_rectal_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Rectal Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_rectal_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_rectal_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_rectal_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["gi_stool_caliber_change"] != "N/A" && $data["gi_stool_caliber_change"] != "" && $data["gi_stool_caliber_change"] != "--") || ( $data["gi_stool_caliber_change_text"] != "" && $data["gi_stool_caliber_change_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Stool Caliber Change:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['gi_stool_caliber_change']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['gi_stool_caliber_change_text'] != null) {
                echo "<span class='text'>(" . text($data['gi_stool_caliber_change_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>INTEGUMENT:</u></span></td></tr>";
        if (($data["integument_pallor"] != "N/A" && $data["integument_pallor"] != "" && $data["integument_pallor"] != "--") || ( $data["integument_pallor_text"] != "" && $data["integument_pallor_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>pallor:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_pallor']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_pallor_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_pallor_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_diaphoresis"] != "N/A" && $data["integument_diaphoresis"] != "" && $data["integument_diaphoresis"] != "--") || ( $data["integument_diaphoresis_text"] != "" && $data["integument_diaphoresis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>diaphoresis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_diaphoresis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_diaphoresis_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_diaphoresis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_rash"] != "N/A" && $data["integument_rash"] != "" && $data["integument_rash"] != "--") || ( $data["integument_rash_text"] != "" && $data["integument_rash_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>rash:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_rash']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_rash_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_rash_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_itching"] != "N/A" && $data["integument_itching"] != "" && $data["integument_itching"] != "--") || ( $data["integument_itching_text"] != "" && $data["integument_itching_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>itching:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_itching']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_itching_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_itching_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_ulcers"] != "N/A" && $data["integument_ulcers"] != "" && $data["integument_ulcers"] != "--") || ( $data["integument_ulcers_text"] != "" && $data["integument_ulcers_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>ulcers:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_ulcers']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_ulcers_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_ulcers_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_abscess"] != "N/A" && $data["integument_abscess"] != "" && $data["integument_abscess"] != "--") || ( $data["integument_abscess_text"] != "" && $data["integument_abscess_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>abscess:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_abscess']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_abscess_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_abscess_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_nodules"] != "N/A" && $data["integument_nodules"] != "" && $data["integument_nodules"] != "--") || ( $data["integument_nodules_text"] != "" && $data["integument_nodules_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>nodules:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_nodules']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_nodules_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_nodules_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_acne"] != "N/A" && $data["integument_acne"] != "" && $data["integument_acne"] != "--") || ( $data["integument_acne_text"] != "" && $data["integument_acne_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Acne:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_acne']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_acne_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_acne_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_recurrent_boils"] != "N/A" && $data["integument_recurrent_boils"] != "" && $data["integument_recurrent_boils"] != "--") || ( $data["integument_recurrent_boils_text"] != "" && $data["integument_recurrent_boils_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Recurrent Boils:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_recurrent_boils']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_recurrent_boils_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_recurrent_boils_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_chronic_eczema"] != "N/A" && $data["integument_chronic_eczema"] != "" && $data["integument_chronic_eczema"] != "--") || ( $data["integument_chronic_eczema_text"] != "" && $data["integument_chronic_eczema_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Chronic Eczema:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_chronic_eczema']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_chronic_eczema_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_chronic_eczema_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_changing_moles"] != "N/A" && $data["integument_changing_moles"] != "" && $data["integument_changing_moles"] != "--") || ( $data["integument_changing_moles_text"] != "" && $data["integument_changing_moles_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Changing Moles:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_changing_moles']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_changing_moles_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_changing_moles_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_nail_abnormalities"] != "N/A" && $data["integument_nail_abnormalities"] != "" && $data["integument_nail_abnormalities"] != "--") || ( $data["integument_nail_abnormalities_text"] != "" && $data["integument_nail_abnormalities_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Nail Abnormalities:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_nail_abnormalities']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_nail_abnormalities_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_nail_abnormalities_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_psoriasis"] != "N/A" && $data["integument_psoriasis"] != "" && $data["integument_psoriasis"] != "--") || ( $data["integument_psoriasis_text"] != "" && $data["integument_psoriasis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Psoriasis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_psoriasis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_psoriasis_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_psoriasis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["integument_recurrent_hives"] != "N/A" && $data["integument_recurrent_hives"] != "" && $data["integument_recurrent_hives"] != "--") || ( $data["integument_recurrent_hives_text"] != "" && $data["integument_recurrent_hives_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Recurrent Hives:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['integument_recurrent_hives']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['integument_recurrent_hives_text'] != null) {
                echo "<span class='text'>(" . text($data['integument_recurrent_hives_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>MUSCULOSKELETAL:</u></span></td></tr>";
        if (($data["musculoskeletal_deformity"] != "N/A" && $data["musculoskeletal_deformity"] != "" && $data["musculoskeletal_deformity"] != "--") || ( $data["musculoskeletal_deformity_text"] != "" && $data["musculoskeletal_deformity_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>deformity:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_deformity']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_deformity_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_deformity_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_edema"] != "N/A" && $data["musculoskeletal_edema"] != "" && $data["musculoskeletal_edema"] != "--") || ( $data["musculoskeletal_edema_text"] != "" && $data["musculoskeletal_edema_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>edema:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_edema']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_edema_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_edema_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_pain"] != "N/A" && $data["musculoskeletal_pain"] != "" && $data["musculoskeletal_pain"] != "--") || ( $data["musculoskeletal_pain_text"] != "" && $data["musculoskeletal_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_limited_rom"] != "N/A" && $data["musculoskeletal_limited_rom"] != "" && $data["musculoskeletal_limited_rom"] != "--") || ( $data["musculoskeletal_limited_rom_text"] != "" && $data["musculoskeletal_limited_rom_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>limited ROM:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_limited_rom']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_limited_rom_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_limited_rom_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_gait"] != "N/A" && $data["musculoskeletal_gait"] != "" && $data["musculoskeletal_gait"] != "--") || ( $data["musculoskeletal_gait_text"] != "" && $data["musculoskeletal_gait_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>gait:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_gait']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_gait_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_gait_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_arthritis"] != "N/A" && $data["musculoskeletal_arthritis"] != "" && $data["musculoskeletal_arthritis"] != "--") || ( $data["musculoskeletal_arthritis_text"] != "" && $data["musculoskeletal_arthritis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Arthritis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_arthritis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_arthritis_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_arthritis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_neck_pain"] != "N/A" && $data["musculoskeletal_neck_pain"] != "" && $data["musculoskeletal_neck_pain"] != "--") || ( $data["musculoskeletal_neck_pain_text"] != "" && $data["musculoskeletal_neck_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Neck Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_neck_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_neck_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_neck_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_mid_back_pain"] != "N/A" && $data["musculoskeletal_mid_back_pain"] != "" && $data["musculoskeletal_mid_back_pain"] != "--") || ( $data["musculoskeletal_mid_back_pain_text"] != "" && $data["musculoskeletal_mid_back_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Mid Back Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_mid_back_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_mid_back_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_mid_back_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_low_back_pain"] != "N/A" && $data["musculoskeletal_low_back_pain"] != "" && $data["musculoskeletal_low_back_pain"] != "--") || ( $data["musculoskeletal_low_back_pain_text"] != "" && $data["musculoskeletal_low_back_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Low Back Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_low_back_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_low_back_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_low_back_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_bursitis"] != "N/A" && $data["musculoskeletal_bursitis"] != "" && $data["musculoskeletal_bursitis"] != "--") || ( $data["musculoskeletal_bursitis_text"] != "" && $data["musculoskeletal_bursitis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Bursitis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_bursitis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_bursitis_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_bursitis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_gout"] != "N/A" && $data["musculoskeletal_gout"] != "" && $data["musculoskeletal_gout"] != "--") || ( $data["musculoskeletal_gout_text"] != "" && $data["musculoskeletal_gout_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Gout:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_gout']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_gout_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_gout_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_joint_injury"] != "N/A" && $data["musculoskeletal_joint_injury"] != "" && $data["musculoskeletal_joint_injury"] != "--") || ( $data["musculoskeletal_joint_injury_text"] != "" && $data["musculoskeletal_joint_injury_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Joint Injury:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_joint_injury']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_joint_injury_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_joint_injury_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_joint_pain"] != "N/A" && $data["musculoskeletal_joint_pain"] != "" && $data["musculoskeletal_joint_pain"] != "--") || ( $data["musculoskeletal_joint_pain_text"] != "" && $data["musculoskeletal_joint_pain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Joint Pain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_joint_pain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_joint_pain_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_joint_pain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_joint_swelling"] != "N/A" && $data["musculoskeletal_joint_swelling"] != "" && $data["musculoskeletal_joint_swelling"] != "--") || ( $data["musculoskeletal_joint_swelling_text"] != "" && $data["musculoskeletal_joint_swelling_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Joint Swelling:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_joint_swelling']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_joint_swelling_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_joint_swelling_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_myalgias"] != "N/A" && $data["musculoskeletal_myalgias"] != "" && $data["musculoskeletal_myalgias"] != "--") || ( $data["musculoskeletal_myalgias_text"] != "" && $data["musculoskeletal_myalgias_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Myalgias:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_myalgias']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_myalgias_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_myalgias_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_sciatica"] != "N/A" && $data["musculoskeletal_sciatica"] != "" && $data["musculoskeletal_sciatica"] != "--") || ( $data["musculoskeletal_sciatica_text"] != "" && $data["musculoskeletal_sciatica_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Sciatica:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_sciatica']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_sciatica_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_sciatica_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["musculoskeletal_scoliosis"] != "N/A" && $data["musculoskeletal_scoliosis"] != "" && $data["musculoskeletal_scoliosis"] != "--") || ( $data["musculoskeletal_scoliosis_text"] != "" && $data["musculoskeletal_scoliosis_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Scoliosis:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['musculoskeletal_scoliosis']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['musculoskeletal_scoliosis_text'] != null) {
                echo "<span class='text'>(" . text($data['musculoskeletal_scoliosis_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>HEMATOLOGICAL</u></span></td></tr>";
        if (($data["hematological_anemia"] != "N/A" && $data["hematological_anemia"] != "" && $data["hematological_anemia"] != "--") || ( $data["hematological_anemia_text"] != "" && $data["hematological_anemia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Anemia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['hematological_anemia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['hematological_anemia_text'] != null) {
                echo "<span class='text'>(" . text($data['hematological_anemia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["hematological_pallor"] != "N/A" && $data["hematological_pallor"] != "" && $data["hematological_pallor"] != "--") || ( $data["hematological_pallor_text"] != "" && $data["hematological_pallor_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Pallor:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['hematological_pallor']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['hematological_pallor_text'] != null) {
                echo "<span class='text'>(" . text($data['hematological_pallor_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["hematological_bleeding_tendencies"] != "N/A" && $data["hematological_bleeding_tendencies"] != "" && $data["hematological_bleeding_tendencies"] != "--") || ( $data["hematological_bleeding_tendencies_text"] != "" && $data["hematological_bleeding_tendencies_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Bleeding Tendencies:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['hematological_bleeding_tendencies']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['hematological_bleeding_tendencies_text'] != null) {
                echo "<span class='text'>(" . text($data['hematological_bleeding_tendencies_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["hematological_bruising"] != "N/A" && $data["hematological_bruising"] != "" && $data["hematological_bruising"] != "--") || ( $data["hematological_bruising_text"] != "" && $data["hematological_bruising_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Bruising:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['hematological_bruising']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['hematological_bruising_text'] != null) {
                echo "<span class='text'>(" . text($data['hematological_bruising_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>ENDOCRINE</u></span></td></tr>";
        if (($data["endocrine_thyroid_problems"] != "N/A" && $data["endocrine_thyroid_problems"] != "" && $data["endocrine_thyroid_problems"] != "--") || ( $data["endocrine_thyroid_problems_text"] != "" && $data["endocrine_thyroid_problems_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Thyroid Problems:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_thyroid_problems']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_thyroid_problems_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_thyroid_problems_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_enlarged_thyroid"] != "N/A" && $data["endocrine_enlarged_thyroid"] != "" && $data["endocrine_enlarged_thyroid"] != "--") || ( $data["endocrine_enlarged_thyroid_text"] != "" && $data["endocrine_enlarged_thyroid_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Enlarged Thyroid:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_enlarged_thyroid']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_enlarged_thyroid_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_enlarged_thyroid_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_hyperglycemia"] != "N/A" && $data["endocrine_hyperglycemia"] != "" && $data["endocrine_hyperglycemia"] != "--") || ( $data["endocrine_hyperglycemia_text"] != "" && $data["endocrine_hyperglycemia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hyperglycemia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_hyperglycemia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_hyperglycemia_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_hyperglycemia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_hypoglycemia"] != "N/A" && $data["endocrine_hypoglycemia"] != "" && $data["endocrine_hypoglycemia"] != "--") || ( $data["endocrine_hypoglycemia_text"] != "" && $data["endocrine_hypoglycemia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hypoglycemia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_hypoglycemia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_hypoglycemia_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_hypoglycemia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_cold_intolerance"] != "N/A" && $data["endocrine_cold_intolerance"] != "" && $data["endocrine_cold_intolerance"] != "--") || ( $data["endocrine_cold_intolerance_text"] != "" && $data["endocrine_cold_intolerance_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Cold Intolerance:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_cold_intolerance']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_cold_intolerance_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_cold_intolerance_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_heat_intolerance"] != "N/A" && $data["endocrine_heat_intolerance"] != "" && $data["endocrine_heat_intolerance"] != "--") || ( $data["endocrine_heat_intolerance_text"] != "" && $data["endocrine_heat_intolerance_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Heat Intolerance:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_heat_intolerance']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_heat_intolerance_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_heat_intolerance_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_early_awakening"] != "N/A" && $data["endocrine_early_awakening"] != "" && $data["endocrine_early_awakening"] != "--") || ( $data["endocrine_early_awakening_text"] != "" && $data["endocrine_early_awakening_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Early Awakening:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_early_awakening']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_early_awakening_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_early_awakening_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_fatigue_unexplained"] != "N/A" && $data["endocrine_fatigue_unexplained"] != "" && $data["endocrine_fatigue_unexplained"] != "--") || ( $data["endocrine_fatigue_unexplained_text"] != "" && $data["endocrine_fatigue_unexplained_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Fatigue unexplained:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_fatigue_unexplained']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_fatigue_unexplained_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_fatigue_unexplained_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_weight_gain"] != "N/A" && $data["endocrine_weight_gain"] != "" && $data["endocrine_weight_gain"] != "--") || ( $data["endocrine_weight_gain_text"] != "" && $data["endocrine_weight_gain_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Weight Gain:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_weight_gain']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_weight_gain_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_weight_gain_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_weight_loss"] != "N/A" && $data["endocrine_weight_loss"] != "" && $data["endocrine_weight_loss"] != "--") || ( $data["endocrine_weight_loss_text"] != "" && $data["endocrine_weight_loss_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Weight Loss:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_weight_loss']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_weight_loss_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_weight_loss_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_premenstrual_symptoms"] != "N/A" && $data["endocrine_premenstrual_symptoms"] != "" && $data["endocrine_premenstrual_symptoms"] != "--") || ( $data["endocrine_premenstrual_symptoms_text"] != "" && $data["endocrine_premenstrual_symptoms_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Premenstrual symptoms:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_premenstrual_symptoms']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_premenstrual_symptoms_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_premenstrual_symptoms_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_hair_no_change_or_no_loss"] != "N/A" && $data["endocrine_hair_no_change_or_no_loss"] != "" && $data["endocrine_hair_no_change_or_no_loss"] != "--") || ( $data["endocrine_hair_no_change_or_no_loss_text"] != "" && $data["endocrine_hair_no_change_or_no_loss_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hair (no change or no loss):<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_hair_no_change_or_no_loss']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_hair_no_change_or_no_loss_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_hair_no_change_or_no_loss_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["endocrine_hot_flashes"] != "N/A" && $data["endocrine_hot_flashes"] != "" && $data["endocrine_hot_flashes"] != "--") || ( $data["endocrine_hot_flashes_text"] != "" && $data["endocrine_hot_flashes_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hot flashes:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['endocrine_hot_flashes']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['endocrine_hot_flashes_text'] != null) {
                echo "<span class='text'>(" . text($data['endocrine_hot_flashes_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>LYMPHATIC</u></span></td></tr>";
        if (($data["lymphatic_swollen_lymph_nodes"] != "N/A" && $data["lymphatic_swollen_lymph_nodes"] != "" && $data["lymphatic_swollen_lymph_nodes"] != "--") || ( $data["lymphatic_swollen_lymph_nodes_text"] != "" && $data["lymphatic_swollen_lymph_nodes_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Swollen lymph nodes:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['lymphatic_swollen_lymph_nodes']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['lymphatic_swollen_lymph_nodes_text'] != null) {
                echo "<span class='text'>(" . text($data['lymphatic_swollen_lymph_nodes_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["lymphatic_swollen_extremities"] != "N/A" && $data["lymphatic_swollen_extremities"] != "" && $data["lymphatic_swollen_extremities"] != "--") || ( $data["lymphatic_swollen_extremities_text"] != "" && $data["lymphatic_swollen_extremities_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Swollen extremities:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['lymphatic_swollen_extremities']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['lymphatic_swollen_extremities_text'] != null) {
                echo "<span class='text'>(" . text($data['lymphatic_swollen_extremities_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='3'><span class='bold'><u>PSYCHIATRIC</u></span></td></tr>";
        if (($data["psychiatric_compulsions"] != "N/A" && $data["psychiatric_compulsions"] != "" && $data["psychiatric_compulsions"] != "--") || ( $data["psychiatric_compulsions_text"] != "" && $data["psychiatric_compulsions_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Compulsions:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_compulsions']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_compulsions_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_compulsions_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_depression"] != "N/A" && $data["psychiatric_depression"] != "" && $data["psychiatric_depression"] != "--") || ( $data["psychiatric_depression_text"] != "" && $data["psychiatric_depression_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Depression:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_depression']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_depression_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_depression_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_fear"] != "N/A" && $data["psychiatric_fear"] != "" && $data["psychiatric_fear"] != "--") || ( $data["psychiatric_fear_text"] != "" && $data["psychiatric_fear_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Fear:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_fear']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_fear_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_fear_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_anxiety"] != "N/A" && $data["psychiatric_anxiety"] != "" && $data["psychiatric_anxiety"] != "--") || ( $data["psychiatric_anxiety_text"] != "" && $data["psychiatric_anxiety_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Anxiety:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_anxiety']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_anxiety_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_anxiety_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_hallucinations"] != "N/A" && $data["psychiatric_hallucinations"] != "" && $data["psychiatric_hallucinations"] != "--") || ( $data["psychiatric_hallucinations_text"] != "" && $data["psychiatric_hallucinations_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Hallucinations:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_hallucinations']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_hallucinations_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_hallucinations_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_loss_of_interest"] != "N/A" && $data["psychiatric_loss_of_interest"] != "" && $data["psychiatric_loss_of_interest"] != "--") || ( $data["psychiatric_loss_of_interest_text"] != "" && $data["psychiatric_loss_of_interest_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Loss of Interest:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_loss_of_interest']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_loss_of_interest_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_loss_of_interest_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_memory_loss"] != "N/A" && $data["psychiatric_memory_loss"] != "" && $data["psychiatric_memory_loss"] != "--") || ( $data["psychiatric_memory_loss_text"] != "" && $data["psychiatric_memory_loss_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Memory Loss:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_memory_loss']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_memory_loss_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_memory_loss_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_mood_swings"] != "N/A" && $data["psychiatric_mood_swings"] != "" && $data["psychiatric_mood_swings"] != "--") || ( $data["psychiatric_mood_swings_text"] != "" && $data["psychiatric_mood_swings_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Mood Swings:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_mood_swings']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_mood_swings_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_mood_swings_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_pananoia"] != "N/A" && $data["psychiatric_pananoia"] != "" && $data["psychiatric_pananoia"] != "--") || ( $data["psychiatric_pananoia_text"] != "" && $data["psychiatric_pananoia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Pananoia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_pananoia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_pananoia_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_pananoia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        if (($data["psychiatric_insomnia"] != "N/A" && $data["psychiatric_insomnia"] != "" && $data["psychiatric_insomnia"] != "--") || ( $data["psychiatric_insomnia_text"] != "" && $data["psychiatric_insomnia_text"] != null )) {
            echo "<tr>";
            echo "<td>";
            echo "<span class='text'>Insomnia:<span>";
            echo "</td>";
            echo "<td>";
            echo "<span class='text'>" . text($data['psychiatric_insomnia']) . "</span>";
            echo "</td>";
            echo "<td>";
            if ($data['psychiatric_insomnia_text'] != null) {
                echo "<span class='text'>(" . text($data['psychiatric_insomnia_text']) . ")</span>";
            } else {
                echo "<br/>";
            }

            echo "</td>";
            echo "</tr>";
        }

        print "</table>";
    }
}

function endsWith($FullStr, $EndStr)
{
    $StrLen = strlen($EndStr);
    $FullStrEnd = substr($FullStr, strlen($FullStr) - $StrLen);
    return $FullStrEnd == $EndStr;
}
