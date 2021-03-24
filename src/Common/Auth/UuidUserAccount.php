<?php

/**
 * UuidUserAccount class.
 *
 *  This class will allow collection of user data and user roles with an identifying uuid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\UserService;

class UuidUserAccount
{
    private $userId;   //uuid
    private $userRole; //user role

    const USER_ROLE_USERS = 'users';
    const USER_ROLE_PATIENT = 'patient';
    const USER_ROLE_SYSTEM = 'system';

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    // get user account information
    public function getUserAccount(): ?array
    {
        if (empty($this->userId)) {
            return null;
        }

        $this->userRole = $this->getUserRole();

        if (empty($this->userRole)) {
            return null;
        }

        switch ($this->userRole) {
            case self::USER_ROLE_SYSTEM:
            case self::USER_ROLE_USERS:
                $account_sql = "SELECT `id`, `username`, `authorized`, `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone`, `email`, `street`, `city`, `state`, `zip`, CONCAT(fname, ' ', lname) AS fullname FROM `users` WHERE `uuid` = ?";
                break;
            case self::USER_ROLE_PATIENT:
                $account_sql = "SELECT `pid`, `uuid`, `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone_contact` AS phone, `sex` AS gender, `email`, `DOB` AS birthdate, `street`, `postal_code` AS zip, `city`, `state`, CONCAT(fname, ' ', lname) AS fullname FROM `patient_data` WHERE `uuid` = ?";
                break;
            default:
                return null;
        }

        $userIdBinary = UuidRegistry::uuidToBytes($this->userId);
        if (empty($userIdBinary)) {
            error_log("OpenEMR ERROR: error in conversion of string uuid to binary id");
            return null;
        }
        $userAccount = sqlQueryNoLog($account_sql, [$userIdBinary]);

        if (!empty($userAccount)) {
            return $userAccount;
        } else {
            return null;
        }
    }

    // get user role
    public function getUserRole(): ?string
    {
        if (!empty($this->userRole)) {
            return $this->userRole;
        } else {
            return $this->collectUserRole();
        }
    }

    // collect user role
    private function collectUserRole(): ?string
    {
        if (!empty($this->userRole)) {
            return $this->userRole;
        }

        if (empty($this->userId)) {
            return null;
        }

        $userIdBinary = UuidRegistry::uuidToBytes($this->userId);
        if (empty($userIdBinary)) {
            error_log("OpenEMR ERROR: error in conversion of string uuid to binary id");
            return null;
        }
        $userRole = sqlQueryNoLog("SELECT `id`,`username` FROM `users` WHERE `uuid` = ?", [$userIdBinary]);
        $patientRole = sqlQueryNoLog("SELECT `pid` FROM `patient_data` WHERE `uuid` = ?", [$userIdBinary]);

        $counter = 0;
        if (!empty($userRole['id'])) {
            $counter++;
            if ($userRole['username'] === UserService::SYSTEM_USER_USERNAME) {
                $this->userRole = self::USER_ROLE_SYSTEM;
            } else {
                $this->userRole = self::USER_ROLE_USERS;
            }
        } else if (!empty($patientRole['pid'])) {
            $counter++;
            $this->userRole = self::USER_ROLE_PATIENT;
        }

        if ($counter == 0) {
            return null;
        }
        if ($counter > 1) {
            error_log("OpenEMR Error: the same uuid has been found in different role tables");
            return null;
        }

        if (!empty($this->userRole)) {
            return $this->userRole;
        } else {
            return null;
        }
    }
}
