<?php
/**
 * Birthday alert library.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



function displayBirthdayAlert($deceased_date, $dob, $pid){
    if($deceased_date > 0 ){
        return false;
    }
    if(date('m-d') >= date('m-d', strtotime($dob))){
        if(isbirthdayAlertOff($pid)) {
            return false;
        }
    }
    return true;
}


function isbirthdayAlertOff($pid){
        $sql = "select * from patient_birthday_alert where pid=?";
        $res = sqlQuery($sql, array($pid));
        //if there is result
        if($res){
            //if the alert has been turned off this year
            if(date('Y') == date('Y',strtotime($res['turned_off_on']))){
                return true;
            }

        }
}

?>