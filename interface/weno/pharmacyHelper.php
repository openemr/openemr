<?php
/**
 * pharmacyHelper
 *
 *  @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *  This helper is to bridge information from the admin page ajax
 */
require_once('../globals.php');

use OpenEMR\Pharmacy\Service\Import;

$city = $_GET['textData'];
$state = $_GET['state'];

$loadPhar = new Import();

$saved = $loadPhar->importPharmacies($city, $state);

echo $saved;
