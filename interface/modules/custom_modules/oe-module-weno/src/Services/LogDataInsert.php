<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @author  Kofi Appiah <kkappiah@medsov.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2023 Omega Systems Group International <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use Exception;

class LogDataInsert
{
    public function __construct()
    {
    }
    public function insertPrescriptions($insertdata)
    {
        $sql = "INSERT INTO prescriptions SET ";
        $sql .= "active = ?, ";
        $sql .= "date_added = ?, ";
        $sql .= "datetime = NOW(), ";
        $sql .= "patient_id = ?, ";
        $sql .= "provider_id = ?, ";
        $sql .= "drug = ?, ";
        $sql .= "quantity = ?, ";
        $sql .= "refills = ?, ";
        $sql .= "substitute = ?,";
        $sql .= "note = ?, ";
        $sql .= "rxnorm_drugcode = ?, ";
        $sql .= "external_id = ?, ";
        $sql .= "indication = ? ";

        try {
            sqlInsert($sql, [
                $insertdata['active'],
                $insertdata['date_added'],
                $insertdata['patient_id'],
                $insertdata['user_id'],
                $insertdata['drug'],
                $insertdata['quantity'],
                $insertdata['refills'],
                $insertdata['substitute'],
                $insertdata['note'],
                $insertdata['rxnorm_drugcode'],
                $insertdata['provider_id'],
                $insertdata['prescriptionguid']
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updatePrescriptions($updatedata)
    {
        $sql = "UPDATE prescriptions SET ";
        $sql .= "active = ?, ";
        $sql .= "date_added = ?, ";
        $sql .= "datetime = NOW(), ";
        $sql .= "patient_id = ?, ";
        $sql .= "provider_id = ?, ";
        $sql .= "drug = ?, ";
        $sql .= "quantity = ?, ";
        $sql .= "refills = ?, ";
        $sql .= "substitute = ?, ";
        $sql .= "note = ?, ";
        $sql .= "rxnorm_drugcode = ?, ";
        $sql .= "external_id = ? ";
        $sql .= "WHERE indication = ?";

        try {
            sqlQuery($sql, [
                $updatedata['active'],
                $updatedata['date_added'],
                $updatedata['patient_id'],
                $updatedata['user_id'],
                $updatedata['drug'],
                $updatedata['quantity'],
                $updatedata['refills'],
                $updatedata['substitute'],
                $updatedata['note'],
                $updatedata['rxnorm_drugcode'],
                $updatedata['provider_id'],
                $updatedata['prescriptionguid']
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
