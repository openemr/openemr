<?php

/**
 * Ajax interface for popup of multi select patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Amiel Elboim <amielel@matrix.co.il
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$type = $_GET['type'];
$search = $_GET['search'] ?? '';

switch ($type) {
    case 'by-id':
        // load patients ids for select2.js library, expect receive 'text' and 'id'.
        $results = getPatientId("$search%", 'pubpid as text, pid as id', 'pubpid');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'by-name':
        // load patients names for select2.js library, expect receive 'text' and 'id'.
        $results = getPatientLnames("%$search%", 'pid as id, CONCAT(lname, ", ",fname)  as text', 'lname ASC, fname ASC');
        foreach ($results as $key => $result) {
            //clean data using 'text' function
            $results[$key] = array_map('text', $result);
        }
        break;
    case 'patient-by-id':
        $results = getPatientData($search, 'id, pid, lname, fname, mname, pubpid, ss, DOB, phone_home');
        //clean data using 'text' function
        $results = array_map('text', $results);
        $results['DOB'] = oeFormatShortDate($results['DOB']);
        break;
}

$output = array('results' => $results);
echo json_encode($output);
die;
