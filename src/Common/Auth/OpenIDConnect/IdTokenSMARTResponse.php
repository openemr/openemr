<?php

/**
 * Handles extra claims required for SMART on FHIR requests
 * @see http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context/index.html
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\PatientService;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Psr\Log\LoggerInterface;

class IdTokenSMARTResponse extends IdTokenResponse
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClaimExtractor $claimExtractor
    ) {
        $this->logger = SystemLogger::instance();
        parent::__construct($identityProvider, $claimExtractor);
    }

    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        $extraParams = parent::getExtraParams($accessToken);
        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() params from parent ", ["params" => $extraParams]);

        if ($this->isStandaloneLaunchPatientRequest($accessToken->getScopes())) {
            // patient id that is currently selected in the session.
            if (!empty($_SESSION['pid'])) {
                $extraParams['patient'] = $_SESSION['pid'];
            } else {
                throw new OAuthServerException("launch/patient scope requested but patient 'pid' was not present in session", 0, 'invalid_patient_context');
            }
        } else if ($this->isLaunchRequest($accessToken->getScopes())) {
            $this->logger->debug("launch scope requested");
            if (!empty($_SESSION['launch'])) {
                $this->logger->debug("IdTokenSMARTResponse->getExtraParams() launch set in session", ['launch' => $_SESSION['launch']]);
                // this is where the launch context is deserialized and we extract any SMART context state we wanted to
                // pass on as part of the EHR request, we only have encounter and patient at this point
                try {
                    // TODO: adunsulag do we want any kind of hmac signature to verify the request hasn't been
                    // tampered with?  Not sure that it matters as the ACL's will verify that the app only has access
                    // to the data the currently authorized oauth2 user can access.
                    $decoded = base64_decode($_SESSION['launch']);
                    $context = json_decode($decoded, true);
                    $this->logger->debug("IdTokenSMARTResponse->getExtraParams() decoded launch context is", ['context' => $context]);
                    if (!empty($context['p'])) {
                        $extraParams['patient'] = $context['p'];
                    }
                    if (!empty($context['e'])) {
                        $extraParams['encounter'] = $context['e'];
                    }
                } catch (\Exception $ex) {
                    $this->logger->error("IdTokenSMARTResponse->getExtraParams() Failed to decode launch context parameter", ['error' => $ex->getMessage()]);
                    throw new OAuthServerException("Invalid launch parameter", 0, 'invalid_launch_context');
                }
            }
        }

        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() final params", ["params" => $extraParams]);
        return $extraParams;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    private function isLaunchRequest($scopes)
    {
        return $this->hasScope($scopes, 'launch');
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    private function isStandaloneLaunchPatientRequest($scopes)
    {
        return $this->hasScope($scopes, 'launch/patient');
    }

    private function hasScope($scopes, $searchScope)
    {
        // Verify scope and make sure openid exists.
        $valid  = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() == $searchScope) {
                $valid = true;
                break;
            }
        }

        return $valid;
    }
}
