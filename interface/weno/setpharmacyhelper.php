<?php
/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Rx\Weno\SetPharmacyController;

if (!empty($_GET['term'])) {
    $term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_SPECIAL_CHARS);
    $list = new SetPharmacyController();
    echo $rlist = $list->getPharmacyApi($term);
} else {
    echo json_encode(["error" => "No term sent!"]);
}
