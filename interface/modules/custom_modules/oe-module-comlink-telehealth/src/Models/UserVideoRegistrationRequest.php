<?php

/**
 * Handles the retrieval of calendar categories that are specific to TeleHealth
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Models;

class UserVideoRegistrationRequest
{
    /**
     * The unique user name for a video api user max string length 64 chars.
     * This is typically a UUIDV1 or UUIDV4
     * @var string
     */
    private $username;

    /**
     * The unique password length for the video api user max string length 256 chars
     * @var string
     */
    private $password;

    /**
     * The unique UUID for the OpenEMR installation we are originating users from.  Max chars 64
     * @var string
     */
    private $instituationId;

    /**
     * the Unique institution name for the OpenEMR installation. Max chars 255
     * @var string
     */
    private $institutionName;

    /**
     * The user's first name used for reporting purposes.  Max chars 255
     * @var string
     */
    private $firstName;

    /**
     *  the User's last name used for reporting purposes.  Max chars 255
     * @var string
     */
    private $lastName;

    /**
     * @var int The unique database id of the record
     */
    private $dbRecordId = null;

    /**
     * @var bool True if the registration request is for a patient or false if its a provider
     */
    private $isPatient = false;

    /**
     * @var string The unique registration code that can be used by the user to identify their mobile app for first time setup.
     */
    private $registrationCode;

    /**
     * @return int
     */
    public function getDbRecordId(): int
    {
        return $this->dbRecordId;
    }

    /**
     * @param int $dbRecordId
     * @return UserVideoRegistrationRequest
     */
    public function setDbRecordId(int $dbRecordId): UserVideoRegistrationRequest
    {
        $this->dbRecordId = $dbRecordId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPatient(): bool
    {
        return $this->isPatient;
    }

    /**
     * @param bool $isPatient
     * @return UserVideoRegistrationRequest
     */
    public function setIsPatient(bool $isPatient): UserVideoRegistrationRequest
    {
        $this->isPatient = $isPatient;
        return $this;
    }


    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return UserVideoRegistrationRequest
     */
    public function setUsername(string $username): UserVideoRegistrationRequest
    {
        if (strlen($username) > 64) {
            throw new \InvalidArgumentException("username must be 64 characters or less");
        }
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return UserVideoRegistrationRequest
     */
    public function setPassword(string $password): UserVideoRegistrationRequest
    {
        if (strlen($password) > 256) {
            throw new \InvalidArgumentException("password must be 256 characters or less");
        }
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstituationId(): string
    {
        return $this->instituationId;
    }

    /**
     * @param string $instituationId
     * @return UserVideoRegistrationRequest
     */
    public function setInstituationId(string $instituationId): UserVideoRegistrationRequest
    {
        if (strlen($instituationId) > 64) {
            throw new \InvalidArgumentException("InstitutionId must be 64 characters or less");
        }
        $this->instituationId = $instituationId;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstitutionName(): ?string
    {
        return $this->institutionName;
    }

    /**
     * @param string $institutionName
     * @return UserVideoRegistrationRequest
     */
    public function setInstitutionName(string $institutionName): UserVideoRegistrationRequest
    {
        if (strlen($institutionName) > 255) {
            throw new \InvalidArgumentException("institutionName must be 255 characters or less");
        }
        $this->institutionName = $institutionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return UserVideoRegistrationRequest
     */
    public function setFirstName(string $firstName): UserVideoRegistrationRequest
    {
        if (strlen($firstName) > 255) {
            throw new \InvalidArgumentException("firstName must be 255 characters or less");
        }
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return UserVideoRegistrationRequest
     */
    public function setLastName(string $lastName): UserVideoRegistrationRequest
    {
        if (strlen($lastName) > 255) {
            throw new \InvalidArgumentException("lastName must be 255 characters or less");
        }
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Returns true if the username, password, and institutionId have been populated, false otherwise.
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->username) && !empty($this->password) && !empty($this->instituationId);
    }

    /**
     * @return string
     */
    public function getRegistrationCode(): string
    {
        return $this->registrationCode;
    }

    /**
     * @param string $registrationCode
     * @return UserVideoRegistrationRequest
     */
    public function setRegistrationCode(string $registrationCode): UserVideoRegistrationRequest
    {
        $this->registrationCode = $registrationCode;
        return $this;
    }

    public function toArray(): array
    {
        // return nothing if our object is not valid
        if (!$this->isValid()) {
            return [];
        }

        return [
            'userName' => $this->getUsername()
            ,'passwordString' => $this->getPassword()
            ,'registrationCode' => $this->getRegistrationCode()
            ,'role' => $this->getArrayRole()
            ,"eu_profile" => [
                'firstName' => $this->getFirstName()
                ,'lastName' => $this->getLastName()
            ]
            ,"institution" => [
                'institutionId' => $this->getInstituationId()
                ,'institutionName' => $this->getInstitutionName()
            ]
        ];
    }

    private function getArrayRole()
    {
        if ($this->isPatient) {
            return 'patient';
        }
        return 'provider';
    }
}
