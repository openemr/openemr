<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Rx\Weno\wenoPharmaciesImport;

//ensure user has proper access
if (!AclMain::aclCheckCore('patient', 'med')) {
    echo xlt('Pharmacy Import not authorized');
    exit;
}
//Weno has decided to not force the import of pharmacies since they are using the iframe
//and the pharmacy can be selected at the time of creating the prescription.
$phIN = new wenoPharmaciesImport();

$status = $phIN->importPharmacy();

echo $status;
