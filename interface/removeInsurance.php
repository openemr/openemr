<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once dirname(__FILE__) . '/globals.php';

$data = json_decode(file_get_contents('php://input'), true);
// Check if the session pid matches the data pid
if ($_SESSION['pid'] === $data['pid']) {
    $query = "DELETE FROM insurance_data WHERE pid = ? ";
    sqlStatement($query, array($_SESSION['pid']));
}
echo 'Insurance removed ' . $data['pid'];
