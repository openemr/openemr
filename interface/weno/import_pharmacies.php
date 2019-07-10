<?php
/**
 * weno rx pharmacy import.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Alfonzo Perez  <aperez@hitechcompliance.net>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$state = filter_input(INPUT_POST, "form_state"); //stores the variable sent in the post
$srchCity = filter_input(INPUT_POST, "form_city");
$ref = $_SERVER["HTTP_REFERER"];     //stores the url the post came from to redirect back to

/*
*  Opens the CSV file and reads each line
*/
$path = '../../contrib/weno/pharmacyList.csv';
$entrys = new SplFileObject($path);
$entrys->setFlags(SplFileObject::READ_CSV);

sqlStatementNoLog("SET autocommit=0");
sqlStatementNoLog("START TRANSACTION"); // Just in case someone else is adding.

$tm = 1; // Let's count how many.
foreach ($entrys as $entry) {//This loop continues till the end of the last line is reached.
    //check entry 7 to match state
    if (strtoupper($entry[7]) == strtoupper($state) && strtoupper($entry[6]) == strtoupper($srchCity)) { //In the next iteration this needs to be gotten from the globals
        /*
         *   check the name is in the table
         *   if it is skip to the next name on the list
         */
        $sql = "SELECT id FROM pharmacies WHERE name = ? And npi = ?";
        $getNameId = sqlQuery($sql, array($entry[3], $entry[2]));

        if (empty($getNameId)) {
            $phone = str_replace(" ", "-", $entry[10]);  //reformat the phone numbers and fax number
            $fax = str_replace(" ", "-", $entry[11]);
            if (strlen($phone) == 10) { // Not Formatted
                $phone = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
            }
            if (strlen($fax) == 10) {
                $fax = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $fax);
            }

            $sql = "SELECT MAX(id) as id FROM pharmacies";  // Find last record in the table
            $getMaxId = sqlQuery($sql);    //load to variable
            $id = $getMaxId['id'] + 1;  // set start import ID to max id plus 1
            $sql = "INSERT INTO pharmacies (id, name, transmit_method, email, ncpdp, npi) VALUES (?,?,?,?,?,?)";
            $newInsert = array($id, $entry[3], 1, null, $entry[1], $entry[2]);
            sqlStatement($sql, $newInsert);

            // Add Address
            $sql = "SELECT MAX(id) as id FROM addresses";  // Let's do this for case others insert addresses besides pharmacies.
            $aid = sqlQuery($sql);
            $aid = $aid['id'] + 1; // ++ with arrays can be troublesome..
            //Insert Address into address table
            $fid = $id;        // Set the foreign_id to the id in the pharmacies table.
            $asql = "INSERT INTO addresses (`id`, `line1`, `line2`, `city`, `state`, `zip`, `plus_four`, `country`, `foreign_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $addressInsert = array($aid, $entry[4], $entry[5], $entry[6], $entry[7], $entry[8], '','USA', $fid);
            sqlStatement($asql, $addressInsert);

            //Insert Phone and Fax number
            $exPhone = explode("-", $phone);
            $exFax  = explode("-", $fax);

            $sql = "SELECT MAX(id) as id FROM phone_numbers";  // Let's do this for the case others insert numbers besides pharmacies.
            $aid = sqlQuery($sql);
            $aid = $aid['id'] + 1;
            $psql = "INSERT INTO phone_numbers (id, country_code, area_code, prefix, number, type, foreign_id) VALUES (?,?,?,?,?,?,?)";
            $phoneInsert = array($aid, 1, $exPhone[0], $exPhone[1], $exPhone[2], 2, $fid);
            sqlStatement($psql, $phoneInsert);
            ++$aid;
            $faxInsert = array($aid, 1, $exFax[0], $exFax[1], $exFax[2], 5, $fid);
            sqlStatement($psql, $faxInsert);

            $tm++;
        } //data insert if not present
    } //loop conditional
} //end of loop

sqlStatementNoLog("COMMIT"); // What else!
sqlStatementNoLog("SET autocommit=1");

header("Location: ". $ref."?status=finished");

?>
 <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
<span class="sr-only"><?php echo xlt("Loading... Please wait"); ?></span>
