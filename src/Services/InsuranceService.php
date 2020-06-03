<?php

/**
 * InsuranceService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
        return $this->getOne($pid, $type) !== false;
    }

    public function update($pid, $type, $data)
    {
        $sql  = " UPDATE insurance_data SET ";
        $sql .= "   provider=?,";
        $sql .= "   plan_name=?,";
        $sql .= "   policy_number=?,";
        $sql .= "   group_number=?,";
        $sql .= "   subscriber_lname=?,";
        $sql .= "   subscriber_mname=?,";
        $sql .= "   subscriber_fname=?,";
        $sql .= "   subscriber_relationship=?,";
        $sql .= "   subscriber_ss=?,";
        $sql .= "   subscriber_DOB=?,";
        $sql .= "   subscriber_street=?,";
        $sql .= "   subscriber_postal_code=?,";
        $sql .= "   subscriber_city=?,";
        $sql .= "   subscriber_state=?,";
        $sql .= "   subscriber_country=?,";
        $sql .= "   subscriber_phone=?,";
        $sql .= "   subscriber_employer=?,";
        $sql .= "   subscriber_employer_street=?,";
        $sql .= "   subscriber_employer_postal_code=?,";
        $sql .= "   subscriber_employer_state=?,";
        $sql .= "   subscriber_employer_country=?,";
        $sql .= "   subscriber_employer_city=?,";
        $sql .= "   copay=?,";
        $sql .= "   date=?,";
        $sql .= "   subscriber_sex=?,";
        $sql .= "   accept_assignment=?,";
        $sql .= "   policy_type=?";
        $sql .= "   WHERE pid=?";
        $sql .= "     AND type=?";

        return sqlStatement(
            $sql,
            array(
                $data["provider"],
                $data["plan_name"],
                $data["policy_number"],
                $data["group_number"],
                $data["subscriber_lname"],
                $data["subscriber_mname"],
                $data["subscriber_fname"],
                $data["subscriber_relationship"],
                $data["subscriber_ss"],
                $data["subscriber_DOB"],
                $data["subscriber_street"],
                $data["subscriber_postal_code"],
                $data["subscriber_city"],
                $data["subscriber_state"],
                $data["subscriber_country"],
                $data["subscriber_phone"],
                $data["subscriber_employer"],
                $data["subscriber_employer_street"],
                $data["subscriber_employer_postal_code"],
                $data["subscriber_employer_state"],
                $data["subscriber_employer_country"],
                $data["subscriber_employer_city"],
                $data["copay"],
                $data["date"],
                $data["subscriber_sex"],
                $data["accept_assignment"],
                $data["policy_type"],
                $pid,
                $type
            )
        );
    }

    public function insert($pid, $type, $data)
    {
        if ($this->doesInsuranceTypeHaveEntry($pid, $type)) {
            return $this->update($pid, $type, $data);
        }

        $sql  = " INSERT INTO insurance_data SET ";
        $sql .= "   type=?,";
        $sql .= "   provider=?,";
        $sql .= "   plan_name=?,";
        $sql .= "   policy_number=?,";
        $sql .= "   group_number=?,";
        $sql .= "   subscriber_lname=?,";
        $sql .= "   subscriber_mname=?,";
        $sql .= "   subscriber_fname=?,";
        $sql .= "   subscriber_relationship=?,";
        $sql .= "   subscriber_ss=?,";
        $sql .= "   subscriber_DOB=?,";
        $sql .= "   subscriber_street=?,";
        $sql .= "   subscriber_postal_code=?,";
        $sql .= "   subscriber_city=?,";
        $sql .= "   subscriber_state=?,";
        $sql .= "   subscriber_country=?,";
        $sql .= "   subscriber_phone=?,";
        $sql .= "   subscriber_employer=?,";
        $sql .= "   subscriber_employer_street=?,";
        $sql .= "   subscriber_employer_postal_code=?,";
        $sql .= "   subscriber_employer_state=?,";
        $sql .= "   subscriber_employer_country=?,";
        $sql .= "   subscriber_employer_city=?,";
        $sql .= "   copay=?,";
        $sql .= "   date=?,";
        $sql .= "   pid=?,";
        $sql .= "   subscriber_sex=?,";
        $sql .= "   accept_assignment=?,";
        $sql .= "   policy_type=?";

        return sqlInsert(
            $sql,
            array(
                $type,
                $data["provider"],
                $data["plan_name"],
                $data["policy_number"],
                $data["group_number"],
                $data["subscriber_lname"],
                $data["subscriber_mname"],
                $data["subscriber_fname"],
                $data["subscriber_relationship"],
                $data["subscriber_ss"],
                $data["subscriber_DOB"],
                $data["subscriber_street"],
                $data["subscriber_postal_code"],
                $data["subscriber_city"],
                $data["subscriber_state"],
                $data["subscriber_country"],
                $data["subscriber_phone"],
                $data["subscriber_employer"],
                $data["subscriber_employer_street"],
                $data["subscriber_employer_postal_code"],
                $data["subscriber_employer_state"],
                $data["subscriber_employer_country"],
                $data["subscriber_employer_city"],
                $data["copay"],
                $data["date"],
                $pid,
                $data["subscriber_sex"],
                $data["accept_assignment"],
                $data["policy_type"]
            )
        );
    }
}
