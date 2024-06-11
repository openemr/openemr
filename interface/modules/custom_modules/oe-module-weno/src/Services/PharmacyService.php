<?php

/**
 * WenoPharmacyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use Exception;

class PharmacyService
{
    public function __construct()
    {
    }

    public function createWenoPharmaciesForPatient($pid, $data)
    {
        $sql = "INSERT INTO weno_assigned_pharmacy SET ";
        $sql .= "pid = ?,";
        $sql .= "primary_ncpdp = ?,";
        $sql .= "alternate_ncpdp = ? ";

        try {
            sqlInsert($sql, [
                $pid,
                $data['primary_pharmacy'],
                $data['alternate_pharmacy'],
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updatePatientWenoPharmacy($pid, $data)
    {
        //check if pharmacies already exist for patient
        if (!$this->getWenoPharmacy($pid)) {
            return $this->createWenoPharmaciesForPatient($pid, $data);
        }
        $sql = "UPDATE weno_assigned_pharmacy SET ";
        $sql .= "primary_ncpdp = ?, ";
        $sql .= "alternate_ncpdp = ? ";
        $sql .= "WHERE pid = ?";

        try {
            sqlInsert($sql, [
                $data['primary_pharmacy'],
                $data['alternate_pharmacy'],
                $pid
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

// todo remove this method
    public function getPatientPrimaryPharmacy($pid)
    {
        $sql = "SELECT pd.pharmacy_id, pd.pid, p.id, p.name, ";
        $sql .= "a.foreign_id, a.city, a.line1 FROM patient_data pd ";
        $sql .= "LEFT JOIN pharmacies p ON pd.pharmacy_id = p.id ";
        $sql .= "LEFT JOIN addresses a ON p.id = a.foreign_id WHERE pd.pid = ?";

        $result = sqlQuery($sql, [$pid]);

        return json_encode($result);
    }

    public function getWenoPharmacyForPatient($pid)
    {
        if ($pid !== 0) {
            $sql = "SELECT p.pid,p.weno_pharmacy, w.state,w.ncpdp_safe,w.business_name,";
            $sql .= "w.address_line_1 FROM patient_data p ";
            $sql .= "INNER JOIN weno_pharmacy w ON p.weno_pharmacy = w.ncpdp_safe WHERE pid = ?";
            $result = sqlQuery($sql, array($pid));
        }
        $pharmacy_data = array(
            "name" => $result['business_name'] ?? '',
            "ncpdp" => $result['ncpdp_safe'] ?? '',
            "state" => $result['state'] ?? '',
            "address" => $result['address_line_1'] ?? ''
        );

        return json_encode($pharmacy_data);
    }

    public function getWenoPrimaryPharm($pid)
    {
        $sql = "SELECT wap.pid, wap.primary_ncpdp, wp.business_name, ";
        $sql .= "wp.city, wp.address_line_1, wp.ncpdp_safe, wp.state FROM weno_assigned_pharmacy wap ";
        $sql .= "INNER JOIN weno_pharmacy wp ON wap.primary_ncpdp = wp.ncpdp_safe ";
        $sql .= "WHERE wap.pid = ?";
        $result = sqlQuery($sql, array($pid));

        return $result;
    }

    public function getWenoAlternatePharm($pid)
    {
        $sql = "SELECT wap.pid, wap.alternate_ncpdp, wp.business_name, ";
        $sql .= "wp.city, wp.address_line_1, wp.ncpdp_safe, wp.state FROM weno_assigned_pharmacy wap ";
        $sql .= "INNER JOIN weno_pharmacy wp ON wap.alternate_ncpdp = wp.ncpdp_safe ";
        $sql .= "WHERE wap.pid = ?";

        $result = sqlQuery($sql, array($pid));

        return $result;
    }

    public function checkWenoPharmacyLog()
    {
        $db_exist = sqlStatement("SELECT * FROM weno_download_log ORDER BY `created_at` DESC, `id` DESC LIMIT 1");
        if (empty($db_exist)) {
            return "empty";
        } else {
            return true;
        }
    }

    public function removeWenoPharmacies()
    {
        $sql = "TRUNCATE TABLE weno_pharmacy";
        sqlStatement($sql);
    }

    public function checkWenoDb()
    {
        $has_data = sqlQuery("SELECT 1 FROM weno_pharmacy LIMIT 1");
        if (!empty($has_data)) {
            return true;
        } else {
            return false;
        }
    }

    public function getWenoPharmacy($pid)
    {
        $sql = "SELECT * FROM weno_assigned_pharmacy WHERE pid = ?";

        $result = sqlStatement($sql, [$pid]);

        if (sqlNumRows($result) <= 0) {
            return false;
        } else {
            return $result;
        }
    }
}
