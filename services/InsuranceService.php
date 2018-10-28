<?php
/**
 * InsuranceService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;
use Particle\Validator\Validator;

class InsuranceService
{

    public function __construct()
    {
    }

    public function validate($data)
    {
        $validator = new Validator();

        $validator->required('pid')->numeric();
        $validator->required('type')->inArray(array('primary', 'secondary', 'tertiary'));
        $validator->required('provider')->numeric();
        $validator->required('plan_name')->lengthBetween(2, 255);
        $validator->required('policy_number')->lengthBetween(2, 255);
        $validator->required('group_number')->lengthBetween(2, 255);
        $validator->required('subscriber_lname')->lengthBetween(2, 255);
        $validator->optional('subscriber_mname')->lengthBetween(2, 255);
        $validator->required('subscriber_fname')->lengthBetween(2, 255);
        $validator->required('subscriber_relationship')->lengthBetween(2, 255);
        $validator->required('subscriber_ss')->lengthBetween(2, 255);
        $validator->required('subscriber_DOB')->datetime('Y-m-d');
        $validator->required('subscriber_street')->lengthBetween(2, 255);
        $validator->required('subscriber_postal_code')->lengthBetween(2, 255);
        $validator->required('subscriber_city')->lengthBetween(2, 255);
        $validator->required('subscriber_state')->lengthBetween(2, 255);
        $validator->required('subscriber_country')->lengthBetween(2, 255);
        $validator->required('subscriber_phone')->lengthBetween(2, 255);
        $validator->required('subscriber_sex')->lengthBetween(1, 25);
        $validator->required('accept_assignment')->lengthBetween(1, 5);
        $validator->required('policy_type')->lengthBetween(1, 25);
        $validator->optional('subscriber_employer')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_street')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_postal_code')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_state')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_country')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_city')->lengthBetween(2, 255);
        $validator->optional('copay')->lengthBetween(2, 255);
        $validator->optional('date')->datetime('Y-m-d');

        return $validator->validate($data);
    }

    public function getOne($pid, $type)
    {
        $sql = "SELECT * FROM insurance_data WHERE pid=? AND type=?";

        return sqlQuery($sql, array($pid, $type));
    }

    public function getAll($pid)
    {
        $sql = "SELECT * FROM insurance_data WHERE pid=?";

        $statementResults = sqlStatement($sql, array($pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function doesInsuranceTypeHaveEntry($pid, $type)
    {
        return $this->getOne($pid, $type) !== null;
    }

    public function update($pid, $type, $data)
    {
        $sql  = " UPDATE insurance_data SET ";
        $sql .= "   provider='" . add_escape_custom($data["provider"]) . "',";
        $sql .= "   plan_name='" . add_escape_custom($data["plan_name"]) . "',";
        $sql .= "   policy_number='" . add_escape_custom($data["policy_number"]) . "',";
        $sql .= "   group_number='" . add_escape_custom($data["group_number"]) . "',";
        $sql .= "   subscriber_lname='" . add_escape_custom($data["subscriber_lname"]) . "',";
        $sql .= "   subscriber_mname='" . add_escape_custom($data["subscriber_mname"]) . "',";
        $sql .= "   subscriber_fname='" . add_escape_custom($data["subscriber_fname"]) . "',";
        $sql .= "   subscriber_relationship='" . add_escape_custom($data["subscriber_relationship"]) . "',";
        $sql .= "   subscriber_ss='" . add_escape_custom($data["subscriber_ss"]) . "',";
        $sql .= "   subscriber_DOB='" . add_escape_custom($data["subscriber_DOB"]) . "',";
        $sql .= "   subscriber_street='" . add_escape_custom($data["subscriber_street"]) . "',";
        $sql .= "   subscriber_postal_code='" . add_escape_custom($data["subscriber_postal_code"]) . "',";
        $sql .= "   subscriber_city='" . add_escape_custom($data["subscriber_city"]) . "',";
        $sql .= "   subscriber_state='" . add_escape_custom($data["subscriber_state"]) . "',";
        $sql .= "   subscriber_country='" . add_escape_custom($data["subscriber_country"]) . "',";
        $sql .= "   subscriber_phone='" . add_escape_custom($data["subscriber_phone"]) . "',";
        $sql .= "   subscriber_employer='" . add_escape_custom($data["subscriber_employer"]) . "',";
        $sql .= "   subscriber_employer_street='" . add_escape_custom($data["subscriber_employer_street"]) . "',";
        $sql .= "   subscriber_employer_postal_code='" . add_escape_custom($data["subscriber_employer_postal_code"]) . "',";
        $sql .= "   subscriber_employer_state='" . add_escape_custom($data["subscriber_employer_state"]) . "',";
        $sql .= "   subscriber_employer_country='" . add_escape_custom($data["subscriber_employer_country"]) . "',";
        $sql .= "   subscriber_employer_city='" . add_escape_custom($data["subscriber_employer_city"]) . "',";
        $sql .= "   copay='" . add_escape_custom($data["copay"]) . "',";
        $sql .= "   date='" . add_escape_custom($data["date"]) . "',";
        $sql .= "   subscriber_sex='" . add_escape_custom($data["subscriber_sex"]) . "',";
        $sql .= "   accept_assignment='" . add_escape_custom($data["accept_assignment"]) . "',";
        $sql .= "   policy_type='" . add_escape_custom($data["policy_type"]) . "'";
        $sql .= "   WHERE pid='" . add_escape_custom($pid) . "'";
        $sql .= "     AND type='" . add_escape_custom($type) . "'";

        return sqlStatement($sql);
    }

    public function insert($pid, $type, $data)
    {
        if ($this->doesInsuranceTypeHaveEntry($pid, $type)) {
            return $this->update($pid, $type, $data);
        }

        $sql  = " INSERT INTO insurance_data SET ";
        $sql .= "   type='" . add_escape_custom($type) . "',";
        $sql .= "   provider='" . add_escape_custom($data["provider"]) . "',";
        $sql .= "   plan_name='" . add_escape_custom($data["plan_name"]) . "',";
        $sql .= "   policy_number='" . add_escape_custom($data["policy_number"]) . "',";
        $sql .= "   group_number='" . add_escape_custom($data["group_number"]) . "',";
        $sql .= "   subscriber_lname='" . add_escape_custom($data["subscriber_lname"]) . "',";
        $sql .= "   subscriber_mname='" . add_escape_custom($data["subscriber_mname"]) . "',";
        $sql .= "   subscriber_fname='" . add_escape_custom($data["subscriber_fname"]) . "',";
        $sql .= "   subscriber_relationship='" . add_escape_custom($data["subscriber_relationship"]) . "',";
        $sql .= "   subscriber_ss='" . add_escape_custom($data["subscriber_ss"]) . "',";
        $sql .= "   subscriber_DOB='" . add_escape_custom($data["subscriber_DOB"]) . "',";
        $sql .= "   subscriber_street='" . add_escape_custom($data["subscriber_street"]) . "',";
        $sql .= "   subscriber_postal_code='" . add_escape_custom($data["subscriber_postal_code"]) . "',";
        $sql .= "   subscriber_city='" . add_escape_custom($data["subscriber_city"]) . "',";
        $sql .= "   subscriber_state='" . add_escape_custom($data["subscriber_state"]) . "',";
        $sql .= "   subscriber_country='" . add_escape_custom($data["subscriber_country"]) . "',";
        $sql .= "   subscriber_phone='" . add_escape_custom($data["subscriber_phone"]) . "',";
        $sql .= "   subscriber_employer='" . add_escape_custom($data["subscriber_employer"]) . "',";
        $sql .= "   subscriber_employer_street='" . add_escape_custom($data["subscriber_employer_street"]) . "',";
        $sql .= "   subscriber_employer_postal_code='" . add_escape_custom($data["subscriber_employer_postal_code"]) . "',";
        $sql .= "   subscriber_employer_state='" . add_escape_custom($data["subscriber_employer_state"]) . "',";
        $sql .= "   subscriber_employer_country='" . add_escape_custom($data["subscriber_employer_country"]) . "',";
        $sql .= "   subscriber_employer_city='" . add_escape_custom($data["subscriber_employer_city"]) . "',";
        $sql .= "   copay='" . add_escape_custom($data["copay"]) . "',";
        $sql .= "   date='" . add_escape_custom($data["date"]) . "',";
        $sql .= "   pid='" . add_escape_custom($pid) . "',";
        $sql .= "   subscriber_sex='" . add_escape_custom($data["subscriber_sex"]) . "',";
        $sql .= "   accept_assignment='" . add_escape_custom($data["accept_assignment"]) . "',";
        $sql .= "   policy_type='" . add_escape_custom($data["policy_type"]) . "'";

        return sqlInsert($sql);
    }
}
