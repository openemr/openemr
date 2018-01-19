<?php

namespace Adldap\Models;

use DateTime;
use Exception;
use Adldap\Utilities;
use Adldap\AdldapException;
use Adldap\Objects\AccountControl;
use Adldap\Objects\BatchModification;
use Adldap\Models\Traits\HasMemberOf;
use Adldap\Models\Traits\HasDescription;
use Adldap\Models\Traits\HasLastLogonAndLogOff;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Entry implements Authenticatable
{
    use HasDescription, HasMemberOf, HasLastLogonAndLogOff;

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->schema->objectSid();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getConvertedSid();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        return;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return;
    }

    /**
     * Returns the users department.
     *
     * https://msdn.microsoft.com/en-us/library/ms675490(v=vs.85).aspx
     *
     * @return string
     */
    public function getDepartment()
    {
        return $this->getFirstAttribute($this->schema->department());
    }

    /**
     * Sets the users department.
     *
     * @param string $department
     *
     * @return $this
     */
    public function setDepartment($department)
    {
        return $this->setFirstAttribute($this->schema->department(), $department);
    }

    /**
     * Returns the department number.
     *
     * @return string
     */
    public function getDepartmentNumber()
    {
        return $this->getFirstAttribute($this->schema->departmentNumber());
    }

    /**
     * Sets the department number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setDepartmentNumber($number)
    {
        return $this->setFirstAttribute($this->schema->departmentNumber(), $number);
    }

    /**
     * Returns the users title.
     *
     * https://msdn.microsoft.com/en-us/library/ms680037(v=vs.85).aspx
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getFirstAttribute($this->schema->title());
    }

    /**
     * Sets the users title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setFirstAttribute($this->schema->title(), $title);
    }

    /**
     * Returns the users first name.
     *
     * https://msdn.microsoft.com/en-us/library/ms675719(v=vs.85).aspx
     *
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->getFirstAttribute($this->schema->firstName());
    }

    /**
     * Sets the users first name.
     *
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        return $this->setFirstAttribute($this->schema->firstName(), $firstName);
    }

    /**
     * Returns the users last name.
     *
     * https://msdn.microsoft.com/en-us/library/ms679872(v=vs.85).aspx
     *
     * @return mixed
     */
    public function getLastName()
    {
        return $this->getFirstAttribute($this->schema->lastName());
    }

    /**
     * Sets the users last name.
     *
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        return $this->setFirstAttribute($this->schema->lastName(), $lastName);
    }

    /**
     * Returns the users info.
     *
     * @return mixed
     */
    public function getInfo()
    {
        return $this->getFirstAttribute($this->schema->info());
    }

    /**
     * Sets the users info.
     *
     * @param string $info
     *
     * @return $this
     */
    public function setInfo($info)
    {
        return $this->setFirstAttribute($this->schema->info(), $info);
    }

    /**
     * Returns the users initials.
     *
     * @return mixed
     */
    public function getInitials()
    {
        return $this->getFirstAttribute($this->schema->initials());
    }

    /**
     * Sets the users initials.
     *
     * @param string $initials
     *
     * @return $this
     */
    public function setInitials($initials)
    {
        return $this->setFirstAttribute($this->schema->initials(), $initials);
    }

    /**
     * Returns the users country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->getFirstAttribute($this->schema->country());
    }

    /**
     * Sets the users country.
     *
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        return $this->setFirstAttribute($this->schema->country(), $country);
    }

    /**
     * Returns the users street address.
     *
     * @return $this
     */
    public function getStreetAddress()
    {
        return $this->getFirstAttribute($this->schema->streetAddress());
    }

    /**
     * Sets the users street address.
     *
     * @param string $address
     *
     * @return $this
     */
    public function setStreetAddress($address)
    {
        return $this->setFirstAttribute($this->schema->streetAddress(), $address);
    }

    /**
     * Returns the users postal code.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->getFirstAttribute($this->schema->postalCode());
    }

    /**
     * Sets the users postal code.
     *
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        return $this->setFirstAttribute($this->schema->postalCode(), $postalCode);
    }

    /**
     * Returns the users physical delivery office name.
     *
     * @return string
     */
    public function getPhysicalDeliveryOfficeName()
    {
        return $this->getFirstAttribute($this->schema->physicalDeliveryOfficeName());
    }

    /**
     * Sets the users physical delivery office name.
     *
     * @param string $deliveryOffice
     *
     * @return $this
     */
    public function setPhysicalDeliveryOfficeName($deliveryOffice)
    {
        return $this->setFirstAttribute($this->schema->physicalDeliveryOfficeName(), $deliveryOffice);
    }

    /**
     * Returns the users telephone number.
     *
     * https://msdn.microsoft.com/en-us/library/ms680027(v=vs.85).aspx
     *
     * @return string
     */
    public function getTelephoneNumber()
    {
        return $this->getFirstAttribute($this->schema->telephone());
    }

    /**
     * Sets the users telephone number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setTelephoneNumber($number)
    {
        return $this->setFirstAttribute($this->schema->telephone(), $number);
    }

    /**
     * Returns the users locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getFirstAttribute($this->schema->locale());
    }

    /**
     * Sets the users locale.
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        return $this->setFirstAttribute($this->schema->locale(), $locale);
    }

    /**
     * Returns the users company.
     *
     * https://msdn.microsoft.com/en-us/library/ms675457(v=vs.85).aspx
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getFirstAttribute($this->schema->company());
    }

    /**
     * Sets the users company.
     *
     * @param string $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        return $this->setFirstAttribute($this->schema->company(), $company);
    }

    /**
     * Returns the users primary email address.
     *
     * https://msdn.microsoft.com/en-us/library/ms676855(v=vs.85).aspx
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getFirstAttribute($this->schema->email());
    }

    /**
     * Sets the users email.
     *
     * Keep in mind this will remove all other
     * email addresses the user currently has.
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setFirstAttribute($this->schema->email(), $email);
    }

    /**
     * Returns the users email addresses.
     *
     * https://msdn.microsoft.com/en-us/library/ms676855(v=vs.85).aspx
     *
     * @return array
     */
    public function getEmails()
    {
        return $this->getAttribute($this->schema->email());
    }

    /**
     * Sets the users email addresses.
     *
     * @param array $emails
     *
     * @return $this
     */
    public function setEmails(array $emails = [])
    {
        return $this->setAttribute($this->schema->email(), $emails);
    }

    /**
     * Returns the users other mailbox attribute.
     *
     * https://msdn.microsoft.com/en-us/library/ms679091(v=vs.85).aspx
     *
     * @return array
     */
    public function getOtherMailbox()
    {
        return $this->getAttribute($this->schema->otherMailbox());
    }

    /**
     * Sets the users other mailboxes.
     *
     * @param array $otherMailbox
     *
     * @return $this
     */
    public function setOtherMailbox($otherMailbox = [])
    {
        return $this->setAttribute($this->schema->otherMailbox(), $otherMailbox);
    }

    /**
     * Returns the users mailbox store DN.
     *
     * https://msdn.microsoft.com/en-us/library/aa487565(v=exchg.65).aspx
     *
     * @return string
     */
    public function getHomeMdb()
    {
        return $this->getFirstAttribute($this->schema->homeMdb());
    }

    /**
     * Returns the users mail nickname.
     *
     * @return string
     */
    public function getMailNickname()
    {
        return $this->getFirstAttribute($this->schema->emailNickname());
    }

    /**
     * Returns the users principal name.
     *
     * This is usually their email address.
     *
     * https://msdn.microsoft.com/en-us/library/ms680857(v=vs.85).aspx
     *
     * @return string
     */
    public function getUserPrincipalName()
    {
        return $this->getFirstAttribute($this->schema->userPrincipalName());
    }

    /**
     * Sets the users user principal name.
     *
     * @param string $userPrincipalName
     *
     * @return $this
     */
    public function setUserPrincipalName($userPrincipalName)
    {
        return $this->setFirstAttribute($this->schema->userPrincipalName(), $userPrincipalName);
    }

    /**
     * Returns the users proxy addresses.
     *
     * https://msdn.microsoft.com/en-us/library/ms679424(v=vs.85).aspx
     *
     * @return array
     */
    public function getProxyAddresses()
    {
        return $this->getAttribute($this->schema->proxyAddresses());
    }

    /**
     * Sets the users proxy addresses.
     *
     * This will remove all proxy addresses on the user and insert the specified addresses.
     *
     * https://msdn.microsoft.com/en-us/library/ms679424(v=vs.85).aspx
     *
     * @param array $addresses
     *
     * @return $this
     */
    public function setProxyAddresses(array $addresses = [])
    {
        return $this->setAttribute($this->schema->proxyAddresses(), $addresses);
    }

    /**
     * Add's a single proxy address to the user.
     *
     * @param string $address
     *
     * @return $this
     */
    public function addProxyAddress($address)
    {
        $addresses = $this->getProxyAddresses();

        $addresses[] = $address;

        return $this->setAttribute($this->schema->proxyAddresses(), $addresses);
    }

    /**
     * Returns the users script path if the user has one.
     *
     * https://msdn.microsoft.com/en-us/library/ms679656(v=vs.85).aspx
     *
     * @return string
     */
    public function getScriptPath()
    {
        return $this->getFirstAttribute($this->schema->scriptPath());
    }

    /**
     * Sets the users script path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setScriptPath($path)
    {
        return $this->setFirstAttribute($this->schema->scriptPath(), $path);
    }

    /**
     * Returns the users bad password count.
     *
     * @return string
     */
    public function getBadPasswordCount()
    {
        return $this->getFirstAttribute($this->schema->badPasswordCount());
    }

    /**
     * Returns the users bad password time.
     *
     * @return string
     */
    public function getBadPasswordTime()
    {
        return $this->getFirstAttribute($this->schema->badPasswordTime());
    }

    /**
     * Returns the time when the users password was set last.
     *
     * @return string
     */
    public function getPasswordLastSet()
    {
        return $this->getFirstAttribute($this->schema->passwordLastSet());
    }

    /**
     * Returns the password last set unix timestamp.
     *
     * @return float|null
     */
    public function getPasswordLastSetTimestamp()
    {
        if ($time = $this->getPasswordLastSet()) {
            return Utilities::convertWindowsTimeToUnixTime($time);
        }
    }

    /**
     * Returns the formatted timestamp of the password last set date.
     *
     * @return string|null
     */
    public function getPasswordLastSetDate()
    {
        if ($timestamp = $this->getPasswordLastSetTimestamp()) {
            return (new DateTime())->setTimestamp($timestamp)->format($this->dateFormat);
        }
    }

    /**
     * Returns the users lockout time.
     *
     * @return string
     */
    public function getLockoutTime()
    {
        return $this->getFirstAttribute($this->schema->lockoutTime());
    }

    /**
     * Returns the users user account control integer.
     *
     * @return string
     */
    public function getUserAccountControl()
    {
        return $this->getFirstAttribute($this->schema->userAccountControl());
    }

    /**
     * Sets the users account control property.
     *
     * @param int|string|AccountControl $accountControl
     *
     * @return $this
     */
    public function setUserAccountControl($accountControl)
    {
        return $this->setAttribute($this->schema->userAccountControl(), (string) $accountControl);
    }

    /**
     * Returns the users profile file path.
     *
     * @return string
     */
    public function getProfilePath()
    {
        return $this->getFirstAttribute($this->schema->profilePath());
    }

    /**
     * Sets the users profile path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setProfilePath($path)
    {
        return $this->setFirstAttribute($this->schema->profilePath(), $path);
    }

    /**
     * Returns the users legacy exchange distinguished name.
     *
     * @return string
     */
    public function getLegacyExchangeDn()
    {
        return $this->getFirstAttribute($this->schema->legacyExchangeDn());
    }

    /**
     * Returns the users account expiry date.
     *
     * @return string
     */
    public function getAccountExpiry()
    {
        return $this->getFirstAttribute($this->schema->accountExpires());
    }

    /**
     * Sets the users account expiry date.
     *
     * https://msdn.microsoft.com/en-us/library/ms675098(v=vs.85).aspx
     *
     * @param float $expiryTime
     *
     * @return $this
     */
    public function setAccountExpiry($expiryTime)
    {
        $time = is_null($expiryTime) ? '9223372036854775807' : (string) Utilities::convertUnixTimeToWindowsTime($expiryTime);

        return $this->setFirstAttribute($this->schema->accountExpires(), $time);
    }

    /**
     * Returns an array of address book DNs
     * that the user is listed to be shown in.
     *
     * @return array
     */
    public function getShowInAddressBook()
    {
        return $this->getAttribute($this->schema->showInAddressBook());
    }

    /**
     * Returns the users thumbnail photo.
     *
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->getFirstAttribute($this->schema->thumbnail());
    }

    /**
     * Returns the users thumbnail photo base 64 encoded.
     *
     * @return null|string
     */
    public function getThumbnailEncoded()
    {
        $thumb = $this->getThumbnail();

        return is_null($thumb) ? $thumb : 'data:image/jpeg;base64,'.base64_encode($thumb);
    }

    /**
     * Returns the users jpeg photo.
     *
     * @return mixed
     */
    public function getJpegPhoto()
    {
        return $this->getFirstAttribute($this->schema->jpegPhoto());
    }

    /**
     * Returns the users jpeg photo.
     *
     * @return null|string
     */
    public function getJpegPhotoEncoded()
    {
        $jpeg = $this->getJpegPhoto();

        return is_null($jpeg) ? $jpeg : 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    /**
     * Returns the distinguished name of the user who is the user's manager.
     *
     * @return string
     */
    public function getManager()
    {
        return $this->getFirstAttribute($this->schema->manager());
    }

    /**
     * Sets the distinguished name of the user who is the user's manager.
     *
     * @param string $managerDn
     *
     * @return $this
     */
    public function setManager($managerDn)
    {
        return $this->setFirstAttribute($this->schema->manager(), $managerDn);
    }

    /**
     * Return the employee ID.
     *
     * @return string
     */
    public function getEmployeeId()
    {
        return $this->getFirstAttribute($this->schema->employeeId());
    }

    /**
     * Sets the employee ID.
     *
     * @param string $employeeId
     *
     * @return $this
     */
    public function setEmployeeId($employeeId)
    {
        return $this->setFirstAttribute($this->schema->employeeId(), $employeeId);
    }

    /**
     * Returns the employee number.
     *
     * @return string
     */
    public function getEmployeeNumber()
    {
        return $this->getFirstAttribute($this->schema->employeeNumber());
    }

    /**
     * Sets the employee number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setEmployeeNumber($number)
    {
        return $this->setFirstAttribute($this->schema->employeeNumber(), $number);
    }

    /**
     * Returns the room number.
     *
     * @return string
     */
    public function getRoomNumber()
    {
        return $this->getFirstAttribute($this->schema->roomNumber());
    }

    /**
     * Sets the room number.
     *
     * @param string $number
     *
     * @return $this
     */
    public function setRoomNumber($number)
    {
        return $this->setFirstAttribute($this->schema->roomNumber(), $number);
    }

    /**
     * Return the personal title.
     *
     * @return $this
     */
    public function getPersonalTitle()
    {
        return $this->getFirstAttribute($this->schema->personalTitle());
    }

    /**
     * Sets the personal title.
     *
     * @param string $personalTitle
     *
     * @return $this
     */
    public function setPersonalTitle($personalTitle)
    {
        return $this->setFirstAttribute($this->schema->personalTitle(), $personalTitle);
    }

    /**
     * Retrieves the primary group of the current user.
     *
     * @return Model|bool
     */
    public function getPrimaryGroup()
    {
        $groupSid = preg_replace('/\d+$/', $this->getPrimaryGroupId(), $this->getConvertedSid());

        return $this->query->newInstance()->findBySid($groupSid);
    }

    /**
     * Sets the password on the current user.
     *
     * @param string $password
     *
     * @throws AdldapException
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->validateSecureConnection();

        return $this->addModification(new BatchModification(
            $this->schema->unicodePassword(),
            LDAP_MODIFY_BATCH_REPLACE,
            [Utilities::encodePassword($password)]
        ));
    }

    /**
     * Change the password of the current user. This must be performed over SSL.
     *
     * @param string $oldPassword      The new password
     * @param string $newPassword      The old password
     * @param bool   $replaceNotRemove Alternative password change method. Set to true if you're receiving 'CONSTRAINT'
     *                                 errors.
     *
     * @throws UserPasswordPolicyException
     * @throws UserPasswordIncorrectException
     * @throws AdldapException
     *
     * @return bool
     */
    public function changePassword($oldPassword, $newPassword, $replaceNotRemove = false)
    {
        $this->validateSecureConnection();

        $attribute = $this->schema->unicodePassword();

        $modifications = [];

        if ($replaceNotRemove) {
            $modifications[] = new BatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_REPLACE,
                [Utilities::encodePassword($newPassword)]
            );
        } else {
            // Create batch modification for removing the old password.
            $modifications[] = new BatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_REMOVE,
                [Utilities::encodePassword($oldPassword)]
            );

            // Create batch modification for adding the new password.
            $modifications[] = new BatchModification(
                $attribute,
                LDAP_MODIFY_BATCH_ADD,
                [Utilities::encodePassword($newPassword)]
            );
        }

        // Add the modifications.
        foreach ($modifications as $modification) {
            $this->addModification($modification);
        }

        try {
            return $this->update();
        } catch (Exception $e) {
            // If the user failed to update, we'll see if we can
            // figure out why by retrieving the extended error.
            $error = $this->query->getConnection()->getExtendedError();
            $code = $this->query->getConnection()->getExtendedErrorCode();

            switch ($code) {
                case '0000052D':
                    throw new UserPasswordPolicyException(
                        "Error: $code. Your new password does not match the password policy."
                    );
                case '00000056':
                    throw new UserPasswordIncorrectException(
                        "Error: $code. Your old password is incorrect."
                    );
                default:
                    throw new AdldapException("Error: $error");
            }
        }
    }

    /**
     * Returns if the user is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return ($this->getUserAccountControl() & AccountControl::ACCOUNTDISABLE) === AccountControl::ACCOUNTDISABLE;
    }

    /**
     * Returns if the user is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getUserAccountControl() === null ? false : !$this->isDisabled();
    }

    /**
     * Return the expiration date of the user account.
     *
     * @return DateTime|null
     */
    public function expirationDate()
    {
        $accountExpiry = $this->getAccountExpiry();

        if ($accountExpiry == 0 || $accountExpiry == $this->getSchema()->neverExpiresDate()) {
            return;
        }

        $unixTime = Utilities::convertWindowsTimeToUnixTime($accountExpiry);

        return new DateTime(date($this->dateFormat, $unixTime));
    }

    /**
     * Return true / false if the AD User is expired.
     *
     * @param DateTime $date Optional date
     *
     * @return bool
     */
    public function isExpired(DateTime $date = null)
    {
        $date = $date ?: new DateTime();

        $expirationDate = $this->expirationDate();

        return $expirationDate ? ($expirationDate <= $date) : false;
    }

    /**
     * Return true / false if AD User is active (enabled & not expired).
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isEnabled() && !$this->isExpired();
    }

    /**
     * Returns true / false if the users password is expired.
     *
     * @return bool
     */
    public function passwordExpired()
    {
        return (int) $this->getPasswordLastSet() === 0;
    }
}
