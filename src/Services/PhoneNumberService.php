<?php

/**
 * PhoneService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use Particle\Validator\Validator;

class PhoneNumberService extends BaseService
{
    public const COUNTRY_CODE = "+1";
    public string $area_code;
    public string $prefix;
    public string $number;
    public int    $type;
    private int   $foreignId;

    public function __construct()
    {
        $this->area_code = $area_code ?? '';
        $this->prefix = $prefix ?? '';
        $this->number = $number ?? '';
        $this->type = $type ?? 2;
        $this->foreignId = $foreignId ?? 0;
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
        $this->foreignId = $foreignId;

        $this->getPhoneParts($data['phone']);

        $phoneNumbersSql  = " INSERT INTO phone_numbers SET";
        $phoneNumbersSql .= "     id=?,";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=?,";
        $phoneNumbersSql .= "     type=?,";
        $phoneNumbersSql .= "     foreign_id=?";

        $phoneNumbersSqlResults = QueryUtils::sqlInsert(
            $phoneNumbersSql,
            array(
                $freshId,
                self::COUNTRY_CODE,
                $this->area_code,
                $this->prefix,
                $this->number,
                $this->type,
                $this->foreignId
            )
        );

        if (!$phoneNumbersSqlResults) {
            return false;
        }

        return $freshId;
    }

    public function update($data, $foreignId)
    {
        $this->foreignId = $foreignId;
        $this->getPhoneParts($data['phone']);

        $phoneNumbersSql  = " UPDATE phone_numbers SET";
        $phoneNumbersSql .= "     country_code=?,";
        $phoneNumbersSql .= "     area_code=?,";
        $phoneNumbersSql .= "     prefix=?,";
        $phoneNumbersSql .= "     number=? ";
        $phoneNumbersSql .= "     WHERE foreign_id=? AND type=?";

        $phoneNumbersSqlResults = sqlStatement(
            $phoneNumbersSql,
            array(
                self::COUNTRY_CODE,
                $this->area_code ,
                $this->prefix,
                $this->number,
                $this->foreignId,
                $this->type
            )
        );

        if (!$phoneNumbersSqlResults) {
            return false;
        }

        $phoneNumbersIdSqlResults = sqlQuery("SELECT id FROM phone_numbers WHERE foreign_id=?", $this->foreignId);

        if (empty($phoneNumbersIdSqlResults)) {
            $this->insert($data, $foreignId);
        }
        return $phoneNumbersIdSqlResults["id"] ?? null;
    }

    public function getPhoneParts(string $phone_number)
    {
        $phone_parts = array();
        preg_match(
            "/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",
            $phone_number,
            $phone_parts
        );

        $this->area_code = $phone_parts[1] ?? '';
        $this->prefix = $phone_parts[2] ?? '';
        $this->number = $phone_parts[3] ?? '';
    }

    public function getOneByForeignId($foreignId)
    {
        $sql = "SELECT * FROM phone_numbers WHERE foreign_id=?";
        return sqlQuery($sql, array($foreignId));
    }
}
