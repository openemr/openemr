<?php
/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

require_once dirname(__FILE__) . "/../../../library/patient.inc";

class NewRxData
{
    //patient object
    private $pid;
    private $prescriber;

    public function getPatientData()
    {
        $this->pid = $GLOBALS['pid'];
        $patientInfo = getPatientData($given = $this->pid);

        return $patientInfo;
    }

    public function getPharmacyData($phid)
    {
        $sql = "SELECT p.name, p.ncpdp, p.npi, a.line1, a.line2, ".
            "a.city, a.state, a.zip FROM pharmacies AS p " .
            "LEFT JOIN addresses AS a ON p.id = a.foreign_id " .
            "WHERE p.id = ?";
        $data = sqlQuery($sql, [$phid]);

        $nsql = "SELECT area_code, prefix, number FROM phone_numbers ".
            "WHERE foreign_id = ? ORDER BY type ASC ";
        $loadnumbers = sqlStatement($nsql, [$phid]);
        while ($row = sqlFetchArray($loadnumbers)) {
                $numbers[] = $row;
        }

        array_push($data, $numbers);
        return $data;
    }

    public function getPrescriberData()
    {
        $this->prescriber = $_SESSION['authUserID'];
        $sql = "SELECT a.fname, a.lname, a.npi, a.federaldrugid, a.weno_prov_id, a.email, b.name, b.phone, b.fax, b.street, b.city, b.state,
				b.postal_code, b.weno_id FROM `users` AS a, facility AS b WHERE a.id = ? AND
				a.facility_id = b.id ";

        $pFinfo = sqlQuery($sql, [$this->prescriber]);

        return array($pFinfo);
    }

    public function getCurrentVitals()
    {
        $this->pid = $GLOBALS['pid'];
        $sql = "SELECT date, weight, height, note FROM form_vitals WHERE pid = ? ".
            " ORDER BY id DESC LIMIT 1";
        $vitals = sqlQuery($sql, [$this->pid]);
        return $vitals;
    }

    public function medicationData($med)
    {
        $this->pid = $GLOBALS['pid'];
        $sql = "SELECT p.date_Added, p.date_Modified,p.drug, p.drug_id, p.dosage, p.refills, p.quantity, p.note,".
            "ew.strength, ew.route, ew.potency_unit_code, ew.drug_db_code_qualifier, ew.dea_schedule, " .
            "ew.code_list_qualifier, l.diagnosis, l.title FROM prescriptions AS p ".
            "RIGHT JOIN erx_weno_drugs AS ew ON p.drug_id = ew.rxcui_drug_coded " .
            "LEFT JOIN lists AS l ON p.patient_id = l.pid " .
            "WHERE p.id = ? AND l.diagnosis != ''";
        $res = sqlQuery($sql, [$med]);
        return $res;
    }
}
