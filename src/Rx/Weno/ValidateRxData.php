<?php
/**
 * ValidateRxData class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Rx\Weno;

use OpenEMR\Common\Http\oeHttp;

class ValidateRxData
{

    /**
     * @param  $uid
     * @return array
     */
    public function getProviderFacility($uid)
    {

        $sql = "SELECT a.fname, a.lname, a.npi, a.weno_prov_id, b.name, b.phone, b.fax, b.street, b.city, b.state,
				b.postal_code FROM `users` AS a, facility AS b WHERE a.id = ? AND
				a.facility_id = b.id ";

        $pFinfo = sqlQuery($sql, array($uid));

        return array($pFinfo);
    }

    /**
     * @param  $id
     * @return array
     */
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

    public function medicalProblem()
    {
        $pid = $GLOBALS['pid'];
        $sql = "SELECT `diagnosis` FROM `lists` WHERE `pid` = ? AND type LIKE 'medical_problem' " .
               "ORDER BY date DESC LIMIT 1";
        $diagnosis = sqlQuery($sql, [$pid]);
        return $diagnosis['diagnosis'];
    }


    /**
     * @param  $pid
     * @return array|null
     */
    public function patientPharmacyInfo($pid)
    {
        $sql = "SELECT a.pharmacy_id, b.name, b.npi, b.ncpdp FROM patient_data AS a, pharmacies AS b WHERE a.pid = ? AND a.pharmacy_id = b.id";
        $res = sqlQuery($sql, $pid);
        return $res;
    }


    /**
     * @param  $pid
     * @return array|null
     */
    public function validatePatient($pid)
    {
         $patientInfo = "SELECT DOB, street, postal_code, city, state, sex FROM patient_data WHERE pid = ?";
         $val = array($pid);
         $patientRes = sqlQuery($patientInfo, $val);
         return $patientRes;
    }

    /**
     * @param  $npi
     * @return mixed
     */
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
