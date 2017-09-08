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
        $sql = "SELECT date_Added,date_Modified,drug,drug_id,dosage,refills,quantity,note FROM prescriptions WHERE id = ?";
        $res = sqlQuery($sql, array($id));
        return $res;
    }


    public function patientPharmacyInfo($pid)
    {
        $sql = "SELECT a.pharmacy_id, b.name FROM patient_data AS a, pharmacies AS b WHERE a.pid = ? AND a.pharmacy_id = b.id";
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
}
