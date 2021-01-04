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

    /**
     * Confidential apps or apps with a 'launch' scope must be manually authorized by an adminstrator before their
     * client can be used.
     * @var bool
     */
    protected $isEnabled;

    public function __construct()
    {
        $this->scopes = [];
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function setRedirectUri($uri): void
    {
        $this->redirectUri = $uri;
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
}
