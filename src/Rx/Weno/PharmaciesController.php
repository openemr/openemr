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

use OpenEMR\Common\Database\Connector;
use OpenEMR\Entities\Pharmacies;

class PharmaciesController
{
    private $entityManager;
    private $repository;

    /**
     * PharmaciesController constructor.
     */
    public function __construct()
    {
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository(Pharmacies::class);
    }

    public function getPharmacyInfo()
    {
        $pharmacy = $this->repository;
        $response = $pharmacy->fetchAddress();

        return $response;
    }
}
