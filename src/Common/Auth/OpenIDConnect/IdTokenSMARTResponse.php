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

use Lcobucci\JWT\Builder;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
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
                $extraParams['need_patient_banner'] = true;
                $extraParams['smart_style_url'] = $this->getSmartStyleURL();
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
                    $launchToken = SMARTLaunchToken::deserializeToken($_SESSION['launch']);
                    $this->logger->debug("IdTokenSMARTResponse->getExtraParams() decoded launch context is", ['context' => $launchToken]);

                    // we assume that if a patient is provided we are already displaying the patient
                    // we may in the future need to adjust the need_patient_banner depending on the 'intent' chosen.
                    if (!empty($launchToken->getPatient())) {
                        $extraParams['patient'] = $launchToken->getPatient();
                        $extraParams['need_patient_banner'] = false;
                    }
                    if (!empty($launchToken->getEncounter())) {
                        $extraParams['encounter'] = $launchToken->getEncounter();
                    }
                    if (!empty($launchToken->getIntent())) {
                        $extraParams['intent'] = $launchToken->getIntent();
                    }
                    $extraParams['smart_style_url'] = $this->getSmartStyleURL();
                } catch (\Exception $ex) {
                    $this->logger->error("IdTokenSMARTResponse->getExtraParams() Failed to decode launch context parameter", ['error' => $ex->getMessage()]);
                    throw new OAuthServerException("Invalid launch parameter", 0, 'invalid_launch_context');
                }
            }
        }

        // response should return the scopes we authorized inside the accessToken to be smart compatible
        // I would think this would be better put in the id_token but to be spec compliant we have to have this here
        $extraParams['scope'] = $this->getScopeString($accessToken->getScopes());

        $this->logger->debug("IdTokenSMARTResponse->getExtraParams() final params", ["params" => $extraParams]);
        return $extraParams;
    }

    /**
     * Needed for OpenEMR\FHIR\SMART\Capability::CONTEXT_STYLE support
     * TODO: adunsulag do we want to try and read from the scss files and generate some kind of styles...
     * Reading the SMART FHIR spec author forums so few app writers are actually using this at all, it seems like we
     * can just use defaults without getting trying to load up based upon which skin we have, or using node &
     * gulp to auto generate a skin.
     */
    private function getSmartStyleURL()
    {
        return $GLOBALS['site_addr_oath'] . "/public/smart-styles/smart-light.json";
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

    private function getScopeString($scopes)
    {
        $scopeList = [];
        foreach ($scopes as $scope) {
            $scopeId = $scope->getIdentifier();
            // don't include scopes like site:default
            // they still get bundled into the AccessToken but for ONC certification
            // it won't allow custom scope permissions even though this is valid per Open ID Connect spec
            // so we will just skip listing in the 'scopes' response that is sent back to
            // the client.
            if (strpos($scopeId, ':') === false) {
                $scopeList[] = $scopeId;
            }
        }
        return implode(' ', $scopeList);
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity): Builder
    {
        // Add required id_token claims
        return (new Builder())
            ->permittedFor($accessToken->getClient()->getIdentifier())
            ->issuedBy($GLOBALS['site_addr_oath'] . $GLOBALS['webroot'] . "/oauth2/" . $_SESSION['site_id'])
            ->issuedAt(new \DateTimeImmutable('@' . time()))
            ->expiresAt(new \DateTimeImmutable('@' . $accessToken->getExpiryDateTime()->getTimestamp()))
            ->relatedTo($userEntity->getIdentifier());
    }
}
