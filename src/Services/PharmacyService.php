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

namespace OpenEMR\Services;

class PharmacyService extends BaseService
{
    public function __construct()
    {
    }

    function createWenoPharmaciesForPatient($pid, $data){
        $sql = "INSERT INTO weno_assigned_pharmacy SET "
            . "pid = ?,"
            . "primary_ncpdp = ?,"
            . "alternate_ncpdp = ?";

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

    function updatePatientWenoPharmacy($pid, $data){
        $sql = "UPDATE weno_assigned_pharmacy SET "
        . "primary_ncpdp = ?, "
        . "alternate_ncpdp = ? "
        . "WHERE pid = ?";

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

    function getPatientPrimaryPharmacy($pid)
    {
        $sql = "SELECT pd.pharmacy_id, pd.pid, p.id, p.name, " .
            "a.foreign_id, a.city, a.line1 FROM patient_data pd " .
            "LEFT JOIN pharmacies p ON pd.pharmacy_id = p.id " .
            "LEFT JOIN addresses a ON p.id = a.foreign_id WHERE pd.pid = ?";
        $result = sqlQuery($sql, [$pid]);
        return json_encode($result);
    }

    public function getWenoPharmacyForPatient($pid)
    {
        if($pid !== 0){
            $sql = "SELECT p.pid,p.weno_pharmacy, w.state,w.ncpdp,w.business_name," .
            "w.address_line_1 FROM patient_data p " .
            "INNER JOIN weno_pharmacy w ON p.weno_pharmacy = w.ncpdp WHERE pid = ?";
            $result = sqlQuery($sql,array($pid));
        }
        $pharmacy_data = array(
            "name"  => $result['business_name'] ?? '',
            "ncpdp"     => $result['ncpdp']  ?? '',
            "state"     => $result['state']  ?? '',
            "address"     => $result['address_line_1']  ?? ''
        );

        return json_encode($pharmacy_data);
    }

    public function getWenoPrimaryPharm($pid){
        $sql = "SELECT wap.pid, wap.primary_ncpdp, wp.business_name, " .
            "wp.city, wp.address_line_1, wp.ncpdp, wp.state FROM weno_assigned_pharmacy wap " .
            "INNER JOIN weno_pharmacy wp ON wap.primary_ncpdp = wp.ncpdp " .
            "WHERE wap.pid = ?";
        $result = sqlQuery($sql, array($pid));

        return $result;
    }

    public function getWenoAlternateParm($pid){
        $sql = "SELECT wap.pid, wap.alternate_ncpdp, wp.business_name, " .
            "wp.city, wp.address_line_1, wp.ncpdp, wp.state FROM weno_assigned_pharmacy wap " .
            "INNER JOIN weno_pharmacy wp ON wap.alternate_ncpdp = wp.ncpdp " .
            "WHERE wap.pid = ?";
        $result = sqlQuery($sql, array($pid));

        return $result;
    }
}
