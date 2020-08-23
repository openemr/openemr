<?php

/**
 * This file use is used specifically to look up drug names when
 * writing a prescription. See the file:
 *    templates/prescriptions/general_edit.html
 * for additional information
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jason Morrill <jason@italktech.net>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008 Jason Morrill <jason@italktech.net>
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (isset($_GET['term'])) {
    $return_arr = array();
    $term = filter_input(INPUT_GET, "term");

    $sql = "SELECT `name` FROM `drugs` WHERE `name` LIKE ? ORDER BY `name`";
    $val = array($term . '%');
    $res = sqlStatement($sql, $val);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] =  $row['name'];
    }

    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
