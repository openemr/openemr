<?php

/**
 * HttpRestRequest represents the current OpenEMR api request
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Common\Uuid\UuidRegistry;

class HttpRestRequest
{

    /**
     * @var \RestConfig
     */
    private $restConfig;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var array
     */
    private $requestUser;

    /**
     * The binary string of the request user uuid
     * @var string
     */
    private $requestUserUUID;

    /**
     * @var string
     */
    private $requestUserUUIDString;

    /**
     * @var 'patient'|'users'
     */
    private $requestUserRole;

    /**
     * @var array
     */
    private $accessTokenScopes;

    /**
     * @var string
     */
    private $requestSite;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $accessTokenId;

    /**
     * @var boolean
     */
    private $isLocalApi;

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * The kind of REST api request this object represents
     * @var string
     */
    private $apiType;

    /**
     * @var string
     */
    private $requestPath;

    public function __construct($restConfig, $server)
    {
        $this->restConfig = $restConfig;
        $this->requestSite = $restConfig::$SITE;

        $this->requestMethod = $server["REQUEST_METHOD"];
    }

    /**
     * @return \RestConfig
     */
    public function getRestConfig(): \RestConfig
    {
        return $this->restConfig;
    }



    /**
     * @return string
     */
    public function getResource(): ?string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     */
    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return array
     */
    public function getRequestUser(): array
    {
        return $this->requestUser;
    }

    /**
     * @param array $requestUser
     */
    public function setRequestUser($userUUIDString, array $requestUser): void
    {
        $this->requestUser = $requestUser;

        // set up any other user context information
        if (empty($requestUser)) {
            $this->requestUserUUIDString = null;
            $this->requestUserUUID = null;
        } else {
            $this->requestUserUUIDString = $userUUIDString ?? null;
            $this->requestUserUUID = UuidRegistry::uuidToBytes($userUUIDString) ?? null;
        }
    }

    /**
     * @return array
     */
    public function getAccessTokenScopes(): array
    {
        return $this->accessTokenScopes;
    }

    /**
     * @param array $scopes
     */
    public function setAccessTokenScopes(array $scopes): void
    {
        $this->accessTokenScopes = $scopes;
    }

    /**
     * @return string
     */
    public function getRequestSite(): ?string
    {
        return $this->requestSite;
    }

    /**
     * @param string $requestSite
     */
    public function setRequestSite(string $requestSite): void
    {
        $this->requestSite = $requestSite;
    }

    /**
     * @return string
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getAccessTokenId(): ?string
    {
        return $this->accessTokenId;
    }

    /**
     * @param string $accessTokenId
     */
    public function setAccessTokenId(string $accessTokenId): void
    {
        $this->accessTokenId = $accessTokenId;
    }

    /**
     * @return bool
     */
    public function isLocalApi(): bool
    {
        return $this->isLocalApi;
    }

    /**
     * @param bool $isLocalApi
     */
    public function setIsLocalApi(bool $isLocalApi): void
    {
        $this->isLocalApi = $isLocalApi;
    }

    /**
     * @return mixed
     */
    public function getRequestUserRole()
    {
        return $this->requestUserRole;
    }

    /**
     * @param string $requestUserRole either 'patients' or 'users'
     */
    public function setRequestUserRole($requestUserRole): void
    {
        if (!in_array($requestUserRole, ['patient', 'users'])) {
            throw new \InvalidArgumentException("invalid user role found");
        }
        $this->requestUserRole = $requestUserRole;
    }

    public function getRequestUserUUID()
    {
        return $this->requestUserUUID;
    }

    public function getRequestUserUUIDString()
    {
        return $this->requestUserUUIDString;
    }

    public function getPatientUUIDString()
    {
        // we may change how this is set, it will depend on if a 'user' role type can still have
        // patient/<resource>.* requests.  IE patient/Patient.read
        return $this->requestUserUUIDString;
    }

    /**
     * @return string
     */
    public function getApiType(): ?string
    {
        return $this->apiType;
    }

    /**
     * @param string $api
     */
    public function setApiType(string $apiType): void
    {
        if (!in_array($apiType, ['fhir', 'oemr', 'port'])) {
            throw new \InvalidArgumentException("invalid api type found");
        }
        $this->apiType = $apiType;
    }

    /**
     * @return string
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }


    public function isPatientRequest()
    {
        return $this->requestUserRole === 'patient';
    }

    public function isFhir()
    {
        return $this->getApiType() === 'fhir';
    }

    /**
     * If this is a patient context request for write/modify of patient context resources
     * @return bool
     */
    public function isPatientWriteRequest()
    {
        return $this->isFhir() && $this->isPatientRequest() && $this->getRequestMethod() != 'GET';
    }

    public function setRequestPath(string $requestPath)
    {
        $this->requestPath = $requestPath;
    }

    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }
}
