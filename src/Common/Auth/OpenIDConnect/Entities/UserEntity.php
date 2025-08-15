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
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PractitionerService;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class UserEntity implements ClaimSetInterface, UserEntityInterface
{
    public $userRole;
    public $identifier;

    protected $claimsType = 'oidc'; // default to oidc claims

    private string $fhirBaseUrl = '';

    public function __construct()
    {
    }

    public function getFhirBaseUrl(): string
    {
        return $this->fhirBaseUrl;
    }

    public function setFhirBaseUrl(string $fhirBaseUrl): void
    {
        if (filter_var($fhirBaseUrl, FILTER_VALIDATE_URL)) {
            $this->fhirBaseUrl = $fhirBaseUrl;
        } else {
            throw OAuthServerException::invalidRequest('fhirBaseUrl', 'Invalid FHIR base URL provided.');
        }
    }

    public function getClaimsType(): string
    {
        return $this->claimsType;
    }

    public function setClaimsType(string $claimsType): void
    {
        if (in_array($claimsType, ['oidc', 'client'])) {
            $this->claimsType = $claimsType;
        } else {
            throw OAuthServerException::invalidRequest('claimsType', 'Invalid claims type provided. Must be "oidc" or "client".');
        }
    }

    public function getClaims()
    {
        $claims = [];
        // TODO: @adunsulag as far as I can tell the UserEntity class is only used w/o the client_credentials grant type
        // so we don't need this.  Will comment for now, but I believe we can remove this code.
        // Note session type claims like nonce get added in the IdTokenSMARTResponse
//        if ($this->getClaimsType() === 'oidc') {
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
            $fhirUser = $this->getFhirBaseUrl() . $fhirUserResource . "/" . $this->identifier;

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
                // TODO: locale should be set to the user's locale
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
//        }
//        // TODO: @adunsulag need to revisit the client_credentials grant type and how we handle claims
//        if ($this->getClaimsType() === 'client') {
//            $claims = [
//                'api:fhir' => true,
//                'api:oemr' => true,
//                'api:port' => true,
//            ];
//        }

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
}
