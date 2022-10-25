<?php

/**
 * Represents a TeleHealth Provisioned User on the Comlink api.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Models;

use Comlink\OpenEMR\Modules\TeleHealthModule\DateTime;

class TeleHealthUser
{
    private $id;
    private $username;
    private $isPatient;
    private $dbRecordId;
    private $authToken;

    /**
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @var \DateTime
     */
    private $dateRegistered;

    /**
     * @var \DateTime
     */
    private $dateUpdated;

    private $isActive;

    /**
     * @var string
     */
    private $registrationCode;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->dateUpdated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return TeleHealthUser
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return TeleHealthUser
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsPatient()
    {
        return $this->isPatient;
    }

    /**
     * @param mixed $isPatient
     * @return TeleHealthUser
     */
    public function setIsPatient($isPatient)
    {
        $this->isPatient = $isPatient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDbRecordId()
    {
        return $this->dbRecordId;
    }

    /**
     * @param mixed $dbRecordId
     * @return TeleHealthUser
     */
    public function setDbRecordId($dbRecordId)
    {
        $this->dbRecordId = $dbRecordId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param mixed $authToken
     * @return TeleHealthUser
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated(): \DateTime
    {
        return $this->dateCreated;
    }

    /**
     * @param mixed $dateCreated
     * @return TeleHealthUser
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateRegistered(): ?\DateTime
    {
        return $this->dateRegistered;
    }

    /**
     * @param mixed $dateRegistered
     * @return TeleHealthUser
     */
    public function setDateRegistered($dateRegistered): TeleHealthUser
    {
        $this->dateRegistered = $dateRegistered;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateUpdated(): \DateTime
    {
        return $this->dateUpdated;
    }

    /**
     * @param mixed $dateUpdated
     * @return TeleHealthUser
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     * @return TeleHealthUser
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegistrationCode(): ?string
    {
        return $this->registrationCode;
    }

    /**
     * @param string $registrationCode
     * @return TeleHealthUser
     */
    public function setRegistrationCode(?string $registrationCode): TeleHealthUser
    {
        $this->registrationCode = $registrationCode;
        return $this;
    }
}
