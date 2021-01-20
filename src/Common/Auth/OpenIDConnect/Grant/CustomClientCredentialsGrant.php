<?php
/**
 * CustomClientCredentialsGrant.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Custom Password Grant
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Signer\Ecdsa\Sha384;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\RequestEvent;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Logging\SystemLogger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class CustomClientCredentialsGrant extends ClientCredentialsGrant
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The http client that retrieves JWK URIs
     * @var ClientInterface
     */
    private $httpClient;

    const OAUTH_JWT_CLIENT_ASSERTION_TYPE = 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer';

    public function __construct()
    {
        $this->logger = new SystemLogger(); // default if we don't have one.
        $this->jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha384(),
            InMemory::plainText('')
        );
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

    public function setHttpClient(ClientInterface $client) {
        $this->httpClient = $client;
    }

    public function getHttpClient() : ClientInterface {
        return $this->httpClient;
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
        if ($assertionType === 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer')
        {
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
                if ($claims->has('sub')) {
                    $this->logger->debug("CustomClientCredentialsGrant->getClientCredentials() jwt token parsed.  Client id is ", [$claims->get('sub')]);
                    return [$claims->get('sub')];
                }
            } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
                $this->logger->error("CustomClientCredentialsGrant->getClientCredentials() failed to parse token",
                    ['exceptionMessage' => $exception->getMessage()]);
                throw OAuthServerException::invalidClient($request);
            }
        }
        return null;
    }

    /**
     * Validate the client.
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
        list($clientId) = $this->getClientCredentials($request);


        // grab the client
        $client = $this->getClientEntityOrFail($clientId, $request);

        // TODO: @adunsulag check with @bradymiller or @sjpadgett on whether this belongs in ClientRepository since we
        // are type checking it against ClientEntity.  Currently all the JWK validation stuff is centralized in this
        // grant... but knowledge of the client entity is inside the ClientRepository, either way I don't like the
        // class cohesion problems this creates.
        if (!($client instanceof ClientEntity) )
        {
            $this->logger->error("CustomClientCredentialsGrant->validateClient() client returned was not a valid ClientEntity ", ['client' => $clientId]);
            throw OAuthServerException::invalidClient($request);
        }

        // validate everything to do with the JWT...

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

        try {
            // get our JWK (either from the client's remote URL, or from the stored JWKS in the db).
            $jwk = $this->getJWKForRequest($client, $request);

            $configuration = $this->jwtConfiguration;
            $configuration->setValidationConstraints(
                new ValidAt(new SystemClock(new DateTimeZone(\date_default_timezone_get()))),
                new SignedWith(new Sha384(), InMemory::plainText($jwk))
            );

            // Attempt to parse and validate the JWT
            $jwt = $this->getJWTFromRequest($request);
            $token = $configuration->parser()->parse($jwt);

            $constraints = $configuration->validationConstraints();

            try {
                $configuration->validator()->assert($token, ...$constraints);
            } catch (RequiredConstraintsViolated $exception) {
                $this->logger->error("CustomClientCredentialsGrant->validateClient() jwt failed required constraints",
                    ['client' => $clientId, 'exceptionMessage' => $exception->getMessage()]);
                throw OAuthServerException::invalidClient($request);
            }
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $exception) {
            $this->logger->error("CustomClientCredentialsGrant->validateClient() failed to parse token",
                ['client' => $clientId, 'exceptionMessage' => $exception->getMessage()]);
            throw OAuthServerException::invalidClient($request);
        }
        catch (\InvalidArgumentException $exception) {
            $this->logger->error("CustomClientCredentialsGrant->validateClient() failed to retrieve jwk for client",
                ['client' => $clientId, 'exceptionMessage' => $exception->getMessage()]);
            throw OAuthServerException::invalidClient($request);
        }

        if ($this->clientRepository->validateClient($clientId, null, $this->getIdentifier()) === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));

            throw OAuthServerException::invalidClient($request);
        }

        // If a redirect URI is provided ensure it matches what is pre-registered
        $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);

        if ($redirectUri !== null) {
            $this->validateRedirectUri($redirectUri, $client, $request);
        }

        return $client;
    }

    private function getJWKForRequest(ClientEntity $client, ServerRequestInterface $request) {
        $jwk_uri = $client->getJwksUri();
        // TODO: @adunsulag we need to verify if we go directly from a JWK Set to a JSON Web Key
        if (!empty($jwk_uri)) {
            $jwk = $this->getJWKFromUri($jwk_uri);
        } else {
            $jwk = $client->getJwks();
        }
        if (empty($jwk)) {
            throw new \InvalidArgumentException("Invalid JWK for client");
        }
    }

    private function getJWKFromUri(ClientEntity $entity, $jwk_uri) {
        try {
            $request = new \GuzzleHttp\Psr7\Request('GET', $jwk_uri);
            $body = $this->httpClient->sendRequest($request)->getBody();
            $json = $body->getContents();
            return json_decode($json);
        }
        catch (RequestException $exception) {
            $this->logger->error("CustomClientCredentialsGrant->validateClient() failed to parse jwk from jwk_uri for client",
                ['client' => $entity->getIdentifier(), 'uri' => $jwk_uri, 'exceptionMessage' => $exception->getMessage()]);
        }
        return null;
    }

    private function getJWTFromRequest(ServerRequestInterface $request) {
        return $this->getRequestParameter('client_assertion', $request, null);
    }
}
