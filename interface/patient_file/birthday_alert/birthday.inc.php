<?php
/**
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sharon Cohen<sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */



function display_birthday_alert($deceased_date,$dob,$pid){
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