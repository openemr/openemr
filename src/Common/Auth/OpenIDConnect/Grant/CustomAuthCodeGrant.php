<?php

/**
 * CustomAuthCodeGrant.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use DateInterval;
use Exception;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use Psr\Http\Message\ServerRequestInterface;

class CustomAuthCodeGrant extends AuthCodeGrant
{
    use SystemLoggerAwareTrait;

    private array $openEMRCodeChallengeVerifiers;

    /**
     * @var JWTClientAuthenticationService
     */
    private JWTClientAuthenticationService $jwtAuthService;

    /**
     * @param AuthCodeRepositoryInterface $authCodeRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param DateInterval $authCodeTTL
     * @param string[] $expectedAudience The expected 'aud' query parameter to validate a JWT grant against
     */
    public function __construct(
        AuthCodeRepositoryInterface $authCodeRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        DateInterval $authCodeTTL,
        private array $expectedAudience
    ) {
        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);
        // the only code challenge methods we allow
        $this->openEMRCodeChallengeVerifiers = ['S256' => true];
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
     * @param ServerRequestInterface $request
     * @return AuthorizationRequest
     * @throws OAuthServerException
     */
    public function validateAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequest
    {
        $logger = $this->getSystemLogger();
        $logger->debug("CustomAuthCodeGrant::validateAuthorizationRequest start");

        // This function will force audience check if using launch scenario (ie. SMART).
        //  In non-launch scenario, it will only check audience if it has been provided in the request.
        $audience = $this->getQueryStringParameter(
            'aud',
            $request
        );
        $launch = $this->getQueryStringParameter(
            'launch',
            $request
        );

        // let's validate the aud param (if it exists)
        //  (note that this check is forced below if using launch scenario; so it is skipped here in the launch scenario)
        if (!empty($audience) && empty($launch)) {
            if (!in_array($audience, $this->expectedAudience)) {
                $this->getSystemLogger()->errorLogCaller("CustomAuthCodeGrant::validateAuthorizationRequest:Aud parameter did not match authorized server in non-launch scenario", ['audience' => $audience, 'expected' => $this->expectedAudience]);
                throw OAuthServerException::invalidRequest("aud", "Aud parameter did not match authorized server");
            }
        } else if (empty($audience) && empty($launch)) {
            $this->getSystemLogger()->debug("CustomAuthCodeGrant::validateAuthorizationRequest: Aud parameter not provided (and non-launch scenario), so not validating aud (audience)");
        }

        // let's validate the launch param
        if (!empty($launch)) {
            if (!in_array($audience, $this->expectedAudience)) {
                $this->getSystemLogger()->errorLogCaller("CustomAuthCodeGrant::validateAuthorizationRequest:Aud parameter did not match authorized server in launch scenario", ['audience' => $audience, 'expected' => $this->expectedAudience]);
                throw OAuthServerException::invalidRequest("aud", "Aud parameter did not match authorized server");
            }
            try {
                // check to see if we can deserialize the launch token
                SMARTLaunchToken::deserializeToken($launch);
            } catch (Exception $exception) {
                $this->getSystemLogger()->errorLogCaller("CustomAuthCodeGrant::validateAuthorizationRequest:Failed to deserialize launch token", ['launch' => $launch, 'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
                throw OAuthServerException::invalidRequest('launch', "launch parameter was incorrectly formatted or did not originate from this server");
            }
        }
        $this->validateCodeChallengeMethod($request);
        $logger->debug("CustomAuthCodeGrant::validateAuthorizationRequest: validateAuthorizationRequest exit");
        return parent::validateAuthorizationRequest($request);
    }

    protected function validateRedirectUri(
        string $redirectUri,
        ClientEntityInterface $client,
        ServerRequestInterface $request
    ): void {
        try {
            // make sure we log the error so we have more details on what is going on here
            parent::validateRedirectUri($redirectUri, $client, $request);
        } catch (OAuthServerException $exception) {
            $this->getSystemLogger()->errorLogCaller(
                "Invalid client detected.  Failed to validate redirect uri",
                ['redirectUri' => $redirectUri, 'client' => $client->getIdentifier(), 'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
            );
            throw $exception;
        }
    }

    /**
     * Override to support JWT client assertions, otherwise fall back to traditional client secret authentication.
     * @param ServerRequestInterface $request
     * @return array
     * @throws OAuthServerException
     */
    protected function getClientCredentials(ServerRequestInterface $request)
    {
        $logger = $this->getSystemLogger();
        // Check if JWT authentication service is available and request has JWT assertion
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            $logger->debug('CustomAuthCodeGrant::getClientCredentials: Detected JWT client assertion, using asymmetric authentication');

            try {
                // Extract client ID from JWT
                $clientId = $this->jwtAuthService->extractClientIdFromJWT($request);
                $logger->debug("CustomAuthCodeGrant::getClientCredentials: Extracted client ID from JWT", ['client_id' => $clientId]);
            } catch (OAuthServerException $e) {
                $logger->error(
                    'CustomAuthCodeGrant::getClientCredentials: Failed to extract client ID from JWT',
                    ['error' => $e->getMessage(), 'hint' => $e->getHint()]
                );
                throw $e;
            }
            return [$clientId, null]; // No client secret for JWT authentication
        } else {
            // Fall back to traditional client secret authentication
            $logger->debug('CustomAuthCodeGrant::getClientCredentials: Using traditional client secret authentication');
            return parent::getClientCredentials($request);
        }
    }

    protected function validateClient(ServerRequestInterface $request): ClientEntityInterface
    {
        $logger = $this->getSystemLogger();
        $logger->debug("CustomAuthCodeGrant::validateClient start");

        // Check if JWT authentication service is available and request has JWT assertion
        if (isset($this->jwtAuthService) && $this->jwtAuthService->hasJWTClientAssertion($request)) {
            $logger->debug('CustomAuthCodeGrant::validateClient: Detected JWT client assertion, using asymmetric authentication');

            try {
                // Extract client ID from JWT
                $clientId = $this->jwtAuthService->extractClientIdFromJWT($request);

                if (empty($clientId)) {
                    throw OAuthServerException::invalidClient($request);
                }

                // Get the client entity from repository
                $client = $this->clientRepository->getClientEntity($clientId);

                if (!($client instanceof ClientEntity)) {
                    $logger->error('CustomAuthCodeGrant::validateClient: Client not found or invalid type', ['client_id' => $clientId]);
                    throw OAuthServerException::invalidClient($request);
                }

                // Validate the JWT assertion
                $this->jwtAuthService->validateJWTClientAssertion($request, $client);

                // Validate client is authorized for this grant type
                if (!$this->clientRepository->validateClient($clientId, null, $this->getIdentifier())) {
                    $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
                    throw OAuthServerException::invalidClient($request);
                }

                // Validate redirect URI if provided
                $redirectUri = $this->getRequestParameter('redirect_uri', $request);
                if ($redirectUri !== null) {
                    $this->validateRedirectUri($redirectUri, $client, $request);
                }

                $logger->debug('CustomAuthCodeGrant::validateClient: JWT authentication successful', ['client_id' => $clientId]);
                return $client;

            } catch (OAuthServerException $e) {
                $logger->error(
                    'CustomAuthCodeGrant::validateClient: JWT authentication failed',
                    ['error' => $e->getMessage(), 'hint' => $e->getHint()]
                );
                throw $e;
            }
        } else {
            // Fall back to traditional client secret authentication
            $logger->debug('CustomAuthCodeGrant::validateClient: Using traditional client secret authentication');
            $client = parent::validateClient($request);
            if (!($client instanceof ClientEntity)) {
                $logger->errorLogCaller("CustomAuthCodeGrant::validateClient client returned was not a valid ClientEntity ", ['client' => $client->getIdentifier()]);
                throw OAuthServerException::invalidClient($request);
            }
        }

        if (!$client->isEnabled()) {
            $this->getSystemLogger()->errorLogCaller("client returned was not enabled", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }
        $this->getSystemLogger()->debug("CustomAuthCodeGrant::validateClient exit");
        return $client;
    }


    /**
     * SMART ON FHIR spec FORBIDS server support of 'plain' code_challenge_method.
     * @see https://build.fhir.org/ig/HL7/smart-app-launch/app-launch.html#considerations-for-pkce-support
     * Last Accessed on August 27th 11:09 AM
     * because of that we nearly duplicate the code_challenge_method check in the parent class and only support
     * the code_method_challenges supported by SMART ON FHIR.
     * @param $request
     * @throws OAuthServerException
     */
    private function validateCodeChallengeMethod($request): void
    {

        $codeChallenge = $this->getQueryStringParameter('code_challenge', $request);
        if ($codeChallenge !== null) {
            $codeChallengeMethod = $this->getQueryStringParameter('code_challenge_method', $request, 'plain');

            if (array_key_exists($codeChallengeMethod, $this->openEMRCodeChallengeVerifiers) === false) {
                throw OAuthServerException::invalidRequest(
                    'code_challenge_method',
                    'Code challenge method must be one of ' . implode(', ', array_map(
                        fn($method): string => '`' . $method . '`',
                        array_keys($this->openEMRCodeChallengeVerifiers)
                    ))
                );
            }
        }
    }
}
