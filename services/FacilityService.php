<?php
/**
 * FacilityService
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
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

namespace services;

class FacilityService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    public function getAll()
    {
        return $this->get(array("order" => "ORDER BY FAC.name ASC"));
    }

    public function getPrimaryBusinessEntity($options = null)
    {
        if (!empty($options) && !empty($options["useLegacyImplementation"])) {
            return getPrimaryBusinessEntityLegacy();
        }

        $args = array(
            "where" => "WHERE FAC.primary_business_entity = 1",
            "data"  => null,
            "limit" => 1
        );

        if (!empty($options) && !empty($options["excludedId"])) {
            $args["where"] .= " AND FAC.id != ?";
            $args["data"] = $options["excludedId"];
            return $this->get($args);
        }

        return $this->get($args);
    }

    public function getAllServiceLocations($options = null)
    {
        $args = array(
            "where" => null,
            "order" => "ORDER BY FAC.name ASC"
        );

        if (!empty($options) && !empty($options["orderField"])) {
            $args["order"] = "ORDER BY FAC." . $options["orderField"] . " ASC";
        }

        $args["where"] = "WHERE FAC.service_location = 1";

        return $this->get($args);
    }

    public function getPrimaryBillingLocation() {
        return $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.id DESC",
            "limit" => 1
        ));
    }

    public function getAllBillingLocations()
    {
        return $this->get(array(
            "where" => "WHERE FAC.billing_location = 1",
            "order" => "ORDER BY FAC.id ASC"
        ));
    }

    public function getById($id)
    {
        return $this->get(array(
            "where" => "WHERE FAC.id = ?",
            "data"  => array($id),
            "limit" => 1
        ));
    }

    public function getFacilityForUser($userId)
    {
        return $this->get(array(
            "where" => "WHERE USER.id = ?",
            "data"  => array($userId),
            "join"  => "JOIN users USER ON FAC.name = USER.facility",
            "limit" => 1
        ));
    }

    public function getFacilityForUserFormatted($userId)
    {
        $facility = $this->getFacilityForUser($userId);

        if (!empty($facility)) {
            $formatted  = "";
            $formatted .= $facility["name"];
            $formatted .= "\n";
            $formatted .= $facility["street"];
            $formatted .= "\n";
            $formatted .= $facility["city"];
            $formatted .= "\n";
            $formatted .= $facility["state"];
            $formatted .= "\n";
            $formatted .= $facility["postal_code"];

            return array("facility_address" => $formatted);
        }

        return array("facility_address" => "");
    }

    public function getFacilityForEncounter($encounterId)
    {
        return $this->get(array(
            "where" => "WHERE ENC.encounter = ?",
            "data"  => array($encounterId),
            "join"  => "JOIN form_encounter ENC ON FAC.id = ENC.facility_id",
            "limit" => 1
        ));
    }

    public function update($data)
    {
        $sql  = " UPDATE facility SET";
        $sql .= "     name='" . $data["name"] . "',";
        $sql .= "     phone='" . $data["phone"] . "',";
        $sql .= "     fax='" . $data["fax"] . "',";
        $sql .= "     street='" . $data["street"] . "',";
        $sql .= "     city='" . $data["city"] . "',";
        $sql .= "     state='" . $data["state"] . "',";
        $sql .= "     postal_code='" . $data["postal_code"] . "',";
        $sql .= "     country_code='" . $data["country_code"] . "',";
        $sql .= "     federal_ein='" . $data["federal_ein"] . "',";
        $sql .= "     website='" . $data["website"] . "',";
        $sql .= "     email='" . $data["email"] . "',";
        $sql .= "     color='" . $data["color"] . "',";
        $sql .= "     service_location='" . $data["service_location"] . "',";
        $sql .= "     billing_location='" . $data["billing_location"] . "',";
        $sql .= "     accepts_assignment='" . $data["accepts_assignment"] . "',";
        $sql .= "     pos_code='" . $data["pos_code"] . "',";
        $sql .= "     domain_identifier='" . $data["domain_identifier"] . "',";
        $sql .= "     attn='" . $data["attn"] . "',";
        $sql .= "     tax_id_type='" . $data["tax_id_type"] . "',";
        $sql .= "     primary_business_entity='" . $data["primary_business_entity"] . "',";
        $sql .= "     facility_npi='" . $data["facility_npi"] . "',";
        $sql .= "     facility_code='" . $data["facility_code"] . "'";
        $sql .= " WHERE id='" . $data["fid"] . "'";

        return sqlStatement($sql);
    }

    public function insert($data)
    {
        $sql  = " INSERT INTO facility SET";
        $sql .= "     name='" . $data["name"] . "',";
        $sql .= "     phone='" . $data["phone"] . "',";
        $sql .= "     fax='" . $data["fax"] . "',";
        $sql .= "     street='" . $data["street"] . "',";
        $sql .= "     city='" . $data["city"] . "',";
        $sql .= "     state='" . $data["state"] . "',";
        $sql .= "     postal_code='" . $data["postal_code"] . "',";
        $sql .= "     country_code='" . $data["country_code"] . "',";
        $sql .= "     federal_ein='" . $data["federal_ein"] . "',";
        $sql .= "     website='" . $data["website"] . "',";
        $sql .= "     email='" . $data["email"] . "',";
        $sql .= "     color='" . $data["color"] . "',";
        $sql .= "     service_location='" . $data["service_location"] . "',";
        $sql .= "     billing_location='" . $data["billing_location"] . "',";
        $sql .= "     accepts_assignment='" . $data["accepts_assignment"] . "',";
        $sql .= "     pos_code='" . $data["pos_code"] . "',";
        $sql .= "     domain_identifier='" . $data["domain_identifier"] . "',";
        $sql .= "     attn='" . $data["attn"] . "',";
        $sql .= "     tax_id_type='" . $data["tax_id_type"] . "',";
        $sql .= "     primary_business_entity='" . $data["primary_business_entity"] . "',";
        $sql .= "     facility_npi='" . $data["facility_npi"] . "',";
        $sql .= "     facility_code='" . $data["facility_code"] . "'";

        return sqlInsert($sql);
    }

    /**
     * Shared getter for the various specific facility getters.
     *
     * @param $map - Query information.
     * @return array of associative arrays | one associative array.
     */
    private function get($map)
    {
        $where = isset($map["where"]) ? $map["where"] : null;
        $data  = isset($map["data"])  ? $map["data"]  : null;
        $join  = isset($map["join"])  ? $map["join"]  : null;
        $order = isset($map["order"]) ? $map["order"] : null;
        $limit = isset($map["limit"]) ? $map["limit"] : null;

        $sql  = " SELECT FAC.id,";
        $sql .= "        FAC.name,";
        $sql .= "        FAC.phone,";
        $sql .= "        FAC.fax,";
        $sql .= "        FAC.street,";
        $sql .= "        FAC.city,";
        $sql .= "        FAC.state,";
        $sql .= "        FAC.postal_code,";
        $sql .= "        FAC.country_code,";
        $sql .= "        FAC.federal_ein,";
        $sql .= "        FAC.website,";
        $sql .= "        FAC.email,";
        $sql .= "        FAC.service_location,";
        $sql .= "        FAC.billing_location,";
        $sql .= "        FAC.accepts_assignment,";
        $sql .= "        FAC.pos_code,";
        $sql .= "        FAC.x12_sender_id,";
        $sql .= "        FAC.attn,";
        $sql .= "        FAC.domain_identifier,";
        $sql .= "        FAC.facility_npi,";
        $sql .= "        FAC.tax_id_type,";
        $sql .= "        FAC.color,";
        $sql .= "        FAC.primary_business_entity,";
        $sql .= "        FAC.facility_code,";
        $sql .= "        FAC.extra_validation";
        $sql .= " FROM facility FAC";

        $sql .= !empty($join)  ? " " . $join        : "";
        $sql .= !empty($where) ? " " . $where       : "";
        $sql .= !empty($order) ? " " . $order       : "";
        $sql .= !empty($limit) ? " LIMIT " . $limit : "";

        if (!empty($data)) {
            if (empty($limit)) {
                $multipleResults = sqlStatement($sql, $data);
                $results = array();

                while ($row = sqlFetchArray($multipleResults)) {
                    array_push($results, $row);
                }

                return $results;
            }

            return sqlQuery($sql, $data);
        }

        if (empty($limit)) {
            $multipleResults = sqlStatement($sql);
            $results = array();

            while ($row = sqlFetchArray($multipleResults)) {
                array_push($results, $row);
            }

            return $results;
        }

        return sqlQuery($sql);
    }

    private function getPrimaryBusinessEntityLegacy()
    {
        return $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.accepts_assignment DESC, FAC.id ASC",
            "limit" => 1
        ));
    }
}
