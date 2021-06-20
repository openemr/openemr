<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

class LogDataInsert
{

    public function __construct()
    {
    }
    public function insertPrescriptions($insertdata)
    {
        $sql = "INSERT INTO prescriptions SET "
            . "id = ?, "
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
            . "txDate = ? ";

        try {
            sqlInsert($sql, [
                $insertdata['id'],
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
                $insertdata['txDate']
            ]);
            echo "inserted data! <br>";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
