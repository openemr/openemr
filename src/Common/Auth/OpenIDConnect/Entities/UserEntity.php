<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClaimSetInterface;
use OpenEMR\Common\Auth\OpenIDConnect\FhirUserClaim;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Utils\ValidationUtils;

class UserEntity implements ClaimSetInterface, UserEntityInterface
{
    public ?string $identifier = null;

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
        if (ValidationUtils::isValidUrl($fhirBaseUrl)) {
            $this->fhirBaseUrl = $fhirBaseUrl;
        } else {
            throw OAuthServerException::invalidRequest('fhirBaseUrl', 'Invalid FHIR base URL provided.');
        }
    }

    /**
     * @return array<string, mixed>
     * @throws OAuthServerException
     */
    public function getClaims(): array
    {
        // Note: session-type claims like nonce get added in OEIdTokenResponse.
        assert($this->identifier !== null);
        $fhirUserClaim = new FhirUserClaim();
        $fhirUserClaim->setFhirBaseUrl($this->fhirBaseUrl);
        $fhirUser = $fhirUserClaim->getFhirUser($this->identifier);
        $uuidToUser = new UuidUserAccount($this->identifier);
        $user = $uuidToUser->getUserAccount();
        if (empty($user)) {
            $user = [
                'fullname' => '',
                'lastname' => '',
                'firstname' => '',
                'middlename' => '',
                'username' => 'unknown',
                'email' => '',
                'phone' => '',
                'street' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
            ];
        }

        return [
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
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $id): void
    {
        $this->identifier = $id;
    }
}
