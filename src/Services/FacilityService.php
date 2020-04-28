<?php

/**
 * FacilityService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class FacilityService extends BaseService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('facility');
    }

    public function validate($facility)
    {
        $validator = new Validator();

        $validator->required('name')->lengthBetween(2, 255);
        $validator->required('phone')->lengthBetween(3, 30);
        $validator->required('city')->lengthBetween(2, 255);
        $validator->required('state')->lengthBetween(2, 50);
        $validator->required('street')->lengthBetween(2, 255);
        $validator->required('postal_code')->lengthBetween(2, 11);
        $validator->required('email')->email();
        $validator->required('fax')->lengthBetween(3, 30);
        $validator->optional('country_code')->lengthBetween(2, 30);
        $validator->optional('federal_ein')->lengthBetween(2, 15);
        $validator->optional('website')->url();
        $validator->optional('color')->lengthBetween(4, 7);
        $validator->optional('service_location')->numeric();
        $validator->optional('billing_location')->numeric();
        $validator->optional('accepts_assignment')->numeric();
        $validator->optional('pos_code')->numeric();
        $validator->optional('domain_identifier')->lengthBetween(2, 60);
        $validator->optional('attn')->lengthBetween(2, 65);
        $validator->optional('tax_id_type')->lengthBetween(2, 31);
        $validator->optional('primary_business_entity')->numeric();
        $validator->optional('facility_npi')->lengthBetween(2, 15);
        $validator->optional('facility_code')->lengthBetween(2, 31);
        $validator->optional('facility_taxonomy')->lengthBetween(2, 15);
        $validator->optional('iban')->lengthBetween(2, 34);

        return $validator->validate($facility);
    }

    public function getAll()
    {
        return $this->get(array("order" => "ORDER BY FAC.name ASC"));
    }

    public function getPrimaryBusinessEntity($options = null)
    {
        if (! empty($options) && ! empty($options["useLegacyImplementation"])) {
            return $this->getPrimaryBusinessEntityLegacy();
        }

        $args = array(
            "where" => "WHERE FAC.primary_business_entity = 1",
            "data" => null,
            "limit" => 1
        );

        if (! empty($options) && ! empty($options["excludedId"])) {
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

        if (! empty($options) && ! empty($options["orderField"])) {
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
            "data" => array($id),
            "limit" => 1
        ));
    }

    public function getFacilityForUser($userId)
    {
        return $this->get(array(
            "where" => "WHERE USER.id = ?",
            "data" => array($userId),
            "join" => "JOIN users USER ON FAC.id = USER.facility_id",
            "limit" => 1
        ));
    }

    public function getFacilityForUserFormatted($userId)
    {
        $facility = $this->getFacilityForUser($userId);

        if (! empty($facility)) {
            $formatted = "";
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
            "data" => array($encounterId),
            "join" => "JOIN form_encounter ENC ON FAC.id = ENC.facility_id",
            "limit" => 1
        ));
    }

    public function update($data)
    {
        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE facility SET ";
        $sql .= $query['set'];
        $sql .= " WHERE id = ?";
        array_push($query['bind'], $data['id']);
        return sqlStatement(
            $sql,
            $query['bind']
        );
    }

    public function insert($data)
    {
        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO facility SET ";
        $sql .= $query['set'];
        return sqlStatement(
            $sql,
            $query['bind']
        );
    }

    public function updateUsersFacility($facility_name, $facility_id)
    {
        $sql = " UPDATE users SET";
        $sql .= " facility=?";
        $sql .= " WHERE facility_id=?";

        return sqlStatement($sql, array($facility_name, $facility_id));
    }

    /**
     * Shared getter for the various specific facility getters.
     *
     * @param $map - Query information.
     * @return array of associative arrays | one associative array.
     */
    private function get($map)
    {
        $sql = " SELECT FAC.id,";
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
        $sql .= "        FAC.extra_validation,";
        $sql .= "        FAC.mail_street,";
        $sql .= "        FAC.mail_street2,";
        $sql .= "        FAC.mail_city,";
        $sql .= "        FAC.mail_state,";
        $sql .= "        FAC.mail_zip,";
        $sql .= "        FAC.oid,";
        $sql .= "        FAC.iban,";
        $sql .= "        FAC.info";
        $sql .= " FROM facility FAC";

        return self::selectHelper($sql, $map);
    }

    private function getPrimaryBusinessEntityLegacy()
    {
        return $this->get(array(
            "order" => "ORDER BY FAC.billing_location DESC, FAC.accepts_assignment DESC, FAC.id ASC",
            "limit" => 1
        ));
    }
}
