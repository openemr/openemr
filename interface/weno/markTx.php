<?php

/**
 * weno rx mark tx.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$date = date("Y-m-d");
$script = filter_input(INPUT_GET, "rx");
$boxState = filter_input(INPUT_GET, "state");

$e_script = explode("-", $script);

if ($e_script[0] === "NewRx") {
    //See if the value is set Note patched out sjp I see no reason to check, just set it.
    //$check = "SELECT ntx FROM prescriptions WHERE id = ?";
    //$getVal = sqlQuery($check, array($e_script[1]));

    //Set for new rx to transmit or reset if not, depends if selected.
    if ($boxState) {
        $sql = "UPDATE prescriptions SET ntx = ?, txDate = ? WHERE id = ?";
        sqlStatement($sql, array($boxState, $date, $e_script[1]));
    } else {
        $sql = "UPDATE prescriptions SET ntx = ? WHERE id = ?";
        sqlStatement($sql, array($boxState, $e_script[1]));
    }
}
 //There is a flaw in the logic because if the doc goes back in to edit the script this date gets wiped out.
if ($e_script[0] === "RefillRx") { // Not sure I see reason this is needed Only worry if to xmit or not! - sjp
    if ($boxState) {
        $sql = "UPDATE prescriptions SET ntx = ?, txDate = ? WHERE id = ?";
        sqlStatement($sql, array($boxState, $date, $e_script[1]));
    } else {
        $sql = "UPDATE prescriptions SET ntx = ? WHERE id = ?";
        sqlStatement($sql, array($boxState, $e_script[1]));
    }
}

if ($_GET['arr']) {
    //First number is the pharmacy. The next number(s) are the records to be update with pharmacy info

    $scriptUpdate = explode(",", $_GET['arr']);

    $i = count($scriptUpdate) - 1; //Since first number is always the pharmacy -1
    $ii = 1;

    while ($i >= $ii) {
            $query = "UPDATE prescriptions SET pharmacy_id = ? WHERE id = ?" ;
            sqlStatement($query, array(trim($scriptUpdate[0]), $scriptUpdate[$ii]));
            $ii++;
    }
}//if statement
