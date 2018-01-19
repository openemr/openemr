<?php
/**
 * weno rx search.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../../interface/globals.php');

if (isset($_GET['term'])) {
    $return_arr = array();
    $term    = filter_input(INPUT_GET, "term");

    $sql = "SELECT drug_label_name, price_per_unit FROM erx_drug_paid WHERE drug_label_name LIKE ? ";
    $val = array('%'.$term.'%');
    $res = sqlStatement($sql, $val);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] =  $row['drug_label_name'] . " - ". $row['price_per_unit'];
    }

    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
