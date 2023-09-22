<?php

/**
 * Contains all of the Visual Dashboard global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Visual EHR <https://visualehr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$data = [
    array(
        "id" => 1,
        "name" => "Problem List",
        "hint" => " (Sort by system)",
        "add_btn" => "New Problem",
        "color" => "#eb5ea8",
        "type" => "medical_problem",
        "tab_name" => "Problem"
    ),
    array(
        "id" => 2,
        "name" => "Medications",
        "hint" => "",
        "add_btn" => "New Medication",
        "color" => "#eb5ea8",
        "type" => "medication",
        "tab_name" => "Medication"
    ),
    array(
        "id" => 3,
        "name" => "Allergy",
        "hint" => "",
        "add_btn" => "New Allergy",
        "color" => "#eb5ea8",
        "type" => "allergy",
        "tab_name" => "Allergy"
    ),
    array(
        "id" => 4,
        "name" => "Surgery",
        "hint" => "",
        "add_btn" => "New Surgery",
        "color" => "#eb5ea8",
        "type" => "surgery",
        "tab_name" => "Surgery"
    ),
    array(
        "id" => 5,
        "name" => "Dental",
        "hint" => "",
        "add_btn" => "New Dental",
        "color" => "#eb5ea8",
        "type" => "dental",
        "tab_name" => "Dental"
    ),
    array(
        "id" => 6,
        "name" => "Medical Device",
        "hint" => "",
        "add_btn" => "New Medical Device",
        "color" => "#eb5ea8",
        "type" => "medical_device",
        "tab_name" => "Device"
    ),
    array(
        "id" => 7,
        "name" => "Vitals",
        "hint" => "",
        "add_btn" => "New Vitals",
        "color" => "#eb5ea8",
        "type" => "vitals",
        "tab_name" => "Vitals"
    ),
    array(
        "id" => 8,
        "name" => "LabTest",
        "hint" => "",
        "add_btn" => "New Lab",
        "color" => "#eb5ea8",
        "type" => "tests",
        "tab_name" => "Lab Tests"
    )

];

$datalist = array(
    "sidebarlist" => $data,
    "vitals" => getVitals(),
    "tests" => getLabTests()
);


function getLabTests()
{
    return [
        array("id" => 1, "name" => "Result Text"),
        array("id" => 2, "name" => "Range"),
        array("id" => 3, "name" => "Result"),
        array("id" => 4, "name" => "Status")
    ];
}

function getVitals()
{
    return [
        array("id" => 0, "name" => "Date"),
        array("id" => 1, "name" => "Blood Pressure"),
        array("id" => 2, "name" => "Height"),
        array("id" => 3, "name" => "Temperature Method"),
        array("id" => 4, "name" => "Respiration"),
        array("id" => 5, "name" => "BMI Status"),
        array("id" => 6, "name" => "Oxygen Flow Rate"),
        array("id" => 7, "name" => "Weight"),
        array("id" => 8, "name" => "Temperature"),
        array("id" => 9, "name" => "Pulse"),
        array("id" => 10, "name" => "BMI"),
        array("id" => 11, "name" => "Oxygen Saturation"),
        array("id" => 12, "name" => "Inhaled Oxygen Concentration"),
    ];
}

echo json_encode($datalist);
