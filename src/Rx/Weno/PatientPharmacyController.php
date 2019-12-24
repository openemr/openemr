<?php
/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

namespace OpenEMR\Rx\Weno;

require_once "../../../interface/globals.php";

use OpenEMR\Common\Database\Connector;
use OpenEMR\Entities\PatientData;


class PatientPharmacyController
{
    private $entityManager;

    private $repository;

    public function __construct()
    {
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository(PatientData::class);
    }

    public function updatePatientPharmacyApi($id,$pid)
    {
        $patientPharmacy = $this->repository;
        $set = $patientPharmacy->updatePatientPharmacy($id,$pid);
        return $set;
    }

}

if (!empty($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $pid = $GLOBALS['pid'];
    $set = new PatientPharmacyController();
    echo $set->updatePatientPharmacyApi($id,$pid);
} else {
    echo json_encode(["error" => "No id sent!"]);
}
