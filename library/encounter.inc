<?php
include_once("../../globals.php");
include_once("$srcdir/forms.inc");

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
            //there is an encounter enterred for today
        } else {
            //addForm($enc, "New Patient Encounter", 0, $pid, 1);
            $return_val = 0;
        }
    }

    $_SESSION['encounter']=$enc;
    $encounter=$enc;
    
    
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
    $sql = "select pc_catid from $table where encounter=? limit 1";
    $result = sqlQuery($sql, array($encounter));
    return $result['pc_catid'];
}
