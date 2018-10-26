<?php
/**
 * AppointmentService
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

class AddressService
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
        $validator->optional('country')->lengthBetween(2, 255);

        return $validator->validate($insuranceCompany);
    }


    public function getFreshId() {
        $id = sqlQuery("SELECT MAX(id)+1 AS id FROM addresses");

        return $id['id'];
    }

    public function insert($data, $foreignId)
    {
        $freshId = $this->getFreshId();

        $addressesSql  = " INSERT INTO addresses SET";
        $addressesSql .= "     id='" . add_escape_custom($freshId) . "',";
        $addressesSql .= "     line1='" . add_escape_custom($data["line1"]) . "',";
        $addressesSql .= "     line2='" . add_escape_custom($data["line2"]) . "',";
        $addressesSql .= "     city='" . add_escape_custom($data["city"]) . "',";
        $addressesSql .= "     state='" . add_escape_custom($data["state"]) . "',";
        $addressesSql .= "     zip='" . add_escape_custom($data["zip"]) . "',";
        $addressesSql .= "     country='" . add_escape_custom($data["country"]) . "',";
        $addressesSql .= "     foreign_id='" . add_escape_custom($foreignId) . "'";

        $addressesSqlResults = sqlInsert($addressesSql);

        if (!$addressesSqlResults) {
            return false;
        }

        return $freshId;
    }

    public function update($data, $foreignId)
    {
        $addressesSql  = " UPDATE addresses SET";
        $addressesSql .= "     line1='" . add_escape_custom($data["line1"]) . "',";
        $addressesSql .= "     line2='" . add_escape_custom($data["line2"]) . "',";
        $addressesSql .= "     city='" . add_escape_custom($data["city"]) . "',";
        $addressesSql .= "     state='" . add_escape_custom($data["state"]) . "',";
        $addressesSql .= "     zip='" . add_escape_custom($data["zip"]) . "',";
        $addressesSql .= "     country='" . add_escape_custom($data["country"]) . "'";
        $addressesSql .= "     WHERE foreign_id='" . add_escape_custom($foreignId) . "'";

        $addressesSqlResults = sqlStatement($addressesSql);

        if (!$addressesSqlResults) {
            return false;
        }

        $addressIdSqlResults = sqlQuery("SELECT id FROM addresses WHERE foreign_id=?", $foreignId);

        return $addressIdSqlResults["id"];
    }
}