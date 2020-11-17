<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class UserEntity implements ClaimSetInterface, UserEntityInterface
{

    public $userRole;
    public $identifier;

    public function getClaims()
    {
        $user = $this->getUserAccount($this->identifier);
        $claims = [
            'name' => $user['fullname'],
            'family_name' => $user['lastname'],
            'given_name' => $user['firstname'],
            'middle_name' => $user['middlename'],
            'nickname' => '',
            'preferred_username' => $user['username'],
            'profile' => '',
            'picture' => '',
            'website' => '',
            'gender' => '',
            'birthdate' => '',
            'zoneinfo' => '',
            'locale' => 'US',
            'updated_at' => '',
            'email' => $user['email'],
            'email_verified' => true,
            'phone_number' => $user['phone'],
            'phone_number_verified' => true,
            'address' => $user['street'] . ' ' . $user['city'] . ' ' . $user['state'],
            'zip' => $user['zip'],
            'api:fhir' => true,
            'api:oemr' => true,
            'api:port' => true,
            'api:pofh' => true,
        ];
        if ($_SESSION['nonce']) {
            $claims['nonce'] = $_SESSION['nonce'];
        }
        if ($_SESSION['site_id']) {
            $claims['site'] = $_SESSION['site_id'];
        }

        return $claims;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($id): void
    {
        $this->identifier = $id;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }

    public function setUserRole($role): void
    {
        $this->userRole = $role;
    }

    public function getUserAccount($userId)
    {
        // We really don't need user roles here especially since not passed along in token but,
        // future is unknown so leaving for now. Could simply use only uuids for all users and
        // set roles in resource server.
        if (!is_numeric($userId)) {
            $uuidreg = new UuidRegistry();
            $userId = $uuidreg::uuidToBytes($userId);
        }

        $userRole = (!empty($this->userRole)) ? $this->userRole : $_SESSION['user_role'];
        switch ($userRole) {
            case 'users':
                $account_sql = "SELECT `username`, `authorized`, `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone`, `email`, `street`, `city`, `state`, `zip`, CONCAT(fname, ' ', lname) AS fullname FROM `users`";
                if (is_numeric($userId)) {
                    $account_sql .= " WHERE `id` = ?";
                } else {
                    $account_sql .= " WHERE `uuid` = ?";
                }
                break;
            case 'patient':
                $account_sql = "SELECT `lname` AS lastname, `fname` AS firstname, `mname` AS middlename, `phone_contact` AS phone, `sex` AS gender, `email`, `DOB` AS birthdate, `street`, `postal_code` AS zip, `city`, `state`, CONCAT(fname, ' ', lname) AS fullname FROM `patient_data`";
                if (is_numeric($userId)) {
                    $account_sql .= " WHERE `pid` = ?";
                } else {
                    $account_sql .= " WHERE `uuid` = ?";
                }
                break;
            default:
                return null;
        }

        return sqlQueryNoLog($account_sql, array($userId));
    }

    protected function getAccountByPassword($userrole, $username, $password, $email = ''): bool
    {
        if ($userrole == "users") {
            $auth = new AuthUtils('api');
            if ($auth->confirmPassword($username, $password)) {
                $this->setUserRole('users');
                $_SESSION['user_role'] = 'users';
                $id = $auth->getUserId();
                (new UuidRegistry(['table_name' => 'users']))->createMissingUuids();
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `users` WHERE `id` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    error_log("OpenEMR Error: unable to map uuid for user when creating oauth password grant token");
                    return false;
                }
                $this->setIdentifier(UuidRegistry::uuidToString($uuid));
                return true;
            }
        } elseif ($userrole == "patient") {
            $auth = new AuthUtils('portal-api');
            if ($auth->confirmPassword($username, $password, $email)) {
                $this->setUserRole('patient');
                $_SESSION['user_role'] = 'patient';
                $id = $auth->getPatientId();
                (new UuidRegistry(['table_name' => 'patient_data']))->createMissingUuids();
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    error_log("OpenEMR Error: unable to map uuid for patient when creating oauth password grant token");
                    return false;
                }
                $this->setIdentifier(UuidRegistry::uuidToString($uuid));
                return true;
            }
        }

        return false;
    }
}
