<?php

/**
 * weno pharmacy search.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Kofi Appiah <kkappiah@medsov.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 require_once(dirname(__FILE__) . "/../../interface/globals.php");

if (isset($_GET['searchFor']) && $_GET['searchFor'] == 'weno_city') {
    $return_arr = array();
    $term    = filter_input(INPUT_GET, "term");

    $sql = "SELECT city, id FROM weno_pharmacy WHERE city LIKE ? LIMIT 10";
    $val = array('%' . $term . '%');
    $res = sqlStatement($sql, $val, $val);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] =  $row['city'];
    }

    echo json_encode($return_arr);
}

if (isset($_GET['searchFor']) && $_GET['searchFor'] == 'weno_pharmacy') {
    $term    = filter_input(INPUT_GET, "term");
    $val = '%' . $term . '%';

    $sql = "SELECT business_name, state, ncpdp, city, address_line_1 " .
            "FROM weno_pharmacy WHERE Business_Name LIKE '$val'";

    $weno_coverage  = $_GET['coverage'] ? $_GET['coverage'] : '';
    $weno_state     = $_GET['weno_state'] ? $_GET['weno_state'] : '';
    $weno_city      = $_GET['weno_city'] ? $_GET['weno_city'] : '';
    $full_day       = $_GET['full_day'] ? 'Yes' : '';
    $weno_only      = $_GET['weno_only'] ? 'True' : '';
    $weno_zipcode   = $_GET['weno_zipcode'] ? $_GET['weno_zipcode'] : '';
    $weno_test_pharmacies   = $_GET['test_pharmacy'] ? 'True' : '';


    if (!empty($weno_coverage)) {
        $sql .= " AND state_wide_mail_order = '$weno_coverage'";
    }
    if (!empty($weno_state)) {
        $sql .= " AND state = '$weno_state'";
    }
    if (!empty($weno_city)) {
        $sql .= " AND city = '$weno_city'";
    }
    if (!empty($weno_only)) {
        $sql .= " AND on_weno = '$weno_only'";
    }
    if (!empty($full_day)) {
        $sql .= " AND 24HR = '$full_day'";
    }
    if (!empty($weno_zipcode)) {
        $sql .= " AND ZipCode = '$weno_zipcode'";
    }
    if (!empty($weno_test_pharmacies)) {
        $sql .= " AND test_pharmacy = '$weno_test_pharmacies'";
    }

    $sql .= " ORDER BY Business_Name ASC";

    $res = sqlStatement($sql);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] = array(
            "name"  => $row['business_name'] . "/ " . $row['address_line_1'] . " / " . $row['city'],
            "ncpdp" => $row['ncpdp']
        );
    }
    echo json_encode($return_arr);
}

if (isset($_GET['searchFor']) && $_GET['searchFor'] == 'weno_drop') {
    $term    = filter_input(INPUT_GET, "term");
    $val = array('%' . $term . '%');

    $sql = "SELECT business_name, state, ncpdp, city, address_line_1 " .
            "FROM weno_pharmacy WHERE";

    $weno_coverage  = $_GET['coverage'] ? $_GET['coverage'] : '';
    $weno_state     = $_GET['weno_state'] ? $_GET['weno_state'] : '';
    $weno_city      = $_GET['weno_city'] ? $_GET['weno_city'] : '';
    $full_day       = $_GET['full_day'] ? 'Yes' : '';
    $weno_zipcode   = $_GET['weno_zipcode'] ? $_GET['weno_zipcode'] : '';
    $weno_test_pharmacies   = $_GET['test_pharmacy'] ? 'True' : '';

    if (!empty($weno_state)) {
        $sql .= " state = '$weno_state'";
    }
    if (!empty($weno_coverage)) {
        $sql .= " AND state_wide_mail_order = '$weno_coverage'";
    }
    if (!empty($weno_city)) {
        $sql .= " AND city = '$weno_city'";
    }
    if (!empty($full_day)) {
        $sql .= " AND 24HR = '$full_day'";
    }
    if (!empty($weno_zipcode)) {
        $sql .= " AND ZipCode = '$weno_zipcode'";
    }
    if (!empty($weno_test_pharmacies)) {
        $sql .= " AND test_pharmacy = '$weno_test_pharmacies'";
    }

    $sql .= " ORDER BY Business_Name ASC";



    $res = sqlStatement($sql);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] = array(
            "name"  => $row['business_name'] . "/ " . $row['address_line_1'] . " / " . $row['city'],
            "ncpdp" => $row['ncpdp']
        );
    }
    echo json_encode($return_arr);
}
