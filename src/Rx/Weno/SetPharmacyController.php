<?php
/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

require_once "../../../interface/globals.php";

use OpenEMR\Common\Database\Connector;
use OpenEMR\Entities\Pharmacies;


/**
 * Class SetPharmacyController
 *
 * @package OpenEMR\Rx\Weno
 */
class SetPharmacyController
{
    /**
     * @var
     */
    private $entityManager;
    private $repository;

    /**
     * SetPharmacyController constructor.
     */
    public function __construct()
    {
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository(Pharmacies::class);
    }

    /**
     * @param  $term
     * @return mixed
     */
    public function getPharmacyApi($term)
    {
        $pharmacies = $this->repository;
        $pharmacy = $pharmacies->findAllMatch($term);
        return  json_encode($pharmacy);

    }


}
if (!empty($_GET['term'])) {
    $term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_SPECIAL_CHARS);
    $list = new SetPharmacyController();
    echo $rlist = $list->getPharmacyApi($term);
} else {
    echo json_encode(["error" => "No term sent!"]);
}


