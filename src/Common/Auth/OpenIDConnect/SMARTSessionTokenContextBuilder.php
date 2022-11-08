<?php

/**
 * Handles the building and aggregation of SMART on FHIR session context information that are returned in the Access
 * Token request response body.  These include context information such as selected patient, launch, encounter, etc.
 *
 * @see http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context/index.html
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2022 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;

class SMARTSessionTokenContextBuilder
{
    private $sessionArray;
    public function __construct($sessionArray = array())
    {
        $this->sessionArray = !empty($sessionArray) ? $sessionArray : $_SESSION ?? [];
        $this->logger = new SystemLogger();
    }

    public function getEHRLaunchContext()
    {
        $launch = $this->sessionArray['launch'] ?? null;
        if (empty($launch)) {
            return [];
        }
        $context = [];
        try {
            // TODO: adunsulag do we want any kind of hmac signature to verify the request hasn't been
            // tampered with?  Not sure that it matters as the ACL's will verify that the app only has access
            // to the data the currently authorized oauth2 user can access.
            $launchToken = SMARTLaunchToken::deserializeToken($launch);
            $this->logger->debug(
                "SMARTSessionTokenContextBuilder->getEHRLaunchContext() decoded launch context is",
                ['patient' => $launchToken->getPatient(), 'encounter' => $launchToken->getEncounter(), 'intent' => $launchToken->getIntent()]
            );

            // we assume that if a patient is provided we are already displaying the patient
            // we may in the future need to adjust the need_patient_banner depending on the 'intent' chosen.
            if (!empty($launchToken->getPatient())) {
                $context['patient'] = $launchToken->getPatient();
                $context['need_patient_banner'] = false;
            }
            if (!empty($launchToken->getEncounter())) {
                $context['encounter'] = $launchToken->getEncounter();
            }
            if (!empty($launchToken->getIntent())) {
                $context['intent'] = $launchToken->getIntent();
            }
            $context['smart_style_url'] = $this->getSmartStyleURL();
        } catch (\Exception $ex) {
            $this->logger->error("SMARTSessionTokenContextBuilder->getAccessTokenContextParameters() Failed to decode launch context parameter", ['error' => $ex->getMessage()]);
            throw new OAuthServerException("Invalid launch parameter", 0, 'invalid_launch_context');
        }
        $this->logger->debug("SMARTSessionTokenContextBuilder->getEHRLaunchContext() ehr launch context is ", $context);
        return $context;
    }

    public function getContextForScopesWithExistingContext(array $context, array $scopes): array
    {
        $returnContext = [];
        $populateKeys = ['patient'];
        if ($this->isStandaloneLaunchPatientRequest($scopes)) {
            $returnContext = [
                'need_patient_banner' => true
                ,'smart_style_url' => $this->getSmartStyleURL()
            ];
        } else if ($this->isEHRLaunchRequest($scopes)) {
            $returnContext = [
                'need_patient_banner' => false
                ,'smart_style_url' => $this->getSmartStyleURL()
            ];
            $populateKeys[] = 'encounter';
            $populateKeys[] = 'intent';
        }

        // populate any values we have from our orig context into our return array
        foreach ($populateKeys as $item) {
            if (isset($context[$item])) {
                $returnContext[$item] = $context[$item];
            }
        }
        return $returnContext;
    }

    public function getContextForScopes($scopes): array
    {
        $context = [];
        if ($this->isStandaloneLaunchPatientRequest($scopes)) {
            $context = $this->getStandaloneLaunchContext();
        } else if ($this->isEHRLaunchRequest($scopes)) {
            $context = $this->getEHRLaunchContext();
        }
        return $context;
    }

    public function getStandaloneLaunchContext()
    {
        $context = [];
        if (empty($this->sessionArray['puuid'])) {
            return $context;
        }
        $context['patient'] = $this->sessionArray['puuid'];
        $context['need_patient_banner'] = true;
        $context['smart_style_url'] = $this->getSmartStyleURL();
        return $context;
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
        return $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . "/public/smart-styles/smart-light.json";
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    public function isStandaloneLaunchPatientRequest($scopes)
    {
        return $this->hasScope($scopes, SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE);
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return bool
     */
    public function isEHRLaunchRequest($scopes)
    {
        return $this->hasScope($scopes, SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE);
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
