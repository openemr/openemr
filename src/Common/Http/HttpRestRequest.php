<?php

/**
 * HttpRestRequest represents the current OpenEMR api request
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use Http\Message\Encoding\GzipDecodeStream;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Common\Uuid\UuidRegistry;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

class HttpRestRequest extends Request implements \Stringable
{

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
    private $isLocalApi = false;

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

    public static function createFromGlobals(): static
    {
        $request = parent::createFromGlobals();
        $request->setPatientRequest(false); // default to false

        if (!empty($request->headers->has('APICSRFTOKEN'))) {
            $request->setIsLocalApi(true);
        }
        return $request;
    }

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Returns the raw request body if this is a POST or PUT request
     */
    public function getRequestBody()
    {
        return $this->content;
    }

    public function getRequestBodyJSON()
    {
        $contentEncoding = $this->encodings;
        // let's decode the gzip content if we can
        if (!empty($contentEncoding) && $contentEncoding[0] === "gzip") {
            $stream = new GzipDecodeStream($this->getContent(true));
        } else {
            $stream = $this->getContent(true);
        }
        return json_decode($stream->getContents(), true);
    }

    public function setRequestMethod($requestMethod)
    {
        $this->method = $requestMethod;
    }

    public function setQueryParams($queryParams)
    {
        $this->query = new InputBag($queryParams);
    }

    public function getQueryParams()
    {
        return $this->query->all();
    }

    public function getQueryParam($key) : bool|float|int|null|string
    {
        return $this->query->get($key);
    }

    /**
     * Return an array of HTTP request headers
     * @return array|string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * Retrieve the value of the passed in request's HTTP header.  Returns an empty array if the header is not found
     * @param $headerName string the name of the header value to retrieve.
     * @return string[]
     */
    public function getHeader($headerName)
    {
        return $this->headers->all($headerName) ?? [];
    }

    /**
     * Checks if the current HTTP request has the passed in header
     * @param $headerName The name of the header to check
     * @return bool true if the header exists, false otherwise.
     */
    public function hasHeader($headerName): bool
    {
        return $this->headers->has($headerName);
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
     * changing the request uri, resets all of the populated methods that derive from the URI as we can't determine
     * what they are, these have to be manually reset
     * @param mixed|string $requestURI
     */
    public function setRequestURI($requestURI): void
    {
        $this->resource = null;
        $this->requestPath = null;
        $this->requestUri = $requestURI;
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

    public function requestHasScope($scope): bool
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
        // TODO: @adunsulag should we update the request URI to remove the site from the path?
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
     * @param $resource string The resource to check (IE Patient, AllergyIntolerance, etc).
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
     * @deprecated use getMethod() instead
     * @return string
     */
    public function getRequestMethod(): ?string
    {
        return $this->getMethod();
    }


    public function isPatientRequest()
    {
        return $this->patientRequest === true;
    }

    public function isFhirRequest(): bool
    {
        return stripos(strtolower($this->getPathInfo()), "/fhir/") !== false;
    }

    public function isPortalRequest(): bool
    {
        return stripos(strtolower($this->getPathInfo()), "/portal/") !== false;
    }

    public function isStandardApiRequest(): bool
    {
        return stripos(strtolower($this->getPathInfo()), "/api/") !== false;
    }

    public function isFhir()
    {
        if (!isset($this->apiType) && $this->isFhirRequest()) {
            // if we don't have an api type set, then we assume its a fhir request
            $this->setApiType('fhir');
        }
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
            return str_ends_with($this->getRequestPath(), '_search') !== false;
        }
        return false;
    }

    public function setRequestPath(string $requestPath)
    {
        throw new \RuntimeException("Feature not implemented yet");
    }

    public function getRequestPathWithoutSite()
    {
        // This will return the request path without the site prefix
        $pathInfo = $this->getPathInfo();
        if (empty($pathInfo)) {
            return null; // no path info available
        }
        if (empty($this->requestSite)) {
            return $pathInfo; // no site prefix set, return full path
        }

        $endOfPath = strpos($pathInfo, '/', 1);
        if ($endOfPath === false) {
            return $pathInfo; // no site prefix found
        }
        return substr($pathInfo, $endOfPath);
    }

    public function getRequestPath(): ?string
    {
        return $this->getPathInfo();
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


    public function withProtocolVersion($version)
    {
        $clonedRequest = clone $this;
        $clonedRequest->server->set("SERVER_PROTOCOL", 'HTTP/' . $version);
        return $clonedRequest;
    }

    public function getHeaderLine($name)
    {
        return implode(",", $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->set($name, $value, true);
        return $clonedRequest;
    }

    public function withAddedHeader($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->set($name, $value);
        return $clonedRequest;
    }

    public function withoutHeader($name)
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->remove($name);
        return $clonedRequest;
    }

    public function getBody()
    {
        return $this->getContent();
    }

    public function withBody(StreamInterface $body)
    {
        $clonedRequest = clone $this;
        $clonedRequest->content = $body;
        return $clonedRequest;
    }

    public function getRequestTarget(): string
    {
        return $this->innerServerRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget)
    {
        $clonedRequest = clone $this;
        // TODO: @adunsulag not sure how to implement this.
        return $clonedRequest;
    }

    public function withMethod($method)
    {
        $clonedRequest = clone $this;
        $clonedRequest->method = $method;
        return $clonedRequest;
    }

    public function getUri(): string
    {
        return $this->requestUri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clonedRequest = clone $this;
        if (!$preserveHost) {
            $clonedRequest->headers->set('HOST', $uri->getHost());
        }
        $clonedRequest->server->set('REQUEST_URI', $uri->getPath() . '?' . $uri->getQuery());
        $clonedRequest->prepareRequestUri();
        $clonedRequest->resource = null;
        $clonedRequest->requestPath = null;
        return $clonedRequest;
    }

    public function getServerParams()
    {
        return $this->server->all();
    }

    public function getCookieParams()
    {
        return $this->cookies->all();
    }

    public function withCookieParams(array $cookies)
    {
        $clonedRequest = clone $this;
        $clonedRequest->cookies = new InputBag($cookies);
        return $clonedRequest;
    }

    public function withQueryParams(array $query)
    {
        $clonedRequest = clone $this;
        $clonedRequest->query = new InputBag($query);
        return $clonedRequest;
    }

    public function getUploadedFiles()
    {
        return $this->files->all();
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $clonedRequest = clone $this;
        $clonedRequest->files = new InputBag($uploadedFiles);
        return $clonedRequest;
    }

    public function getParsedBody()
    {
        return $this->getPayload()->all();
    }

    public function withParsedBody($data)
    {
        $clonedRequest = clone $this;
        $clonedRequest->request = new InputBag($data);
        return $clonedRequest;
    }

    public function getAttributes()
    {
        return $this->attributes->all();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    public function withAttribute($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->attributes->set($name, $value);
        return $clonedRequest;
    }

    public function withoutAttribute($name)
    {
        $clonedRequest = clone $this;
        $clonedRequest->attributes->remove($name);
        return $clonedRequest;
    }
}
