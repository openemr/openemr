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

        if ($this->isLaunchPatientRequest($accessToken->getScopes())) {
            // for testing purposes we are going to return just the first patient we find for our launch context...
            // what we need to do is have a patient selector and return the selected patient as part of the OAUTH
            // sequence.
            $patientService = new PatientService();
            // patient id that is currently selected in the session.
            if (!empty($_SESSION['pid'])) {
                $extraParams['patient'] = $_SESSION['pid'];
            } else {
                throw new OAuthServerException("launch/patient scope requested but patient 'pid' was not present in session", 0, 'invalid_patient_context');
            }
        }

        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() final params", ["params" => $extraParams]);
        return $extraParams;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    private function isLaunchPatientRequest($scopes)
    {
        // Verify scope and make sure openid exists.
        $valid  = false;

        foreach ($scopes as $scope) {
            if ($scope->getIdentifier() === 'launch/patient') {
                $valid = true;
                break;
            }
        }

        return $valid;
    }
}
