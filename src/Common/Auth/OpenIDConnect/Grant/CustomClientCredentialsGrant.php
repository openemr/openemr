<?php

/**
 * CustomClientCredentialsGrant implements the requirements for ONC SMART Bulk FHIR Client Credentials grant.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use DateInterval;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\Token\Plain;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\RequestEvent;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomClientCredentialsGrant extends ClientCredentialsGrant
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * The http client that retrieves JWK URIs
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var TrustedUserService
     */
    private readonly TrustedUserService $trustedUserService;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var JWTClientAuthenticationService
     */
    private JWTClientAuthenticationService $jwtAuthService;

    /**
     * The required value for the jwt assertion type
     */
    const OAUTH_JWT_CLIENT_ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';
    /**
     * CustomClientCredentialsGrant constructor.
     * @param SessionInterface $session
     * @param string $authTokenUrl The OAUTH2 token issuing url to be used as the audience parameter for JWT validation
     */
    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $authTokenUrl
    ) {
        $this->logger = new SystemLogger();
        $this->trustedUserService = new TrustedUserService();
        $this->userService = new UserService();
    }

    /**
     * Set the JWT authentication service
     *
     * @param JWTClientAuthenticationService $jwtAuthService
     */
    public function setJWTAuthenticationService(JWTClientAuthenticationService $jwtAuthService): void
    {
        $this->jwtAuthService = $jwtAuthService;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Allows the http client that retrieves jwks to be overriden.  Useful for unit testing
     * @param ClientInterface $client
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
    }

    /**
     * Allows the http client that retrieves jwks to be overriden.  Useful for unit testing
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * @return UserService
     */
    public function getUserService(): UserService
    {
        return $this->userService;
    }

    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @param JWTRepository $jwtRepository
     */
    public function setJwtRepository(JWTRepository $jwtRepository): void
    {
//        $this->jwtRepository = $jwtRepository;
    }

    /**
     * We issue an access token, but we force the user account to be our OpenEMR API system user.  We also save off the
     * grant as a TrustedUser which we can use later for revocation if necessary.
     * @param DateInterval $accessTokenTTL
     * @param ClientEntityInterface $client
     * @param string|null $userIdentifier
     * @param array $scopes
     * @return \League\OAuth2\Server\Entities\AccessTokenEntityInterface
     * @throws OAuthServerException If there is a server error, or some other oauth2 violation
     * @throws \League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException
     */
    protected function issueAccessToken(DateInterval $accessTokenTTL, ClientEntityInterface $client, $userIdentifier, array $scopes = [])
    {
        // let's grab our user id here.
        if ($userIdentifier === null) {
            // we want to grab our system user
            $systemUser = $this->userService->getSystemUser();
            if (empty($systemUser['uuid'])) {
                $this->logger->error("SystemUser was missing.  System is not setup properly");
                throw OAuthServerException::serverError("Server was not properly setup");
            }
            $userIdentifier = $systemUser['uuid'] ?? null;
        }
        $accessToken = parent::issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes); // TODO: Change the autogenerated stub

        // gotta remove since binary and will break json_encode (not used for password granttype, so ok to remove)
        $this->session->remove('csrf_private_key');

        $session_cache = json_encode($this->session->all(), JSON_THROW_ON_ERROR);
        $code = null; // code is used only in authorization_code grant types

        $scopeList = [];

        foreach ($scopes as $scope) {
            if ($scope instanceof ScopeEntityInterface) {
                $scopeList[] = $scope->getIdentifier();
            }
        }

        // we can't get past the api dispatcher without having a trusted user.
        $this->trustedUserService->saveTrustedUser(
            $client->getIdentifier(),
            $userIdentifier,
            implode(" ", $scopeList),
            0,
            $code,
            $session_cache,
            $this->getIdentifier()
        );

        return $accessToken;
    }

    /**
     * Gets the client credentials from the request from the request body or
     * the Http Basic Authorization header
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    protected function getClientCredentials(ServerRequestInterface $request)
    {
        $this->logger->debug("CustomClientCredentialsGrant->getClientCredentials() inside request");
        // @see https://tools.ietf.org/html/rfc7523#section-2.2
        $assertionType = $this->getRequestParameter('client_assertion_type', $request, null);
        if ($assertionType === 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer') {
            $this->logger->debug("CustomClientCredentialsGrant->getClientCredentials() client_assertion_type of jwt-bearer.  Attempting to retrieve client id");
            $jwtToken = $this->getJWTFromRequest($request);
            // see if we can grab the client from here.

            try {
                // we skip validation so that we can grab our client id, from there we can hit the database to find our
                // JWK to validate against.
                $configuration = Configuration::forUnsecuredSigner(
                // You may also override the JOSE encoder/decoder if needed by providing extra arguments here
                );

                $token = $configuration->parser()->parse($jwtToken);

                assert($token instanceof Plain);
                $claims = $token->claims(); // Retrieves the token claims
                if ($claims->has('sub')) { // no subject means invalid client...
                    $this->logger->debug("CustomClientCredentialsGrant->getClientCredentials() jwt token parsed.  Client id is ", [$claims->get('sub')]);
                    return [$claims->get('sub')];
                }
            } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
                $this->logger->error(
                    "CustomClientCredentialsGrant->getClientCredentials() failed to parse token",
                    ['exceptionMessage' => $exception->getMessage()]
                );
                throw OAuthServerException::invalidClient($request);
            }
        } else {
            throw OAuthServerException::invalidRequest("client_assertion_type", "assertion type is not supported");
        }
        return null;
    }

    /**
     * Validate the client against the client's jwks
     * @see https://tools.ietf.org/html/rfc7523#section-3
     *
     * @param ServerRequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return ClientEntityInterface
     */
    protected function validateClient(ServerRequestInterface $request)
    {
        // skip everything else for now.
        [$clientId] = $this->getClientCredentials($request);


        // grab the client
        $client = $this->getClientEntityOrFail($clientId, $request);

        // Currently all the JWK validation stuff is centralized in this
        // grant... but knowledge of the client entity is inside the ClientRepository, either way I don't like the
        // class cohesion problems this creates.
        if (!($client instanceof ClientEntity)) {
            $this->logger->error("CustomClientCredentialsGrant->validateClient() client returned was not a valid ClientEntity ", ['client' => $clientId]);
            throw OAuthServerException::invalidClient($request);
        }
        // validate everything to do with the JWT...
        // Check if JWT authentication service is available and request has JWT assertion
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            // Validate JWT assertion
            try {
                $this->jwtAuthService->validateJWTClientAssertion($request, $client);
                $this->logger->debug("CustomClientCredentialsGrant->validateClient() JWT assertion validated successfully");
            } catch (OAuthServerException $e) {
                $this->logger->error(
                    "CustomClientCredentialsGrant->validateClient() JWT validation failed",
                    ['error' => $e->getMessage(), 'hint' => $e->getHint()]
                );
                throw $e;
            }
        } else {
            // we only support JWT client assertions for this grant type
            throw OAuthServerException::invalidClient($request);
        }
//
//        $jwtRepository = $this->getJwtRepository();
//        $token = null;
//        try {
//            // http client required for fetching jwks from the jwks uri and makes unit testing easier
//            $jsonWebKeySet = new JsonWebKeySet($this->getHttpClient(), $client->getJwksUri(), $client->getJwks());
//
//
//            $configuration = Configuration::forUnsecuredSigner();
//            $configuration->setValidationConstraints(
//                // we only allow 1 minute drift (note the 'T' specifier here, super important as we want to do time
//                // we had a bug here where P1M was a 1 month drift which is BAD.
//                new ValidAt(new SystemClock(new \DateTimeZone(\date_default_timezone_get())), new \DateInterval('PT1M')),
//                new SignedWith(new RsaSha384Signer(), $jsonWebKeySet),
//                new IssuedBy($client->getIdentifier()),
//                new PermittedFor($this->authTokenUrl), // allowed audience
//                new UniqueID($jwtRepository)
//            );
//
//            // Attempt to parse and validate the JWT
//            $jwt = $this->getJWTFromRequest($request);
//            // issuer = issue URI of sender application so redirectUri
//            // subject claim
//            $token = $configuration->parser()->parse($jwt);
//            $this->logger->debug(
//                "Token parsed",
//                ['claims' => $token->claims()->all(), 'headers' => $token->headers()->all(), 'signature' => $token->signature()->toString()]
//            );
//
//            $constraints = $configuration->validationConstraints();
//
//            try {
//                // phpseclib's RSA validation triggers a NOTICE that gets printed to the screen which messes up the JSON result returned
//                // TODO: if phpseclib fixes this error remove the @ ignore sign, note this does not disable the exceptions.
//                @$configuration->validator()->assert($token, ...$constraints);
//            } catch (RequiredConstraintsViolated $exception) {
//                $this->logger->error(
//                    "CustomClientCredentialsGrant->validateClient() jwt failed required constraints",
//                    [
//                        'client' => $clientId, 'exceptionMessage' => $exception->getMessage()
//                        , 'claims' => $token->claims()->all()
//                        ,'expectedAudience' => $this->authTokenUrl
//                    ]
//                );
//                // ONC Inferno server refuses to allow a 401 HTTP status code to pass their test suite and requires
//                // a 400 HTTP status code, despite the SMART spec specifically stating that invalid_client w/ 401 is
//                // the response https://hl7.org/fhir/uv/bulkdata/authorization/index.html#signature-verification
//                // so we force this to be a 400 exception
//                // TODO: @adunsulag is there an update to inferno that fixes this issue? (as of inferno 1.9.0 there is no update).
//                $exception = new OAuthServerException('Client authentication failed', 4, 'invalid_client', 400);
//                $exception->setServerRequest($request);
//                throw $exception;
//            }
//        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
//            $this->logger->error(
//                "CustomClientCredentialsGrant->validateClient() failed to parse token",
//                ['client' => $clientId, 'exceptionMessage' => $exception->getMessage()]
//            );
//            throw OAuthServerException::invalidClient($request);
//        } catch (JWKValidatorException | \InvalidArgumentException $exception) {
//            $this->logger->error(
//                "CustomClientCredentialsGrant->validateClient() failed to retrieve jwk for client",
//                ['client' => $clientId, 'exceptionMessage' => $exception->getMessage()]
//            );
//            throw OAuthServerException::invalidClient($request);
//        }

        if ($this->clientRepository->validateClient($clientId, null, $this->getIdentifier()) === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidClient($request);
        }

        // If a redirect URI is provided ensure it matches what is pre-registered
        $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);

        if ($redirectUri !== null) {
            $this->validateRedirectUri($redirectUri, $client, $request);
        }

        // if everything is valid we are going to save off the jti so we can prevent replay attacks
//        $this->saveJwtHistory($jwtRepository, $clientId, $token);

        return $client;
    }

    /**
     * Retrieves the JWT assertion object.
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getJWTFromRequest(ServerRequestInterface $request)
    {
        return $this->getRequestParameter('client_assertion', $request, null);
    }

    private function saveJwtHistory(JWTRepository $jwtRepository, $clientId, Token $token)
    {
        $exp = null;
        $jti = null;
        try {
            $exp = $token->claims()->get("exp", null);
            if ($exp instanceof \DateTimeInterface) {
                $exp = $exp->getTimestamp();
            }
            $jti = $token->claims()->get("jti");
            $jwtRepository->saveJwtHistory($jti, $clientId, $exp);
        } catch (SqlQueryException $exception) {
            (new SystemLogger())->error(
                "Failed to save jti to database Exception: " . $exception->getMessage(),
                ['clientId' => $clientId, 'exp' => $exp, 'jti' => $jti]
            );
            throw OAuthServerException::serverError("Server error occurred in parsing JWT", $exception);
        }
    }
}
