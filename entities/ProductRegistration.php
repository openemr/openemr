<?php
/**
 * Product Registration entity.
 *
 * Copyright (C) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
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
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

/**
 * @Table(name="product_registration")
 * @Entity(repositoryClass="OpenEMR\Repositories\ProductRegistrationRepository")
 */
class ProductRegistration
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Id
     * @Column(name="registration_id"), type="char", length=36 nullable=false, options={"default" : 0})
     */
    private $registrationId;

    /**
     * @Column(name="email"), type="varchar", length=255 nullable=false, options={"default" : 0})
     */
    private $email;

    /**
     * @Column(name="opt_out"), type="tinyint", length=1 nullable=false, options={"default" : 0})
     */
    private $optOut;

    /**
     * Status that can be set/get as a human-readable string for the client. Not stored in the database.
     */
    private $statusAsString;

    /**
     * Getter for registration id.
     *
     * return registration id
     */
    public function getRegistrationId()
    {
        return $this->registrationId;
    }

    /**
     * Setter for registration id.
     *
     * @param registration id
     */
    public function setRegistrationId($value)
    {
        $this->registrationId = $value;
    }

    /**
     * Getter for email.
     *
     * return email string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Setter for email.
     *
     * @param email string
     */
    public function setEmail($value)
    {
        $this->email = $value;
    }

    /**
     * Getter for opt out.
     *
     * return opt out string
     */
    public function getOptOut()
    {
        return $this->optOut;
    }

    /**
     * Setter for opt out.
     *
     * @param opt out number
     */
    public function setOptOut($value)
    {
        $this->optOut = $value;
    }

    /**
     * Setter for status that can be set/get as a human-readable string for the client.
     * Not stored in the database.
     *
     * return human-readable status.
     */
    public function getStatusAsString()
    {
        return $this->statusAsString;
    }

    /**
     * Getter for status that can be set/get as a human-readable string for the client.
     * Not stored in the database.
     *
     * @param human-readable status.
     */
    public function setStatusAsString($value)
    {
        $this->statusAsString = $value;
    }

    /**
     * ToString of the entire object.
     *
     * @return object as string
     */
    public function __toString()
    {
        return "registrationId: '" . $this->getRegistrationId() . "' " .
               "email: '" . $this->getEmail() . "' " .
               "statusAsString: '" . $this->getStatusAsString() . "' " .
               "optOut" . $this->getOptOut() . "' " ;
    }

    /**
     * ToSerializedObject of the entire object.
     *
     * @return object as serialized object.
     */
    public function toSerializedObject()
    {
        return get_object_vars($this);
    }
}
