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
     * The Resource that is being requested in this http rest call.
     * @var string
     */
    private $resource;

    /**
     * The FHIR operation that this request represents.  FHIR operations are prefixed with a $ ie $export
     * @var string
     */
    private $operation;

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
     * @var string The uuid of the patient in the current EHR context.  Could be logged in patient,
     * or patient that is in the EHR context session or was selected with a launch/patient context scope
     */
    private $patientUUIDString;

    /**
     * @var array
     */
    private $accessTokenScopes;

    /**
     * @var hashmap of resource => scopeContext where scopeContext is patient,user, or system
     */
    private $resourceScopeContexts;

    /*
     * @var bool True if the current request is a patient context request, false if its not.
     */
    private $patientRequest;

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

    /**
     * @var string the URL for the api base full url
     */
    private $apiBaseFullUrl;

    /**
     * @var string[] The request headers
     */
    private $headers;

    /**
     * @var mixed[]
     */
    private $queryParams;

    /**
     * The raw POST or PUT body contents
     * @var null|string
     */
    private $requestBody;

    public function __construct($restConfig, $server)
    {
        $this->restConfig = $restConfig;
        $this->requestSite = $restConfig::$SITE;

        $this->setRequestMethod($server["REQUEST_METHOD"]);
        $this->setRequestURI($server['REQUEST_URI'] ?? "");
        $this->headers = $this->parseHeadersFromServer($server);
        $queryParams = $_GET ?? [];
        // remove the OpenEMR queryParams that our rewrite command injected so we don't mess stuff up.
        if (isset($queryParams['_REWRITE_COMMAND'])) {
            unset($queryParams['_REWRITE_COMMAND']);
        }
        $this->setQueryParams($queryParams);

        if ($this->getRequestMethod() == "POST" || $this->getRequestMethod() == "PUT") {
            $this->requestBody = file_get_contents("php://input") ?? null;
        }
        $this->setPatientRequest(false); // default to false
    }

    /**
     * Returns the raw request body if this is a POST or PUT request
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    public function getRequestBodyJSON()
    {
        if (!empty($this->requestBody)) {
            return (array) (json_decode($this->requestBody));
        }
        return null;
    }

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    public function setQueryParams($queryParams)
    {
        $this->queryParams = $queryParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getQueryParam($key)
    {
        return $this->queryParams[$key] ?? null;
    }

    /**
     * Return an array of HTTP request headers
     * @return array|string[]
     */
    public function getHeaders()
    {
        return array_values($this->headers);
    }

    /**
     * Retrieve the value of the passed in request's HTTP header.  Return's null if the value does not exist
     * @param $headerName string the name of the header value to retrieve.
     * @return mixed|string|null
     */
    public function getHeader($headerName)
    {
        return $this->headers[$headerName] ?? null;
    }

    /**
     * Checks if the current HTTP request has the passed in header
     * @param $headerName The name of the header to check
     * @return bool true if the header exists, false otherwise.
     */
    public function hasHeader($headerName)
    {
        return !empty($this->headers[$headerName]);
    }

    /**
     * @return \RestConfig
     */
    public function getRestConfig(): \RestConfig
    {
        return $this->restConfig;
    }

    /**
     * Return the Request URI (matches the $_SERVER['REQUEST_URI'])
     * @return mixed|string
     */
    public function getRequestURI()
    {
        return $this->requestURI;
    }

    /**
     * Return the Request URI (matches the $_SERVER['REQUEST_URI'])
     * @param mixed|string $requestURI
     */
    public function setRequestURI($requestURI): void
    {
        $this->requestURI = $requestURI;
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
    public function setResource(?string $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * Returns the operation name for this request if this request represents a FHIR operation.
     * Operations are prefixed with a $
     * @return string
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * Sets the operation name for this request if this request represents a FHIR operation.
     * Operations are prefixed with a $
     * @param string $operation The operation name
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * @return array
     */
    public function getRequestUser(): array
    {
        return $this->requestUser;
    }

    /**
     * Returns the current user id if we have one
     * @return int|null
     */
    public function getRequestUserId(): ?int
    {
        $user = $this->getRequestUser();
        return $user['id'] ?? null;
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
        return array_values($this->accessTokenScopes);
    }

    public function requestHasScope($scope)
    {
        return isset($this->accessTokenScopes[$scope]);
    }

    /**
     * @param array $scopes
     */
    public function setAccessTokenScopes(array $scopes): void
    {
        // scopes are in format of <context>/<resource>.<permission>
        $scopeDelimiters = "/.";
        $validContext = ['patient', 'user','system'];
        $this->accessTokenScopes = [];
        $this->resourceScopeContexts = [];
        foreach ($scopes as $scope) {
            // make sure to populate our scopes
            $this->accessTokenScopes[$scope] = $scope;

            $scopeContext = "patient";
            $context = strtok($scope, $scopeDelimiters) ?? null;
            $resource = strtok($scopeDelimiters) ?? null;
            if (empty($context) || empty($resource)) {
                continue; // nothing to do here
                // skip over any launch parameters, fhiruser, etc.
            } else if (array_search($context, $validContext) === false) {
                continue;
            }
            $currentContext = $this->resourceScopeContexts[$resource] ?? $scopeContext;
            // user scope overwrites user and patient
            if ($context == "user" && $currentContext != 'system') {
                $scopeContext = "user";
            // system scope for the resource overwrites everything
            } else if ($context == "system") {
                $scopeContext = "system";
            } else if ($currentContext != "patient") {
                // if what we have currently is not a patient context we want to use that value and not overwrite
                // it with a patient context
                $scopeContext = $currentContext;
            }
            $this->resourceScopeContexts[$resource] = $scopeContext;
        }
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
        if (!in_array($requestUserRole, ['patient', 'users', 'system'])) {
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
        // if the logged in account is a patient user this will match with requestUserUUID
        // If the logged in account is a practitioner user this will be the user selected as part of the launch/patient
        // EHR context session.
        // This is used in
        // patient/<resource>.* requests.  IE patient/Patient.read
        return $this->patientUUIDString;
    }

    public function setPatientUuidString(?string $value)
    {
        $this->patientUUIDString = $value;
    }

    public function setPatientRequest($isPatientRequest)
    {
        $this->patientRequest = $isPatientRequest;
    }

    /**
     * Returns the scope context (patient,user,system) that is used for the given resource as parsed from the request scopes
     * @param $resource The resource to check (IE Patient, AllergyIntolerance, etc).
     * @return string|null The context or null if the resource does not exist in the scopes.
     */
    public function getScopeContextForResource($resource)
    {
        return $this->resourceScopeContexts[$resource] ?? null;
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
        return $this->patientRequest === true;
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

    public function isFhirSearchRequest(): bool
    {
        if ($this->isFhir() && $this->getRequestMethod() == "POST") {
            //ensure compliant wth php 7.4 (no str_ends_with() function in 7.4)
            if (!function_exists('str_ends_with')) {
                return substr($this->getRequestPath(), -7) === '_search';
            } else { // function_exists('str_ends_with')
                return str_ends_with($this->getRequestPath(), '_search') !== false;
            }
        }
        return false;
    }

    public function setRequestPath(string $requestPath)
    {
        $this->requestPath = $requestPath;
    }

    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }

    /**
     * Returns the full URL to the api server
     * @return string
     */
    public function getApiBaseFullUrl(): string
    {
        return $this->apiBaseFullUrl;
    }

    /**
     * Set the full URL to the api server that api requests are appended to.
     * @param string $apiBaseFullUrl
     */
    public function setApiBaseFullUrl(string $apiBaseFullUrl): void
    {
        $this->apiBaseFullUrl = $apiBaseFullUrl;
    }

    /**
     * Given an array of server variables (typically the $_SERVER superglobal) parse out all of the HTTP_X headers
     * and convert them into a hashmap of header -> header
     * @param $server array of server variables typically the $_SERVER superglobal
     * @return array hashmap of header -> header
     */
    private function parseHeadersFromServer($server)
    {
        $headers = array();
        foreach ($server as $key => $value) {
            $prefix = substr($key, 0, 5);

            if ($prefix != 'HTTP_') {
                continue;
            }

            $serverHeader = strtolower(substr($key, 5));
            $uppercasedServerHeader = ucwords(str_replace('_', ' ', $serverHeader));

            $header = str_replace(' ', '-', $uppercasedServerHeader);
            $headers[$header] = $value;
        }
        return $headers;
    }
}
