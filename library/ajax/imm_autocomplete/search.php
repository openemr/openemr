<?php

/**
 * immunization lot search.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../../interface/globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!empty($_GET['term'])) {
    $term = $_GET['term'];
    $return_arr = array();

    $sql = "SELECT DISTINCT lot_number FROM immunizations WHERE lot_number LIKE ?";
    $res = sqlstatement($sql, array("%" . $term . "%"));
    while ($row = sqlFetchArray($res)) {
        $return_arr[] =  $row['lot_number'] ;
    }
    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
