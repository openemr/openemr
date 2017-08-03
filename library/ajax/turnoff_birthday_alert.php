<?php
/**
 * Turn off Birthday alert .
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . "/../../interface/globals.php");

if($_POST['turnOff'] == "true"){
    $date = date('Y-m-d',strtotime("now"));
}else{

    $date  = date('Y-m-d',strtotime("-1 year"));

}

$sql = "REPLACE INTO patient_birthday_alert VALUES(?,?)";
$res = sqlQuery($sql, array($_POST['pid'],$date));
