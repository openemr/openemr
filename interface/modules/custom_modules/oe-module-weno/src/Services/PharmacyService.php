<?php

/**
 * WenoPharmacyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Omega Systems Group International. <info@omegasystemsgroup.com>
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
        $sql .= "alternate_ncpdp = ?, ";
        $sql .= "search_persist = ? ";

        try {
            sqlInsert($sql, [
                $pid,
                $data['primary_pharmacy'],
                $data['alternate_pharmacy'],
                $data['search_persist'],
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updatePatientWenoPharmacy($pid, $data)
    {
        // check if pharmacies already exist for patient
        if (!$this->getWenoPharmacy($pid)) {
            return $this->createWenoPharmaciesForPatient($pid, $data);
        }
        $sql = "UPDATE weno_assigned_pharmacy SET ";
        $sql .= "primary_ncpdp = ?, ";
        $sql .= "alternate_ncpdp = ?, ";
        $sql .= "search_persist = ? ";
        $sql .= "WHERE pid = ?";

        try {
            sqlInsert($sql, [
                $data['primary_pharmacy'],
                $data['alternate_pharmacy'],
                $data['search_persist'],
                $pid
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getWenoLastSearch($pid)
    {
        $sql = "SELECT `search_persist` FROM weno_assigned_pharmacy WHERE pid = ?";
        return json_decode(sqlQuery($sql, array($pid))['search_persist'] ?? '');
    }

    public function getWenoPrimaryPharm($pid): false|array|null
    {
        $sql = "SELECT wap.pid, wap.primary_ncpdp, wp.business_name, ";
        $sql .= "wp.city, wp.address_line_1, wp.ncpdp_safe, wp.state FROM weno_assigned_pharmacy wap ";
        $sql .= "INNER JOIN weno_pharmacy wp ON wap.primary_ncpdp = wp.ncpdp_safe ";
        $sql .= "WHERE wap.pid = ?";
        $result = sqlQuery($sql, array($pid));

        return $result;
    }

    public function getWenoAlternatePharm($pid): false|array|null
    {
        $sql = "SELECT wap.pid, wap.alternate_ncpdp, wp.business_name, ";
        $sql .= "wp.city, wp.address_line_1, wp.ncpdp_safe, wp.state FROM weno_assigned_pharmacy wap ";
        $sql .= "INNER JOIN weno_pharmacy wp ON wap.alternate_ncpdp = wp.ncpdp_safe ";
        $sql .= "WHERE wap.pid = ?";

        $result = sqlQuery($sql, array($pid));

        return $result;
    }

    public function removeWenoPharmacies(): void
    {
        $sql = "TRUNCATE TABLE weno_pharmacy";
        sqlStatement($sql);
    }

    public function checkWenoDb(): bool
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
