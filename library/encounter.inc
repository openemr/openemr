<?php

require_once(__DIR__ . "/forms.inc");

use OpenEMR\Common\Session\SessionUtil;

//function called to set the global session variable for encounter number
function setencounter($enc)
{

    // Escape $enc by forcing it to an integer to protect from sql injection
    $enc = intval($enc);

    $return_val = 1;
    global $encounter;
    global $pid;
    global $attendant_type;

    $attendant_id = $attendant_type == 'pid' ? $pid : $_SESSION['therapy_group'];
    if ($enc == "") {
        $enc = date("Ymd");
        if (getFormByEncounter($attendant_id, $enc)) {
            //there is an encounter entered for today
        } else {
            //addForm($enc, "New Patient Encounter", 0, $pid, 1);
            $return_val = 0;
        }
    }

    SessionUtil::setSession('encounter', $enc);
    $encounter = $enc;

    //returns 1 on successful global set, or 0 if there was no
    //current encounter, signifying that the interface should load
    //the screen for a new encounter
    return $return_val;
}


//fetches encounter pc_catid by encounter number
function fetchCategoryIdByEncounter($encounter)
{
    global $attendant_type;
    $table = $attendant_type == 'pid' ? 'form_encounter' : 'form_groups_encounter';
    $sql = "SELECT pc_catid FROM " . escape_table_name($table) . " WHERE encounter = ? limit 1";
    $result = sqlQuery($sql, array($encounter));
    return $result['pc_catid'];
}

/**
 * @param $encounter
 * @return mixed
 */
function fetchDateService($encounter)
{
    $sql = "select date from form_encounter where encounter = ?";
    $result = sqlQuery($sql, [$encounter]);
    $result = explode(" ", $result['date']);
    return $result[0];
}
