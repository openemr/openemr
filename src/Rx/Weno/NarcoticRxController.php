<?php
/**
 * Narcotic Prescription Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Entities\Prescriptions;

class NarcoticRxController
{
    private $entityManager;
    private $repository;

    public function __construct()
    {
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository(Prescriptions::class);
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function buildRx()
    {
        $referrer = $_SERVER['HTTP_REFERER'];
        $prescriptions = self::getPrescriptions();
        $pharmacyOnFile = self::getPharmacy();

        return $GLOBALS['twig']->render(
            'weno/narcotic.html.twig',
            [
            'referrer' => $referrer,
            'tabtitle' => xl('Prescription Order'),
            'pagetitle' => xl('Prescription Confirm & Transmit'),
            'prescriptions' => $prescriptions,
            'pharmacy' => $pharmacyOnFile,
            'url' => '../../interface/weno/setpharmacyhelper.php',
            'savePharmUrl' => '../../src/Rx/Weno/PatientPharmacyController.php'
            ]
        );
    }

    public function getPrescriptions()
    {
        $resultsList = [];
        $scripts = $this->repository;
        $items = $scripts->getDrugsTx();
        foreach ($items as $item) {
            $resultsList[] = $item;
        }

        return $resultsList;
    }

    public function getPharmacy()
    {
        //$pharmacy = $this->repository;
        //$store = $pharmacy->findPatientPharmacy();
        $pid = $GLOBALS['pid'];
        $sql = "SELECT p.name, pa.line1, pa.city, pa.state FROM pharmacies AS p ".
            "LEFT JOIN addresses AS pa ON p.id = pa.foreign_id ".
            "LEFT JOIN patient_data AS pd ON pd.pharmacy_id = pa.foreign_id ".
            "WHERE pd.pid = ?";
        $store = sqlQuery($sql, [$pid]);
        if (!empty($store)) {
            return $store['name'] . " " . $store['line1'] . " " . $store['city'] . " " . $store['state'];
        } else {
            return "";
        }
    }
}
