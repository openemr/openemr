<?php

/**
 * Document Helper Functions for New Documents Module.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$req = array(
    'term' => (isset($_GET["term"]) ? filter_input(INPUT_GET, 'term') : ''),
    'sql_limit' => (isset($_GET["limit"]) ? filter_input(INPUT_GET, 'limit') : 20),
);

function get_patients_list($req)
{
    $term = "%" . $req['term'] . "%";
    $clear = "- " . xl("Reset to no patient") . " -";
    $response = sqlStatement(
        "SELECT CONCAT(fname, ' ',lname,IF(IFNULL(deceased_date,0)=0,'','*')) as label, pid as value
            FROM patient_data
            HAVING label LIKE ?
            ORDER BY IF(IFNULL(deceased_date,0)=0, 0, 1) ASC, IFNULL(deceased_date,0) DESC, lname ASC, fname ASC
            LIMIT " . escape_limit($req['sql_limit']),
        array($term)
    );
    $resultpd[] = array(
        'label' => $clear,
        'value' => '00'
    );
    while ($row = sqlFetchArray($response)) {
        if ($GLOBALS['pid'] == $row['value']) {
            $row['value'] = "00";
            $row['label'] = xl("Locked") . "-" . xl("In Use") . ":" . $row['label'];
        }

        $resultpd[] = $row;
    }

    echo json_encode($resultpd);
}

get_patients_list($req);
