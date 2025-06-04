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
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\MfaUtils;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PractitionerService;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class UserEntity implements ClaimSetInterface, UserEntityInterface
{
    public $userRole;
    public $identifier;

    public function getClaims()
    {
        $claimsType = (!empty($_REQUEST['grant_type']) && ($_REQUEST['grant_type'] === 'client_credentials')) ? 'client' : 'oidc';
        if ($claimsType === 'oidc') {
            $uuidToUser = new UuidUserAccount($this->identifier);
            $fhirUser = '';
            $userRole = $uuidToUser->getUserRole();
            $fhirUserResource = "Person";
            if ($userRole == UuidUserAccount::USER_ROLE_USERS) {
                // need to find out if its a practitioner or not
                $practitionerService = new PractitionerService();
                // ONC validation does not accept Person as a valid test case so we have to differentiate practitioners
                // from the more generic Person resource.
                if ($practitionerService->isValidPractitionerUuid($this->identifier)) {
                    $fhirUserResource = "Practitioner";
                }
            } else if ($userRole == UuidUserAccount::USER_ROLE_PATIENT) {
                $fhirUserResource = "Patient";
            } else {
                (new SystemLogger())->error("user role not supported for fhirUser claim ", ['role' => $userRole]);
            }
            $fhirUser = $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . '/apis/' . $_SESSION['site_id'] . "/fhir/" . $fhirUserResource . "/" . $this->identifier;

            (new SystemLogger())->debug("UserEntity->getClaims() fhirUser claim is ", ['role' => $userRole, 'fhirUser' => $fhirUser]);

            $user = $uuidToUser->getUserAccount();
            if (empty($user)) {
                $user = false;
            }
            $claims = [
                'name' => $user['fullname'],
                'family_name' => $user['lastname'],
                'given_name' => $user['firstname'],
                'middle_name' => $user['middlename'],
                'nickname' => '',
                'preferred_username' => $user['username'] ?? '',
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
                'fhirUser' => $fhirUser,
                'api:fhir' => true,
                'api:oemr' => true,
                'api:port' => true,
            ];
        }
        if ($claimsType === 'client') {
            $claims = [
                'fhirUser' => $fhirUser,
                'api:fhir' => true,
                'api:oemr' => true,
                'api:port' => true,
            ];
        }
        if (!empty($_SESSION['nonce'])) {
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

    protected function getAccountByPassword($userrole, $username, $password, $email = ''): bool
    {
        if (($userrole == UuidUserAccount::USER_ROLE_USERS) && (($GLOBALS['oauth_password_grant'] == 1) || ($GLOBALS['oauth_password_grant'] == 3))) {
            $auth = new AuthUtils('api');
            if ($auth->confirmPassword($username, $password)) {
                $id = $auth->getUserId();
                UuidRegistry::createMissingUuidsForTables(['users']);
                $uuid = sqlQueryNoLog("SELECT `uuid` FROM `users` WHERE `id` = ?", [$id])['uuid'];
                if (empty($uuid)) {
                    error_log("OpenEMR Error: unable to map uuid for user when creating oauth password grant token");
                    return false;
                }
                $this->setIdentifier(UuidRegistry::uuidToString($uuid));

                // If an mfa_token was provided, then will force TOTP MFA (U2F impossible to support via password grant)
                //  (note that this is only forced if mfa_token is provided)
                $mfa = new MfaUtils($id);
                $mfaToken = $mfa->tokenFromRequest(MfaUtils::TOTP);
                if (!is_null($mfaToken)) {
                    if (!$mfa->isMfaRequired() || !in_array(MfaUtils::TOTP, $mfa->getType())) {
                        // A mfa_token was provided, however the user is not configured for totp
                        throw new OAuthServerException(
                            'MFA not supported.',
                            11,
                            'mfa_not_supported',
                            403
                        );
                    } else {
                        //Check the validity of the totp token, if applicable
                        if (!empty($mfaToken) && $mfa->check($mfaToken, MfaUtils::TOTP)) {
                            return true;
                        } else {
                            throw new OAuthServerException(
                                $mfa->errorMessage(),
                                12,
                                'mfa_token_invalid',
                                401
                            );
                        }
                    }
                }

                return true;
            }
        } elseif (($userrole == UuidUserAccount::USER_ROLE_PATIENT) && (($GLOBALS['oauth_password_grant'] == 2) || ($GLOBALS['oauth_password_grant'] == 3))) {
            $auth = new AuthUtils('portal-api');
            if ($auth->confirmPassword($username, $password, $email)) {
                $id = $auth->getPatientId();
                UuidRegistry::createMissingUuidsForTables(['patient_data']);
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
