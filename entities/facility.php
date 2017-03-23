<?php
/**
 * Facility entity.
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

namespace entities;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Table(name="facility")
 * @Entity(repositoryClass="repositories\FacilityRepository")
 */
class Facility {
    /**
     * Default constructor.
     */
    public function __construct() {}

    /**
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(name="name", type="string")
     */
    private $name;

    /**
     * @Column(name="phone", type="string")
     */
    private $phone;

    /**
     * @Column(name="fax", type="string")
     */
    private $fax;

    /**
     * @Column(name="street", type="string")
     */
    private $street;

    /**
     * @Column(name="city", type="string")
     */
    private $city;

    /**
     * @Column(name="state", type="string")
     */
    private $state;

    /**
     * @Column(name="postal_code", type="string")
     */
    private $postalCode;

    /**
     * @Column(name="country_code", type="string")
     */
    private $countryCode;

    /**
     * @Column(name="federal_ein", type="string")
     */
    private $federalEin;

    /**
     * @Column(name="website", type="string")
     */
    private $website;

    /**
     * @Column(name="email", type="string")
     */
    private $email;

    /**
     * @Column(name="service_location", type="boolean")
     */
    private $serviceLocation;

    /**
     * @Column(name="billing_location", type="boolean")
     */
    private $billingLocation;

    /**
     * @Column(name="accepts_assignment", type="boolean")
     */
    private $acceptsAssignment;

    /**
     * @Column(name="pos_code", type="integer")
     */
    private $posCode;

    /**
     * @Column(name="x12_sender_id", type="string")
     */
    private $x12SenderId;

    /**
     * @Column(name="attn", type="string")
     */
    private $attn;

    /**
     * @Column(name="domain_identifier", type="string")
     */
    private $domainIdentifier;

    /**
     * @Column(name="facility_npi", type="string")
     */
    private $facilityNpi;

    /**
     * @Column(name="tax_id_type", type="string")
     */
    private $taxIdType;

    /**
     * @Column(name="color", type="string")
     */
    private $color;

    /**
     * @Column(name="primary_business_entity", type="integer")
     */
    private $primaryBusinessEntity;

    /**
     * @Column(name="facility_code", type="string")
     */
    private $facilityCode;

    /**
     * @Column(name="extra_validation", type="boolean")
     */
    private $extraValidation;

    public function getId() {
        return $this->id;
    }

    public function setId($value) {
        $this->id = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($value) {
        $this->phone = $value;
    }

    public function getFax() {
        return $this->fax;
    }

    public function setFax($value) {
        $this->fax = $value;
    }

    public function getStreet() {
        return $this->street;
    }

    public function setStreet($value) {
        $this->street = $value;
    }

    public function getCity() {
        return $this->city;
    }

    public function setCity($value) {
        $this->city = $value;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($value) {
        $this->state = $value;
    }

    public function getPostalCode() {
        return $this->postalCode;
    }

    public function setPostalCode($value) {
        $this->postalCode = $value;
    }

    public function getCountryCode() {
        return $this->countryCode;
    }

    public function setCountryCode($value) {
        $this->countryCode = $value;
    }

    public function getFederalEin() {
        return $this->federalEin;
    }

    public function setFederalEin($value) {
        $this->federalEin = $value;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function setWebsite($value) {
        $this->website = $value;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($value) {
        $this->email = $value;
    }

    public function getServiceLocation() {
        return $this->serviceLocation;
    }

    public function setServiceLocation($value) {
        $this->serviceLocation = $value;
    }

    public function getBillingLocation() {
        return $this->billingLocation;
    }

    public function setBillingLocation($value) {
        $this->billingLocation = $value;
    }

    public function getAcceptsAssignment() {
        return $this->acceptsAssignment;
    }

    public function setAcceptsAssignment($value) {
        $this->acceptsAssignment = $value;
    }

    public function getPosCode() {
        return $this->posCode;
    }

    public function setPosCode($value) {
        $this->posCode = $value;
    }

    public function getX12SenderId() {
        return $this->x12SenderId;
    }

    public function setX12SenderId($value) {
        $this->x12SenderId = $value;
    }

    public function getAttn() {
        return $this->attn;
    }

    public function setAttn($value) {
        $this->attn = $value;
    }

    public function getDomainIdentifier() {
        return $this->domainIdentifier;
    }

    public function setDomainIdentifier($value) {
        $this->domainIdentifier = $value;
    }

    public function getFacilityNpi() {
        return $this->facilityNpi;
    }

    public function setFacilityNpi($value) {
        $this->facilityNpi = $value;
    }

    public function getTaxIdType() {
        return $this->taxIdType;
    }

    public function setTaxIdType($value) {
        $this->taxIdType = $value;
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($value) {
        $this->color = $value;
    }

    public function getPrimaryBusinessEntity() {
        return $this->primaryBusinessEntity;
    }

    public function setPrimaryBusinessEntity($value) {
        $this->primaryBusinessEntity = $value;
    }

    public function getFacilityCode() {
        return $this->facilityCode;
    }

    public function setFacilityCode($value) {
        $this->facilityCode = $value;
    }

    public function getExtraValidation() {
        return $this->extraValidation;
    }

    public function setExtraValidation($value) {
        $this->extraValidation = $value;
    }

    /**
     * ToString of the entire object.
     *
     * return object as string
     */
    public function __toString() {
        return "id: '" . $this->getId() . "' " .
               "name: '" . $this->getName() . "' " .
               "phone: '" . $this->getPhone() . "' " .
               "fax: '" . $this->getFax() . "' " .
               "street: '" . $this->getStreet() . "' " .
               "city: '" . $this->getCity() . "' " .
               "state: '" . $this->getState() . "' " .
               "postalCode: '" . $this->getPostalCode() . "' " .
               "countryCode: '" . $this->getCountryCode() . "' " .
               "federalEin: '" . $this->getFederalEin() . "' " .
               "website: '" . $this->getWebsite() . "' " .
               "email: '" . $this->getEmail() . "' " .
               "serviceLocation: '" . $this->getServiceLocation() . "' " .
               "billingLocation: '" . $this->getBillingLocation() . "' " .
               "acceptsAssignment: '" . $this->getAcceptsAssignment() . "' " .
               "posCode: '" . $this->getPosCode() . "' " .
               "x12SenderId: '" . $this->getX12SenderId() . "' " .
               "attn: '" . $this->getAttn() . "' " .
               "domainIdentifier: '" . $this->getDomainIdentifier() . "' " .
               "facilityNpi: '" . $this->getFacilityNpi() . "' " .
               "taxIdType: '" . $this->getTaxIdType() . "' " .
               "color: '" . $this->getColor() . "' " .
               "primaryBusinessEntity: '" . $this->getPrimaryBusinessEntity() . "' " .
               "facilityCode: '" . $this->getFacilityCode() . "' " .
               "extraValidation: '" . $this->getExtraValidation() . "' ";
    }

    /**
     * ToSerializedObject of the entire object.
     *
     * @return object as serialized object.
     */
    public function toSerializedObject() {
        return get_object_vars($this);
    }
}
