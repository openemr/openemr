<?php
/**
 * InsuranceCompanyService
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
use OpenEMR\Services\AddressService;

class InsuranceCompanyService
{
    private $addressService = null;
    
    public function __construct()
    {
        $this->addressService = new AddressService();
    }

    public function validate($insuranceCompany)
    {
        $validator = new Validator();

        $validator->required('name')->lengthBetween(2, 255);
        $validator->optional('attn')->lengthBetween(2, 255);
        $validator->optional('cms_id')->lengthBetween(2, 15);
        $validator->optional('alt_cms_id')->lengthBetween(2, 15);
        $validator->optional('ins_type_code')->numeric();
        $validator->optional('x12_receiver_id')->lengthBetween(2, 25);
        $validator->optional('x12_default_partner_id')->numeric();

        return $validator->validate($insuranceCompany);
    }

    public function getAll()
    {
        $sql  = " SELECT i.id,";
        $sql .= "        i.name,";
        $sql .= "        i.attn,";
        $sql .= "        i.cms_id,";
        $sql .= "        i.ins_type_code,";
        $sql .= "        i.x12_receiver_id,";
        $sql .= "        i.x12_default_partner_id,";
        $sql .= "        i.alt_cms_id,";
        $sql .= "        i.inactive,";
        $sql .= "        a.line1,";
        $sql .= "        a.line2,";
        $sql .= "        a.city,";
        $sql .= "        a.state,";
        $sql .= "        a.zip,";
        $sql .= "        a.country";
        $sql .= " FROM insurance_companies i";
        $sql .= " JOIN addresses a ON i.id = a.foreign_id";

        $statementResults = sqlStatement($sql);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getInsuranceTypes()
    {
        return array(
            1 => xl('Other HCFA'),
            2 => xl('Medicare Part B'),
            3 => xl('Medicaid'),
            4 => xl('ChampUSVA'),
            5 => xl('ChampUS'),
            6 => xl('Blue Cross Blue Shield'),
            7 => xl('FECA'),
            8 => xl('Self Pay'),
            9 => xl('Central Certification'),
            10 => xl('Other Non-Federal Programs'),
            11 => xl('Preferred Provider Organization (PPO)'),
            12 => xl('Point of Service (POS)'),
            13 => xl('Exclusive Provider Organization (EPO)'),
            14 => xl('Indemnity Insurance'),
            15 => xl('Health Maintenance Organization (HMO) Medicare Risk'),
            16 => xl('Automobile Medical'),
            17 => xl('Commercial Insurance Co.'),
            18 => xl('Disability'),
            19 => xl('Health Maintenance Organization'),
            20 => xl('Liability'),
            21 => xl('Liability Medical'),
            22 => xl('Other Federal Program'),
            23 => xl('Title V'),
            24 => xl('Veterans Administration Plan'),
            25 => xl('Workers Compensation Health Plan'),
            26 => xl('Mutually Defined')
        );
    }

    public function getFreshId() {
        $id = sqlQuery("SELECT MAX(id)+1 AS id FROM insurance_companies");

        return $id['id'];
    }

    public function insert($data)
    {
        $freshId = $this->getFreshId();

        $sql  = " INSERT INTO insurance_companies SET";
        $sql .= "     id='" . add_escape_custom($freshId) . "',";
        $sql .= "     name='" . add_escape_custom($data["name"]) . "',";
        $sql .= "     attn='" . add_escape_custom($data["attn"]) . "',";
        $sql .= "     cms_id='" . add_escape_custom($data["cms_id"]) . "',";
        $sql .= "     ins_type_code='" . add_escape_custom($data["ins_type_code"]) . "',";
        $sql .= "     x12_receiver_id='" . add_escape_custom($data["x12_receiver_id"]) . "',";
        $sql .= "     x12_default_partner_id='" . add_escape_custom($data["x12_default_partner_id"]) . "',";
        $sql .= "     alt_cms_id='" . add_escape_custom($data["alt_cms_id"]) . "'";

        $insuranceResults = sqlInsert($sql);

        if (!$insuranceResults) {
            return false;
        }

        $addressesResults = $this->addressService->insert($data, $freshId);

        if (!$addressesResults) {
            return false;
        }

        return $freshId;
    }

}
