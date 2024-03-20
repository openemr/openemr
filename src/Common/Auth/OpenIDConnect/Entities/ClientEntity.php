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

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    protected $userId;
    protected $clientRole;
    protected $scopes;
    protected $launchUri;

    protected $jwks;

    protected $jwksUri;

    /**
     * Confidential apps or apps with a 'launch' scope must be manually authorized by an adminstrator before their
     * client can be used.
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var array[] Array of trusted user objects
     */
    protected $trustedUsers;

    /**
     * @var string[] The list of contact email addresses to reach out for questions about the client app
     */
    protected $contacts;

    /**
     * @var string The logout uri to send users to to logout from the application.
     */
    protected $logoutRedirectUris;

    protected $registrationDate;

    /**
     * @var bool true if the authorization flow (authentication and scope authorization) should be skipped for logged in ehr users
     */
    private $skipEHRLaunchAuthorizationFlow;

    public function __construct()
    {
        $this->scopes = [];
        $this->skipEHRLaunchAuthorizationFlow = false;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setRedirectUri($uri): void
    {
        if (\is_string($uri)) {
            $this->redirectUri = [$uri];
        } else if (\is_array($uri)) {
            $this->redirectUri = $uri;
        } else {
            throw new \InvalidArgumentException("redirectUri must be a string or array");
        }
    }

    public function setIsConfidential($set): void
    {
        $this->isConfidential = $set;
    }

    public function setIsEnabled($set): void
    {
        $this->isEnabled = $set === 1 || $set === true;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setUserId($id): void
    {
        $this->userId = $id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setClientRole($role): void
    {
        $this->clientRole = $role;
    }

    public function getClientRole()
    {
        return $this->clientRole;
    }

    public function getScopes()
    {
        return $this->scopes;
    }
    public function setScopes($scopes)
    {
        // clear out the scopes if our scopes are empty
        if (empty($scopes)) {
            $this->scopes = [];
            return;
        }

        if (is_string($scopes)) {
            $scopes = explode(" ", $scopes);
        } else if (!is_array($scopes)) {
            throw new \InvalidArgumentException("scopes parameter must be a valid array or string");
        }
        $this->scopes = $scopes;
    }

    /**
     * Checks if a given entity
     * @param $scope
     * @return bool
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scopes);
    }

    /**
     * Returns the registered launch URI (as a string).
     *
     * @params $launchParams string A URL query string params to append to the launch uri.
     * @return string
     */
    public function getLaunchUri($launchParams = '')
    {
        $launchParams = isset($launchParams) ? $launchParams : '';
        return $this->launchUri . $launchParams;
    }

    public function setLaunchUri($uri): void
    {
        $this->launchUri = $uri;
    }

    /**
     * @return string
     */
    public function getJwks()
    {
        return $this->jwks;
    }

    /**
     * @return string
     */
    public function getJwksUri()
    {
        return $this->jwksUri;
    }

    /**
     * @param string $jwks
     */
    public function setJwks($jwks): void
    {
        $this->jwks = $jwks;
    }

    /**
     * @param string $jwksUri
     */
    public function setJwksUri($jwksUri): void
    {
        $this->jwksUri = $jwksUri;
    }

    /**
     * Array of records from the oauth2_trusted_users table
     * @return array[]
     */
    public function getTrustedUsers(): array
    {
        return $this->trustedUsers;
    }

    /**
     * Set the trusted user records (these come from the oauth2_trusted_users table
     * @param array $trustedUsers
     */
    public function setTrustedUsers(array $trustedUsers)
    {
        $this->trustedUsers = $trustedUsers;
    }

    public function getContacts()
    {
        $this->contacts = (!empty($this_contacts)) ?: [];
        return $this->contacts;
    }

    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
    }

    public function setRegistrationDate($registerDate)
    {
        $this->registrationDate = $registerDate;
    }

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    /**
     * @return string
     */
    public function getLogoutRedirectUris(): string
    {
        return $this->logoutRedirectUris;
    }

    /**
     * @param string $logoutRedirectUris
     */
    public function setLogoutRedirectUris(?string $logoutRedirectUris): void
    {
        $this->logoutRedirectUris = $logoutRedirectUris;
    }

    /**
     * Whether the ehr launch should skip the authorization flow for a logged in user.
     * @return bool
     */
    public function shouldSkipEHRLaunchAuthorizationFlow(): bool
    {
        return $this->skipEHRLaunchAuthorizationFlow;
    }

    public function setSkipEHRLaunchAuthorizationFlow(bool $shouldSkip)
    {
        $this->skipEHRLaunchAuthorizationFlow = $shouldSkip;
    }
}
