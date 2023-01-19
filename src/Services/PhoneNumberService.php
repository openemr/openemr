<?php

/**
 * PhoneService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class PhoneNumberService extends BaseService
{
    public function __construct()
    {
    }

    public function validate($phoneNumber)
    {
        $validator = new Validator();

        $validator->optional('country_code')->lengthBetween(1, 5);
        $validator->optional('area_code')->lengthBetween(1, 3);
        $validator->optional('prefix')->lengthBetween(1, 3);
        $validator->optional('number')->lengthBetween(1, 4);
        $validator->optional('type')->lengthBetween(1, 11);
        $validator->optional('foreign_id')->lengthBetween(1, 11);

        return $validator->validate($phoneNumber);
    }

    public function insert($data, $foreignId)
    {
        $freshId = $this->getFreshId("id", "phone_numbers");

        $phone_parts = array();
        preg_match(
            "/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
            $data['phone'],
            $phone_parts
        );

        $data['country_code'] = "+1";
        $data['area_code'] = $phone_parts[1] ?? '';
        $data['prefix'] = $phone_parts[2] ?? '';
        $data['number'] = $phone_parts[3] ?? '';
        $data['type'] = "2";

        $phoneNumbersSql  = " INSERT INTO phone_numbers SET";
        $phoneNumbersSql .= "     id=?,";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=?,";
        $phoneNumbersSql .= "     type=?,";
        $phoneNumbersSql .= "     foreign_id=?";

        $phoneNumbersSqlResults = sqlInsert(
            $phoneNumbersSql,
            array(
                $freshId,
                $data["country_code"],
                $data["area_code"],
                $data["prefix"],
                $data["number"],
                $data["type"],
                $foreignId
            )
        );

        if (!$phoneNumbersSqlResults) {
            return false;
        }

        return $freshId;
    }

    public function update($data, $foreignId)
    {
        $phone_parts = array();
        preg_match(
            "/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
            $data['phone'],
            $phone_parts
        );

        $data['country_code'] = "+1";
        $data['area_code'] = $phone_parts[1] ?? '';
        $data['prefix'] = $phone_parts[2] ?? '';
        $data['number'] = $phone_parts[3] ?? '';
        $data['type'] = "2";

        $phoneNumbersSql  = " UPDATE phone_numbers SET";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=?,";
        $phoneNumbersSql .= "     type=?";
        $phoneNumbersSql .= "     WHERE foreign_id=?";

        $phoneNumbersSqlResults = sqlStatement(
            $phoneNumbersSql,
            array(
                $data["country_code"] ?? null,
                $data["area_code"] ?? null,
                $data["prefix"] ?? null,
                $data["number"] ?? null,
                $data["type"] ?? null,
                $foreignId
            )
        );

        if (!$phoneNumbersSqlResults) {
            return false;
        }

        $phoneNumbersIdSqlResults = sqlQuery("SELECT id FROM phone_numbers WHERE foreign_id=?", $foreignId);

        return $phoneNumbersIdSqlResults["id"];
    }

    public function getOneByForeignId($foreignId)
    {
        $sql = "SELECT * FROM phone_numbers WHERE foreign_id=?";
        return sqlQuery($sql, array($foreignId));
    }
}
