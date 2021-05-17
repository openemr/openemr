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
// will never be both
$is_rxnorm = $_GET['use_rxnorm'] == "true";
$is_rxcui = $_GET['use_rxcui'] == "true";

if (isset($_GET['term'])) {
    $return_arr = array();
    $term = filter_input(INPUT_GET, "term");
    if ($is_rxnorm) {
        $sql = "SELECT `str` as name, `RXCUI` as `rxnorm` FROM `rxnconso` WHERE `SAB` = 'RXNORM' AND `str` LIKE ? GROUP BY `RXCUI` ORDER BY `str` LIMIT 100";
    } elseif ($is_rxcui) {
        $sql = "SELECT `code_text` as name, `code` as rxnorm FROM `codes` WHERE `code_text` LIKE ? AND `code_type` = ? GROUP BY `code` ORDER BY `code_text` LIMIT 100";
    } else {
        $sql = "SELECT `name`, `drug_code` as rxnorm FROM `drugs` WHERE `name` LIKE ? GROUP BY `drug_code` ORDER BY `name` LIMIT 100";
    }
    $val = array($term . '%');
    if ($is_rxcui) {
        $code_type = sqlQuery("SELECT ct_id FROM `code_types` WHERE `ct_key` = ? AND `ct_active` = 1", array('RXCUI'));
        $val = array($term . '%', $code_type['ct_id']);
        if (empty($code_type['ct_id'])) {
            throw new \Exception(xlt('Install RxCUI monthly via Native Load or enable in Lists!'));
        }
    }
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
