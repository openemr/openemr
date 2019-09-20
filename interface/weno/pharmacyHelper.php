<?php
/**
 * pharmacyHelper
 * This helper is to bridge information from the admin page ajax
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Rx\Weno;

require_once('../globals.php');


use OpenEMR\Pharmacy\Service\Import;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$loadPhar = new Import();

if (isset($_GET)) {
    $city = $_GET['textData'][0];
    $state = $_GET['textData'][1];

    $saved = $loadPhar->importPharmacies($city, $state);
    $response = ['saved' => $saved];
    echo json_encode($response) ;
}
