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
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use Psr\Http\Message\ServerRequestInterface;

class CustomAuthCodeGrant extends AuthCodeGrant
{
    use SystemLoggerAwareTrait;

    /**
     * @var string[] The expected 'aud' query parameter to validate a JWT grant against
     */
    private array $expectedAudience;

    private array $openEMRCodeChallengeVerifiers;

    public function __construct(AuthCodeRepositoryInterface $authCodeRepository, RefreshTokenRepositoryInterface $refreshTokenRepository, DateInterval $authCodeTTL, $expectedAudience)
    {
        parent::__construct($authCodeRepository, $refreshTokenRepository, $authCodeTTL);
        $this->expectedAudience = $expectedAudience;
        // the only code challenge methods we allow
        $this->openEMRCodeChallengeVerifiers = ['S256' => true];
    }

    /**
     * @param ServerRequestInterface $request
     * @return AuthorizationRequest
     * @throws OAuthServerException
     */
    public function validateAuthorizationRequest(ServerRequestInterface $request) : AuthorizationRequest
    {
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
                $this->getSystemLogger()->errorLogCaller("Aud parameter did not match authorized server in non-launch scenario", ['audience' => $audience, 'expected' => $this->expectedAudience]);
                throw OAuthServerException::invalidRequest("aud", "Aud parameter did not match authorized server");
            }
        } else if (empty($audience) && empty($launch)) {
            $this->getSystemLogger()->debug("Aud parameter not provided (and non-launch scenario), so not validating aud (audience)");
        }

        // let's validate the launch param
        if (!empty($launch)) {
            if (!in_array($audience, $this->expectedAudience)) {
                $this->getSystemLogger()->errorLogCaller("Aud parameter did not match authorized server in launch scenario", ['audience' => $audience, 'expected' => $this->expectedAudience]);
                throw OAuthServerException::invalidRequest("aud", "Aud parameter did not match authorized server");
            }
            try {
                // check to see if we can deserialize the launch token
                SMARTLaunchToken::deserializeToken($launch);
            } catch (Exception $exception) {
                $this->getSystemLogger()->errorLogCaller("Failed to deserialize launch token", ['launch' => $launch, 'message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
                throw OAuthServerException::invalidRequest('launch', "launch parameter was incorrectly formatted or did not originate from this server");
            }
        }
        $this->validateCodeChallengeMethod($request);
        return parent::validateAuthorizationRequest($request);
    }

    protected function validateRedirectUri(
        string $redirectUri,
        ClientEntityInterface $client,
        ServerRequestInterface $request
    ) : void {
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

    protected function validateClient(ServerRequestInterface $request) : ClientEntityInterface
    {
        $this->getSystemLogger()->debug("CustomAuthCodeGrant::validateClient start");
        $client = parent::validateClient($request);
        if (!($client instanceof ClientEntity)) {
            $this->getSystemLogger()->errorLogCaller("client returned was not a valid ClientEntity ", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
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
    private function validateCodeChallengeMethod($request) : void
    {

        $codeChallenge = $this->getQueryStringParameter('code_challenge', $request);
        if ($codeChallenge !== null) {
            $codeChallengeMethod = $this->getQueryStringParameter('code_challenge_method', $request, 'plain');

            if (array_key_exists($codeChallengeMethod, $this->openEMRCodeChallengeVerifiers) === false) {
                throw OAuthServerException::invalidRequest(
                    'code_challenge_method',
                    'Code challenge method must be one of ' . implode(', ', array_map(
                        function ($method) {
                            return '`' . $method . '`';
                        },
                        array_keys($this->openEMRCodeChallengeVerifiers)
                    ))
                );
            }
        }
    }
}
