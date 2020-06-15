<?php

/**
 * TransmitData class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

use OpenEMR\Common\Http\oeHttp;

class TransmitData
{

    public function __construct()
    {
    }

    public function getDrugList($pid, $date)
    {

        $sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND ntx = 1 AND txDate = ?";
        $res = sqlStatement($sql, array($pid, $date));
        return $res;
    }

    public function getProviderFacility($uid)
    {

        $sql = "SELECT a.fname, a.lname, a.npi, a.weno_prov_id, b.name, b.phone, b.fax, b.street, b.city, b.state, 
				b.postal_code FROM `users` AS a, facility AS b WHERE a.id = ? AND 
				a.facility_id = b.id ";

        $pFinfo = sqlQuery($sql, array($uid));

        return array($pFinfo);
    }

    public function findPharmacy($id)
    {
        //$sql = "SELECT store_name, NCPDP, NPI, Pharmacy_Phone, Pharmacy_Fax FROM erx_pharmacies WHERE id = ?";
        $sql = "SELECT name, ncpdp, npi FROM pharmacies WHERE id = ? ";
        $find = sqlQuery($sql, array($id));

        $nSql = "SELECT area_code, prefix, type, number FROM phone_numbers WHERE foreign_id = ?";
        $numbers = sqlStatement($nSql, array($id));

        $numberArray = array();
        while ($row = sqlFetchArray($numbers)) {
            $numberArray[] = $row;
        }

        return array($find, $numberArray);
    }

    public function oneDrug($id)
    {
        $sql = "SELECT p.date_Added, p.date_Modified,p.drug, p.drug_id, p.dosage, p.refills, p.quantity, p.note," .
               "ew.strength, ew.route, ew.potency_unit_code, ew.drug_db_code_qualifier,ew.dea_schedule FROM prescriptions AS p " .
               "RIGHT JOIN erx_weno_drugs AS ew ON p.drug_id = ew.rxcui_drug_coded WHERE p.id = ?";
        $res = sqlQuery($sql, array($id));
        return $res;
    }


    public function patientPharmacyInfo($pid)
    {
        $sql = "SELECT a.pharmacy_id, b.name, b.npi, b.ncpdp FROM patient_data AS a, pharmacies AS b WHERE a.pid = ? AND a.pharmacy_id = b.id";
        $res = sqlQuery($sql, $pid);
        return $res;
    }

    public function mailOrderPharmacy()
    {
        $sql = "SELECT id FROM pharmacies WHERE name LIKE ?";
        $res = sqlQuery($sql, 'CCS Medical');
        return $res;
    }


    public function validatePatient($pid)
    {
         $patientInfo = "SELECT DOB, street, postal_code, city, state, sex FROM patient_data WHERE pid = ?";
         $val = array($pid);
         $patientRes = sqlQuery($patientInfo, $val);
         return $patientRes;
    }

    public function validateNPI($npi)
    {
        $query = [
            'number' => $npi,
            'enumeration_type' => '',
            'taxonomy_description' => '',
            'first_name' => '',
            'last_name' => '',
            'organization_name'  => '',
            'address_purpose' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country_code' => '',
            'limit' => '',
            'skip' => '',
            'version' => '2.0',
        ];
        $response = oeHttp::get('https://npiregistry.cms.hhs.gov/api/', $query);

        $body = $response->body(); // already should be json.

        $validated = json_decode($body);

        return $validated->result_count;
    }
}
