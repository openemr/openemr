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
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Stringable;
use InvalidArgumentException;

class HttpRestRequest extends Request implements Stringable
{
    /**
     * The Resource that is being requested in this http rest call.
     * @var string|null
     */
    private ?string $resource = null;

    /**
     * The FHIR operation that this request represents.  FHIR operations are prefixed with a $ ie $export
     * @var string|null
     */
    private ?string $operation = null;

    /**
     * @var array
     */
    private array $requestUser;

    /**
     * The binary string of the request user uuid
     * @var string|null
     */
    private ?string $requestUserUUID = null;

    /**
     * @var string|null
     */
    private ?string $requestUserUUIDString = null;

    /**
     * @var 'patient'|'users'
     */
    private string $requestUserRole;

    /**
     * @var string|null The uuid of the patient in the current EHR context.  Could be logged in patient,
     * or patient that is in the EHR context session or was selected with a launch/patient context scope
     */
    private ?string $patientUUIDString = null;

    /**
     * @var ResourceScopeEntityList[] hashmap of string => ResourceScopeEntityList where the string is the scope lookup key
     */
    private array $accessTokenScopes;

    /**
     * @var array hashmap of resource => scopeContext where scopeContext is patient,user, or system
     */
    private array $resourceScopeContexts;

    /*
     * @var bool True if the current request is a patient context request, false if its not.
     */
    private bool $patientRequest;

    /**
     * @var string
     */
    private string $requestSite = "default"; // default site

    /**
     * @var string|null
     */
    private ?string $clientId = null;

    /**
     * @var string
     */
    private string $accessTokenId;

    /**
     * @var bool
     */
    private bool $isLocalApi = false;

    /**
     * The kind of REST api request this object represents
     * @var string|null
     */
    private ?string $apiType = null;

    /**
     * @var string the URL for the api base full url
     */
    private string $apiBaseFullUrl;

    public static function createFromGlobals(): static
    {
        // Handle the rewrite command transformation before calling parent
        // TODO: change this logic if we decide to change up our _REWRITE_COMMAND logic.
        // our current mod_rewrite rules will set the _REWRITE_COMMAND in the query string which is different than
        // how php will typically handle clean urls where PATH_INFO is set to come after the script name.
        // If the _REWRITE_COMMAND is set, we will use it to set the PATH_INFO and REQUEST_URI so we can handle the
        // request properly in Symfony.
        if (isset($_GET['_REWRITE_COMMAND'])) {
            $rewritePath = $_GET['_REWRITE_COMMAND'];

            // Remove the _REWRITE_COMMAND from $_GET so Symfony doesn't see it
            unset($_GET['_REWRITE_COMMAND']);

            // Set up PATH_INFO for Symfony
            $_SERVER['PATH_INFO'] = '/' . ltrim((string) $rewritePath, '/');

            // Update REQUEST_URI to reflect the clean path
            $queryString = http_build_query($_GET);
            // need to imitate the request the way symfony would parse it.
            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'] . ($queryString ? '?' . $queryString : '');

            // Update QUERY_STRING to exclude the _REWRITE_COMMAND
            $_SERVER['QUERY_STRING'] = $queryString;

            // Set PHP_SELF appropriately
            $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
        }

        $request = parent::createFromGlobals();
        $request->setPatientRequest(false); // default to false

        if (!empty($request->headers->has('APICSRFTOKEN'))) {
            $request->setIsLocalApi(true);
        }
        return $request;
    }

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->patientRequest = false;
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Returns the raw request body if this is a POST or PUT request
     * @return false|null|string|resource
     */
    public function getRequestBody(): mixed
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

    public function setRequestMethod($requestMethod): void
    {
        $this->method = $requestMethod;
    }

    public function setQueryParams($queryParams): void
    {
        $this->query = new InputBag($queryParams);
    }

    public function getQueryParams(): array
    {
        return $this->query->all();
    }

    public function getQueryParam($key): bool|float|int|null|string
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
    public function getHeader(string $headerName): array
    {
        return $this->headers->all($headerName);
    }

    /**
     * Checks if the current HTTP request has the passed in header
     * @param string $headerName The name of the header to check
     * @return bool true if the header exists, false otherwise.
     */
    public function hasHeader(string $headerName): bool
    {
        return $this->headers->has($headerName);
    }

    /**
     * Return the Request URI (matches the $_SERVER['REQUEST_URI'])
     * changing the request uri, resets all of the populated methods that derive from the URI as we can't determine
     * what they are, these have to be manually reset
     * @param string $requestURI
     * @deprecated
     */
    public function setRequestURI(string $requestURI): void
    {
        $this->resource = null;
        $this->requestUri = $requestURI;
    }

    /**
     * @return string|null
     */
    public function getResource(): ?string
    {
        return $this->resource;
    }

    /**
     * @param string|null $resource
     */
    public function setResource(?string $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * Returns the operation name for this request if this request represents a FHIR operation.
     * Operations are prefixed with a $
     * @return string|null
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * Sets the operation name for this request if this request represents a FHIR operation.
     * Operations are prefixed with a $
     * @param string|null $operation The operation name
     */
    public function setOperation(?string $operation): void
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
     * @param string|null $userUUIDString
     * @param array $requestUser
     */
    public function setRequestUser(?string $userUUIDString, array $requestUser): void
    {
        $this->requestUser = $requestUser;

        // set up any other user context information
        if (empty($requestUser)) {
            $this->requestUserUUIDString = null;
            $this->requestUserUUID = null;
        } else {
            $this->requestUserUUIDString = $userUUIDString ?? null;
            $this->requestUserUUID = UuidRegistry::uuidToBytes($userUUIDString);
        }
    }

    /**
     * Returns an array of strings that represent the access token scopes for this request.
     * @deprecated Use getAccessTokenScopeEntityList()
     * @return string[]
     */
    public function getAccessTokenScopes(): array
    {
        $scopes = [];
        foreach ($this->accessTokenScopes as $scopeList) {
            foreach ($scopeList as $scopeEntity) {
                $scopes[] = $scopeEntity->getIdentifier();
            }
        }
        return $scopes;
    }

    /**
     * Returns an array of ScopeEntity objects that represent the access token scopes for this request.
     * @return ScopeEntity[]
     */
    public function getAccessTokenScopeEntityList(): array
    {
        // returns the access token scopes as a list of ResourceScopeEntityList
        $scopes = [];
        foreach ($this->accessTokenScopes as $scopeList) {
            foreach ($scopeList as $scopeEntity) {
                $scopes[] = $scopeEntity;
            }
        }
        return $scopes;
    }

    /**
     * Checks if the request's access token has the given scope identifier.
     * @param string $scope
     * @deprecated use requestHasScopeEntity() instead which receives a ScopeEntity object
     * @return bool true if the request has the scope, false otherwise.
     */
    public function requestHasScope(string $scope): bool
    {
        // TODO: would prefer to move this into a Permission Decision Point code (PDP)
        $scopeEntity = ScopeEntity::createFromString($scope);
        return $this->requestHasScopeEntity($scopeEntity);
    }

    /**
     * Checks if the request's access token has the given scope contained within the access token scopes.
     * @param ScopeEntity $scopeEntity
     * @return bool
     */
    public function requestHasScopeEntity(ScopeEntity $scopeEntity): bool
    {
        $scopeKey = $scopeEntity->getScopeLookupKey();
        if (isset($this->accessTokenScopes[$scopeKey])) {
            return $this->accessTokenScopes[$scopeKey]->containsScope($scopeEntity);
        }
        return false;
    }

    public function getAllContainedScopesForScopeEntity(ScopeEntity $scopeEntity): array
    {
        // returns all scopes that are contained within the access token scopes for the given scope entity
        $scopeKey = $scopeEntity->getScopeLookupKey();
        if (isset($this->accessTokenScopes[$scopeKey])) {
            return $this->accessTokenScopes[$scopeKey]->getContainedScopes($scopeEntity);
        }
        return [];
    }

    /**
     * @param ResourceScopeEntityList[] $scopeValidationArray
     * @return void
     */
    public function setAccessTokenScopeValidationArray(array $scopeValidationArray): void
    {
        $this->accessTokenScopes = $scopeValidationArray;
        $this->buildResourceScopeContexts($scopeValidationArray);
    }

    /**
     * @param ResourceScopeEntityList[] $scopeValidationArray
     * @return void
     */
    private function buildResourceScopeContexts(array $scopeValidationArray): void
    {
        $this->resourceScopeContexts = [];
        $validContext = ['patient', 'user','system'];
        foreach ($scopeValidationArray as $resourceScopeList) {
            foreach ($resourceScopeList as $scopeEntity) {
                $scopeContext = "patient";
                $context = $scopeEntity->getContext();
                $resource = $scopeEntity->getResource();
                if (empty($context) || empty($resource)) {
                    continue; // nothing to do here
                    // skip over any launch parameters, fhiruser, etc.
                } else if (!in_array($context, $validContext)) {
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
    }

    /**
     * @param array $scopes
     * @deprecated use setAccessTokenScopeValidationArray() instead which receives a ResourceScopeEntityList[] that is built from the ScopeRepository->buildValidationArray
     */
    public function setAccessTokenScopes(array $scopes): void
    {
        // when we remove this function we can drop the dependency on the ScopeValidatorFactory
        $scopeValidatorFactory = new ScopeValidatorFactory();
        $this->accessTokenScopes = $scopeValidatorFactory->buildScopeValidatorArray($scopes);
        $this->buildResourceScopeContexts($this->accessTokenScopes);
    }

    /**
     * @return string
     */
    public function getRequestSite(): string
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
     * @return string|null
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
    public function getAccessTokenId(): string
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
     * @return string
     */
    public function getRequestUserRole(): string
    {
        return $this->requestUserRole;
    }

    /**
     * @param string $requestUserRole either 'patients' or 'users'
     */
    public function setRequestUserRole(string $requestUserRole): void
    {
        if (!in_array($requestUserRole, ['patient', 'users', 'system'])) {
            throw new InvalidArgumentException("invalid user role found");
        }
        $this->requestUserRole = $requestUserRole;
    }

    public function getRequestUserUUID(): ?string
    {
        return $this->requestUserUUID;
    }

    public function getRequestUserUUIDString(): ?string
    {
        return $this->requestUserUUIDString;
    }

    public function getPatientUUIDString(): ?string
    {
        // if the logged in account is a patient user this will match with requestUserUUID
        // If the logged in account is a practitioner user this will be the user selected as part of the launch/patient
        // EHR context session.
        // This is used in
        // patient/<resource>.* requests.  IE patient/Patient.read
        return $this->patientUUIDString;
    }

    public function setPatientUuidString(?string $value): void
    {
        $this->patientUUIDString = $value;
    }

    public function setPatientRequest($isPatientRequest): void
    {
        $this->patientRequest = $isPatientRequest;
    }

    /**
     * Returns the scope context (patient,user,system) that is used for the given resource as parsed from the request scopes
     * @param $resource string|null The resource to check (IE Patient, AllergyIntolerance, etc).
     * @return string|null The context or null if the resource does not exist in the scopes.
     */
    public function getScopeContextForResource(?string $resource): ?string
    {
        return $this->resourceScopeContexts[$resource] ?? null;
    }

    /**
     * @return string|null
     */
    public function getApiType(): ?string
    {
        return $this->apiType;
    }

    /**
     * @param string $apiType
     */
    public function setApiType(string $apiType): void
    {
        if (!in_array($apiType, ['fhir', 'oemr', 'port'])) {
            throw new InvalidArgumentException("invalid api type found");
        }
        $this->apiType = $apiType;
    }

    /**
     * @deprecated use getMethod() instead
     * @return string|null
     */
    public function getRequestMethod(): ?string
    {
        return $this->getMethod();
    }


    public function isPatientRequest(): bool
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

    public function isFhir(): bool
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
    public function isPatientWriteRequest(): bool
    {
        return $this->isFhir() && $this->isPatientRequest() && $this->getRequestMethod() != 'GET';
    }

    public function isFhirSearchRequest(): bool
    {
        if ($this->isFhir() && $this->getRequestMethod() == "POST") {
            return str_ends_with((string) $this->getRequestPath(), '_search') !== false;
        }
        return false;
    }

    public function setRequestPath(string $requestPath): void
    {
        // TODO: @adunsulag is there a better way to do this?
        $this->pathInfo = $requestPath;
        $this->server->set('PATH_INFO', $requestPath);
    }

    public function getRequestPathWithoutSite(): ?string
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


    public function withProtocolVersion($version): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->server->set("SERVER_PROTOCOL", 'HTTP/' . $version);
        return $clonedRequest;
    }

    public function getHeaderLine($name): string
    {
        return implode(",", $this->getHeader($name));
    }

    public function withHeader($name, $value): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->set($name, $value);
        return $clonedRequest;
    }

    public function withAddedHeader($name, $value): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->set($name, $value);
        return $clonedRequest;
    }

    public function withoutHeader($name): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->headers->remove($name);
        return $clonedRequest;
    }

    /**
     * @return false|resource|string|null
     */
    public function getBody(): mixed
    {
        return $this->getContent();
    }

    public function withBody(StreamInterface $body): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->content = $body;
        return $clonedRequest;
    }

    public function withMethod($method): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->method = $method;
        return $clonedRequest;
    }

    public function getUri(): string
    {
        return $this->requestUri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $clonedRequest = clone $this;
        if (!$preserveHost) {
            $clonedRequest->headers->set('HOST', $uri->getHost());
        }
        $clonedRequest->server->set('REQUEST_URI', $uri->getPath() . '?' . $uri->getQuery());
        $clonedRequest->prepareRequestUri();
        $clonedRequest->resource = null;
        return $clonedRequest;
    }

    public function getServerParams(): array
    {
        return $this->server->all();
    }

    public function getCookieParams(): array
    {
        return $this->cookies->all();
    }

    public function withCookieParams(array $cookies): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->cookies = new InputBag($cookies);
        return $clonedRequest;
    }

    public function withQueryParams(array $query): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->query = new InputBag($query);
        return $clonedRequest;
    }

    public function getUploadedFiles(): array
    {
        return $this->files->all();
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->files = new FileBag($uploadedFiles);
        return $clonedRequest;
    }

    public function getParsedBody(): array
    {
        return $this->getPayload()->all();
    }

    public function withParsedBody($data): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->request = new InputBag($data);
        return $clonedRequest;
    }

    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    public function getAttribute($name, $default = null): mixed
    {
        return $this->attributes->get($name, $default);
    }

    public function withAttribute($name, $value): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->attributes->set($name, $value);
        return $clonedRequest;
    }

    public function withoutAttribute($name): self
    {
        $clonedRequest = clone $this;
        $clonedRequest->attributes->remove($name);
        return $clonedRequest;
    }
}
