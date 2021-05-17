<?php

/**
 * This file is used specifically to look up drug names when
 * writing a prescription. See the file:
 * templates/prescription/general_edit.html
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
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$is_rxnorm = $_GET['use_rxnorm'] == "true";

if (isset($_GET['term'])) {
    $return_arr = array();
    $term = filter_input(INPUT_GET, "term");
    if ($is_rxnorm) {
        $sql = "SELECT `str` as name, `RXCUI` as `rxnorm` FROM `rxnconso` WHERE `SAB` = 'RXNORM' AND `str` LIKE ? GROUP BY `RXCUI` ORDER BY `name` LIMIT 100";
    } else {
        $sql = "SELECT `name`, `drug_code` as rxnorm FROM `drugs` WHERE `name` LIKE ? GROUP BY `rxnorm` ORDER BY `name` LIMIT 100";
    }
    $val = array($term . '%');
    $res = sqlStatement($sql, $val);
    while ($row = sqlFetchArray($res)) {
        $return_arr[] = array(
            'display_name' => text($row['name'] . " (RxCUI:" . trim($row['rxnorm']) . ")"),
            'id_name' => text($row['name']),
            'rxnorm' => text($row['rxnorm'])
        );
    }

    /* Toss back results as json encoded array. */
    echo json_encode($return_arr);
}
