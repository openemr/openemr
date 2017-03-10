<?php
/**
 * weno rx mark tx.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
$date = date("Y-m-d");
$script = filter_input(INPUT_GET, "rx");

$e_script = explode("-", $script);

if($e_script[0] === "NewRx"){
    //See if the value is set
    $check = "SELECT ntx FROM prescriptions WHERE id = ?";
    $getVal = sqlStatement($check, array($e_script[1]));
    $val = sqlFetchArray($getVal);
    
    //If the value is not set to 1 then set it for new rx to transmit
    // ToDo add transmit date
    if(empty($val['ntx'])){
        $sql = "UPDATE prescriptions SET ntx = '1', txDate = ? WHERE id = ?";
        sqlStatement($sql, array($date, $e_script[1]));
    }

}
 //There is a flaw in the logic because if the doc goes back in to edit the script this date gets wiped out.
if($e_script[0] === "RefillRx"){
    $sql = "UPDATE prescriptions SET txDate = ? WHERE id = ?";
    sqlStatement($sql, array($date, $e_script[1]));
    
}

if($_GET['arr']){
    //First number is the pharmacy. The next number(s) are the records to be update with pharmacy info
    
    $scriptUpdate = explode(",", $_GET['arr']);

    $i = count($scriptUpdate) - 1; //Since first number is always the pharmacy -1
    $ii = 1;
     
    while($i >= $ii){
            $query = "UPDATE prescriptions SET pharmacy_id = ? WHERE id = ?" ;
            sqlStatement($query, array(trim($scriptUpdate[0]), $scriptUpdate[$ii]));
            $ii++;
    }
    
}//if statement
