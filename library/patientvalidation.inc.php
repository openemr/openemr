<?php
/**
 * Created by Dror Golan clinikal team matrix.
  * Date: 28/07/16
 * Time: 5:44 PM
 *
 *
 */


//Check if new patient is added to hooks
function checkIfPatientValidationHookIsActive()
{
    $module_query = sqlStatement("SELECT * FROM modules WHERE mod_name= 'Patientvalidation' and mod_active=1");

    if (sqlNumRows($module_query)) {
        //if you want to check inactive active hook please uncheck the following comment
        //$s = "<div style='margin-bottom:10px; border:1px solid black;padding: 5px 5px 5px 5px;width:300px;background-color: #79bbff'><center>  " . xl("You are using patient validation module") . "</center></div>";
       // echo $s;
        return true;
    }
    else
        return false;
}