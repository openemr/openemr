<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @author  Kofi Appiah <kkappiah@medsov.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2023 Omega Systems Group International <info@omegasystemsgroup.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

use Exception;

class LogDataInsert
{
    public function __construct()
    {
    }
    public function insertPrescriptions($insertdata)
    {
        //Weno would want a full database replacement on a weekly basis
        // hence we truncate the database first before inserting
        $db_exist = sqlQuery("SELECT 1 FROM weno_pharmacy LIMIT 1");

        if (!empty($db_exist)) {
            $this->removeWenoPharmacies();
        }

        $sql = "INSERT INTO prescriptions SET "
            . "active = ?, "
            . "date_added = ?, "
            . "patient_id = ?, "
            . "drug = ?, "
            . "form = ?, "
            . "quantity = ?, "
            . "refills = ?, "
            . "substitute = ?,"
            . "note = ?, "
            . "rxnorm_drugcode = ?, "
            . "external_id = ?, "
            . "indication = ?, "
            . "start_date = ? ";

        try {
            sqlInsert($sql, [
                $insertdata['active'],
                $insertdata['date_added'],
                $insertdata['patient_id'],
                $insertdata['drug'],
                $insertdata['form'],
                $insertdata['quantity'],
                $insertdata['refills'],
                $insertdata['substitute'],
                $insertdata['note'],
                $insertdata['rxnorm_drugcode'],
                $insertdata['provider_id'],
                $insertdata['prescriptionguid'],
                $insertdata['start_date']
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function insertPharmacies($insertdata)
    {
        $sql = "INSERT INTO weno_pharmacy SET "
            . "ncpdp = ?, "
            . "npi = ?, "
            . "business_name = ?, "
            . "address_line_1 = ?, "
            . "address_line_2 = ?, "
            . "city = ?, "
            . "state = ?, "
            . "zipcode = ?,"
            . "country_code = ?, "
            . "international = ?, "
            . "pharmacy_phone = ?, "
            . "on_weno = ?, "
            . "test_pharmacy = ?, "
            . "state_wide_mail_order = ?, "
            . "24hr = ? ";

        try {
            sqlInsert($sql, [
                $insertdata['ncpdp'],
                $insertdata['npi'],
                $insertdata['business_name'],
                $insertdata['address_line_1'],
                $insertdata['address_line_2'],
                $insertdata['city'],
                $insertdata['state'],
                $insertdata['zipcode'],
                $insertdata['country'],
                $insertdata['international'],
                $insertdata['pharmacy_phone'],
                $insertdata['on_weno'],
                $insertdata['test_pharmacy'],
                $insertdata['state_wide_mail'],
                $insertdata['fullDay'],
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function updatePharmacies($insertdata)
    {
        $sql = "UPDATE weno_pharmacy SET "
        . "npi = ?, "
        . "business_name = ?, "
        . "address_line_1 = ?, "
        . "address_line_2 = ?, "
        . "city = ?, "
        . "state = ?, "
        . "zipcode = ?,"
        . "country_code = ?, "
        . "international = ?, "
        . "pharmacy_phone = ?, "
       // . "on_weno = ?, "
        . "test_pharmacy = ?, "
        . "state_wide_mail_order = ?, "
        . "24hr = ? "
        . "WHERE ncpdp = ?";

        try {
            sqlStatement($sql, [
                $insertdata['npi'],
                $insertdata['business_name'],
                $insertdata['address_line_1'],
                $insertdata['address_line_2'],
                $insertdata['city'],
                $insertdata['state'],
                $insertdata['zipcode'],
                $insertdata['country'],
                $insertdata['international'],
                $insertdata['pharmacy_phone'],
                //$insertdata['on_weno'], //pharmacy lite directory dooesnt contain on_weno field
                $insertdata['test_pharmacy'],
                $insertdata['state_wide_mail'],
                $insertdata['fullDay'],
                $insertdata['ncpdp']
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function removeWenoPharmacies()
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
}
