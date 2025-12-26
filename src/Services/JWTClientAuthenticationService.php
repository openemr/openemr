<?php

/**
 * JWTClientAuthenticationService handles JWT-based client authentication for OAuth2 flows
 * Implements RFC 7523 and SMART Backend Services specifications
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Public Domain for portions marked as AI Generated which were created with the assistance of Claude.AI and Microsoft Copilot
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use DateInterval;
use GuzzleHttp\Client;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeySet;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JWKValidatorException;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\RsaSha384Signer;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\Validation\UniqueID;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
// AI Generated - Start
class JWTClientAuthenticationService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The required value for the jwt assertion type
     */
    const OAUTH_JWT_CLIENT_ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

    /**
     * Maximum allowed JWT expiration time (24 hours as per SMART spec)
     */
    const MAX_JWT_EXPIRATION_HOURS = 24;

    /**
     * Maximum allowed clock drift (1 minute)
     */
    const MAX_CLOCK_DRIFT_MINUTES = 1;

    /**
     * JWTClientAuthenticationService constructor
     *
     * @param string $authTokenUrl The OAuth2 token endpoint URL to be used as audience
     * @param ClientRepository $clientRepository Repository for client operations
     * @param JWTRepository $jwtRepository Repository for JWT tracking (replay prevention)
     * @param ClientInterface|null $httpClient HTTP client for fetching JWKS
     */
    public function __construct(
        private readonly string $authTokenUrl,
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly JWTRepository $jwtRepository,
        /**
         * The http client that retrieves JWK URIs
         */
        private ?ClientInterface $httpClient = null
    ) {
        $this->logger = new SystemLogger();
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
        if (!isset($this->httpClient)) {
            $this->httpClient = new Client();
        }
        return $this->httpClient;
    }


    /**
     * Set logger for debugging
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Check if request contains JWT client assertion
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function hasJWTClientAssertion(ServerRequestInterface $request): bool
    {
        $params = (array) $request->getParsedBody();

        return !empty($params['client_assertion']) &&
            !empty($params['client_assertion_type']) &&
            $params['client_assertion_type'] === self::OAUTH_JWT_CLIENT_ASSERTION_TYPE;
    }

    /**
     * Extract client ID from JWT assertion without full validation
     * Used for initial client lookup
     *
     * @param ServerRequestInterface $request
     * @return string|null Client ID or null if extraction fails
     * @throws OAuthServerException
     */
    public function extractClientIdFromJWT(ServerRequestInterface $request): ?string
    {
        $params = (array) $request->getParsedBody();
        $assertionType = $params['client_assertion_type'] ?? '';
        $jwt = $params['client_assertion'] ?? '';

        if ($assertionType !== self::OAUTH_JWT_CLIENT_ASSERTION_TYPE) {
            throw OAuthServerException::invalidRequest(
                'client_assertion_type',
                'Assertion type must be ' . self::OAUTH_JWT_CLIENT_ASSERTION_TYPE
            );
        }

        if (empty($jwt)) {
            throw OAuthServerException::invalidRequest('client_assertion', 'Client assertion is required');
        }

        try {
            $configuration = Configuration::forUnsecuredSigner();
            $token = $configuration->parser()->parse($jwt);

            if (!$token instanceof Plain) {
                throw OAuthServerException::invalidClient($request);
            }

            $claims = $token->claims();

            // Both 'sub' and 'iss' should contain the client_id per RFC 7523
            $clientId = $claims->get('sub');

            if (empty($clientId)) {
                $this->logger->error('JWT assertion missing required sub claim');
                throw OAuthServerException::invalidClient($request);
            }

            $this->logger->debug('Extracted client ID from JWT', ['client_id' => $clientId]);
            return $clientId;

        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
            $this->logger->error(
                'Failed to parse JWT assertion',
                ['exception' => $exception->getMessage()]
            );
            throw OAuthServerException::invalidClient($request);
        }
    }
    // AI Generated - End

    /**
     * Validate JWT client assertion according to RFC 7523 and SMART specifications
     *
     * @param ServerRequestInterface $request
     * @param ClientEntity $client The client entity to validate against
     * @return Token The validated token
     * @throws OAuthServerException If validation fails
     */
    public function validateJWTClientAssertion(
        ServerRequestInterface $request,
        ClientEntity $client
    ): Token {
        $clientId = $client->getIdentifier();


        // @see https://tools.ietf.org/html/rfc7523#section-3

        // 1. iss claim required && must match iss URI of sender application
        // 2.B sub claim required && must match client id
        // 3. aud claim required && must match this oauth2 server.  Token endpoint may be used.  This value needs to
        // be communicated out of band to registering applications (put in SMART App registration page
        // 4. exp claim required && must be > (current time - skew time[60seconds]).
        //      OPTIONAL choice reject extended period exp claim.  OpenEMR choice set to 24 hours
        // 5. nbf claim if sent must be < (current time) or REJECT
        // 6. iat claim if sent may be rejected.  OpenEMR choice set to 5 minutes
        // 7. jti claim represents id of the web token, may be stored and checked against to prevent replay attacks
        // 8. has other claims
        // 9. MAC signature verification or REJECT
        // MAC signature needs to use ES384 or RS384 signature verification @see https://tools.ietf.org/html/rfc7518
        //
        // 10. Reject any other invalid JWT per RFC 7519

        // @see https://tools.ietf.org/html/rfc7523#section-3.2
        // IF ERROR set "error" parameter to "invalid_client" use "error_description" or "error_uri" to provide error
        // information

        // Check if client is enabled
        if (!$client->isEnabled()) {
            $this->logger->error('Client is not enabled', ['client_id' => $clientId]);
            throw OAuthServerException::invalidClient($request);
        }

        // Check if client has JWKS configured
        if (empty($client->getJwksUri()) && empty($client->getJwks())) {
            $this->logger->error('Client has no JWKS or JWKS URI configured', ['client_id' => $clientId]);
            throw OAuthServerException::invalidClient($request);
        }

        $params = (array) $request->getParsedBody();
        $jwt = $params['client_assertion'] ?? '';

        try {
            // Get the JSON Web Key Set for signature validation
            $jsonWebKeySet = new JsonWebKeySet(
                $this->getHttpClient(),
                $client->getJwksUri(),
                $client->getJwks()
            );

            // Configure JWT validation per RFC 7523 Section 3
            $configuration = Configuration::forUnsecuredSigner();

            // Set up validation constraints
            $configuration->setValidationConstraints(
            // 1. Clock validation with 1 minute drift tolerance
                new LooseValidAt(
                    new SystemClock(new \DateTimeZone(\date_default_timezone_get())),
                    new DateInterval('PT' . self::MAX_CLOCK_DRIFT_MINUTES . 'M')
                ),
                // 2. Signature validation using RS384
                new SignedWith(new RsaSha384Signer(), $jsonWebKeySet),
                // 3. Issuer must be the client_id
                new IssuedBy($clientId),
                // 4. Audience must be the token endpoint
                new PermittedFor($this->authTokenUrl),
                // 5. JTI uniqueness check for replay prevention
                new UniqueID($this->jwtRepository)
            );

            // Parse the JWT
            $token = $configuration->parser()->parse($jwt);

            $this->logger->debug(
                'Parsed JWT token',
                [
                    'claims' => $token->claims()->all(),
                    'headers' => $token->headers()->all()
                ]
            );

            // AI Generated - Start
            // Additional validations per SMART Backend Services
            $this->performAdditionalValidations($token, $clientId);

            // Validate all constraints
            $constraints = $configuration->validationConstraints();

            try {
                // Note: @ suppresses phpseclib RSA validation notice that gets printed to screen
                @$configuration->validator()->assert($token, ...$constraints);
            } catch (RequiredConstraintsViolated $exception) {
                $this->logger->error(
                    'JWT failed validation constraints',
                    [
                        'client_id' => $clientId,
                        'exception' => $exception->getMessage(),
                        'expected_audience' => $this->authTokenUrl,
                        'claims' => $token->claims()->all()
                    ]
                );

                // Per ONC Inferno requirements, use 400 status instead of 401
                $oauthException = new OAuthServerException(
                    'Client authentication failed',
                    4,
                    'invalid_client',
                    400
                );
                $oauthException->setServerRequest($request);
                throw $oauthException;
            }
            // AI Generated - End

            // Save JTI to prevent replay attacks
            $this->saveJwtHistory($token, $clientId);

            $this->logger->debug('JWT client assertion validated successfully', ['client_id' => $clientId]);

            return $token;

        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
            $this->logger->error(
                'Failed to parse JWT token',
                ['client_id' => $clientId, 'exception' => $exception->getMessage()]
            );
            throw OAuthServerException::invalidClient($request);
        } catch (JWKValidatorException | \InvalidArgumentException $exception) {
            $this->logger->error(
                'Failed to retrieve or validate JWK for client',
                ['client_id' => $clientId, 'exception' => $exception->getMessage()]
            );
            throw OAuthServerException::invalidClient($request);
        }
    }

    /**
     * Perform additional SMART-specific validations
     *
     * @param Token $token
     * @param string $clientId
     * @throws OAuthServerException
     */
    private function performAdditionalValidations(Token $token, string $clientId): void
    {
        $claims = $token->claims();

        // Verify 'sub' claim matches client_id (RFC 7523 requirement)
        $sub = $claims->get('sub');
        if ($sub !== $clientId) {
            $this->logger->error(
                'JWT sub claim does not match client_id',
                ['sub' => $sub, 'client_id' => $clientId]
            );
            throw OAuthServerException::invalidRequest('client_assertion', 'Invalid subject claim');
        }

        // Verify 'iss' claim matches client_id (SMART requirement)
        $iss = $claims->get('iss');
        if ($iss !== $clientId) {
            $this->logger->error(
                'JWT iss claim does not match client_id',
                ['iss' => $iss, 'client_id' => $clientId]
            );
            throw OAuthServerException::invalidRequest('client_assertion', 'Invalid issuer claim');
        }

        // Check expiration is not too far in future (24 hours max per SMART spec)
        $exp = $claims->get('exp');
        if ($exp instanceof \DateTimeInterface) {
            $maxExp = (new \DateTimeImmutable())->add(
                new DateInterval('PT' . self::MAX_JWT_EXPIRATION_HOURS . 'H')
            );
            if ($exp > $maxExp) {
                $this->logger->error(
                    'JWT expiration exceeds maximum allowed time',
                    ['exp' => $exp->format('c'), 'max_exp' => $maxExp->format('c')]
                );
                throw OAuthServerException::invalidRequest(
                    'client_assertion',
                    'Token expiration exceeds maximum allowed time'
                );
            }
        }

        // Check 'iat' (issued at) is not too old (5 minutes)
        $iat = $claims->get('iat');
        if ($iat instanceof \DateTimeInterface) {
            $minIat = (new \DateTimeImmutable())->sub(new DateInterval('PT5M'));
            if ($iat < $minIat) {
                $this->logger->error(
                    'JWT issued too far in the past',
                    ['iat' => $iat->format('c'), 'min_iat' => $minIat->format('c')]
                );
                throw OAuthServerException::invalidRequest(
                    'client_assertion',
                    'Token issued too far in the past'
                );
            }
        }

        // JTI (JWT ID) is required for replay prevention
        if (!$claims->has('jti')) {
            $this->logger->error('JWT missing required jti claim');
            throw OAuthServerException::invalidRequest('client_assertion', 'Missing jti claim');
        }
    }

    /**
     * Save JWT information for replay prevention
     *
     * @param Token $token
     * @param string $clientId
     * @throws OAuthServerException
     */
    private function saveJwtHistory(Token $token, string $clientId): void
    {
        try {
            $exp = $token->claims()->get('exp');
            if ($exp instanceof \DateTimeInterface) {
                $exp = $exp->getTimestamp();
            }

            $jti = $token->claims()->get('jti');

            $this->jwtRepository->saveJwtHistory($jti, $clientId, $exp);

            $this->logger->debug(
                'Saved JWT history for replay prevention',
                ['client_id' => $clientId, 'jti' => $jti, 'exp' => $exp]
            );

        } catch (SqlQueryException $exception) {
            $this->logger->error(
                'Failed to save JWT history',
                ['client_id' => $clientId, 'exception' => $exception->getMessage()]
            );
            throw OAuthServerException::serverError('Server error occurred while processing JWT');
        }
    }

    /**
     * Validate client using either JWT assertion or traditional client secret
     * This is a convenience method for endpoints that support both authentication methods
     *
     * @param ServerRequestInterface $request
     * @return ClientEntity The validated client
     * @throws OAuthServerException
     */
    public function validateClient(ServerRequestInterface $request): ClientEntity
    {
        // Check if JWT assertion is present
        if ($this->hasJWTClientAssertion($request)) {
            // Extract client ID from JWT
            $clientId = $this->extractClientIdFromJWT($request);

            // Get the client entity
            $client = $this->clientRepository->getClientEntity($clientId);

            if (!($client instanceof ClientEntity)) {
                $this->logger->error('Client not found or invalid type', ['client_id' => $clientId]);
                throw OAuthServerException::invalidClient($request);
            }

            // Validate the JWT assertion
            $this->validateJWTClientAssertion($request, $client);

            return $client;
        }

        // Fall back to traditional client secret validation
        // This would need to be implemented based on your existing logic
        throw OAuthServerException::invalidRequest(
            'client_assertion',
            'Client authentication required'
        );
    }
}
// AI Generated - End
