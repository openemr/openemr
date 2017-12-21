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

namespace OpenEMR\Services;

use OpenEMR\Common\Utils\QueryUtils;

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
            return $this->getPrimaryBusinessEntityLegacy();
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
            $args["order"] = "ORDER BY FAC." . escape_sql_column_name($options["orderField"], array("facility")) . " ASC";
        }

        $args["where"] = "WHERE FAC.service_location = 1";

        return $this->get($args);
    }

    public function getPrimaryBillingLocation()
    {
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
        $sql .= "     name='" . add_escape_custom($data["name"]) . "',";
        $sql .= "     phone='" . add_escape_custom($data["phone"]) . "',";
        $sql .= "     fax='" . add_escape_custom($data["fax"]) . "',";
        $sql .= "     street='" . add_escape_custom($data["street"]) . "',";
        $sql .= "     city='" . add_escape_custom($data["city"]) . "',";
        $sql .= "     state='" . add_escape_custom($data["state"]) . "',";
        $sql .= "     postal_code='" . add_escape_custom($data["postal_code"]) . "',";
        $sql .= "     country_code='" . add_escape_custom($data["country_code"]) . "',";
        $sql .= "     federal_ein='" . add_escape_custom($data["federal_ein"]) . "',";
        $sql .= "     website='" . add_escape_custom($data["website"]) . "',";
        $sql .= "     email='" . add_escape_custom($data["email"]) . "',";
        $sql .= "     color='" . add_escape_custom($data["color"]) . "',";
        $sql .= "     service_location='" . add_escape_custom($data["service_location"]) . "',";
        $sql .= "     billing_location='" . add_escape_custom($data["billing_location"]) . "',";
        $sql .= "     accepts_assignment='" . add_escape_custom($data["accepts_assignment"]) . "',";
        $sql .= "     pos_code='" . add_escape_custom($data["pos_code"]) . "',";
        $sql .= "     domain_identifier='" . add_escape_custom($data["domain_identifier"]) . "',";
        $sql .= "     attn='" . add_escape_custom($data["attn"]) . "',";
        $sql .= "     tax_id_type='" . add_escape_custom($data["tax_id_type"]) . "',";
        $sql .= "     primary_business_entity='" . add_escape_custom($data["primary_business_entity"]) . "',";
        $sql .= "     facility_npi='" . add_escape_custom($data["facility_npi"]) . "',";
        $sql .= "     facility_code='" . add_escape_custom($data["facility_code"]) . "',";
        $sql .= "     facility_taxonomy='" . add_escape_custom($data["facility_taxonomy"]) . "'";
        $sql .= " WHERE id='" . add_escape_custom($data["fid"]) . "'";

        return sqlStatement($sql);
    }

    public function insert($data)
    {
        $sql  = " INSERT INTO facility SET";
        $sql .= "     name='" . add_escape_custom($data["name"]) . "',";
        $sql .= "     phone='" . add_escape_custom($data["phone"]) . "',";
        $sql .= "     fax='" . add_escape_custom($data["fax"]) . "',";
        $sql .= "     street='" . add_escape_custom($data["street"]) . "',";
        $sql .= "     city='" . add_escape_custom($data["city"]) . "',";
        $sql .= "     state='" . add_escape_custom($data["state"]) . "',";
        $sql .= "     postal_code='" . add_escape_custom($data["postal_code"]) . "',";
        $sql .= "     country_code='" . add_escape_custom($data["country_code"]) . "',";
        $sql .= "     federal_ein='" . add_escape_custom($data["federal_ein"]) . "',";
        $sql .= "     website='" . add_escape_custom($data["website"]) . "',";
        $sql .= "     email='" . add_escape_custom($data["email"]) . "',";
        $sql .= "     color='" . add_escape_custom($data["color"]) . "',";
        $sql .= "     service_location='" . add_escape_custom($data["service_location"]) . "',";
        $sql .= "     billing_location='" . add_escape_custom($data["billing_location"]) . "',";
        $sql .= "     accepts_assignment='" . add_escape_custom($data["accepts_assignment"]) . "',";
        $sql .= "     pos_code='" . add_escape_custom($data["pos_code"]) . "',";
        $sql .= "     domain_identifier='" . add_escape_custom($data["domain_identifier"]) . "',";
        $sql .= "     attn='" . add_escape_custom($data["attn"]) . "',";
        $sql .= "     tax_id_type='" . add_escape_custom($data["tax_id_type"]) . "',";
        $sql .= "     primary_business_entity='" . add_escape_custom($data["primary_business_entity"]) . "',";
        $sql .= "     facility_npi='" . add_escape_custom($data["facility_npi"]) . "',";
        $sql .= "     facility_code='" . add_escape_custom($data["facility_code"]) . "',";
        $sql .= "     facility_taxonomy='" . add_escape_custom($data["facility_taxonomy"]) . "'";

        return sqlInsert($sql);
    }

    public function updateUsersFacility($facility_name, $facility_id)
    {
        $sql = " UPDATE users SET";
        $sql .= " facility='" . add_escape_custom($facility_name) . "'";
        $sql .= " WHERE facility_id='" . add_escape_custom($facility_id) . "'";

        return sqlStatement($sql);
    }

    /**
     * Shared getter for the various specific facility getters.
     *
     * @param $map - Query information.
     * @return array of associative arrays | one associative array.
     */
    private function get($map)
    {
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
        $sql .= "        FAC.facility_taxonomy,";
        $sql .= "        FAC.tax_id_type,";
        $sql .= "        FAC.color,";
        $sql .= "        FAC.primary_business_entity,";
        $sql .= "        FAC.facility_code,";
        $sql .= "        FAC.extra_validation";
        $sql .= " FROM facility FAC";

        return QueryUtils::selectHelper($sql, $map);
    }

    private function getPrimaryBusinessEntityLegacy()
    {
        return $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.accepts_assignment DESC, FAC.id ASC",
            "limit" => 1
        ));
    }
}
