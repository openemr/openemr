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

class HttpRestRequest implements ServerRequestInterface, \Stringable
{
    /**
     * @var ServerRequestInterface
     */
    private $innerServerRequest;

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

    public function __construct($restConfig, $server)
    {
        $this->restConfig = $restConfig;
        $this->requestSite = $restConfig::$SITE;


        $headers = $this->parseHeadersFromServer($server);
        $body = null;
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
        if ($method == "POST" || $method == "PUT") {
            $body = file_get_contents("php://input") ?? null;
        }
        // we use the URI from the restConfig to handle our ServerRequestInterface
        $requestUri = $this->restConfig::getRequestEndPoint();
        $this->requestSite = $this->restConfig::$SITE;
        $requestUri = str_replace('/' . $this->requestSite, '', $requestUri);
        // we use this to handle our ServerRequestInterface
        $this->innerServerRequest =  new ServerRequest(
            $method,
            $requestUri ?? "",
            $headers,
            $body,
            '1.1',
            $server
        );

        $queryParams = $_GET ?? [];
        // remove the OpenEMR queryParams that our rewrite command injected so we don't mess stuff up.
        if (isset($queryParams['_REWRITE_COMMAND'])) {
            unset($queryParams['_REWRITE_COMMAND']);
        }
        $this->innerServerRequest = $this->innerServerRequest->withQueryParams($queryParams);
        $this->setPatientRequest(false); // default to false

        if (!empty($headers['APICSRFTOKEN'])) {
            $this->setIsLocalApi(true);
        }
    }

    /**
     * Returns the raw request body if this is a POST or PUT request
     */
    public function getRequestBody()
    {
        $stream = $this->innerServerRequest->getBody(); // Nyholm points things at the end of the stream, so we need to rewind it.
        $stream->rewind();
        return $stream->getContents();
    }

    public function getRequestBodyJSON()
    {
        $stream = $this->innerServerRequest->getBody(); // Nyholm points things at the end of the stream, so we need to rewind it.
        $stream->rewind();
        $contentEncoding = $this->getHeader("Content-Encoding");
        // let's decode the gzip content if we can
        if (!empty($contentEncoding) && $contentEncoding[0] === "gzip") {
            $stream = new GzipDecodeStream($stream);
        }
        return json_decode($stream->getContents(), true);
    }

    public function setRequestMethod($requestMethod)
    {
        $this->innerServerRequest = $this->innerServerRequest->withMethod($requestMethod);
    }

    public function setQueryParams($queryParams)
    {
        $this->innerServerRequest = $this->innerServerRequest->withQueryParams($queryParams);
    }

    public function getQueryParams()
    {
        return $this->innerServerRequest->getQueryParams();
    }

    public function getQueryParam($key)
    {
        $params = $this->getQueryParams();
        return $params[$key] ?? null;
    }

    /**
     * Return an array of HTTP request headers
     * @return array|string[][]
     */
    public function getHeaders()
    {
        return $this->innerServerRequest->getHeaders();
    }

    /**
     * Retrieve the value of the passed in request's HTTP header.  Returns an empty array if the header is not found
     * @param $headerName string the name of the header value to retrieve.
     * @return string[]
     */
    public function getHeader($headerName)
    {
        return $this->innerServerRequest->getHeader($headerName);
    }

    /**
     * Checks if the current HTTP request has the passed in header
     * @param $headerName The name of the header to check
     * @return bool true if the header exists, false otherwise.
     */
    public function hasHeader($headerName)
    {
        return $this->innerServerRequest->hasHeader($headerName);
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
        return $this->innerServerRequest->getUri();
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
        $this->innerServerRequest = $this->innerServerRequest->withUri(new Uri($requestURI));
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
        // while here parse site from endpoint
        $resource = str_replace('/' . $requestSite, '', $this->innerServerRequest->getUri()->getPath());
        $this->innerServerRequest->withUri(new Uri($resource));
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
     * @return string
     */
    public function getRequestMethod(): ?string
    {
        return $this->innerServerRequest->getMethod();
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
            return str_ends_with($this->getRequestPath(), '_search') !== false;
        }
        return false;
    }

    public function setRequestPath(string $requestPath)
    {
        $this->innerServerRequest = $this->innerServerRequest->withUri($this->innerServerRequest->getUri()->withPath($requestPath));
    }

    public function getRequestPath(): ?string
    {
        return $this->innerServerRequest->getUri()->getPath();
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

    public function getProtocolVersion()
    {
        return $this->innerServerRequest->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withProtocolVersion($version);
        return $clonedRequest;
    }

    public function getHeaderLine($name)
    {
        return $this->innerServerRequest->getHeaderLine($name);
    }

    public function withHeader($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withHeader($name, $value);
        return $clonedRequest;
    }

    public function withAddedHeader($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withAddedHeader($name, $value);
        return $clonedRequest;
    }

    public function withoutHeader($name)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withoutHeader($name);
        return $clonedRequest;
    }

    public function getBody()
    {
        return $this->innerServerRequest->getBody();
    }

    public function withBody(StreamInterface $body)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withBody($body);
        return $clonedRequest;
    }

    public function getRequestTarget()
    {
        $this->innerServerRequest->getRequestTarget();
    }

    public function withRequestTarget($requestTarget)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withRequestTarget($requestTarget);
        return $clonedRequest;
    }

    public function getMethod()
    {
        return $this->innerServerRequest->getMethod();
    }

    public function withMethod($method)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withRequestTarget($method);
        return $clonedRequest;
    }

    public function getUri()
    {
        return $this->innerServerRequest->getUri();
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withUri($uri, $preserveHost);
        $clonedRequest->resource = null;
        $clonedRequest->requestPath = null;
        return $clonedRequest;
    }

    public function getServerParams()
    {
        return $this->innerServerRequest->getServerParams();
    }

    public function getCookieParams()
    {
        return $this->innerServerRequest->getCookieParams();
    }

    public function withCookieParams(array $cookies)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withCookieParams($cookies);
        return $clonedRequest;
    }

    public function withQueryParams(array $query)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withQueryParams($query);
        return $clonedRequest;
    }

    public function getUploadedFiles()
    {
        return $this->innerServerRequest->getUploadedFiles();
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withUploadedFiles($uploadedFiles);
        return $clonedRequest;
    }

    public function getParsedBody()
    {
        return $this->innerServerRequest->getParsedBody();
    }

    public function withParsedBody($data)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withParsedBody($data);
        return $clonedRequest;
    }

    public function getAttributes()
    {
        return $this->innerServerRequest->getAttributes();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->innerServerRequest->getAttribute($name, $default);
    }

    public function withAttribute($name, $value)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withAttribute($name, $value);
        return $clonedRequest;
    }

    public function withoutAttribute($name)
    {
        $clonedRequest = clone $this;
        $clonedRequest->innerServerRequest = $clonedRequest->innerServerRequest->withoutAttribute($name);
        return $clonedRequest;
    }
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
            if (empty($headers[$header])) {
                $headers[$header] = [];
            }
            $headers[$header][] = $value;
        }
        return $headers;
    }

    public function __toString()
    {
        return self::class; // just returning the class name for now, at some point we can return a summary of the full request
    }
}
