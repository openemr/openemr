<?php

/**
 * AddressService
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

use OpenEMR\Common\Database\QueryUtils;
use Particle\Validator\Validator;

class AddressService extends BaseService
{
    public function __construct()
    {
    }

    public function validate($insuranceCompany)
    {
        $validator = new Validator();

        $validator->optional('line1')->lengthBetween(2, 255);
        $validator->optional('line2')->lengthBetween(2, 255);
        $validator->optional('city')->lengthBetween(2, 255);
        $validator->optional('state')->lengthBetween(2, 35);
        $validator->optional('zip')->lengthBetween(2, 10);
        $validator->optional('plus_four')->lengthBetween(2, 4);
        $validator->optional('country')->lengthBetween(2, 255);

        return $validator->validate($insuranceCompany);
    }

    public function getAddressFromRecordAsString(array $addressRecord)
    {
        // works for patients and users
        $address = [];
        if (!empty($addressRecord['street'])) {
            $address[] = $addressRecord['street'];
            $address[] = "\n";
        }
        if (!empty($addressRecord['city'])) {
            $address[] = $addressRecord['city'];
            $address[] = ", ";
        }
        if (!empty($addressRecord['state'])) {
            $address[] = $addressRecord['state'];
            $address[] = " ";
        }
        if (!empty($addressRecord['postal_code'])) {
            $address[] = $addressRecord['postal_code'];
            $address[] = " ";
        }
        if (!empty($addressRecord['country_code'])) {
            $address[] = $addressRecord['country_code'];
        }
        return implode("", $address);
    }

    public function insert($data, $foreignId)
    {
        $freshId = $this->getFreshId("id", "addresses");

        $addressesSql  = " INSERT INTO addresses SET";
        $addressesSql .= "     id=?,";
        $addressesSql .= "     line1=?,";
        $addressesSql .= "     line2=?,";
        $addressesSql .= "     city=?,";
        $addressesSql .= "     state=?,";
        $addressesSql .= "     zip=?,";
        $addressesSql .= "     plus_four=?,";
        $addressesSql .= "     country=?,";
        $addressesSql .= "     foreign_id=?";

        $addressesSqlResults = QueryUtils::sqlInsert(
            $addressesSql,
            array(
                $freshId,
                $data["line1"],
                $data["line2"],
                $data["city"],
                $data["state"],
                $data["zip"],
                $data["plus_four"] ?? null,
                $data["country"],
                $foreignId
            )
        );

        if (!$addressesSqlResults) {
            return false;
        }

        return $freshId;
    }

    public function update($data, $foreignId)
    {
        $addressesSql  = " UPDATE addresses SET";
        $addressesSql .= "     line1=?,";
        $addressesSql .= "     line2=?,";
        $addressesSql .= "     city=?,";
        $addressesSql .= "     state=?,";
        $addressesSql .= "     zip=?,";
        $addressesSql .= "     plus_four=?,";
        $addressesSql .= "     country=?";
        $addressesSql .= "     WHERE foreign_id=?";

        $addressesSqlResults = sqlStatement(
            $addressesSql,
            array(
                $data["line1"],
                $data["line2"],
                $data["city"],
                $data["state"],
                $data["zip"],
                $data["plus_four"] ?? null,
                $data["country"],
                $foreignId
            )
        );

        if (!$addressesSqlResults) {
            return false;
        }

        $addressIdSqlResults = sqlQuery("SELECT id FROM addresses WHERE foreign_id=?", $foreignId);

        return $addressIdSqlResults["id"];
    }

    public function getOneByForeignId($foreignId)
    {
        $sql = "SELECT * FROM addresses WHERE foreign_id=?";
        return sqlQuery($sql, array($foreignId));
    }
}
