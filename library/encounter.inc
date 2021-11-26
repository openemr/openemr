<?php

require_once(__DIR__ . "/forms.inc");

use OpenEMR\Common\Session\EncounterSessionUtil;

//function called to set the global session variable for encounter number
function setencounter($enc)
{
    return EncounterSessionUtil::setEncounter($enc);
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
